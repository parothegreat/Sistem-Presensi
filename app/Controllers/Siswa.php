<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\WalikelasModel;
use App\Models\WhatsAppNotificationModel;
use App\Models\TeacherModel;
use App\Models\NotificationTemplateModel;

class Siswa extends Controller
{
    protected $studentModel;
    protected $attendanceModel;
    protected $walikelasModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->attendanceModel = new AttendanceModel();
        $this->walikelasModel = new WalikelasModel();
    }

    public function dashboard()
    {
        // Check if logged in (handled by filter but re-check for safety)
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $student = $this->studentModel->where('user_id', $userId)->first();

        $classInfo = null;
        $todayAttendance = null;
        $attendanceStats = [
            'total_hadir' => 0,
            'total_terlambat' => 0,
            'total_izin' => 0,
            'total_sakit' => 0,
            'total_alpha' => 0,
        ];

        if ($student) {
            // Get class info if student has wali_kelas_id
            if ($student['wali_kelas_id']) {
                $classInfo = $this->walikelasModel->find($student['wali_kelas_id']);
            }

            // Get informations for this class
            $informations = [];
            if ($student['wali_kelas_id']) {
                $infoModel = new \App\Models\InformationModel();
                // Simple search in JSON string
                $informations = $infoModel->like('target_classes', '"' . $student['wali_kelas_id'] . '"')
                    ->where('type', 'student')
                    ->orderBy('created_at', 'DESC')
                    ->limit(5)
                    ->findAll();
            }

            // Get today's attendance
            $today = date('Y-m-d');
            $todayAttendance = $this->attendanceModel
                ->where('user_id', $userId)
                ->where('date', $today)
                ->first();

            // Get attendance stats (last 30 days)
            $dateFrom = date('Y-m-d', strtotime('-30 days'));
            $stats = $this->attendanceModel
                ->where('user_id', $userId)
                ->where('date >=', $dateFrom)
                ->findAll();

            foreach ($stats as $att) {
                match ($att['masuk_status']) {
                    'on_time' => $attendanceStats['total_hadir']++,
                    'late' => $attendanceStats['total_terlambat']++,
                    'izin' => $attendanceStats['total_izin']++,
                    'sakit' => $attendanceStats['total_sakit']++,
                    'alpha' => $attendanceStats['total_alpha']++,
                    default => null,
                };
            }
        }

        // Prepare monthly attendance history (month param as YYYY-MM)
        $requestedMonth = $this->request->getGet('month') ?? date('Y-m');
        // validate format YYYY-MM
        if (! preg_match('/^\d{4}-\d{2}$/', $requestedMonth)) {
            $requestedMonth = date('Y-m');
        }

        $startDate = $requestedMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $attendanceHistory = [];
        $monthlyStats = [
            'total_masuk' => 0,
            'total_pulang' => 0,
            'total_izin' => 0,
            'total_sakit' => 0,
            'total_alpha' => 0,
            'total_terlambat' => 0,
        ];

        if ($student) {
            $attendanceHistory = $this->attendanceModel
                ->where('user_id', $userId)
                ->where('date >=', $startDate)
                ->where('date <=', $endDate)
                ->orderBy('date', 'DESC')
                ->findAll();

            // Calculate monthly stats
            foreach ($attendanceHistory as $att) {
                $status = $att['masuk_status'];
                if ($status === 'on_time' || $status === 'early') {
                    $monthlyStats['total_masuk']++;
                } elseif ($status === 'late') {
                    $monthlyStats['total_terlambat']++;
                } elseif ($status === 'izin') {
                    $monthlyStats['total_izin']++;
                } elseif ($status === 'sakit') {
                    $monthlyStats['total_sakit']++;
                } elseif ($status === 'alpha') {
                    $monthlyStats['total_alpha']++;
                }

                // Count pulang (check-out) if present
                if ($att['pulang_at']) {
                    $monthlyStats['total_pulang']++;
                }
            }
        }

        // Prev/next month strings
        $prevMonth = date('Y-m', strtotime($startDate . ' -1 month'));
        $nextMonth = date('Y-m', strtotime($startDate . ' +1 month'));

        $data = [
            'student' => $student,
            'classInfo' => $classInfo,
            'todayAttendance' => $todayAttendance,
            'attendanceStats' => $attendanceStats,
            'attendanceHistory' => $attendanceHistory,
            'monthlyStats' => $monthlyStats,
            'month' => $requestedMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'monthStart' => $startDate,
            'monthStart' => $startDate,
            'monthEnd' => $endDate,
            'informations' => $informations ?? [],
        ];

        return view('siswa/dashboard', $data);
    }

    public function attendance()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $student = $this->studentModel->where('user_id', $userId)->first();

        $attendance = [];
        if ($student) {
            $attendance = $this->attendanceModel->where('user_id', $userId)->orderBy('date', 'DESC')->findAll();
        }

        return view('siswa/attendance', ['attendance' => $attendance, 'student' => $student]);
    }

    public function profile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $student = $this->studentModel->where('user_id', $userId)->first();

        return view('siswa/profile', ['student' => $student]);
    }

    public function editProfile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $student = $this->studentModel->where('user_id', $userId)->first();
        // Ensure form helper is available for form_open(), set_value(), etc.
        helper('form');

        $data = [
            'student' => $student,
            'validation' => \Config\Services::validation(),
        ];

        return view('siswa/profile_edit', $data);
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
        $student = $this->studentModel->where('user_id', $userId)->first();
        if (! $student) {
            session()->setFlashdata('error', 'Data siswa tidak ditemukan');
            return redirect()->to('/siswa/profile');
        }

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[191]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        // Only allow updating the student's full_name from this form.
        $data = [
            'full_name' => $this->request->getPost('full_name'),
        ];

        $this->studentModel->update($student['id'], $data);

        session()->setFlashdata('success', 'Profil berhasil diperbarui');
        return redirect()->to('/siswa/profile');
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

        return view('siswa/password_edit', $data);
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
            return redirect()->to('/siswa/profile');
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]|max_length[255]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            helper('form');
            return view('siswa/password_edit', [
                'validation' => \Config\Services::validation(),
            ]);
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Verify current password
        if (! password_verify($currentPassword, $user['password_hash'])) {
            session()->setFlashdata('error', 'Password saat ini tidak sesuai');
            helper('form');
            return view('siswa/password_edit', [
                'validation' => \Config\Services::validation(),
            ]);
        }

        // Update password
        $data = [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ];

        $userModel->update($userId, $data);

        session()->setFlashdata('success', 'Password berhasil diperbarui');
        return redirect()->to('/siswa/password/edit');
    }

    /**
     * Halaman Pengajuan Izin
     */
    public function permission()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $student = $this->studentModel->where('user_id', $userId)->first();

        if (! $student) {
            return redirect()->to('/siswa/dashboard');
        }

        $permissionModel = new \App\Models\PermissionModel();
        $permissions = $permissionModel->getByStudent($student['id']);

        $data = [
            'title' => 'Pengajuan Izin',
            'student' => $student,
            'permissions' => $permissions,
            'validation' => \Config\Services::validation()
        ];

        return view('siswa/permission', $data);
    }

    /**
     * Proses Pengajuan Izin
     */
    public function submitPermission()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $student = $this->studentModel->where('user_id', $userId)->first();

        if (! $student) {
            return redirect()->to('/siswa/dashboard');
        }

        $rules = [
            'date' => 'required|valid_date',
            'status' => 'required|in_list[izin,sakit]',
            'reason' => 'required|min_length[5]',
            'evidence' => [
                'rules' => 'uploaded[evidence]|max_size[evidence,2048]|is_image[evidence]|mime_in[evidence,image/jpg,image/jpeg,image/png]',
                'label' => 'Bukti Foto'
            ]
        ];

        if ($this->request->getPost('end_date')) {
            $rules['end_date'] = 'required|valid_date';
             // Custom check logic could be added here if needed to ensure end_date >= date
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. Mohon periksa kembali isian form Anda.');
        }

        $file = $this->request->getFile('evidence');
        $fileName = '';

        if ($file->isValid() && ! $file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/permissions', $fileName);
        }
        
        $date = $this->request->getPost('date');
        $endDate = $this->request->getPost('end_date');
        
        // Ensure end_date is null if empty string
        if (empty($endDate)) {
            $endDate = null;
        }

        // VALIDATION: Check for existing attendance or permission in range
        $startDateObj = new \DateTime($date);
        $endDateObj = $endDate ? new \DateTime($endDate) : new \DateTime($date);

        if ($startDateObj > $endDateObj) {
             session()->setFlashdata('error', 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.');
             return redirect()->back()->withInput();
        }

        $checkDate = clone $startDateObj;
        $attendanceModel = new \App\Models\AttendanceModel();
        $permissionModel = new \App\Models\PermissionModel();

        while ($checkDate <= $endDateObj) {
            $currentDateStr = $checkDate->format('Y-m-d');
            
            // 1. Check Attendance (Already checked in/out/alpha/etc)
            // We allow re-submitting if status is 'alpha' maybe? But user asked for error if data exists.
            // Let's be strict: if ANY data exists for this date, block it.
            $existsAtt = $attendanceModel->where('user_id', $student['user_id'])
                                         ->where('date', $currentDateStr)
                                         ->first();
            
            if ($existsAtt) {
                session()->setFlashdata('error', "Anda sudah memiliki data absensi pada tanggal {$currentDateStr}.");
                return redirect()->back()->withInput();
            }

            // 2. Check Pending/Approved Permission
            $overlappingPerm = $permissionModel
                ->where('student_id', $student['id'])
                ->whereIn('approval_status', ['pending', 'approved'])
                ->groupStart()
                    ->where('date <=', $currentDateStr)
                    ->groupStart()
                        ->where('end_date >=', $currentDateStr)
                        ->orGroupStart()
                            ->where('end_date', null)
                            ->where('date', $currentDateStr)
                        ->groupEnd()
                    ->groupEnd()
                ->groupEnd()
                ->first();

            if ($overlappingPerm) {
                log_message('error', 'Overlap detected: ' . $currentDateStr);
                session()->setFlashdata('error', "Anda sudah mengajukan izin untuk tanggal {$currentDateStr}.");
                return redirect()->back()->withInput();
            }

            $checkDate->modify('+1 day');
        }

        $permissionModel = new \App\Models\PermissionModel();
        $permissionModel->insert([
            'student_id' => $student['id'],
            'date' => $date,
            'end_date' => $endDate,
            'status' => $this->request->getPost('status'),
            'reason' => $this->request->getPost('reason'),
            'evidence' => $fileName,
            'approval_status' => 'pending'
        ]);

        session()->setFlashdata('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan wali kelas.');
        log_message('info', 'Permission submitted successfully for student: ' . $student['id']);

        // NOTIFICATION: Send WhatsApp to Wali Kelas
        if ($student['wali_kelas_id']) {
            $waliKelas = $this->walikelasModel->find($student['wali_kelas_id']);
            if ($waliKelas && $waliKelas['teacher_id']) {
                $teacherModel = new TeacherModel();
                $teacher = $teacherModel->find($waliKelas['teacher_id']);
                
                if ($teacher && !empty($teacher['phone_number'])) {
                    $waModel = new WhatsAppNotificationModel();
                    $templateModel = new NotificationTemplateModel();
                    $template = $templateModel->getTemplate('wa_permission_submitted_to_teacher');

                    $message = "";
                    $dateRange = date('d/m/Y', strtotime($date)) . ($endDate ? ' s/d ' . date('d/m/Y', strtotime($endDate)) : '');

                    if ($template) {
                        $variables = [
                            '{name}' => $student['full_name'],
                            '{class}' => $waliKelas['class_name'],
                            '{date}' => $dateRange,
                            '{status}' => ucfirst($this->request->getPost('status')),
                            '{reason}' => $this->request->getPost('reason')
                        ];
                        $message = \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $variables);
                    } else {
                        // Fallback Hardcoded
                        $message = "📢 *Pengajuan Izin Baru*\n\n" .
                                   "Siswa: {$student['full_name']}\n" .
                                   "Kelas: {$waliKelas['class_name']}\n" .
                                   "Tanggal: {$dateRange}\n" .
                                   "Ket: " . ucfirst($this->request->getPost('status')) . "\n" .
                                   "Alasan: " . $this->request->getPost('reason') . "\n\n" .
                                   "Mohon cek dashboard guru untuk persetujuan.";
                    }

                    $waModel->insert([
                        'phone_number' => $teacher['phone_number'],
                        'message' => $message,
                        'status' => 'pending',
                        'recipient_type' => 'individual'
                    ]);
                }
            }
        }

        return redirect()->to('/siswa/permission');
    }
}
