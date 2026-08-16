<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\ShiftModel;
use App\Models\WhatsAppNotificationModel;
use App\Models\AndroidNotificationModel;
use App\Models\StudentDeviceTokenModel;
use App\Models\TelegramNotificationModel;
use App\Helpers\QrCodeHelper;
use App\Helpers\TelegramHelper;
use App\Models\NotificationTemplateModel;
use App\Helpers\NotificationTemplateHelper;

class QrCode extends Controller
{
    /**
     * Display list of students for QR code printing
     */
    public function printCards()
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $studentModel = new StudentModel();
        $query = $studentModel->select('students.*')
            ->orderBy('students.class', 'ASC')
            ->orderBy('students.full_name', 'ASC');

        // Optional: Filter by specific ID for single card printing
        $studentId = $this->request->getGet('id');
        if ($studentId) {
            $query->where('students.id', $studentId);
        }

        $students = $query->findAll();

        // Generate QR codes if missing
        foreach ($students as &$student) {
            if (empty($student['qr_code_data'])) {
                $student['qr_code_data'] = QrCodeHelper::getDataUrl($student['nis']);
            }
        }

        // Fetch Template Config
        $templateConfig = [];
        $configPath = WRITEPATH . 'uploads/card_template.json';
        if (file_exists($configPath)) {
            $templateConfig = json_decode(file_get_contents($configPath), true);
        }

        // Fetch School Settings
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getAll();

        // Default settings for template if missing
        if (empty($settings['school_name'])) {
            $settings['school_name'] = 'NAMA SEKOLAH';
        }
        if (empty($settings['card_header_text'])) {
            $settings['card_header_text'] = 'KARTU TANDA PELAJAR';
        }
        if (empty($settings['school_address'])) {
            // Optional: leave empty or set placeholder
            // $settings['school_address'] = 'Alamat Sekolah...';
        }

