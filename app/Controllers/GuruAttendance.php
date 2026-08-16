<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\WalikelasModel;
use App\Models\TeacherModel;
use App\Helpers\TelegramHelper;

use App\Models\NotificationTemplateModel;
use App\Models\WhatsAppNotificationModel;
use App\Models\TelegramNotificationModel;
use App\Models\AndroidNotificationModel;
use App\Models\StudentDeviceTokenModel;
use App\Models\SettingsModel;
use CodeIgniter\Controller;

class GuruAttendance extends Controller
{
    protected $studentModel;
    protected $attendanceModel;
    protected $walikelasModel;
    protected $teacherModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->attendanceModel = new AttendanceModel();
        $this->walikelasModel = new WalikelasModel();
        $this->teacherModel = new TeacherModel();
    }

    /**
     * List today's attendance for wali kelas
     */
    public function index()
    {
        // Check if logged in and is guru
        if (! session()->get('isLoggedIn') || session()->get('role') !== 'guru') {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();

        if (! $teacher) {
            return redirect()->to('/guru/dashboard')->with('error', 'Data guru tidak ditemukan');
        }

        // Get wali kelas for this teacher
        $waliKelas = $this->walikelasModel->getByTeacherId($teacher['id']);

        if (! $waliKelas) {
            return redirect()->to('/guru/dashboard')->with('error', 'Anda tidak ditugaskan sebagai wali kelas');
        }

        // Get date from query parameter, default to today
        $selectedDate = $this->request->getGet('date');
        if ($selectedDate && $this->isValidDate($selectedDate)) {
            $today = $selectedDate;
        } else {
            $today = date('Y-m-d');
        }

        $students = $this->studentModel->where('wali_kelas_id', $waliKelas['id'])->findAll();

        // Get today's attendance for all students in class
        $todayAttendance = [];
        if (!empty($students)) {
            $todayAttendance = $this->attendanceModel
                ->where('date', $today)
                ->whereIn('user_id', array_column($students, 'user_id'))
                ->findAll();
        }

        // Create attendance map
        $attendanceMap = [];
        foreach ($todayAttendance as $att) {
            $attendanceMap[$att['user_id']] = $att;
        }

        // Prepare students with attendance status
        $studentsWithAttendance = [];
        foreach ($students as $student) {
            $student['attendance'] = $attendanceMap[$student['user_id']] ?? null;
            $student['masuk_status'] = $student['attendance']['masuk_status'] ?? 'unknown';
            $studentsWithAttendance[] = $student;
        }

        $data = [
            'title' => 'Absensi Harian',
            'waliKelas' => $waliKelas,
            'students' => $studentsWithAttendance,
            'today' => $today,
            'stats' => $this->calculateStats($todayAttendance),
        ];

        return view('guru/attendance/index', $data);
    }

    /**
     * Mark attendance for a student
     */
    public function markStudent()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['ok' => false, 'message' => 'Method not allowed']);
        }

        // Check authorization
        if (! session()->get('isLoggedIn') || session()->get('role') !== 'guru') {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'message' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();

        if (! $teacher) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Guru tidak ditemukan']);
        }

        $waliKelas = $this->walikelasModel->getByTeacherId($teacher['id']);

        if (! $waliKelas) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Anda bukan wali kelas']);
        }

        $studentId = $this->request->getPost('student_id');
        $status = $this->request->getPost('status');
        $date = $this->request->getPost('date') ?? date('Y-m-d');

        // Validate status
        $validStatuses = ['on_time', 'late', 'izin', 'sakit', 'alpha', 'unknown'];
        if (! in_array($status, $validStatuses)) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Status tidak valid']);
        }

        // Verify student belongs to this wali kelas
        $student = $this->studentModel->find($studentId);
        if (! $student || $student['wali_kelas_id'] != $waliKelas['id']) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Siswa tidak ditemukan di kelas Anda']);
        }

        // Get or create attendance record
        $existingAttendance = $this->attendanceModel
            ->where('user_id', $student['user_id'])
            ->where('date', $date)
            ->first();

        $attendanceData = [
            'user_id' => $student['user_id'],
            'date' => $date,
            'masuk_status' => $status,
            'masuk_at' => $status !== 'unknown' ? date('Y-m-d H:i:s') : null,
            'device_id' => 'manual_guru',
            'created_by' => $userId,
        ];

        if ($existingAttendance) {
            $this->attendanceModel->update($existingAttendance['id'], $attendanceData);
            $attendanceId = $existingAttendance['id'];
        } else {
            $attendanceId = $this->attendanceModel->insert($attendanceData);
        }

            // Send Notification (Telegram, WA, Android) - Unified Logic
            $this->sendManualNotification($student['user_id'], $date, 'manual_guru', $status, date('H:i:s'));
            
            // Legacy direct call removed in favor of unified method above
            // TelegramHelper::sendAttendanceNotification(...) 

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Absensi berhasil diupdate',
            'attendance' => $attendanceData,
            'csrf_token' => csrf_hash(),
        ]);
    }

    /**
     * Get history of a student's attendance
     */
    public function studentHistory($studentId)
    {
        // Check authorization
        if (! session()->get('isLoggedIn') || session()->get('role') !== 'guru') {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $teacher = $this->teacherModel->where('user_id', $userId)->first();
        $waliKelas = $this->walikelasModel->getByTeacherId($teacher['id']);

        if (! $waliKelas) {
            return redirect()->to('/guru/dashboard')->with('error', 'Anda bukan wali kelas');
        }

        $student = $this->studentModel->find($studentId);
        if (! $student || $student['wali_kelas_id'] != $waliKelas['id']) {
            return redirect()->to('/guru/attendance')->with('error', 'Siswa tidak ditemukan');
        }

        // Get attendance history (last 30 days)
        $dateFrom = date('Y-m-d', strtotime('-30 days'));
        $attendanceHistory = $this->attendanceModel
            ->where('user_id', $student['user_id'])
            ->where('date >=', $dateFrom)
            ->orderBy('date', 'DESC')
            ->findAll();

        // Calculate stats
        $stats = [
            'total' => count($attendanceHistory),
            'on_time' => 0,
            'late' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
        ];

        foreach ($attendanceHistory as $att) {
            if (isset($stats[$att['masuk_status']])) {
                $stats[$att['masuk_status']]++;
            }
        }

        $data = [
            'title' => 'Riwayat Absensi Siswa',
            'student' => $student,
            'attendanceHistory' => $attendanceHistory,
            'stats' => $stats,
            'waliKelas' => $waliKelas,
        ];

        return view('guru/attendance/student_history', $data);
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateStats($attendance)
    {
        $stats = [
            'total' => count($attendance),
            'on_time' => 0,
            'late' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'unknown' => 0,
        ];

        foreach ($attendance as $att) {
            $status = $att['masuk_status'];
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        return $stats;
    }

    /**
     * Validate if string is valid date format YYYY-MM-DD
     */
    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Send notifications for manual attendance update (Ported from AdminAttendance)
     */
    private function sendManualNotification($userId, $date, $type, $status, $time)
    {
        // Import necessary models/helpers manually if not in use already
        $studentModel = new StudentModel();
        // Join walikelas to get wa_group_id
        $student = $studentModel->select('students.*, walikelas.wa_group_id')
            ->join('walikelas', 'walikelas.id = students.wali_kelas_id', 'left')
            ->where('user_id', $userId)
            ->first();
        
        $templateModel = new NotificationTemplateModel();
        $telegramModel = new TelegramNotificationModel();
        $waModel = new WhatsAppNotificationModel();
        $settingsModel = new SettingsModel();

        if ($student) {
            // SISWA NOTIFICATIONS
            $data = [
                'name' => $student['full_name'],
                'time' => substr($time, 0, 5),
                'status_label' => \App\Helpers\NotificationTemplateHelper::getStatusLabel($status, 'text'),
                'date' => date('d/m/Y', strtotime($date)),
                'type' => ucfirst(str_replace('_', ' ', $type)) 
            ];

            // Telegram
            if (!empty($student['telegram_chat_id'])) {
                $template = $templateModel->getTemplate('tele_manual_update');
                if ($template) {
                     $message = \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data);
                     $telegramModel->insert([
                        'student_id' => $student['id'],
                        'chat_id' => $student['telegram_chat_id'],
                        'message' => $message,
                        'payload' => json_encode(['mode' => 'manual_update', 'status' => $status, 'time' => $time]),
                        'status' => 'pending',
                        'attempts' => 0,
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    TelegramHelper::sendAttendanceNotification(
                        $student['id'],
                        $student['telegram_chat_id'],
                        $student['full_name'],
                        'manual_update',
                        $status,
                        substr($time, 0, 5)
                    );
                }
            }

            // WhatsApp Notification Logic
            $target = $settingsModel->getSetting('wa_notification_target') ?? 'guardian'; // guardian, group, both

            // 1. Send to Guardian (Individual) if target is 'guardian' or 'both'
            if (($target === 'guardian' || $target === 'both') && !empty($student['guardian_phone'])) {
                $template = $templateModel->getTemplate('wa_manual_update_guardian');
                $message = $template 
                    ? \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data)
                    : "📱 Absensi " . ucfirst($type) . " Siswa (Manual)\nNama: {$student['full_name']}\nJam: " . substr($time, 0, 5) . "\nStatus: " . \App\Helpers\NotificationTemplateHelper::getStatusLabel($status);
                
                $waModel->insert([
                    'student_id' => $student['id'],
                    'phone_number' => $student['guardian_phone'],
                    'message' => $message,
                    'payload' => json_encode(['type' => $type, 'status' => $status, 'time' => substr($time, 0, 5), 'date' => $date, 'recipient' => 'guardian', 'recipient_type' => 'individual']),
                    'status' => 'pending',
                    'scheduled_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // 2. Send to Class Group if target is 'group' or 'both'
            if (($target === 'group' || $target === 'both') && !empty($student['wa_group_id'])) {
                $template = $templateModel->getTemplate('wa_manual_update_group');
                $message = $template 
                    ? \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data)
                    : "📝 Absensi Manual (Group)\n\nSiswa: *{$student['full_name']}*\nInfo: " . ucfirst($type) . "\nStatus: " . \App\Helpers\NotificationTemplateHelper::getStatusLabel($status);

                $waModel->insert([
                    'student_id' => $student['id'],
                    'phone_number' => $student['wa_group_id'], // Group JID
                    'message' => $message,
                    'payload' => json_encode(['type' => $type, 'status' => $status, 'time' => substr($time, 0, 5), 'date' => $date, 'recipient' => 'group', 'recipient_type' => 'group']),
                    'status' => 'pending',
                    'scheduled_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Android Push
            $tokenModel = new StudentDeviceTokenModel();
            $npsn = getenv('SCHOOL_NPSN');
            $deviceTokens = $tokenModel->getActiveTokensByStudent($student['id'], $npsn);
            
            if (!empty($deviceTokens)) {
                $androidModel = new AndroidNotificationModel();
                $androidTitle = 'Absensi ' . ucfirst(str_replace('_', ' ', $type));
                $androidMessage = "Nama: {$student['full_name']}\nJam: " . substr($time, 0, 5) . "\nStatus: " . ucfirst(str_replace('_', ' ', $status));

                $payload = [
                    'nis' => $student['nis'],
                    'npsn' => $npsn,
                    'title' => $androidTitle,
                    'message' => $androidMessage,
                    'data' => [
                        'type' => $type,
                        'status' => $status,
                        'tanggal' => $date,
                        'jam' => $time, // full H:i:s
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
        }
    }
}
