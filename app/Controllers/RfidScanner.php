<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\WhatsAppNotificationModel;
use App\Models\TelegramNotificationModel;
use App\Models\AndroidNotificationModel;
use App\Models\StudentDeviceTokenModel;

class RfidScanner extends Controller
{
    /**
     * Display RFID Scanner page
     */
    public function index()
    {
        return view('rfid/scanner');
    }

    /**
     * Process RFID scan from USB reader
     * POST /api/rfid-scan
     */
    public function scan()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)
                ->setJSON(['success' => false, 'message' => 'Only POST method allowed']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $rfidId = isset($data['rfid_id']) ? trim($data['rfid_id']) : null;
        $mode = isset($data['mode']) ? trim($data['mode']) : 'masuk';

        if (empty($rfidId)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['success' => false, 'message' => 'RFID ID is required']);
        }
        try {
            $studentModel = model('StudentModel');
            $teacherModel = model('TeacherModel');
            $biometricLogModel = model('BiometricLogModel');
            $attModel = model('AttendanceModel');

            // Find user by RFID
            $student = $studentModel->select('students.*, walikelas.wa_group_id')
                ->join('walikelas', 'walikelas.id = students.wali_kelas_id', 'left')
                ->where('students.rfid_id', $rfidId)
                ->first();
            $teacher = null;
            $userType = null;
            $identifier = null;
            $userData = null;
            $userId = null;

            if ($student) {
                $userType = 'siswa';
                $identifier = $student['nis'];
                $userId = $student['user_id'];
                $userData = [
                    'id' => $student['id'],
                    'name' => $student['full_name'],
                    'nis' => $student['nis'],
                    'class' => $student['class'],
                ];
            } else {
                $teacher = $teacherModel->where('rfid_id', $rfidId)->first();
                if ($teacher) {
                    $userType = 'guru';
                    $identifier = $teacher['nip'];
                    $userId = $teacher['user_id'];
                    $userData = [
                        'id' => $teacher['id'],
                        'name' => $teacher['full_name'],
                        'nip' => $teacher['nip'],
                        'subject' => $teacher['subject'],
                    ];
                }
            }

            if (!$student && !$teacher) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                    ->setJSON([
                        'success' => false,
                        'message' => 'RFID tidak terdaftar',
                        'data' => null,
                        'lcd_display' => [
                            'line1' => 'RFID TIDAK',
                            'line2' => 'TERDAFTAR ✗',
                            'duration_seconds' => 2
                        ]
                    ]);
            }

            $today = date('Y-m-d');
            $currentTime = date('H:i:s');
            $att = $attModel->where('user_id', $userId)->where('date', $today)->first();

            // Strict Mode-based Decision
            $attendanceStatus = null;
            $action = null;

            if ($mode === 'masuk') {
                // === MODE MASUK ===

                // 1. Validate: Cannot check-in if already checked in
                if (!empty($att) && !empty($att['masuk_at'])) {
                    return $this->response
                        ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                        ->setJSON([
                            'success' => false,
                            'message' => 'Anda sudah check-in. Pindah ke tab Pulang untuk check-out',
                            'data' => null,
                            'lcd_display' => [
                                'line1' => 'SUDAH MASUK',
                                'line2' => 'KE TAB PULANG',
                                'duration_seconds' => 2
                            ]
                        ]);
                }

                // 2. Process Check-in
                if ($userType === 'siswa') {
                    $shiftModel = model('ShiftModel');
                    $shift = $student['shift_id'] ? $shiftModel->find($student['shift_id']) : null;

                    if (!$shift) {
                        return $this->response
                            ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                            ->setJSON([
                                'success' => false,
                                'message' => 'Shift tidak ditentukan',
                                'data' => null,
                                'lcd_display' => [
                                    'line1' => 'ERROR',
                                    'line2' => 'Tidak ada shift ✗',
                                    'duration_seconds' => 2
                                ]
                            ]);
                    }

                    // Validate shift time
                    if ($currentTime < $shift['start_time'] || $currentTime > $shift['end_time']) {
                        return $this->response
                            ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                            ->setJSON([
                                'success' => false,
                                'message' => 'Diluar jam masuk (' . $shift['start_time'] . ' - ' . $shift['end_time'] . ')',
                                'data' => null,
                                'lcd_display' => [
                                    'line1' => 'JAM DILUAR',
                                    'line2' => 'SHIFT ✗',
                                    'duration_seconds' => 2
                                ]
                            ]);
                    }

                    // Determine status
                    $deadline = $shift['checkin_deadline'];
                    $attendanceStatus = $currentTime <= $deadline ? 'on_time' : 'late';
                } else {
                    // Guru check-in
                    $scheduleModel = model('TeacherScheduleModel');
                    $dayOfWeek = (int)date('N');
                    $schedule = $scheduleModel
                        ->where('user_id', $userId)
                        ->where('hari', $dayOfWeek)
                        ->where('status', 'aktif')
                        ->first();

                    if (!$schedule) {
                        return $this->response
                            ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                            ->setJSON([
                                'success' => false,
                                'message' => 'Tidak ada jadwal hari ini',
                                'data' => null,
                                'lcd_display' => [
                                    'line1' => 'TIDAK ADA',
                                    'line2' => 'JADWAL ✗',
                                    'duration_seconds' => 2
                                ]
                            ]);
                    }

                    $graceEnd = date('H:i:s', strtotime($schedule['jam_masuk'] . ' +15 minutes'));
                    $attendanceStatus = $currentTime <= $graceEnd ? 'on_time' : 'late';
                }

                // Create or update attendance
                if (!$att) {
                    $attModel->insert([
                        'user_id' => $userId,
                        'date' => $today,
                        'masuk_at' => date('Y-m-d H:i:s'),
                        'masuk_status' => $attendanceStatus,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    $attModel->update($att['id'], [
                        'masuk_at' => date('Y-m-d H:i:s'),
                        'masuk_status' => $attendanceStatus,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                $action = 'masuk';
                $actionLabel = 'Masuk';
                $statusLabel = $attendanceStatus === 'on_time' ? 'Tepat Waktu' : 'Terlambat';
            } elseif ($mode === 'pulang') {
                // === MODE PULANG ===

                // 1. Validate: Cannot check-out if not checked in
                if (empty($att) || empty($att['masuk_at'])) {
                    return $this->response
                        ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                        ->setJSON([
                            'success' => false,
                            'message' => 'Harap lakukan check-in terlebih dahulu',
                            'data' => null,
                            'lcd_display' => [
                                'line1' => 'HARAP',
                                'line2' => 'CHECK-IN',
                                'duration_seconds' => 2
                            ]
                        ]);
                }

                // 2. Validate: Cannot check-out if already checked out
                if (!empty($att['pulang_at'])) {
                    return $this->response
                        ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                        ->setJSON([
                            'success' => false,
                            'message' => 'Sudah pulang',
                            'data' => null,
                            'lcd_display' => [
                                'line1' => 'SUDAH',
                                'line2' => 'PULANG ✗',
                                'duration_seconds' => 2
                            ]
                        ]);
                }

                // 3. Process Check-out
                if ($userType === 'siswa') {
                    $shiftModel = model('ShiftModel');
                    $shift = $shiftModel->find($student['shift_id']);
                    $attendanceStatus = $currentTime >= $shift['checkout_earliest'] ? 'on_time' : 'early';
                } else {
                    $scheduleModel = model('TeacherScheduleModel');
                    $dayOfWeek = (int)date('N');
                    $schedule = $scheduleModel
                        ->where('user_id', $userId)
                        ->where('hari', $dayOfWeek)
                        ->where('status', 'aktif')
                        ->first();
                    $attendanceStatus = $currentTime >= $schedule['jam_pulang'] ? 'on_time' : 'early';
                }

                // Update attendance
                $attModel->update($att['id'], [
                    'pulang_at' => date('Y-m-d H:i:s'),
                    'pulang_status' => $attendanceStatus,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $action = 'pulang';
                $actionLabel = 'Pulang';
                $statusLabel = $attendanceStatus === 'on_time' ? 'Tepat Waktu' : 'Lebih Awal';
            } else {
                // Invalid Mode
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                    ->setJSON(['success' => false, 'message' => 'Invalid mode']);
            }

            // Send notifications
            $this->sendAttendanceNotifications(
                $userType,
                $userData,
                $student,
                $teacher,
                $action,
                $statusLabel,
                $today,
                $currentTime
            );

            // Log to biometric_logs
            $biometricLogModel->insert([
                'device_id' => 'USB_READER',
                'user_id' => $identifier,
                'user_type' => $userType,
                'date' => $today,
                'time' => $currentTime,
                'timestamp' => date('Y-m-d H:i:s'),
                'biometric_type' => 'rfid',
                'status' => ($action === 'masuk') ? 'checkin' : 'checkout',
                'processed' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON([
                    'success' => true,
                    'message' => $actionLabel . ' tercatat',
                    'data' => [
                        'user_type' => $userType,
                        'user' => $userData,
                        'action' => $action,
                        'status' => $attendanceStatus,
                        'time' => $currentTime,
                    ],
                    'lcd_display' => [
                        'line1' => substr($userData['name'], 0, 16),
                        'line2' => $currentTime . ' ✓',
                        'duration_seconds' => 3
                    ]
                ]);
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Send attendance notifications via Telegram, WhatsApp, and Android
     */
    private function sendAttendanceNotifications($userType, $userData, $student, $teacher, $action, $statusLabel, $today, $time)
    {
        try {
            $settingsModel = new \App\Models\SettingsModel();
            $templateModel = new \App\Models\NotificationTemplateModel();

            // Prepare common data for templates
            $data = [
                'name' => $userData['name'],
                'time' => $time,
                'status_label' => $statusLabel,
                'date' => date('d/m/Y', strtotime($today)),
            ];

            if ($userType === 'siswa') {
                // SISWA NOTIFICATIONS: Telegram, WhatsApp, Android
                $userRecord = $student;

                // Telegram notification
                $tnModel = new TelegramNotificationModel();
                if (!empty($userRecord['telegram_chat_id'])) {
                    $template = $templateModel->getTemplate($action === 'masuk' ? 'tele_student_checkin' : 'tele_student_checkout');

                    $message = $template
                        ? \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data)
                        : ("✓ " . ($action === 'masuk' ? 'Absensi Masuk' : 'Absensi Pulang') . "\nNama: {$userData['name']}\nJam: {$time}\nTanggal: {$today}\nStatus: {$statusLabel}");

                    $tnModel->insert([
                        'student_id' => $userRecord['id'],
                        'chat_id' => $userRecord['telegram_chat_id'],
                        'message' => $message,
                        'payload' => json_encode(['type' => $action, 'status' => $statusLabel, 'time' => $time, 'date' => $today, 'recipient' => 'siswa']),
                        'status' => 'pending',
                        'attempts' => 0,
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // WhatsApp notification
                $waModel = new WhatsAppNotificationModel();
                $target = $settingsModel->getSetting('wa_notification_target') ?? 'guardian'; // guardian, group, both

                // 1. WhatsApp to guardian
                if (($target === 'guardian' || $target === 'both') && !empty($userRecord['guardian_phone'])) {
                    $template = $templateModel->getTemplate($action === 'masuk' ? 'wa_guardian_checkin' : 'wa_guardian_checkout');
                    // $data['status_label'] = $statusLabel . ($action === 'masuk' ? ($masukStatus == 'on_time' ? ' ✓' : ' ⚠') : ($pulangStatus == 'on_time' ? ' ✓' : ' ⚠')); // Re-add visual indicators if needed, or rely on template

                    $message = $template
                        ? \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data)
                        : (($action === 'masuk' ? '📱 Absensi Masuk Siswa' : '📱 Absensi Pulang Siswa') . "\nNama: {$userData['name']}\nJam: {$time}\nStatus: {$statusLabel}");

                    $waModel->insert([
                        'student_id' => $userRecord['id'],
                        'phone_number' => $userRecord['guardian_phone'],
                        'message' => $message,
                        'payload' => json_encode(['type' => $action, 'status' => $statusLabel, 'time' => $time, 'date' => $today, 'recipient' => 'guardian', 'recipient_type' => 'individual']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // 2. WhatsApp to Group
                if (($target === 'group' || $target === 'both') && !empty($userRecord['wa_group_id'])) {
                    $template = $templateModel->getTemplate($action === 'masuk' ? 'wa_checkin_group' : 'wa_checkout_group');
                    // Reset status label for group (clean text)
                    $data['status_label'] = $statusLabel;

                    $message = $template
                        ? \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data)
                        : (($action === 'masuk' ? '✅ Absensi Masuk (Group)' : '👋 Absensi Pulang (Group)') . "\n\nSiswa: *{$userData['name']}*\nWaktu: {$time}\nStatus: {$statusLabel}");

                    $waModel->insert([
                        'student_id' => $userRecord['id'],
                        'phone_number' => $userRecord['wa_group_id'],
                        'message' => $message,
                        'payload' => json_encode(['type' => $action, 'status' => $statusLabel, 'time' => $time, 'date' => $today, 'recipient' => 'group', 'recipient_type' => 'group']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Android push notification to student devices
                $androidModel = new AndroidNotificationModel();
                $tokenModel = new StudentDeviceTokenModel();
                $npsn = getenv('SCHOOL_NPSN');

                log_message('info', "RFID: Getting device tokens for student ID: {$userRecord['id']}, NPSN: {$npsn}");

                $deviceTokens = $tokenModel->getActiveTokensByStudent($userRecord['id'], $npsn);

                log_message('info', "RFID: Device tokens found: " . count($deviceTokens));

                if (!empty($deviceTokens)) {
                    // Reset label for android
                    $data['status_label'] = $statusLabel;

                    $templateTitle = $templateModel->getTemplate($action === 'masuk' ? 'android_student_checkin_title' : 'android_student_checkout_title');
                    $templateBody = $templateModel->getTemplate($action === 'masuk' ? 'android_student_checkin_body' : 'android_student_checkout_body');

                    $androidTitle = $templateTitle ? \App\Helpers\NotificationTemplateHelper::replaceVariables($templateTitle['content'], $data) : ($action === 'masuk' ? 'Absensi Masuk' : 'Absensi Pulang');
                    $androidMessage = $templateBody ? \App\Helpers\NotificationTemplateHelper::replaceVariables($templateBody['content'], $data) : "Nama: {$userData['name']}\nJam: {$time}\nStatus: {$statusLabel}";

                    $payload = [
                        'nis' => $userData['nis'],
                        'npsn' => $npsn,
                        'title' => $androidTitle,
                        'message' => $androidMessage,
                        'data' => [
                            'nis' => $userData['nis'],
                            'type' => $action,
                            'status' => $statusLabel,
                            'time' => $time,
                            'date' => $today,
                        ]
                    ];

                    foreach ($deviceTokens as $token) {
                        log_message('info', "RFID: Inserting android notification for token: {$token['device_token']}");
                        $androidModel->insert([
                            'student_id' => $userRecord['user_id'],
                            'nis' => $userData['nis'],
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
                } else {
                    log_message('info', "RFID: No device tokens found for student ID: {$userRecord['id']}");
                }
            } else {
                // GURU NOTIFICATIONS: Telegram, WhatsApp
                $userRecord = $teacher;
                // $templateModel already instantiated above
                $waModel = new WhatsAppNotificationModel();

                // Determine template code
                $templateCode = ($action === 'masuk') ? 'wa_teacher_checkin' : 'wa_teacher_checkout';

                // WhatsApp notification to teacher
                if (!empty($userRecord['phone_number'])) {
                    $template = $templateModel->getTemplate($templateCode);
                    $data = [
                        'name' => $userRecord['full_name'],
                        'time' => $time,
                        'date' => date('d/m/Y', strtotime($today)),
                        'status' => $statusLabel
                    ];

                    $message = "";
                    if ($template) {
                        $message = \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data);
                    } else {
                        // Fallback
                        $actionLabel = $action === 'masuk' ? '📱 Absensi Masuk' : '📱 Absensi Pulang';
                        $message = "{$actionLabel}\nNama: {$userData['name']}\nJam: {$time}\nStatus: {$statusLabel}";
                    }

                    $waModel->insert([
                        'phone_number' => $userRecord['phone_number'],
                        'message' => $message,
                        'payload' => json_encode(['type' => $action, 'status' => $statusLabel, 'time' => $time, 'date' => $today, 'recipient' => 'guru']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error sending RFID attendance notifications: ' . $e->getMessage());
        }
    }
}
