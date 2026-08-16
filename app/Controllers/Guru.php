<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\TeacherModel;
use App\Models\WalikelasModel;
use App\Models\StudentModel;
use App\Models\PermissionModel;
use App\Models\AttendanceModel;
use App\Models\WhatsAppNotificationModel;
use App\Models\SettingsModel;
use App\Models\NotificationTemplateModel;

class Guru extends Controller
{
    protected $teacherModel;
    protected $walikelasModel;
    protected $studentModel;

    public function __construct()
    {
        $this->teacherModel = new TeacherModel();
        $this->walikelasModel = new WalikelasModel();
        $this->studentModel = new StudentModel();
    }

    public function dashboard()
    {
        // Check if logged in (handled by filter but re-check for safety)
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();

        $waliKelas = null;
        $studentsInClass = [];

        if ($teacher) {
            $waliKelas = $this->walikelasModel->getByTeacherId($teacher['id']);

            if ($waliKelas) {
                // Get students by wali_kelas_id (primary method)
                $studentsInClass = $this->studentModel->getByWaliKelas($waliKelas['id']);

                // If no students found by wali_kelas_id, also try by class name (backward compatibility)
                if (empty($studentsInClass)) {
                    $studentsInClass = $this->studentModel->getByClass($waliKelas['class_name']);
                }
            }
        }

        // Get informations for this teacher
        $informations = [];
        if ($teacher) {
            $infoModel = new \App\Models\InformationModel();
            // Search in JSON string for teacher ID
            // Using like for basic JSON array search (works for simple ["1", "2"])
            $informations = $infoModel->like('target_classes', '"' . $teacher['id'] . '"')
                ->where('type', 'teacher')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->findAll();
        }

        $data = [
            'teacher' => $teacher,
            'waliKelas' => $waliKelas,
            'studentsCount' => count($studentsInClass),
            'students' => $studentsInClass,
            'isWaliKelas' => $waliKelas !== null,
            'informations' => $informations,
        ];

        return view('guru/dashboard', $data);
    }

    public function profile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();

