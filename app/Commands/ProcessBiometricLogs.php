<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BiometricLogModel;
use App\Models\StudentModel;
use App\Models\TeacherModel;
use App\Models\AttendanceModel;
use App\Models\ShiftModel;
use App\Models\TeacherScheduleModel;
use App\Models\WhatsAppNotificationModel;
use App\Models\AndroidNotificationModel;
use App\Models\StudentDeviceTokenModel;
use App\Models\TelegramNotificationModel;
use App\Models\NotificationTemplateModel;
use App\Helpers\TelegramHelper;
use App\Helpers\NotificationTemplateHelper;

class ProcessBiometricLogs extends BaseCommand
{
    protected $group = 'Attendance';
    protected $name = 'attendance:process-biometric';
    protected $description = 'Process unprocessed biometric logs and update attendance records';
    protected $usage = 'php spark attendance:process-biometric [options]';
    protected $arguments = [];
    protected $options = [
        '--limit' => 'Limit records to process (default: 100)',
        '--dry-run' => 'Show what would be processed without actually saving'
    ];

    public function run(array $params = [])
    {
        $limit = (int)($params['limit'] ?? 100);
        $dryRun = isset($params['dry-run']);

        CLI::write('=== Processing Biometric Logs ===', 'green');
        CLI::write("Limit: {$limit}");
        CLI::write("Dry Run: " . ($dryRun ? 'Yes' : 'No'));
        CLI::newLine();

        $biometricLogModel = new BiometricLogModel();
        $studentModel = new StudentModel();
        $teacherModel = new TeacherModel();
        $attendanceModel = new AttendanceModel();
        $shiftModel = new ShiftModel();
        $teacherScheduleModel = new TeacherScheduleModel();
        $waModel = new WhatsAppNotificationModel();
        $androidModel = new AndroidNotificationModel();
        $tokenModel = new StudentDeviceTokenModel();
        $telegramModel = new TelegramNotificationModel();
        $templateModel = new NotificationTemplateModel();
        $settingsModel = new \App\Models\SettingsModel();

        // Get unprocessed logs
        $logs = $biometricLogModel->getUnprocessed($limit);

        if (empty($logs)) {
            CLI::write('No unprocessed logs found', 'yellow');
            return;
        }

        CLI::write("Found " . count($logs) . " unprocessed logs", 'cyan');
        CLI::newLine();

        $processed = 0;
        $failed = 0;

        foreach ($logs as $log) {
            try {
                CLI::write("Processing: {$log['user_id']} ({$log['user_type']}) at {$log['timestamp']}", 'white');

                // Parse timestamp
                $logDateTime = new \DateTime($log['timestamp']);
                $logDate = $logDateTime->format('Y-m-d');
                $logTime = $logDateTime->format('H:i:s');
                $dayOfWeek = (int) $logDateTime->format('N'); // 1-7 (Monday-Sunday)

                // Determine user type and get user data
                $user = null;
                $userType = $log['user_type']; // Don't default yet, check finding
                $userIdInLog = $log['user_id'];

                // 1. Try to find as Student (NIS)
                $student = $studentModel->select('students.*, walikelas.wa_group_id')
                    ->join('walikelas', 'walikelas.id = students.wali_kelas_id', 'left')
                    ->where('nis', $userIdInLog)
                    ->first();

                // 2. Try to find as Teacher (NIP)
                $teacher = $teacherModel->where('nip', $userIdInLog)->first();

                // 3. Determine who it really is
                if ($student) {
                    $user = $student;
                    $userType = 'siswa';
                } elseif ($teacher) {
                    $user = $teacher;
                    $userType = 'guru'; // Auto-correct if log said 'siswa'
                } else {
                    // Try to find by direct User ID if NIP/NIS failed (Fallback logic, mostly for testing or older systems)
                    // Strict mode requested: "tetap saja untuk siswa pakai nis dan guru pakai nip"
                    // So we will NOT fallback to internal ID lookup unless explicitly needed.
                    // We just log warning.

                    // Double check if maybe the log user_type was correct but ID was wrong?
                    // No, usually ID is consistent from machine.

                    throw new \Exception("User not found (NIS/NIP: {$userIdInLog})");
                }

                if (empty($user['user_id'])) {
                    throw new \Exception("User found but not linked to system account (user_id null)");
                }

                // Validate schedule/shift
                if ($userType === 'siswa') {
                    // Get shift (required for time validation)
                    $schedule = !empty($user['shift_id']) ? $shiftModel->find($user['shift_id']) : null;
                    if (!$schedule) {
                        throw new \Exception('Student has no shift assigned');
                    }
                    // Validate: scan must be within shift hours (start_time to end_time)
                    if ($logTime < $schedule['start_time'] || $logTime > $schedule['end_time']) {
                        throw new \Exception('Scan outside shift hours (' . $schedule['start_time'] . ' - ' . $schedule['end_time'] . ')');
                    }
                } else {
                    // Get teacher schedule for this day
                    $schedule = $teacherScheduleModel
                        ->where('user_id', $user['user_id'])
                        ->where('hari', $dayOfWeek)
                        ->where('status', 'aktif')
                        ->first();
                    if (!$schedule) {
                        throw new \Exception('Teacher has no schedule for ' . $this->getDayName($dayOfWeek));
                    }
                    // Validate: scan must be within schedule hours (jam_masuk to jam_pulang)
                    if ($logTime < $schedule['jam_masuk'] || $logTime > $schedule['jam_pulang']) {
                        throw new \Exception('Scan outside schedule hours (' . $schedule['jam_masuk'] . ' - ' . $schedule['jam_pulang'] . ')');
                    }
                }

                // Check if attendance record exists for this date

                // === ACTIVITY ATTENDANCE INTERCEPT ===
                $activityService = new \App\Services\ActivityAttendanceService();
                $activityResult = $activityService->processScan($user['user_id'], $log['timestamp'], 'fingerprint');

                if ($activityResult['processed']) {
                    if ($activityResult['should_stop_regular']) {
                        CLI::write("  ✓ Activity Logged: " . $activityResult['message'], 'cyan');
                        // Mark as processed for activity (we can pass null for attendance_id since it's activity table)
                        if (!$dryRun) {
                            $biometricLogModel->markProcessed($log['id'], null);
                        }
                        $processed++;
                        continue; // Skip regular attendance log
                    } else {
                        CLI::write("  ✓ Activity Logged (Dual): " . $activityResult['message'], 'cyan');
                        // Continue to regular attendance logic
                    }
                }
                // =====================================

                $existing = $attendanceModel
                    ->where('user_id', $user['user_id'])
                    ->where('date', $logDate)
                    ->first();

                // Decide masuk vs pulang based on masuk_at status:
                // - if masuk_at is empty -> this is MASUK
                // - if masuk_at is filled but pulang_at is empty -> this is PULANG
                // - if both filled -> error (duplicate scan)
                $isMasuk = empty($existing['masuk_at']);
                $attendanceId = null;

                if ($isMasuk) {
                    // MASUK (CHECK-IN)
                    if ($existing && !empty($existing['masuk_at'])) {
                        throw new \Exception($userType === 'siswa' ? 'Student already checked in today' : 'Teacher already checked in today');
                    }

                    // Determine masuk status based on schedule/shift
                    if ($userType === 'siswa') {
                        $masukStatus = $this->determineMasukStatus($user, $shiftModel, $logTime);
                    } else {
                        $masukStatus = $this->determineTeacherMasukStatus($user, $schedule, $logTime);
                    }

                    // Create or update attendance record
                    if ($existing) {
                        $attendanceModel->update($existing['id'], [
                            'masuk_at' => $log['timestamp'],
                            'masuk_status' => $masukStatus,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        $attendanceId = $existing['id'];
                    } else {
                        $attendanceId = $attendanceModel->insert([
                            'user_id' => $user['user_id'],
                            'date' => $logDate,
                            'masuk_at' => $log['timestamp'],
                            'masuk_status' => $masukStatus,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }

                    // === NOTIFICATIONS ===
                    if ($userType === 'siswa') {
                        // SISWA: Telegram + WhatsApp (student) + WhatsApp (guardian) + Android
                        $data = [
                            'name' => $user['full_name'],
                            'time' => $logTime,
                            'status_label' => NotificationTemplateHelper::getStatusLabel($masukStatus, 'text'),
                            'date' => date('d/m/Y', strtotime($logDate)),
                        ];

                        // Telegram to student
                        if ($user['telegram_chat_id']) {
                            $template = $templateModel->getTemplate('tele_student_checkin');
                            if ($template) {
                                $message = NotificationTemplateHelper::replaceVariables($template['content'], $data);
                                $telegramModel->insert([
                                    'student_id' => $user['id'],
                                    'chat_id' => $user['telegram_chat_id'],
                                    'message' => $message,
                                    'payload' => json_encode(['attendance_id' => $attendanceId, 'event' => 'biometric_masuk', 'status' => $masukStatus]),
                                    'status' => 'pending',
                                    'attempts' => 0,
                                    'scheduled_at' => date('Y-m-d H:i:s'),
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            } else {
                                // Fallback
                                TelegramHelper::sendAttendanceNotification(
                                    $user['id'],
                                    $user['telegram_chat_id'],
                                    $user['full_name'],
                                    'masuk',
                                    $masukStatus,
                                    $logTime
                                );
                            }
                        }

                        $target = $settingsModel->getSetting('wa_notification_target') ?? 'guardian'; // guardian, group, both

                        // WhatsApp to guardian
                        if (($target === 'guardian' || $target === 'both') && !empty($user['guardian_phone'])) {
                            $template = $templateModel->getTemplate('wa_guardian_checkin');
                            $data['status_label'] = NotificationTemplateHelper::getStatusLabel($masukStatus, 'text') . ($masukStatus == 'on_time' ? ' ✓' : ' ⚠');

                            $message = $template
                                ? NotificationTemplateHelper::replaceVariables($template['content'], $data)
                                : "📱 Absensi Masuk Siswa\nNama: {$user['full_name']}\nJam: {$logTime}\nStatus: " . ($masukStatus === 'on_time' ? 'Tepat Waktu ✓' : 'Terlambat ⚠');

                            $waModel->insert([
                                'student_id' => $user['id'],
                                'phone_number' => $user['guardian_phone'],
                                'message' => $message,
                                'payload' => json_encode(['type' => 'masuk', 'status' => $masukStatus, 'time' => $logTime, 'date' => $logDate, 'recipient' => 'guardian', 'recipient_type' => 'individual']),
                                'status' => 'pending',
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }

                        // WhatsApp to Group
                        if (($target === 'group' || $target === 'both') && !empty($user['wa_group_id'])) {
                            $template = $templateModel->getTemplate('wa_checkin_group');
                            // Use pure text label for group
                            $data['status_label'] = NotificationTemplateHelper::getStatusLabel($masukStatus, 'text');

                            $message = $template
                                ? NotificationTemplateHelper::replaceVariables($template['content'], $data)
                                : "✅ Absensi Masuk (Group)\n\nSiswa: *{$user['full_name']}*\nWaktu: {$logTime}\nStatus: " . ($masukStatus === 'on_time' ? 'Tepat Waktu' : 'Terlambat');

                            $waModel->insert([
                                'student_id' => $user['id'],
                                'phone_number' => $user['wa_group_id'],
                                'message' => $message,
                                'payload' => json_encode(['type' => 'masuk', 'status' => $masukStatus, 'time' => $logTime, 'date' => $logDate, 'recipient' => 'group', 'recipient_type' => 'group']),
                                'status' => 'pending',
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }

                        // Android push to student
                        $npsn = getenv('SCHOOL_NPSN');
                        $deviceTokens = $tokenModel->getActiveTokensByStudent($user['id'], $npsn);
                        if (!empty($deviceTokens)) {
                            // Reset label for android
                            $data['status_label'] = NotificationTemplateHelper::getStatusLabel($masukStatus, 'text');

                            $templateTitle = $templateModel->getTemplate('android_student_checkin_title');
                            $templateBody = $templateModel->getTemplate('android_student_checkin_body');

                            $androidTitle = $templateTitle ? NotificationTemplateHelper::replaceVariables($templateTitle['content'], $data) : 'Absensi Masuk';
                            $androidMessage = $templateBody ? NotificationTemplateHelper::replaceVariables($templateBody['content'], $data) : "Nama: {$user['full_name']}\nJam: {$logTime}\nStatus: {$data['status_label']}";

                            $payload = [
                                'nis' => $user['nis'],
                                'npsn' => $npsn,
                                'title' => $androidTitle,
                                'message' => $androidMessage,
                                'data' => [
                                    'type' => 'masuk',
                                    'status' => $masukStatus,
                                    'tanggal' => date('Y-m-d'),
                                    'jam' => $logTime,
                                    'school' => getenv('SCHOOL_NAME') ?: 'A'
                                ]
                            ];

                            foreach ($deviceTokens as $token) {
                                $androidModel->insert([
                                    'student_id' => $user['id'],
                                    'nis' => $user['nis'],
                                    'npsn' => $npsn,
                                    'device_token' => $token['device_token'],
                                    'title' => $androidTitle,
                                    'message' => $androidMessage,
                                    'payload' => json_encode($payload),
                                    'notification_status' => 'pending',
                                    'attempts' => 0,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }
                    } else {
                        // GURU: Telegram + WhatsApp saja
                        $data = [
                            'name' => $user['full_name'],
                            'time' => $logTime,
                            'status_label' => NotificationTemplateHelper::getStatusLabel($masukStatus, 'text'),
                            'date' => date('d/m/Y', strtotime($logDate)),
                        ];

                        // Telegram to guru
                        if ($user['telegram_chat_id']) {
                            $template = $templateModel->getTemplate('tele_teacher_checkin');

                            $message = $template
                                ? NotificationTemplateHelper::replaceVariables($template['content'], $data)
                                : sprintf("✓ %s masuk pada %s\nTanggal: %s\nStatus: %s", $user['full_name'], date('H:i', strtotime($log['timestamp'])), $logDate, $data['status_label']);

                            $telegramModel->insert([
                                'teacher_id' => $user['id'],
                                'chat_id' => $user['telegram_chat_id'],
                                'message' => $message,
                                'payload' => json_encode(['attendance_id' => $attendanceId, 'event' => 'biometric_masuk', 'status' => $masukStatus]),
                                'status' => 'pending',
                                'attempts' => 0,
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }

                        // WhatsApp to guru
                        // Currently no template setup for teacher whatsapp in default set, but we can reuse or just use default
                        if (!empty($user['phone_number'])) {
                            $statusLabel = $masukStatus === 'on_time' ? 'Tepat Waktu ✓' : 'Terlambat ⚠';
                            $message = "📱 Absensi Masuk\nNama: {$user['full_name']}\nJam: {$logTime}\nStatus: {$statusLabel}";

                            $waModel->insert([
                                'student_id' => null,
                                'phone_number' => $user['phone_number'],
                                'message' => $message,
                                'payload' => json_encode(['type' => 'guru_masuk', 'status' => $masukStatus, 'time' => $logTime, 'date' => $logDate]),
                                'status' => 'pending',
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }

                    $statusMsg = $masukStatus === 'on_time' ? 'Tepat Waktu' : 'Terlambat';
                    CLI::write("  ✓ Check-in: {$statusMsg}", 'green');
                } else {
                    // PULANG (CHECK-OUT)
                    if (!$existing) {
                        throw new \Exception($userType === 'siswa' ? 'Student has not checked in today' : 'Teacher has not checked in today');
                    }

                    if (!empty($existing['pulang_at'])) {
                        throw new \Exception($userType === 'siswa' ? 'Student already checked out today' : 'Teacher already checked out today');
                    }

                    // Check if within schedule hours
                    if (!empty($schedule['start_time'])) {
                        $startTime = $userType === 'siswa' ? $schedule['start_time'] : $schedule['jam_masuk'];
                        $endTime = $userType === 'siswa' ? $schedule['end_time'] : $schedule['jam_pulang'];
                        if ($logTime < $startTime || $logTime > $endTime) {
                            throw new \Exception('Checkout outside schedule hours');
                        }
                    }

                    // Debounce protection: Check time difference between masuk_at and now
                    // Must be at least 10 minutes (600 seconds) to be considered a valid pulang
                    // This prevents double-tapping from recording as Masuk -> Pulang immediately
                    if (!empty($existing['masuk_at'])) {
                        $masukTime = strtotime($existing['masuk_at']);
                        $pulangTime = strtotime($log['timestamp']);
                        $diff = $pulangTime - $masukTime;

                        // Configurable minimum minutes (default 10)
                        $minMinutes = 10;
                        if ($diff < ($minMinutes * 60)) {
                            throw new \Exception("Scan too soon after check-in (Debounce: < {$minMinutes} mins). Ignored.");
                        }
                    }

                    // Determine pulang status
                    if ($userType === 'siswa') {
                        $pulangStatus = $this->determinePulangStatus($user, $shiftModel, $logTime);
                    } else {
                        $pulangStatus = $this->determineTeacherPulangStatus($user, $schedule, $logTime);
                    }

                    // Update attendance record with pulang
                    $attendanceModel->update($existing['id'], [
                        'pulang_at' => $log['timestamp'],
                        'pulang_status' => $pulangStatus,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    // === NOTIFICATIONS ===
                    if ($userType === 'siswa') {
                        // SISWA: Telegram + WhatsApp (student) + WhatsApp (guardian) + Android
                        $data = [
                            'name' => $user['full_name'],
                            'time' => $logTime,
                            'status_label' => NotificationTemplateHelper::getStatusLabel($pulangStatus, 'text'),
                            'date' => date('d/m/Y', strtotime($logDate)),
                        ];

                        // Telegram to student
                        if ($user['telegram_chat_id']) {
                            $template = $templateModel->getTemplate('tele_student_checkout');
                            if ($template) {
                                $message = NotificationTemplateHelper::replaceVariables($template['content'], $data);
                                $telegramModel->insert([
                                    'student_id' => $user['id'],
                                    'chat_id' => $user['telegram_chat_id'],
                                    'message' => $message,
                                    'payload' => json_encode(['attendance_id' => $attendanceId, 'event' => 'biometric_pulang', 'status' => $pulangStatus]),
                                    'status' => 'pending',
                                    'attempts' => 0,
                                    'scheduled_at' => date('Y-m-d H:i:s'),
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            } else {
                                TelegramHelper::sendAttendanceNotification(
                                    $user['id'],
                                    $user['telegram_chat_id'],
                                    $user['full_name'],
                                    'pulang',
                                    $pulangStatus,
                                    $logTime
                                );
                            }
                        }

                        $target = $settingsModel->getSetting('wa_notification_target') ?? 'guardian';

                        // WhatsApp to guardian
                        if (($target === 'guardian' || $target === 'both') && !empty($user['guardian_phone'])) {
                            $template = $templateModel->getTemplate('wa_guardian_checkout');
                            $data['status_label'] = NotificationTemplateHelper::getStatusLabel($pulangStatus, 'text') . ($pulangStatus == 'on_time' ? ' ✓' : ' ⚠');

                            $message = $template
                                ? NotificationTemplateHelper::replaceVariables($template['content'], $data)
                                : "👋 Absensi Pulang Siswa\nNama: {$user['full_name']}\nJam: {$logTime}\nStatus: " . ($pulangStatus === 'on_time' ? 'Tepat Waktu ✓' : 'Lebih Awal ⚠');

                            $waModel->insert([
                                'student_id' => $user['id'],
                                'phone_number' => $user['guardian_phone'],
                                'message' => $message,
                                'payload' => json_encode(['type' => 'pulang', 'status' => $pulangStatus, 'time' => $logTime, 'date' => $logDate, 'recipient' => 'guardian', 'recipient_type' => 'individual']),
                                'status' => 'pending',
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }

                        // WhatsApp to Group
                        if (($target === 'group' || $target === 'both') && !empty($user['wa_group_id'])) {
                            $template = $templateModel->getTemplate('wa_checkout_group');
                            $data['status_label'] = NotificationTemplateHelper::getStatusLabel($pulangStatus, 'text');

                            $message = $template
                                ? NotificationTemplateHelper::replaceVariables($template['content'], $data)
                                : "👋 Absensi Pulang (Group)\n\nSiswa: *{$user['full_name']}*\nWaktu: {$logTime}\nStatus: " . ($pulangStatus === 'on_time' ? 'Tepat Waktu' : 'Lebih Awal');

                            $waModel->insert([
                                'student_id' => $user['id'],
                                'phone_number' => $user['wa_group_id'],
                                'message' => $message,
                                'payload' => json_encode(['type' => 'pulang', 'status' => $pulangStatus, 'time' => $logTime, 'date' => $logDate, 'recipient' => 'group', 'recipient_type' => 'group']),
                                'status' => 'pending',
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }


                        // Android push to student
                        $npsn = getenv('SCHOOL_NPSN');
                        $deviceTokens = $tokenModel->getActiveTokensByStudent($user['id'], $npsn);
                        if (!empty($deviceTokens)) {
                            // Reset label for android
                            $data['status_label'] = NotificationTemplateHelper::getStatusLabel($pulangStatus, 'text');

                            $templateTitle = $templateModel->getTemplate('android_student_checkout_title');
                            $templateBody = $templateModel->getTemplate('android_student_checkout_body');

                            $androidTitle = $templateTitle ? NotificationTemplateHelper::replaceVariables($templateTitle['content'], $data) : 'Absensi Pulang';
                            $androidMessage = $templateBody ? NotificationTemplateHelper::replaceVariables($templateBody['content'], $data) : "Nama: {$user['full_name']}\nJam: {$logTime}\nStatus: {$data['status_label']}";

                            $payload = [
                                'nis' => $user['nis'],
                                'npsn' => $npsn,
                                'title' => $androidTitle,
                                'message' => $androidMessage,
                                'data' => [
                                    'type' => 'pulang',
                                    'status' => $pulangStatus,
                                    'tanggal' => date('Y-m-d'),
                                    'jam' => $logTime,
                                    'school' => getenv('SCHOOL_NAME') ?: 'A'
                                ]
                            ];

                            foreach ($deviceTokens as $token) {
                                $androidModel->insert([
                                    'student_id' => $user['id'],
                                    'nis' => $user['nis'],
                                    'npsn' => $npsn,
                                    'device_token' => $token['device_token'],
                                    'title' => $androidTitle,
                                    'message' => $androidMessage,
                                    'payload' => json_encode($payload),
                                    'notification_status' => 'pending',
                                    'attempts' => 0,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }
                    } else if ($userType === 'guru') {
                        // === TEACHER NOTIFICATION (Checkout) ===
                        if (!empty($user['phone_number'])) {
                            $template = $templateModel->getTemplate('wa_teacher_checkout');
                            $data = [
                                'name' => $user['full_name'],
                                'time' => $logTime,
                                'date' => date('d/m/Y', strtotime($logDate)),
                                'status' => 'Pulang'
                            ];

                            $message = $template
                                ? NotificationTemplateHelper::replaceVariables($template['content'], $data)
                                : "🔔 *Presensi Pulang*\n\nHalo {$user['full_name']},\nAbsensi PULANG Anda telah tercatat.\n\n📅 Tanggal: {$data['date']}\n⏰ Waktu: {$logTime}\n\nTerima kasih atas kerja kerasnya! 🏠";

                            $waModel->insert([
                                'phone_number' => $user['phone_number'],
                                'message' => $message,
                                'status' => 'pending',
                                'recipient_type' => 'individual',
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }

                    $statusMsg = $pulangStatus === 'on_time' ? 'Tepat Waktu' : 'Lebih Awal';
                    CLI::write("  ✓ Check-out: {$statusMsg}", 'green');
                    $attendanceId = $existing['id'];
                }

                // Mark log as processed
                if (!$dryRun) {
                    $biometricLogModel->markProcessed($log['id'], $attendanceId);
                }

                $processed++;
            } catch (\Exception $e) {
                $failed++;
                $error = $e->getMessage();
                CLI::write("  ✗ Error: {$error}", 'red');

                // Mark as processed with error
                if (!$dryRun) {
                    $biometricLogModel->markProcessed($log['id'], null, $error);
                }
            }

            CLI::newLine();
        }

        // Summary
        CLI::newLine();
        CLI::write('=== Summary ===', 'green');
        CLI::write("Processed: {$processed}", 'cyan');
        CLI::write("Failed: {$failed}", $failed > 0 ? 'red' : 'cyan');
        CLI::write("Total: " . ($processed + $failed), 'cyan');
    }

    /**
     * Determine masuk (check-in) status based on shift
     * - on_time: jika scan <= checkin_deadline
     * - late: jika scan > checkin_deadline
     */
    private function determineMasukStatus($student, $shiftModel, $logTime)
    {
        if (!empty($student['shift_id'])) {
            $shift = $shiftModel->find($student['shift_id']);
            if ($shift && !empty($shift['checkin_deadline'])) {
                return $logTime <= $shift['checkin_deadline'] ? 'on_time' : 'late';
            }
        }

        // Default deadline if no shift or deadline not set
        return $logTime <= '07:30:00' ? 'on_time' : 'late';
    }

    /**
     * Determine masuk (check-in) status based on teacher schedule
     * - on_time: jika scan <= jam_masuk + grace period (15 menit)
     * - late: jika scan > grace period
     */
    private function determineTeacherMasukStatus($teacher, $schedule, $logTime)
    {
        if ($schedule && !empty($schedule['jam_masuk'])) {
            $jamMasuk = strtotime($schedule['jam_masuk']);
            $graceEnd = $jamMasuk + (15 * 60); // 15 menit grace
            $logTimeTs = strtotime($logTime);
            return $logTimeTs <= $graceEnd ? 'on_time' : 'late';
        }

        // Default: 07:30 deadline if no schedule set
        return $logTime <= '07:30:00' ? 'on_time' : 'late';
    }

    /**
     * Determine pulang (check-out) status based on teacher schedule
     * - on_time: jika scan >= jam_pulang
     * - early: jika scan < jam_pulang
     */
    private function determineTeacherPulangStatus($teacher, $schedule, $logTime)
    {
        if ($schedule && !empty($schedule['jam_pulang'])) {
            return $logTime >= $schedule['jam_pulang'] ? 'on_time' : 'early';
        }

        // Default: 15:00 if no schedule set
        return $logTime >= '15:00:00' ? 'on_time' : 'early';
    }

    /**
     * Get day name from day number (1-7)
     */
    private function getDayName($dayNumber)
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];
        return $days[$dayNumber] ?? 'Unknown';
    }

    /**
     * Determine pulang (check-out) status based on shift
     * - on_time: jika scan >= checkout_earliest
     * - early: jika scan < checkout_earliest
     * - default: unknown
     */
    private function determinePulangStatus($student, $shiftModel, $logTime)
    {
        if (!empty($student['shift_id'])) {
            $shift = $shiftModel->find($student['shift_id']);
            if ($shift && !empty($shift['checkout_earliest'])) {
                return $logTime >= $shift['checkout_earliest'] ? 'on_time' : 'early';
            }
        }

        // Default checkout time if no shift or checkout_earliest not set
        return $logTime >= '15:00:00' ? 'on_time' : 'early';
    }
}