        return view('admin/qrcode/print_cards', [
            'students' => $students,
            'templateConfig' => $templateConfig,
            'settings' => $settings
        ]);
    }

    /**
     * Display list of teachers for QR code printing
     */
    public function printCardsTeacher()
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $teacherModel = new \App\Models\TeacherModel();
        $teachers = $teacherModel->orderBy('full_name', 'ASC')->findAll();

        // Generate QR codes on the fly (since we don't store them for teachers yet)
        foreach ($teachers as &$teacher) {
            if (!empty($teacher['nip'])) {
                $teacher['qr_code_data'] = QrCodeHelper::getDataUrl($teacher['nip']);
            } else {
                $teacher['qr_code_data'] = null;
            }
        }

        // Fetch Template Config
        $templateConfig = [];
        $configPath = WRITEPATH . 'uploads/card_template.json';
        if (file_exists($configPath)) {
            $templateConfig = json_decode(file_get_contents($configPath), true);
        }

        // Fetch School Settings
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getAll();

        // Default settings for template if missing
        if (empty($settings['school_name'])) {
            $settings['school_name'] = 'NAMA SEKOLAH';
        }
        if (empty($settings['card_header_text'])) {
            $settings['card_header_text'] = 'KARTU TANDA PENGENAL';
        }

        return view('admin/qrcode/print_cards_teacher', [
            'teachers' => $teachers,
            'templateConfig' => $templateConfig,
            'settings' => $settings
        ]);
    }

    /**
     * Generate QR code for a specific student
     */
    public function generateStudent($studentId)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Unauthorized']);
        }

        $studentModel = new StudentModel();
        $result = $studentModel->generateQrCode($studentId);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'qr_code' => $result,
            ]);
        }

        return $this->response->setStatusCode(404)
            ->setJSON(['error' => 'Student not found']);
    }

    /**
     * Generate all missing QR codes
     */
    public function generateAll()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Unauthorized']);
        }

        $studentModel = new StudentModel();
        $count = $studentModel->generateMissingQrCodes();

        session()->setFlashdata('success', "QR code berhasil di-generate untuk {$count} siswa");
        return redirect()->back();
    }

    /**
     * API endpoint for scanning QR code and marking attendance
     * POST /api/qrcode/scan
     * Support: NIS (student), NIP (teacher/guru)
     */
    public function scan()
    {
        // Debug: Log all request data
        log_message('debug', 'QrCode::scan called');
        log_message('debug', 'Request method: ' . $this->request->getMethod());
        log_message('debug', 'Request body: ' . $this->request->getBody());
        log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));

        // Try to get NIS or NIP from POST data
        $nis = $this->request->getPost('nis');
        $nip = $this->request->getPost('nip');
        $mode = $this->request->getPost('mode') ?? 'masuk'; // masuk or pulang

        // If not found in POST, try JSON body
        if (!$nis && !$nip) {
            $json = $this->request->getJSON(true);
            log_message('debug', 'JSON data: ' . json_encode($json));
            $nis = $json['nis'] ?? null;
            $nip = $json['nip'] ?? null;
            $mode = $json['mode'] ?? 'masuk';
        }

        // Fallback: try GET (for testing)
        if (!$nis && !$nip) {
            $nis = $this->request->getGet('nis');
            $nip = $this->request->getGet('nip');
            $mode = $this->request->getGet('mode') ?? 'masuk';
        }

        log_message('debug', 'NIS received: ' . ($nis ?? 'NULL') . ', NIP received: ' . ($nip ?? 'NULL') . ', Mode: ' . $mode);

        if (!$nis && !$nip) {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'NIS (siswa) atau NIP (guru) required']);
        }

        $studentModel = new StudentModel();
        $student = null;
        $isTeacher = false;

        // Try to find student by NIS
        if ($nis) {
            log_message('debug', "Searching for student with NIS: $nis");
            $student = $studentModel->where('nis', $nis)->first();
            if ($student) {
                log_message('debug', "Found student: " . $student['full_name']);
            } else {
                log_message('debug', "Student not found with NIS: $nis");
            }
        }

        // If not student, try to find teacher by NIP
        // Try $nip first, if not provided, fallback to trying $nis as NIP (auto-detection)
        $nipToCheck = $nip ?: $nis;

        if (!$student && $nipToCheck) {
            log_message('debug', "Searching for teacher with NIP: $nipToCheck");
            $teacherModel = new \App\Models\TeacherModel();
            $teacher = $teacherModel->where('nip', $nipToCheck)->first();

            if ($teacher) {
                log_message('debug', "Found teacher: " . $teacher['full_name']);
                $isTeacher = true;
                // Get user info for teacher
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($teacher['user_id']);

                if ($user) {
                    // Create student-like object for consistency
                    $student = [
                        'user_id' => $teacher['user_id'],
                        'full_name' => $teacher['full_name'],
                        'nip' => $teacher['nip'],
                        'role' => 'guru',
                        'telegram_chat_id' => $teacher['telegram_chat_id'] ?? null,
                        'phone_number' => $teacher['phone_number'] ?? null,
                    ];
                    log_message('debug', 'Teacher found: ' . $teacher['full_name']);
                } else {
                    log_message('debug', "User not found for teacher ID: " . $teacher['user_id']);
                }
            } else {
                log_message('debug', "Teacher not found with NIP: $nipToCheck");
            }
        }

        if (!$student) {
            $message = $nis ? 'Siswa tidak ditemukan' : 'Guru tidak ditemukan';
            return $this->response->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => $message]);
        }

        // Check if user_id exists
        if (!$student['user_id']) {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'User belum terdaftar']);
        }

        // === ACTIVITY ATTENDANCE INTERCEPT ===
        // Check if this scan is for an activity
        $activityService = new \App\Services\ActivityAttendanceService();
        $activityResult = $activityService->processScan($student['user_id'], date('Y-m-d H:i:s'), 'qr_code');

        if ($activityResult['processed']) {
            if ($activityResult['should_stop_regular']) {
                $response = [
                    'success' => true,
                    'message' => 'Absensi Kegiatan Berhasil (' . $activityResult['message'] . ')',
                    'activity' => true,
                    'details' => $activityResult
                ];
                // Match required fields for QR response if needed
                if ($isTeacher) {
                    $response['nip'] = $student['nip'];
                    $response['role'] = $student['role'];
                } else {
                    $response['nis'] = $student['nis'];
                    $response['class'] = $student['class'];
                }
                return $this->response->setJSON($response);
            }
        }
        // =====================================

        // Mark attendance for today
        $attendanceModel = new AttendanceModel();
        $today = date('Y-m-d');

        // Check if already present today
        $existing = $attendanceModel->where('user_id', $student['user_id'])
            ->where('date', $today)
            ->first();

        if ($mode === 'masuk') {
            // MASUK MODE
            if ($existing && ! empty($existing['masuk_at'])) {
                $response = [
                    'success' => false,
                    'message' => ($isTeacher ? 'Guru' : 'Siswa') . ' sudah absen masuk hari ini',
                    'name' => $student['full_name'],
                    'status' => $existing['masuk_status'],
                    'time' => date('H:i', strtotime($existing['masuk_at']))
                ];
                if ($isTeacher) {
                    $response['nip'] = $student['nip'];
                } else {
                    $response['nis'] = $student['nis'];
                }
                return $this->response->setJSON($response);
            }

            $currentTime = date('H:i:s');
            $status = 'on_time'; // Default

            // GURU/KARYAWAN FLOW (has role attribute)
            if ($isTeacher) {
                // Validate against teacher schedule
                $teacherScheduleModel = new \App\Models\TeacherScheduleModel();
                $dayOfWeek = (int) date('w'); // 0=Sunday, 1=Monday, ..., 6=Saturday
                if ($dayOfWeek == 0) $dayOfWeek = 7; // Convert Sunday to 7

                $schedule = $teacherScheduleModel->where('user_id', $student['user_id'])
                    ->where('hari', $dayOfWeek)
                    ->first();

                if (!$schedule) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Tidak ada jadwal untuk hari ini',
                        'student_name' => $student['full_name'],
                        'nip' => $student['nip'],
                    ]);
                }

                // Allow check-in at any time, determine status based on jam_masuk
                // Before jam_masuk = tepat waktu, after = terlambat
                $status = $currentTime <= $schedule['jam_masuk'] ? 'on_time' : 'late';

                log_message('debug', "Teacher {$student['nip']} checkin: schedule {$schedule['jam_masuk']}, current {$currentTime}, status {$status}");
            }
            // SISWA FLOW (has shift_id)
            else {
                $shiftModel = new ShiftModel();

                // Check if student has a shift assigned
                if (!empty($student['shift_id'])) {
                    $shift = $shiftModel->find($student['shift_id']);
                    if ($shift) {
                        // Check if current time is within shift hours (masuk dapat dilakukan mulai dari start_time sampai end_time)
                        if ($currentTime < $shift['start_time'] || $currentTime > $shift['end_time']) {
                            log_message('warning', "Student {$student['nis']} trying to check-in outside shift hours. Shift: {$shift['start_time']} - {$shift['end_time']}, Current: {$currentTime}");
                            return $this->response->setJSON([
                                'success' => false,
                                'message' => 'Diluar jam shift. Shift ' . $shift['name'] . ' dimulai pukul ' . date('H:i', strtotime($shift['start_time'])) . ' sampai ' . date('H:i', strtotime($shift['end_time'])),
                                'student_name' => $student['full_name'],
                                'nis' => $student['nis'],
                            ]);
                        }

                        // Use shift-based deadline
                        $status = $currentTime <= $shift['checkin_deadline'] ? 'on_time' : 'late';
                        log_message('debug', "Using shift {$shift['name']} deadline: {$shift['checkin_deadline']}, current time: {$currentTime}, status: {$status}");
                    } else {
                        // Fallback to default if shift not found
                        $status = $currentTime <= '07:30:00' ? 'on_time' : 'late';
                        log_message('warning', "Shift {$student['shift_id']} not found for student {$student['nis']}, using default deadline");
                    }
                } else {
                    // No shift assigned, use default deadline
                    $status = $currentTime <= '07:30:00' ? 'on_time' : 'late';
                    log_message('debug', "No shift assigned for student {$student['nis']}, using default deadline 07:30:00");
                }
            }

            // Create or update attendance record
            if ($existing) {
                $attendanceModel->update($existing['id'], [
                    'masuk_at' => date('Y-m-d H:i:s'),
                    'masuk_status' => $status,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $insertData = [
                    'user_id' => $student['user_id'],
                    'date' => $today,
                    'masuk_at' => date('Y-m-d H:i:s'),
                    'masuk_status' => $status,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $attendanceModel->insert($insertData);
            }

            // Send notifications (Telegram, WhatsApp, Android)
            if ($isTeacher) {
                // GURU NOTIFICATIONS
                $tnModel = new TelegramNotificationModel();
                $waModel = new WhatsAppNotificationModel();
                $androidModel = new AndroidNotificationModel();
                $templateModel = new NotificationTemplateModel();

                // Queue Telegram notification
                if (!empty($student['telegram_chat_id'])) {
                    $statusLabel = ($status === 'on_time') ? 'Tepat Waktu' : 'Terlambat';
                    $message = "✓ Absensi Masuk\nNama: {$student['full_name']}\nJam: " . date('H:i') . "\nTanggal: {$today}\nStatus: {$statusLabel}";
                    $tnModel->insert([
                        'chat_id' => $student['telegram_chat_id'],
                        'message' => $message,
                        'payload' => json_encode(['type' => 'masuk', 'status' => $status, 'time' => date('H:i'), 'date' => $today, 'recipient' => 'guru']),
                        'status' => 'pending',
                        'attempts' => 0,
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // Queue WhatsApp notification using Template
                if (!empty($student['phone_number'])) {
                    $template = $templateModel->getTemplate('wa_teacher_checkin');
                    $data = [
                        'name' => $student['full_name'],
                        'time' => date('H:i'),
                        'date' => date('d/m/Y', strtotime($today)),
                        'status' => ($status === 'on_time') ? 'Tepat Waktu' : 'Terlambat'
                    ];

                    $message = "";
                    if ($template) {
                        $message = NotificationTemplateHelper::replaceVariables($template['content'], $data);
                    } else {
                        // Fallback
                        $statusLabel = ($status === 'on_time') ? 'Tepat Waktu ✓' : 'Terlambat ⚠';
                        $message = "📱 Absensi Masuk Guru\nNama: {$student['full_name']}\nJam: " . date('H:i') . "\nTanggal: {$today}\nStatus: {$statusLabel}";
                    }

                    $waModel->insert([
                        'phone_number' => $student['phone_number'],
                        'message' => $message,
                        'payload' => json_encode(['type' => 'masuk', 'status' => $status, 'time' => date('H:i'), 'date' => $today, 'recipient' => 'guru']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                // SISWA NOTIFICATIONS
                TelegramHelper::sendAttendanceNotification(
                    $student['id'],
                    $student['telegram_chat_id'],
                    $student['full_name'],
                    'masuk',
                    $status,
                    date('H:i')
                );

                // Queue WhatsApp notification to student
                $waModel = new WhatsAppNotificationModel();
                if (!empty($student['phone_number'])) {
                    $message = "📱 Absensi Masuk\nNama: {$student['full_name']}\nJam: " . date('H:i') . "\nStatus: " .
                        ($status === 'on_time' ? 'Tepat Waktu ✓' : 'Terlambat ⚠');
                    $waModel->insert([
                        'student_id' => $student['id'],
                        'phone_number' => $student['phone_number'],
                        'message' => $message,
                        'payload' => json_encode(['type' => 'masuk', 'status' => $status, 'time' => date('H:i'), 'date' => $today, 'recipient' => 'student']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Queue WhatsApp notification to guardian
                if (!empty($student['guardian_phone'])) {
                    $message = "📱 Absensi Masuk Siswa\nNama: {$student['full_name']}\nJam: " . date('H:i') . "\nStatus: " .
                        ($status === 'on_time' ? 'Tepat Waktu ✓' : 'Terlambat ⚠');
                    $waModel->insert([
                        'student_id' => $student['id'],
                        'phone_number' => $student['guardian_phone'],
                        'message' => $message,
                        'payload' => json_encode(['type' => 'masuk', 'status' => $status, 'time' => date('H:i'), 'date' => $today, 'recipient' => 'guardian']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Queue Android push notification to student devices
                $androidModel = new AndroidNotificationModel();
                $tokenModel = new StudentDeviceTokenModel();
                $npsn = getenv('SCHOOL_NPSN');
                $deviceTokens = $tokenModel->getActiveTokensByStudent($student['id'], $npsn);
                if (!empty($deviceTokens)) {
                    $androidTitle = 'Absensi Masuk';
                    $androidMessage = "Nama: {$student['full_name']}\nJam: " . date('H:i') . "\nStatus: " .
                        ($status === 'on_time' ? 'Tepat Waktu' : 'Terlambat');

                    // Build payload for APK
                    $payload = [
                        'nis' => $student['nis'],
                        'npsn' => $npsn,
                        'title' => $androidTitle,
                        'message' => $androidMessage,
                        'data' => [
                            'type' => 'masuk',
                            'status' => $status,
                            'tanggal' => $today,
                            'jam' => date('H:i:s'),
                            'school' => getenv('SCHOOL_NAME') ?: 'A'
                        ]
                    ];

                    foreach ($deviceTokens as $token) {
                        $androidModel->insert([
                            'student_id' => $student['id'],
                            'nis' => $student['nis'],
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

                // Send notification to external API
                $this->sendToExternalAPI($student, $today, 'masuk', $status, date('H:i:s'));
            }

            // Build response - different structure for student vs teacher
            $response = [
                'success' => true,
                'message' => 'Absensi masuk berhasil dicatat',
                'name' => $student['full_name'],
                'time' => date('H:i'),
                'mode' => 'masuk',
                'status' => $status
            ];

            // Add student-specific fields only if not a teacher
            if (!$isTeacher) {
                $response['nis'] = $student['nis'];
                $response['class'] = $student['class'];
                $response['status_text'] = $status === 'on_time' ? 'Tepat Waktu' : 'Terlambat';
            } else {
                $response['nip'] = $student['nip'];
                $response['role'] = $student['role'];
            }

            return $this->response->setJSON($response);
        } else if ($mode === 'pulang') {
            // PULANG MODE
            if (!$existing) {
                $response = [
                    'success' => false,
                    'message' => ($isTeacher ? 'Guru' : 'Siswa') . ' belum melakukan absen masuk hari ini',
                    'name' => $student['full_name']
                ];
                if ($isTeacher) {
                    $response['nip'] = $student['nip'];
                } else {
                    $response['nis'] = $student['nis'];
                }
                return $this->response->setJSON($response);
            }

            if (! empty($existing['pulang_at'])) {
                $response = [
                    'success' => false,
                    'message' => ($isTeacher ? 'Guru' : 'Siswa') . ' sudah absen pulang hari ini',
                    'name' => $student['full_name'],
                    'status' => $existing['pulang_status'],
                    'time' => date('H:i', strtotime($existing['pulang_at']))
                ];
                if ($isTeacher) {
                    $response['nip'] = $student['nip'];
                } else {
                    $response['nis'] = $student['nis'];
                }
                return $this->response->setJSON($response);
            }

            // Determine pulang status based on shift (for students) or schedule (for teachers)
            $currentTime = date('H:i:s');
            $status = 'on_time'; // Default

            if ($isTeacher) {
                // Teacher checkout - allow check-out at any time, determine status based on jam_pulang
                // Before jam_pulang = lebih awal, on/after = tepat waktu
                $teacherScheduleModel = new \App\Models\TeacherScheduleModel();
                $dayOfWeek = (int) date('w'); // 0=Sunday, 1=Monday, ..., 6=Saturday
                if ($dayOfWeek == 0) $dayOfWeek = 7; // Convert Sunday to 7

                $schedule = $teacherScheduleModel->where('user_id', $student['user_id'])
                    ->where('hari', $dayOfWeek)
                    ->first();

                if ($schedule) {
                    $status = $currentTime < $schedule['jam_pulang'] ? 'early' : 'on_time';
                    log_message('debug', "Teacher {$student['nip']} checkout: schedule {$schedule['jam_pulang']}, current {$currentTime}, status {$status}");
                }
            } else {
                // Student checkout - use shift times
                $shiftModel = new ShiftModel();

                // Check if student has a shift assigned
                if (!empty($student['shift_id'])) {
                    $shift = $shiftModel->find($student['shift_id']);
                    if ($shift) {
                        // Check if current time is within shift hours (pulang dapat dilakukan mulai dari checkout_earliest sampai end_time)
                        if ($currentTime < $shift['start_time'] || $currentTime > $shift['end_time']) {
                            log_message('warning', "Student {$student['nis']} trying to check-out outside shift hours. Shift: {$shift['start_time']} - {$shift['end_time']}, Current: {$currentTime}");
                            return $this->response->setJSON([
                                'success' => false,
                                'message' => 'Diluar jam shift. Shift ' . $shift['name'] . ' dimulai pukul ' . date('H:i', strtotime($shift['start_time'])) . ' sampai ' . date('H:i', strtotime($shift['end_time'])),
                                'name' => $student['full_name'],
                                'nis' => $student['nis'],
                            ]);
                        }

                        // Use shift-based checkout time
                        $status = $currentTime >= $shift['checkout_earliest'] ? 'on_time' : 'early';
                        log_message('debug', "Using shift {$shift['name']} checkout: {$shift['checkout_earliest']}, current time: {$currentTime}, status: {$status}");
                    } else {
                        // Fallback to default if shift not found
                        $status = $currentTime >= '15:00:00' ? 'on_time' : 'early';
                        log_message('warning', "Shift {$student['shift_id']} not found for student {$student['nis']}, using default checkout time");
                    }
                } else {
                    // No shift assigned, use default checkout time
                    $status = $currentTime >= '15:00:00' ? 'on_time' : 'early';
                    log_message('debug', "No shift assigned for student {$student['nis']}, using default checkout time 15:00:00");
                }
            }

            // Update attendance record with pulang
            $attendanceModel->update($existing['id'], [
                'pulang_at' => date('Y-m-d H:i:s'),
                'pulang_status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Send notifications (Telegram, WhatsApp, Android)
            if ($isTeacher) {
                // GURU NOTIFICATIONS
                $tnModel = new TelegramNotificationModel();
                $waModel = new WhatsAppNotificationModel();
                $templateModel = new NotificationTemplateModel();

                // Queue Telegram notification
                if (!empty($student['telegram_chat_id'])) {
                    $statusLabel = ($status === 'on_time') ? 'Pulang Tepat' : 'Pulang Lebih Awal';
                    $message = "⟲ Absensi Pulang\nNama: {$student['full_name']}\nJam: " . date('H:i') . "\nTanggal: {$today}\nStatus: {$statusLabel}";
                    $tnModel->insert([
                        'chat_id' => $student['telegram_chat_id'],
                        'message' => $message,
                        'payload' => json_encode(['type' => 'pulang', 'status' => $status, 'time' => date('H:i'), 'date' => $today, 'recipient' => 'guru']),
                        'status' => 'pending',
                        'attempts' => 0,
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // Queue WhatsApp notification using Template
                if (!empty($student['phone_number'])) {
                    $template = $templateModel->getTemplate('wa_teacher_checkout');
                    $data = [
                        'name' => $student['full_name'],
                        'time' => date('H:i'),
                        'date' => date('d/m/Y', strtotime($today)),
                        'status' => ($status === 'on_time') ? 'Pulang Tepat' : 'Pulang Lebih Awal'
                    ];

                    $message = "";
                    if ($template) {
                        $message = NotificationTemplateHelper::replaceVariables($template['content'], $data);
                    } else {
                        // Fallback
                        $statusLabel = ($status === 'on_time') ? 'Pulang Tepat ✓' : 'Pulang Lebih Awal ⚠';
                        $message = "👋 Absensi Pulang Guru\nNama: {$student['full_name']}\nJam: " . date('H:i') . "\nTanggal: {$today}\nStatus: {$statusLabel}";
                    }

                    $waModel->insert([
                        'phone_number' => $student['phone_number'],
                        'message' => $message,
                        'payload' => json_encode(['type' => 'pulang', 'status' => $status, 'time' => date('H:i'), 'date' => $today, 'recipient' => 'guru']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                // SISWA NOTIFICATIONS
                TelegramHelper::sendAttendanceNotification(
                    $student['id'],
                    $student['telegram_chat_id'],
                    $student['full_name'],
                    'pulang',
                    $status,
                    date('H:i')
                );

                // Queue WhatsApp notification to student
                $waModel = new WhatsAppNotificationModel();
                if (!empty($student['phone_number'])) {
                    $message = "👋 Absensi Pulang\nNama: {$student['full_name']}\nJam: " . date('H:i') . "\nStatus: " .
                        ($status === 'on_time' ? 'Tepat Waktu ✓' : 'Lebih Awal ⚠');
                    $waModel->insert([
                        'student_id' => $student['id'],
                        'phone_number' => $student['phone_number'],
                        'message' => $message,
                        'payload' => json_encode(['type' => 'pulang', 'status' => $status, 'time' => date('H:i'), 'date' => $today, 'recipient' => 'student']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Queue WhatsApp notification to guardian
                if (!empty($student['guardian_phone'])) {
                    $message = "👋 Absensi Pulang Siswa\nNama: {$student['full_name']}\nJam: " . date('H:i') . "\nStatus: " .
                        ($status === 'on_time' ? 'Tepat Waktu ✓' : 'Lebih Awal ⚠');
                    $waModel->insert([
                        'student_id' => $student['id'],
                        'phone_number' => $student['guardian_phone'],
                        'message' => $message,
                        'payload' => json_encode(['type' => 'pulang', 'status' => $status, 'time' => date('H:i'), 'date' => $today, 'recipient' => 'guardian']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Queue Android push notification to student devices
                $androidModel = new AndroidNotificationModel();
                $tokenModel = new StudentDeviceTokenModel();
                $npsn = getenv('SCHOOL_NPSN');
                $deviceTokens = $tokenModel->getActiveTokensByStudent($student['id'], $npsn);
                if (!empty($deviceTokens)) {
                    $androidTitle = 'Absensi Pulang';
                    $androidMessage = "Nama: {$student['full_name']}\nJam: " . date('H:i') . "\nStatus: " .
                        ($status === 'on_time' ? 'Tepat Waktu' : 'Lebih Awal');

                    // Build payload for APK
                    $payload = [
                        'nis' => $student['nis'],
                        'npsn' => $npsn,
                        'title' => $androidTitle,
                        'message' => $androidMessage,
                        'data' => [
                            'type' => 'pulang',
                            'status' => $status,
                            'tanggal' => $today,
                            'jam' => date('H:i:s'),
                            'school' => getenv('SCHOOL_NAME') ?: 'A'
                        ]
                    ];

                    foreach ($deviceTokens as $token) {
                        $androidModel->insert([
                            'student_id' => $student['id'],
                            'nis' => $student['nis'],
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

                // Send notification to external API
                $this->sendToExternalAPI($student, $today, 'pulang', $status, date('H:i:s'));
            }

            // Build response - different structure for student vs teacher
            $response = [
                'success' => true,
                'message' => 'Absensi pulang berhasil dicatat',
                'name' => $student['full_name'],
                'time' => date('H:i'),
                'mode' => 'pulang',
                'status' => $status
            ];

            // Add student-specific fields only if not a teacher
            if (!$isTeacher) {
                $response['nis'] = $student['nis'];
                $response['class'] = $student['class'];
                $response['status_text'] = $status === 'on_time' ? 'Tepat Waktu' : 'Lebih Awal';
            } else {
                $response['nip'] = $student['nip'];
                $response['role'] = $student['role'];
            }

            return $this->response->setJSON($response);
        }

        return $this->response->setStatusCode(400)
            ->setJSON(['success' => false, 'message' => 'Invalid mode']);
    }

    /**
     * Display QR code scanner interface
     */
    public function scanner()
    {
        return view('admin/qrcode/scanner');
    }

    /**
     * Send attendance data to external API via curl
     */
    private function sendToExternalAPI($student, $date, $type, $status, $time)
    {
        try {
            // Get external API URL from environment
            $externalApiUrl = getenv('EXTERNAL_NOTIFICATION_URL');

            if (!$externalApiUrl) {
                log_message('warning', "QrCode::scan - EXTERNAL_NOTIFICATION_URL not configured");
                return;
            }

            // Check if this is a teacher (has 'nip' field) or student (has 'nis' field)
            $identifier = isset($student['nis']) ? $student['nis'] : $student['nip'];
            $identifierType = isset($student['nis']) ? 'nis' : 'nip';

            // Prepare payload sesuai format yang diminta
            $payload = [
                $identifierType => $identifier,
                'npsn' => getenv('SCHOOL_NPSN') ?? '20401559',
                'title' => $type === 'masuk' ? 'Absensi - ' . ucfirst($status) : 'Pulang - ' . ucfirst(str_replace('_', ' ', $status)),
                'message' => ($identifierType === 'nis' ? 'Siswa' : 'Guru') . " {$student['full_name']} ({$identifier}) " .
                    ($type === 'masuk' ? 'masuk' : 'pulang') .
                    " " . str_replace('_', ' ', $status) . " pada {$time} tanggal {$date}.",
                'data' => [
                    'type' => $type,
                    'status' => $status,
                    'tanggal' => $date,
                    'jam' => $time,
                    'tempat' => 'Gerbang Sekolah A',
                    'school' => 'A'
                ]
            ];

            // Send via curl
            $this->curlRequest($externalApiUrl, $payload);

            log_message('info', "QrCode::scan - Sent external notification for {$identifierType} {$identifier}");
        } catch (\Exception $e) {
            log_message('error', "QrCode::scan - Error sending external notification - " . $e->getMessage());
        }
    }

    /**
     * Send curl request to external API
     */
    private function curlRequest($url, $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Execute and close
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_message('error', "QrCode::scan - Curl error - {$curlError}");
        }

        log_message('info', "QrCode::scan - External API response - HTTP {$httpCode}");

        return $httpCode >= 200 && $httpCode < 300;
    }
}