        return view('guru/profile', ['teacher' => $teacher]);
    }

    public function editProfile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();

        // Ensure form helper is available for form_open(), set_value(), etc.
        helper('form');

        $data = [
            'teacher' => $teacher,
            'validation' => \Config\Services::validation(),
        ];

        return view('guru/profile_edit', $data);
    }

    public function updateProfile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();
        if (! $teacher) {
            session()->setFlashdata('error', 'Data guru tidak ditemukan');
            return redirect()->to('/guru/profile');
        }

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[191]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        // Only allow updating the teacher's full_name from this form.
        $data = [
            'full_name' => $this->request->getPost('full_name'),
        ];

        $this->teacherModel->update($teacher['id'], $data);

        // Update session with new full_name
        session()->set('full_name', $data['full_name']);

        session()->setFlashdata('success', 'Profil berhasil diperbarui');
        return redirect()->to('/guru/profile');
    }

    public function editPassword()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        helper('form');

        $data = [
            'validation' => \Config\Services::validation(),
        ];

        return view('guru/password_edit', $data);
    }

    public function updatePassword()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            session()->setFlashdata('error', 'User tidak ditemukan');
            return redirect()->to('/guru/profile');
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]|max_length[255]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            helper('form');
            return view('guru/password_edit', [
                'validation' => \Config\Services::validation(),
            ]);
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Verify current password
        if (! password_verify($currentPassword, $user['password_hash'])) {
            session()->setFlashdata('error', 'Password saat ini tidak sesuai');
            helper('form');
            return view('guru/password_edit', [
                'validation' => \Config\Services::validation(),
            ]);
        }

        // Update password
        $data = [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ];

        $userModel->update($userId, $data);

        session()->setFlashdata('success', 'Password berhasil diperbarui');
        return redirect()->to('/guru/password/edit');
    }

    /**
     * Check-In untuk guru
     * POST endpoint untuk guru melakukan check-in
     */
    public function checkIn()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak terautentikasi']);
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user || $user['role'] !== 'guru') {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $attendanceModel = new \App\Models\AttendanceModel();
        $teacherScheduleModel = new \App\Models\TeacherScheduleModel();

        $today = date('Y-m-d');
        $now = date('H:i:s');

        // Check apakah sudah check-in hari ini
        if ($attendanceModel->hasCheckedInToday($userId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda sudah check-in hari ini']);
        }

        // Get expected time dari schedule
        $expectedTime = $attendanceModel->getExpectedTimeForTeacher($userId, $today);

        if (!$expectedTime) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada jadwal untuk hari ini']);
        }

        // Calculate status
        $status = \App\Models\AttendanceModel::calculateMasukStatus($now, $expectedTime['jam_masuk'], 30);

        // Check if attendance record exists for today
        $todayAttendance = $attendanceModel->where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if ($todayAttendance) {
            // Update existing record
            $attendanceModel->update($todayAttendance['id'], [
                'masuk_at' => $now,
                'masuk_status' => $status,
            ]);
        } else {
            // Insert new record
            $attendanceModel->insert([
                'user_id' => $userId,
                'date' => $today,
                'masuk_at' => $now,
                'masuk_status' => $status,
            ]);
        }

        $statusText = $status === 'on_time' ? 'Tepat Waktu' : 'Terlambat';

        return $this->response->setJSON([
            'success' => true,
            'message' => "Check-in berhasil ($statusText)",
            'data' => [
                'masuk_at' => $now,
                'masuk_status' => $status,
                'expected_time' => $expectedTime['jam_masuk'],
            ],
        ]);
    }

    /**
     * Check-Out untuk guru
     * POST endpoint untuk guru melakukan check-out
     */
    public function checkOut()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak terautentikasi']);
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user || $user['role'] !== 'guru') {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        $attendanceModel = new \App\Models\AttendanceModel();

        $today = date('Y-m-d');
        $now = date('H:i:s');

        // Get today's attendance record
        $todayAttendance = $attendanceModel->where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if (!$todayAttendance) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda belum check-in hari ini']);
        }

        if ($todayAttendance['pulang_at'] !== null) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda sudah check-out hari ini']);
        }

        // Get expected time dari schedule
        $expectedTime = $attendanceModel->getExpectedTimeForTeacher($userId, $today);

        if (!$expectedTime) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada jadwal untuk hari ini']);
        }

        // Calculate status
        $status = \App\Models\AttendanceModel::calculatePulangStatus($now, $expectedTime['jam_pulang']);

        // Update record
        $attendanceModel->update($todayAttendance['id'], [
            'pulang_at' => $now,
            'pulang_status' => $status,
        ]);

        $statusText = $status === 'on_time' ? 'Tepat Waktu' : 'Pulang Awal';

        return $this->response->setJSON([
            'success' => true,
            'message' => "Check-out berhasil ($statusText)",
            'data' => [
                'pulang_at' => $now,
                'pulang_status' => $status,
                'expected_time' => $expectedTime['jam_pulang'],
            ],
        ]);
    }

    /**
     * Get today's attendance status (untuk dashboard)
     * GET endpoint untuk cek status check-in/out hari ini
     */
    public function todayStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak terautentikasi']);
        }

        $attendanceModel = new \App\Models\AttendanceModel();
        $teacherScheduleModel = new \App\Models\TeacherScheduleModel();

        $today = date('Y-m-d');

        // Get today's attendance
        $todayAttendance = $attendanceModel->getTodayAttendance($userId);

        // Get expected time
        $expectedTime = $attendanceModel->getExpectedTimeForTeacher($userId, $today);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'attendance' => $todayAttendance,
                'expectedTime' => $expectedTime,
                'hasSchedule' => !empty($expectedTime),
                'hasCheckedIn' => !empty($todayAttendance) && !empty($todayAttendance['masuk_at']),
                'hasCheckedOut' => !empty($todayAttendance) && !empty($todayAttendance['pulang_at']),
            ],
        ]);
    }

    /**
     * Get monthly attendance history untuk guru
     * GET endpoint dengan query param: month, year
     */
    public function attendance()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user || $user['role'] !== 'guru') {
            return redirect()->to('/guru/dashboard');
        }

        $month = (int) $this->request->getGet('month') ?: (int) date('m');
        $year = (int) $this->request->getGet('year') ?: (int) date('Y');

        $attendanceModel = new \App\Models\AttendanceModel();
        $attendance = $attendanceModel->getMonthlyAttendance($userId, $month, $year);

        // Calculate summary
        $summary = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'total_days' => count($attendance),
        ];

        foreach ($attendance as $record) {
            if ($record['masuk_status'] === 'alpha') {
                $summary['alpha']++;
            } elseif ($record['masuk_status'] === 'late') {
                $summary['terlambat']++;
            } else {
                $summary['hadir']++;
            }
        }

        $data = [
            'title' => 'Riwayat Absensi',
            'user' => $user,
            'month' => $month,
            'year' => $year,
            'monthName' => strftime('%B', mktime(0, 0, 0, $month, 1)),
            'attendance' => $attendance,
            'summary' => $summary,
        ];

        return view('guru/attendance', $data);
    }

    /**
     * Daftar Izin Siswa (Wali Kelas Only)
     */
    public function permissions()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();
        
        // Cek Wali Kelas
        $waliKelas = $this->walikelasModel->getByTeacherId($teacher['id']);
        if (! $waliKelas) {
            return redirect()->to('/guru/dashboard')->with('error', 'Anda bukan Wali Kelas.');
        }

        // Get Permissions for students in this class
        $students = $this->studentModel->getByWaliKelas($waliKelas['id']);
        $studentIds = array_column($students, 'id');

        $permissionModel = new PermissionModel();
        $pendingPermissions = $permissionModel->getPendingByStudents($studentIds);

        $data = [
            'title' => 'Daftar Pengajuan Izin',
            'permissions' => $pendingPermissions,
            'teacher' => $teacher,
            'isWaliKelas' => true
        ];

        return view('guru/permission/index', $data);
    }

    /**
     * Approve Permission
     */
    public function approvePermission($id)
    {
        if (! session()->get('isLoggedIn')) return redirect()->to('/login');

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();
        $waliKelas = $this->walikelasModel->getByTeacherId($teacher['id']);

        if (! $waliKelas) return redirect()->back()->with('error', 'Akses ditolak.');

        $permissionModel = new PermissionModel();
        $permission = $permissionModel->find($id);

        if (! $permission) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        // Verify student belongs to this wali kelas
        $student = $this->studentModel->find($permission['student_id']);
        if ($student['wali_kelas_id'] != $waliKelas['id']) {
            return redirect()->back()->with('error', 'Siswa ini bukan anggota kelas Anda.');
        }

        // 1. Update Permission Status
        $permissionModel->update($id, [
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Insert Attendance (Loop for Range)
        $attendanceModel = new AttendanceModel();
        $status = $permission['status'];
        
        $startDate = new \DateTime($permission['date']);
        $endDate = !empty($permission['end_date']) ? new \DateTime($permission['end_date']) : new \DateTime($permission['date']);
        
        // Loop through each day
        while ($startDate <= $endDate) {
            $currentDateStr = $startDate->format('Y-m-d');
            
            $existingAtt = $attendanceModel->where('user_id', $student['user_id'])->where('date', $currentDateStr)->first();
    
            if ($existingAtt) {
                $attendanceModel->update($existingAtt['id'], [
                    'masuk_status' => $status,
                    'masuk_at' => '07:00:00', 
                    'pulang_at' => '12:00:00',
                    'pulang_status' => 'on_time'
                ]);
            } else {
                $attendanceModel->insert([
                    'user_id' => $student['user_id'],
                    'date' => $currentDateStr,
                    'masuk_at' => '07:00:00',
                    'masuk_status' => $status,
                    'pulang_at' => '12:00:00',
                    'pulang_status' => 'on_time'
                ]);
            }

            $startDate->modify('+1 day');
        }

        // 3. Send Multi-channel Notification
        $this->sendApprovalNotification($student, $permission, $waliKelas);

        return redirect()->back()->with('success', 'Pengajuan disetujui dan absensi tercatat.');
    }

    /**
     * Reject Permission
     */
    public function rejectPermission($id)
    {
        if (! session()->get('isLoggedIn')) return redirect()->to('/login');

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();
        $waliKelas = $this->walikelasModel->getByTeacherId($teacher['id']);

        if (! $waliKelas) return redirect()->back()->with('error', 'Akses ditolak.');

        $permissionModel = new PermissionModel();
        $permission = $permissionModel->find($id);
        
        // Verify student belongs to this wali kelas
        $student = $this->studentModel->find($permission['student_id']);
        if ($student['wali_kelas_id'] != $waliKelas['id']) {
            return redirect()->back()->with('error', 'Siswa ini bukan anggota kelas Anda.');
        }

        $permissionModel->update($id, [
            'approval_status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Pengajuan ditolak.');
    }

    /**
     * Helper: Send Multi-channel Notification
     */
    private function sendApprovalNotification($student, $permission, $waliKelas)
    {
        $settingsModel = new SettingsModel();
        $target = $settingsModel->getSetting('wa_notification_target') ?? 'both';
        $schoolName = $settingsModel->getSetting('school_name') ?? 'Sekolah';

        $templateModel = new NotificationTemplateModel();
        // Reusing template text but modifying it for range support
        $template = $templateModel->where('name', 'wa_manual_update_guardian')->first(); 
        $groupTemplate = $templateModel->where('name', 'wa_manual_update_group')->first();
        
        // Construct Date String
        $dateStr = date('d-m-Y', strtotime($permission['date']));
        if (!empty($permission['end_date'])) {
            $dateStr .= ' s/d ' . date('d-m-Y', strtotime($permission['end_date']));
        }

        $statusLabel = ucfirst($permission['status']);
        
        // We might want to customize the message for approval to be more specific
        // For now, we reuse the manual update template but inject our custom date range
    
        $variables = [
            '{name}' => $student['full_name'],
            '{time}' => '07:00',
            '{date}' => $dateStr, // Range Date
            '{status}' => $statusLabel,
            '{school_name}' => $schoolName
        ];

        // --- WhatsApp Notification ---
        $waModel = new WhatsAppNotificationModel();
        // --- WhatsApp Notification (Robust) ---
        $waModel = new WhatsAppNotificationModel();
        
        // Always try to send to Guardian (Priority)
        if (!empty($student['guardian_phone'])) {
            $message = "";
            
            // Try Template First
            if ($template) {
                $message = \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $variables);
            } else {
                // Fallback Hardcoded Message
                $message = "📢 *Status Izin Siswa*\n\n" .
                           "Yth. Wali Murid {$student['full_name']},\n\n" .
                           "Pengajuan " . ($permission['status'] == 'sakit' ? 'Sakit' : 'Izin') . " untuk tanggal {$dateStr} telah *DISETUJUI*.\n\n" .
                           "Terima kasih.";
            }

            $waModel->insert([
                'phone_number' => $student['guardian_phone'],
                'message' => $message,
                'status' => 'pending',
                'recipient_type' => 'individual'
            ]);
        } else {
             log_message('warning', 'No guardian_phone for student ID: ' . $student['id']);
        }

        // Optional: Send to Group (Only if configured and template exists)
        if (($target === 'group' || $target === 'both') && !empty($waliKelas['wa_group_id'])) {
             if ($groupTemplate) {
                $message = \App\Helpers\NotificationTemplateHelper::replaceVariables($groupTemplate['content'], $variables);
                $waModel->insert([
                    'phone_number' => $waliKelas['wa_group_id'],
                    'message' => $message,
                    'status' => 'pending',
                    'recipient_type' => 'group'
                ]);
            }
        }

        // --- Telegram Notification ---
        if (!empty($student['telegram_chat_id'])) {
            $teleMessage = "";
            $teleTemplate = $templateModel->getTemplate('tele_manual_update');
            
            if ($teleTemplate) {
                 $teleMessage = \App\Helpers\NotificationTemplateHelper::replaceVariables($teleTemplate['content'], $variables);
            } else {
                 // Fallback Telegram Message
                 $teleMessage = "✅ *Status Izin Disetujui*\n\n" .
                                "Siswa: {$student['full_name']}\n" .
                                "Tanggal: {$dateStr}\n" .
                                "Status: {$statusLabel}\n\n" .
                                "Izin telah disetujui oleh Wali Kelas.";
            }

            // Send via Helper
            try {
                \App\Helpers\TelegramHelper::sendMessage($student['telegram_chat_id'], $teleMessage);
            } catch (\Exception $e) {
                log_message('error', 'Failed to send Telegram notification: ' . $e->getMessage());
            }
        }

        // --- Android Push Notification ---
        $tokenModel = new \App\Models\StudentDeviceTokenModel();
        // Assuming SCHOOL_NPSN env is set, or fallback empty/default
        $npsn = getenv('SCHOOL_NPSN') ?: 'DEFAULT'; 
        
        $deviceTokens = $tokenModel->getActiveTokensByStudent($student['id'], $npsn);
        
        if (!empty($deviceTokens)) {
            $androidModel = new \App\Models\AndroidNotificationModel();
            $androidTitle = "Persetujuan Izin {$statusLabel}";
            $androidMessage = "Pengajuan izin untuk {$student['full_name']} pada {$dateStr} telah disetujui.";
            
            foreach ($deviceTokens as $token) {
                 $androidModel->insert([
                    'token' => $token['device_token'],
                    'title' => $androidTitle,
                    'message' => $androidMessage,
                    'payload' => json_encode([
                        'type' => 'permission_approved',
                        'student_id' => $student['id'],
                        'date' => $permission['date']
                    ]),
                    'status' => 'pending'
                ]);
            }
        }
    }
}
