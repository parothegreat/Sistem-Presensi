<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AttendanceModel;
use App\Models\StudentModel;
use App\Models\UserModel;
use Config\Database;
use App\Models\WhatsAppNotificationModel;
use App\Models\TelegramNotificationModel;
use App\Models\AndroidNotificationModel;
use App\Models\StudentDeviceTokenModel;
use App\Helpers\TelegramHelper;
use App\Models\TeacherModel;
use App\Models\NotificationTemplateModel;
use App\Helpers\NotificationTemplateHelper;

class AdminAttendance extends Controller
{
    protected $studentModel;
    protected $attendanceModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->attendanceModel = new AttendanceModel();
    }

    /**
     * Daily attendance page for admin - all classes
     */
    public function index()
    {
        $today = date('Y-m-d');
        $selectedClass = $this->request->getGet('class') ?? null;

        // Get all students or filtered by class
        $query = $this->studentModel;
        if ($selectedClass) {
            $query = $query->where('class', $selectedClass);
        }
        $students = $query->orderBy('class', 'ASC')->orderBy('full_name', 'ASC')->findAll();

        // Get today's attendance for all students
        $todayAttendance = $this->attendanceModel
            ->where('date', $today)
            ->whereIn('user_id', array_column($students, 'user_id'))
            ->findAll();

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

        // Get unique classes for filter
        $classes = $this->studentModel->distinct()
            ->select('class')
            ->orderBy('class', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Absensi Harian',
            'students' => $studentsWithAttendance,
            'classes' => $classes,
            'today' => $today,
            'selectedClass' => $selectedClass,
            'stats' => $this->calculateStats($todayAttendance),
        ];

        return view('admin/attendance/index', $data);
    }

    /**
     * Attendance report page
     */
    public function report()
    {
        $attModel = new AttendanceModel();
        $studentModel = new StudentModel();

        // Get filter params
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-d', strtotime('-7 days'));
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');
        $class = $this->request->getGet('class') ?? null;
        $search = $this->request->getGet('search') ?? null;

        // Query builder - only show attendance with valid student records
        $query = $attModel->select('attendances.*, students.nis, students.full_name, students.class')
            ->join('students', 'attendances.user_id = students.user_id', 'inner')
            ->where('attendances.date >=', $dateFrom)
            ->where('attendances.date <=', $dateTo);

        if ($class) {
            $query->where('students.class', $class);
        }

        if ($search) {
            $query->groupStart()
                ->like('students.nis', $search)
                ->orLike('students.full_name', $search)
                ->groupEnd();
        }

        $attendance = $query->orderBy('students.class', 'ASC')
            ->orderBy('students.full_name', 'ASC')
            ->findAll();

        // Get unique classes for filter
        $classes = $studentModel->distinct()
            ->select('class')
            ->orderBy('class', 'ASC')
            ->findAll();

        // Calculate stats
        $stats = [
            'total' => count($attendance),
            'on_time' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'on_time')),
            'late' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'late')),
            'izin' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'izin')),
            'sakit' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'sakit')),
            'alpha' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'alpha')),
        ];

        return view('admin/attendance/report', [
            'title' => 'Laporan Absensi',
            'attendance' => $attendance,
            'classes' => $classes,
            'stats' => $stats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'class' => $class,
            'search' => $search,
        ]);
    }

    /**
     * Rekap attendance report - siswa per row, tanggal per column
     */
    public function rekap()
    {
        $studentModel = new StudentModel();
        $attModel = new AttendanceModel();

        // Get filter params
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01'); // first day of current month
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');
        $class = $this->request->getGet('class');

        // Get students
        $query = $studentModel->select('students.*, users.username')
            ->join('users', 'students.user_id = users.id', 'left')
            ->orderBy('students.class', 'ASC')
            ->orderBy('students.full_name', 'ASC');

        if ($class) {
            $query->where('students.class', $class);
        }

        $students = $query->findAll();

        // Generate date range
        $dates = [];
        $current = strtotime($dateFrom);
        $end = strtotime($dateTo);
        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        // Get all attendance records for the period
        $attendanceRecords = $attModel
            ->where('date >=', $dateFrom)
            ->where('date <=', $dateTo)
            ->findAll();

        // Map attendance by user_id and date
        $attendanceMap = [];
        foreach ($attendanceRecords as $att) {
            $attendanceMap[$att['user_id']][$att['date']] = $att;
        }

        // Get unique classes for filter
        $classes = $studentModel->distinct()
            ->select('class')
            ->orderBy('class', 'ASC')
            ->findAll();

        return view('admin/attendance/rekap', [
            'title' => 'Rekap Absensi',
            'students' => $students,
            'dates' => $dates,
            'attendanceMap' => $attendanceMap,
            'classes' => $classes,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'class' => $class,
        ]);
    }

    /**
     * Edit attendance page
     */
    public function edit($id)
    {
        $attModel = new AttendanceModel();
        $attendance = $attModel->select('attendances.*, users.username, students.nis, students.full_name, students.class')
            ->join('users', 'attendances.user_id = users.id', 'left')
            ->join('students', 'users.id = students.user_id', 'left')
            ->where('attendances.id', $id)
            ->first();

        if (!$attendance) {
            return redirect()->to('/admin/attendance')->with('error', 'Absensi tidak ditemukan');
        }

        return view('admin/attendance/edit', [
            'title' => 'Edit Absensi',
            'attendance' => $attendance,
        ]);
    }

    public function update($id)
    {
        $attModel = new AttendanceModel();
        $attendance = $attModel->find($id);

        if (!$attendance) {
            return redirect()->to('/admin/attendance')->with('error', 'Absensi tidak ditemukan');
        }

        try {
            $data = $this->request->getPost();
            $updates = [];

            // Update masuk_status and masuk_at
            if (isset($data['masuk_status']) && $data['masuk_status']) {
                $updates['masuk_status'] = $data['masuk_status'];
            }
            if (isset($data['masuk_at']) && $data['masuk_at']) {
                $updates['masuk_at'] = date('Y-m-d H:i:s', strtotime($data['masuk_at']));
            }

            // Update pulang_status and pulang_at
            if (isset($data['pulang_status']) && $data['pulang_status']) {
                $updates['pulang_status'] = $data['pulang_status'];
            }
            if (isset($data['pulang_at']) && $data['pulang_at']) {
                $updates['pulang_at'] = date('Y-m-d H:i:s', strtotime($data['pulang_at']));
            }

            // Update note (keterangan)
            if (isset($data['note'])) {
                $updates['note'] = $data['note'];
            }

            // Only add update tracking if there are actual updates
            if (!empty($updates)) {
                $updates['updated_by'] = session()->get('user_id') ?? null;
                $updates['updated_at'] = date('Y-m-d H:i:s');

                $attModel->update($id, $updates);

                // Send Notifications
                if (isset($updates['masuk_status']) && $updates['masuk_status'] && $updates['masuk_status'] !== 'unknown') {
                    $time = $updates['masuk_at'] ?? ($attendance['masuk_at'] ? date('Y-m-d H:i:s', strtotime($attendance['masuk_at'])) : date('Y-m-d H:i:s'));
                    $this->sendManualNotification($attendance['user_id'], $attendance['date'], 'masuk', $updates['masuk_status'], date('H:i:s', strtotime($time)));
                }

                if (isset($updates['pulang_status']) && $updates['pulang_status'] && $updates['pulang_status'] !== 'unknown') {
                    $time = $updates['pulang_at'] ?? ($attendance['pulang_at'] ? date('Y-m-d H:i:s', strtotime($attendance['pulang_at'])) : date('Y-m-d H:i:s'));
                    $this->sendManualNotification($attendance['user_id'], $attendance['date'], 'pulang', $updates['pulang_status'], date('H:i:s', strtotime($time)));
                }

                log_message('info', "AdminAttendance: Updated attendance ID {$id} by user " . session()->get('user_id'));
                return redirect()->to('/admin/attendance')->with('success', 'Absensi berhasil diperbarui');
            } else {
                log_message('warning', "AdminAttendance: No updates provided for attendance ID {$id}");
                return redirect()->to('/admin/attendance')->with('warning', 'Tidak ada data yang diubah');
            }
        } catch (\Exception $e) {
            log_message('error', "AdminAttendance: Error updating attendance - " . $e->getMessage());
            return redirect()->to('/admin/attendance')->with('error', 'Gagal mengupdate absensi: ' . $e->getMessage());
        }
    }

    /**
     * Edit guru attendance page
     */
    public function editGuru($id)
    {
        $attModel = new AttendanceModel();
        // Join with Teachers table instead of Students
        $attendance = $attModel->select('attendances.*, users.username, teachers.full_name')
            ->join('users', 'attendances.user_id = users.id', 'left')
            ->join('teachers', 'users.id = teachers.user_id', 'left')
            ->where('attendances.id', $id)
            ->first();

        if (!$attendance) {
            return redirect()->to('/admin/attendance/insert-guru')->with('error', 'Absensi tidak ditemukan');
        }

        return view('admin/attendance/edit_guru', [
            'title' => 'Edit Absensi Guru',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Update guru attendance
     */
    public function updateGuru($id)
    {
        $attModel = new AttendanceModel();
        $attendance = $attModel->find($id);

        if (!$attendance) {
            return redirect()->to('/admin/attendance/insert-guru')->with('error', 'Absensi tidak ditemukan');
        }

        try {
            $data = $this->request->getPost();
            $updates = [];

            // Update masuk_status and masuk_at
            if (isset($data['masuk_status']) && $data['masuk_status']) {
                $updates['masuk_status'] = $data['masuk_status'];
            }
            if (isset($data['masuk_at']) && $data['masuk_at']) {
                $updates['masuk_at'] = date('Y-m-d H:i:s', strtotime($data['masuk_at']));
            }

            // Update pulang_status and pulang_at
            if (isset($data['pulang_status']) && $data['pulang_status']) {
                $updates['pulang_status'] = $data['pulang_status'];
            }
            if (isset($data['pulang_at']) && $data['pulang_at']) {
                $updates['pulang_at'] = date('Y-m-d H:i:s', strtotime($data['pulang_at']));
            }

            // Update note (keterangan)
            if (isset($data['note'])) {
                $updates['note'] = $data['note'];
            }

            // Only add update tracking if there are actual updates
            if (!empty($updates)) {
                $updates['updated_by'] = session()->get('user_id') ?? null;
                $updates['updated_at'] = date('Y-m-d H:i:s');

                $attModel->update($id, $updates);
                
                // --- TEACHER MANUAL UPDATE NOTIFICATION ---
                // We need to fetch teacher data to get phone number
                $userModel = new UserModel();
                $teacherModel = new TeacherModel();
                $user = $userModel->find($attendance['user_id']);
                $teacher = $teacherModel->where('user_id', $attendance['user_id'])->first();

                if ($teacher) {
                     $waModel = new WhatsAppNotificationModel();
                     $templateModel = new NotificationTemplateModel();
                     
                     // 1. Checkin Notification (if changed)
                     if (isset($updates['masuk_status']) && $updates['masuk_status'] && $updates['masuk_status'] !== 'unknown') {
                         if (!empty($teacher['phone_number'])) {
                             $template = $templateModel->getTemplate('wa_teacher_checkin');
                             $time = $updates['masuk_at'] ? date('H:i:s', strtotime($updates['masuk_at'])) : date('H:i:s');
                             $data = [
                                'name' => $teacher['full_name'],
                                'time' => $time,
                                'date' => date('d/m/Y', strtotime($attendance['date'])),
                                'status' => ucfirst($updates['masuk_status'])
                             ];
                             
                             $message = $template 
                                ? NotificationTemplateHelper::replaceVariables($template['content'], $data)
                                : "🔔 Presensi Masuk (Manual)\nHalo {$teacher['full_name']}, Absen MASUK Anda.";

                             $waModel->insert([
                                'phone_number' => $teacher['phone_number'],
                                'message' => $message,
                                'status' => 'pending',
                                'recipient_type' => 'individual'
                             ]);
                         }
                     }

                     // 2. Checkout Notification (if changed)
                     if (isset($updates['pulang_status']) && $updates['pulang_status'] && $updates['pulang_status'] !== 'unknown') {
                         if (!empty($teacher['phone_number'])) {
                             $template = $templateModel->getTemplate('wa_teacher_checkout');
                             $time = $updates['pulang_at'] ? date('H:i:s', strtotime($updates['pulang_at'])) : date('H:i:s');
                             $data = [
                                'name' => $teacher['full_name'],
                                'time' => $time,
                                'date' => date('d/m/Y', strtotime($attendance['date'])),
                                'status' => ucfirst($updates['pulang_status'])
                             ];
                             
                             $message = $template 
                                ? NotificationTemplateHelper::replaceVariables($template['content'], $data)
                                : "🔔 Presensi Pulang (Manual)\nHalo {$teacher['full_name']}, Absen PULANG Anda.";

                             $waModel->insert([
                                'phone_number' => $teacher['phone_number'],
                                'message' => $message,
                                'status' => 'pending',
                                'recipient_type' => 'individual'
                             ]);
                         }
                     }
                }
                // --- END NOTIFICATION ---

                log_message('info', "AdminAttendance: Updated guru attendance ID {$id} by user " . session()->get('user_id'));
                return redirect()->to('/admin/attendance/insert-guru')->with('success', 'Absensi guru berhasil diperbarui');
            } else {
                return redirect()->to('/admin/attendance/insert-guru')->with('warning', 'Tidak ada data yang diubah');
            }
        } catch (\Exception $e) {
            log_message('error', "AdminAttendance: Error updating guru attendance - " . $e->getMessage());
            return redirect()->to('/admin/attendance/insert-guru')->with('error', 'Gagal mengupdate absensi: ' . $e->getMessage());
        }
    }

    /**
     * Insert Guru Attendance - Daily attendance for teachers
     */
    public function insertGuru()
    {
        $userModel = new UserModel();
        $teacherModel = new \App\Models\TeacherModel();

        // Get today's date
        $today = date('Y-m-d');

        // Get all teachers (users with role='guru')
        $teachers = $userModel->select('users.id, users.username, teachers.full_name')
            ->join('teachers', 'users.id = teachers.user_id', 'left')
            ->where('users.role', 'guru')
            ->orderBy('teachers.full_name', 'ASC')
            ->findAll();

        // Get today's attendance for all teachers
        $todayAttendance = $this->attendanceModel
            ->where('date', $today)
            ->whereIn('user_id', array_column($teachers, 'id'))
            ->findAll();

        // Create attendance map
        $attendanceMap = [];
        foreach ($todayAttendance as $att) {
            $attendanceMap[$att['user_id']] = $att;
        }

        // Prepare teachers with attendance status
        $teachersWithAttendance = [];
        foreach ($teachers as $teacher) {
            $teacher['attendance'] = $attendanceMap[$teacher['id']] ?? null;
            $teacher['masuk_status'] = $teacher['attendance']['masuk_status'] ?? 'unknown';
            $teachersWithAttendance[] = $teacher;
        }

        $data = [
            'title' => 'Insert Absensi Guru',
            'teachers' => $teachersWithAttendance,
            'today' => $today,
            'stats' => $this->calculateStats($todayAttendance),
        ];

        return view('admin/attendance/insert_guru', $data);
    }

    /**
     * Guru Riwayat - Attendance history for teachers
     */
    public function guruRiwayat()
    {
        $attModel = new AttendanceModel();
        $userModel = new UserModel();

        // Get filter params
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-d', strtotime('-7 days'));
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');
        $search = $this->request->getGet('search') ?? null;

        // Query attendance for teachers (join with users and teachers table) - only show attendance with valid teacher records
        $query = $attModel->select('attendances.*, users.username, teachers.full_name')
            ->join('users', 'attendances.user_id = users.id', 'inner')
            ->join('teachers', 'users.id = teachers.user_id', 'inner')
            ->where('users.role', 'guru')
            ->where('attendances.date >=', $dateFrom)
            ->where('attendances.date <=', $dateTo);

        if ($search) {
            $query->groupStart()
                ->like('users.username', $search)
                ->orLike('teachers.full_name', $search)
                ->groupEnd();
        }

        $attendance = $query->orderBy('teachers.full_name', 'ASC')
            ->orderBy('attendances.date', 'DESC')
            ->findAll();

        // Calculate stats
        $stats = [
            'total' => count($attendance),
            'on_time' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'on_time')),
            'late' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'late')),
            'izin' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'izin')),
            'sakit' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'sakit')),
            'alpha' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'alpha')),
        ];

        return view('admin/attendance/guru_riwayat', [
            'title' => 'Riwayat Absensi Guru',
            'attendance' => $attendance,
            'stats' => $stats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
        ]);
    }

    /**
     * Guru Rekap - Attendance summary for teachers
     */
    public function guruRekap()
    {
        $attModel = new AttendanceModel();
        $userModel = new UserModel();

        // Get filter params
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01'); // first day of current month
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

        // Get all teachers with their full names from teachers table
        $db = Database::connect();
        $teachers = $db->table('teachers')
            ->select('users.id, users.username, teachers.full_name')
            ->join('users', 'teachers.user_id = users.id', 'left')
            ->where('users.role', 'guru')
            ->orderBy('teachers.full_name', 'ASC')
            ->get()
            ->getResultArray();

        // Generate date range
        $dates = [];
        $current = strtotime($dateFrom);
        $end = strtotime($dateTo);
        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        // Get all attendance records for teachers in the period
        $attendanceRecords = $attModel
            ->select('attendances.*, users.id as user_id')
            ->join('users', 'attendances.user_id = users.id', 'left')
            ->where('users.role', 'guru')
            ->where('attendances.date >=', $dateFrom)
            ->where('attendances.date <=', $dateTo)
            ->findAll();

        // Map attendance by user_id and date
        $attendanceMap = [];
        foreach ($attendanceRecords as $att) {
            $attendanceMap[$att['user_id']][$att['date']] = $att;
        }

        return view('admin/attendance/guru_rekap', [
            'title' => 'Rekap Absensi Guru',
            'teachers' => $teachers,
            'dates' => $dates,
            'attendanceMap' => $attendanceMap,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Export guru riwayat to Excel
     */
    public function exportGuruRiwayat()
    {
        $attModel = new AttendanceModel();

        // Get filter params (same as guruRiwayat)
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-d', strtotime('-7 days'));
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');
        $search = $this->request->getGet('search') ?? null;

        // Query attendance for teachers
        $query = $attModel->select('attendances.*, users.username, teachers.full_name')
            ->join('users', 'attendances.user_id = users.id', 'left')
            ->join('teachers', 'users.id = teachers.user_id', 'left')
            ->where('users.role', 'guru')
            ->where('attendances.date >=', $dateFrom)
            ->where('attendances.date <=', $dateTo);

        if ($search) {
            $query->groupStart()
                ->like('users.username', $search)
                ->orLike('teachers.full_name', $search)
                ->groupEnd();
        }

        $attendance = $query->orderBy('teachers.full_name', 'ASC')
            ->orderBy('attendances.date', 'DESC')
            ->findAll();

        // Calculate stats
        $stats = [
            'total' => count($attendance),
            'on_time' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'on_time')),
            'late' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'late')),
            'izin' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'izin')),
            'sakit' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'sakit')),
            'alpha' => count(array_filter($attendance, fn($a) => $a['masuk_status'] === 'alpha')),
        ];

        // Generate Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getProperties()
            ->setCreator('Presensi Sekolah')
            ->setTitle('Laporan Absensi Guru')
            ->setSubject('Laporan Absensi Guru');

        $sheet->setTitle('Laporan Absensi');

        // Title and info
        $sheet->setCellValue('A1', 'LAPORAN ABSENSI GURU');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A855F7']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Filter info
        $row = 2;
        $sheet->setCellValue("A{$row}", 'Periode:');
        $sheet->setCellValue("B{$row}", date('d-m-Y', strtotime($dateFrom)) . ' s/d ' . date('d-m-Y', strtotime($dateTo)));
        $row++;
        if ($search) {
            $sheet->setCellValue("A{$row}", 'Pencarian:');
            $sheet->setCellValue("B{$row}", $search);
            $row++;
        }
        $sheet->setCellValue("A{$row}", 'Tanggal Cetak:');
        $sheet->setCellValue("B{$row}", date('d-m-Y H:i:s'));
        $row++;

        // Stats summary
        $row++;
        $sheet->setCellValue("A{$row}", 'RINGKASAN');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
        ]);
        $row++;
        $sheet->setCellValue("A{$row}", 'Total Data:');
        $sheet->setCellValue("B{$row}", $stats['total']);
        $row++;
        $sheet->setCellValue("A{$row}", 'Tepat Waktu:');
        $sheet->setCellValue("B{$row}", $stats['on_time']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('16A34A');
        $row++;
        $sheet->setCellValue("A{$row}", 'Terlambat:');
        $sheet->setCellValue("B{$row}", $stats['late']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('EA580C');
        $row++;
        $sheet->setCellValue("A{$row}", 'Izin:');
        $sheet->setCellValue("B{$row}", $stats['izin']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('2563EB');
        $row++;
        $sheet->setCellValue("A{$row}", 'Sakit:');
        $sheet->setCellValue("B{$row}", $stats['sakit']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('DC2626');
        $row++;
        $sheet->setCellValue("A{$row}", 'Alpha:');
        $sheet->setCellValue("B{$row}", $stats['alpha']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('9333EA');
        $row++;

        // Table header
        $row++;
        $headerRow = $row;
        $headers = ['No', 'Nama Guru', 'Username', 'Tanggal', 'Status Masuk', 'Jam Masuk', 'Status Pulang', 'Jam Pulang', 'Catatan'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A855F7']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $row++;

        // Data rows
        $statusMap = [
            'on_time' => 'Tepat Waktu',
            'late' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
            'unknown' => 'Belum Absen',
        ];

        $no = 1;
        $startDataRow = $row;
        foreach ($attendance as $att) {
            $pulang_status = $att['pulang_status'] ?? null;
            if (!empty($att['pulang_at']) && (empty($pulang_status) || $pulang_status === 'unknown')) {
                $pulang_time = date('H:i:s', strtotime($att['pulang_at']));
                $checkout_time = '15:00:00';
                $pulang_status = ($pulang_time >= $checkout_time) ? 'on_time' : 'early';
            } elseif (empty($att['pulang_at'])) {
                $pulang_status = 'unknown';
            }

            if (!isset($statusMap['early'])) {
                $statusMap['early'] = 'Pulang Awal';
            }

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $att['full_name'] ?? '-');
            $sheet->setCellValue("C{$row}", $att['username'] ?? '-');
            $sheet->setCellValue("D{$row}", date('d-m-Y', strtotime($att['date'])));
            $sheet->setCellValue("E{$row}", $statusMap[$att['masuk_status']] ?? '-');
            $sheet->setCellValue("F{$row}", ($att['masuk_at'] ?? null) ? substr($att['masuk_at'], 11, 5) : '-');
            $sheet->setCellValue("G{$row}", $statusMap[$pulang_status] ?? '-');
            $sheet->setCellValue("H{$row}", ($att['pulang_at'] ?? null) ? substr($att['pulang_at'], 11, 5) : '-');
            $sheet->setCellValue("I{$row}", $att['note'] ?? '-');

            // Color coding
            $masukColor = $this->getStatusColor($att['masuk_status']);
            if ($masukColor) {
                $sheet->getStyle("E{$row}")->getFont()->getColor()->setRGB($masukColor);
            }

            $pulangColor = $this->getStatusColor($pulang_status);
            if ($pulangColor) {
                $sheet->getStyle("G{$row}")->getFont()->getColor()->setRGB($pulangColor);
            }

            $row++;
        }

        // Apply borders to data
        if (!empty($attendance)) {
            $endDataRow = $row - 1;
            $sheet->getStyle("A{$startDataRow}:I{$endDataRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'Laporan_Absensi_Guru_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export guru rekap to Excel
     */
    public function exportGuruRekap()
    {
        $attModel = new AttendanceModel();

        // Get filter params (same as guruRekap)
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

        // Get all teachers
        $db = Database::connect();
        $teachers = $db->table('teachers')
            ->select('users.id, users.username, teachers.full_name')
            ->join('users', 'teachers.user_id = users.id', 'left')
            ->where('users.role', 'guru')
            ->orderBy('teachers.full_name', 'ASC')
            ->get()
            ->getResultArray();

        // Generate date range
        $dates = [];
        $current = strtotime($dateFrom);
        $end = strtotime($dateTo);
        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        // Get all attendance records for teachers
        $attendanceRecords = $attModel
            ->select('attendances.*, users.id as user_id')
            ->join('users', 'attendances.user_id = users.id', 'left')
            ->where('users.role', 'guru')
            ->where('attendances.date >=', $dateFrom)
            ->where('attendances.date <=', $dateTo)
            ->findAll();

        // Map attendance by user_id and date
        $attendanceMap = [];
        foreach ($attendanceRecords as $att) {
            $attendanceMap[$att['user_id']][$att['date']] = $att;
        }

        // Generate Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getProperties()
            ->setCreator('Presensi Sekolah')
            ->setTitle('Rekap Absensi Guru')
            ->setSubject('Rekap Absensi Guru');

        $sheet->setTitle('Rekap Absensi');

        // Title and info
        $sheet->setCellValue('A1', 'REKAP ABSENSI GURU');
        $maxCol = chr(65 + count($dates) + 3); // A + teachers cols + date cols
        $sheet->mergeCells('A1:' . $maxCol . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A855F7']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Filter info
        $row = 2;
        $sheet->setCellValue("A{$row}", 'Periode:');
        $sheet->setCellValue("B{$row}", date('d-m-Y', strtotime($dateFrom)) . ' s/d ' . date('d-m-Y', strtotime($dateTo)));
        $row++;
        $sheet->setCellValue("A{$row}", 'Tanggal Cetak:');
        $sheet->setCellValue("B{$row}", date('d-m-Y H:i:s'));
        $row++;

        // Status legend
        $row++;
        $sheet->setCellValue("A{$row}", 'Keterangan Status: H=Hadir, T=Terlambat, I=Izin, S=Sakit, A=Alpha, -=Belum');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
        ]);
        $row++;

        // Table header
        $row++;
        $headerRow = $row;
        $colIndex = 1; // Start with column A (1)

        // Static columns (A=1, B=2, C=3)
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, 'No');
        $colIndex++;
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, 'Nama Guru');
        $colIndex++;
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, 'Username');
        $colIndex++;

        // Date columns
        $dateStartCol = $colIndex;
        foreach ($dates as $date) {
            $dateDisplay = date('d/m', strtotime($date));
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, $dateDisplay);
            $colIndex++;
        }

        // Summary column
        $lastCol = $colIndex;
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, 'Ringkasan');

        // Style header
        $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);
        $sheet->getStyle("A{$row}:{$endCol}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A855F7']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $row++;

        // Data rows
        $statusMap = [
            'on_time' => 'H',
            'late' => 'T',
            'izin' => 'I',
            'sakit' => 'S',
            'alpha' => 'A',
        ];

        $startDataRow = $row;
        $no = 1;
        foreach ($teachers as $teacher) {
            $colIndex = 1;

            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, $no++);
            $colIndex++;
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, $teacher['full_name']);
            $colIndex++;
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, $teacher['username']);
            $colIndex++;

            $hadir = 0;
            $terlambat = 0;
            $izin = 0;
            $sakit = 0;
            $alpha = 0;

            foreach ($dates as $date) {
                $att = $attendanceMap[$teacher['id']][$date] ?? null;
                $masuk_status = $att['masuk_status'] ?? null;

                $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row;
                if ($masuk_status) {
                    $displayStatus = $statusMap[$masuk_status] ?? '-';
                    $sheet->setCellValue($cellRef, $displayStatus);

                    // Count statuses
                    if ($masuk_status === 'on_time') $hadir++;
                    elseif ($masuk_status === 'late') $terlambat++;
                    elseif ($masuk_status === 'izin') $izin++;
                    elseif ($masuk_status === 'sakit') $sakit++;
                    elseif ($masuk_status === 'alpha') $alpha++;
                } else {
                    $sheet->setCellValue($cellRef, '-');
                }

                // Color based on status
                if ($masuk_status === 'on_time') {
                    $sheet->getStyle($cellRef)->getFont()->getColor()->setRGB('16A34A');
                } elseif ($masuk_status === 'late') {
                    $sheet->getStyle($cellRef)->getFont()->getColor()->setRGB('EA580C');
                } elseif ($masuk_status === 'izin') {
                    $sheet->getStyle($cellRef)->getFont()->getColor()->setRGB('2563EB');
                } elseif ($masuk_status === 'sakit') {
                    $sheet->getStyle($cellRef)->getFont()->getColor()->setRGB('DC2626');
                } elseif ($masuk_status === 'alpha') {
                    $sheet->getStyle($cellRef)->getFont()->getColor()->setRGB('9333EA');
                }

                $colIndex++;
            }

            // Summary
            $summary = "H:{$hadir} T:{$terlambat} I:{$izin} S:{$sakit} A:{$alpha}";
            $summaryRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row;
            $sheet->setCellValue($summaryRef, $summary);
            $sheet->getStyle($summaryRef)->applyFromArray([
                'font' => ['size' => 9],
                'alignment' => ['wrapText' => true],
            ]);

            $row++;
        }

        // Apply borders
        if (!empty($teachers)) {
            $endDataRow = $row - 1;
            $sheet->getStyle("A{$startDataRow}:{$endCol}{$endDataRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        // Auto-fit date columns
        for ($i = 4; $i <= $lastCol; $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth(8);
        }

        // Output
        $filename = 'Rekap_Absensi_Guru_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Store new guru attendance record via AJAX
     */
    public function markGuru()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Method not allowed']);
        }

        $teacherId = $this->request->getPost('teacher_id');
        $status = $this->request->getPost('status');
        $date = $this->request->getPost('date') ?? date('Y-m-d');

        // Validate status
        $validStatuses = ['on_time', 'late', 'izin', 'sakit', 'alpha', 'unknown'];
        if (!in_array($status, $validStatuses)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Status tidak valid']);
        }

        // Verify teacher exists (check in users table with role guru)
        $userModel = new UserModel();
        $user = $userModel->where('id', $teacherId)->where('role', 'guru')->first();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'Guru tidak ditemukan']);
        }

        // Get or create attendance record
        $existingAttendance = $this->attendanceModel
            ->where('user_id', $teacherId)
            ->where('date', $date)
            ->first();

        $masukAt = $status !== 'unknown' ? date('Y-m-d H:i:s') : null;
        $attendanceData = [
            'user_id' => $teacherId,
            'date' => $date,
            'masuk_status' => $status,
            'masuk_at' => $masukAt,
            'device_id' => 'manual_admin',
            'created_by' => session()->get('user_id') ?? null,
        ];

        if ($existingAttendance) {
        $this->attendanceModel->update($existingAttendance['id'], $attendanceData);
        } else {
            $this->attendanceModel->insert($attendanceData);
        }

        // Send Notification (Template-based)
    if ($status !== 'unknown') {
        $teacherModel = new \App\Models\TeacherModel();
        $teacher = $teacherModel->where('user_id', $teacherId)->first();
        
        if ($teacher && !empty($teacher['phone_number'])) {
             $waModel = new \App\Models\WhatsAppNotificationModel();
             $templateModel = new \App\Models\NotificationTemplateModel();
             
             // Since markGuru only sets 'masuk_status', we we treat it as checkin
             // Determine template based on action - but wait, the UI might be strictly for 'masuk' or daily attendance status?
             // Looking at the code: 'masuk_status' => $status. So it's treated as check-in status.
             // If user changes it to Pulang status via some other way, that's different.
             // But here it seems to update `masuk_status`.
             
             $templateCode = 'wa_teacher_checkin';
             $template = $templateModel->getTemplate($templateCode);
             
             $time = $masukAt ? date('H:i:s', strtotime($masukAt)) : date('H:i:s');
             $data = [
                'name' => $teacher['full_name'],
                'time' => $time,
                'date' => date('d/m/Y', strtotime($date)),
                'status' => ucfirst($status)
             ];
             
             $message = $template 
                ? \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data)
                : "🔔 Presensi Masuk (Manual)\nHalo {$teacher['full_name']}, Absen MASUK Anda.";

             $waModel->insert([
                'phone_number' => $teacher['phone_number'],
                'message' => $message,
                'status' => 'pending',
                'recipient_type' => 'individual'
            ]);
        }
    }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Absensi berhasil diupdate',
            'time' => $masukAt ? substr($masukAt, 11, 5) : null,
            'csrf_token' => csrf_hash(),
        ]);
    }

    /**
     * Store new attendance record via AJAX
     */
    public function markStudent()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['ok' => false, 'message' => 'Method not allowed']);
        }

        $studentId = $this->request->getPost('student_id');
        $status = $this->request->getPost('status');
        $date = $this->request->getPost('date') ?? date('Y-m-d');

        // Validate status
        $validStatuses = ['on_time', 'late', 'izin', 'sakit', 'alpha', 'unknown'];
        if (!in_array($status, $validStatuses)) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Status tidak valid']);
        }

        // Verify student exists
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Siswa tidak ditemukan']);
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
            'device_id' => 'manual_admin',
            'created_by' => session()->get('user_id') ?? null,
        ];

        if ($existingAttendance) {
            $this->attendanceModel->update($existingAttendance['id'], $attendanceData);
        } else {
            $this->attendanceModel->insert($attendanceData);
        }

        // Send Notification
        if ($status !== 'unknown') {
            $this->sendManualNotification($student['user_id'], $date, 'masuk', $status, date('H:i:s'));
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Absensi berhasil diupdate',
            'attendance' => $attendanceData,
            'csrf_token' => csrf_hash(),
        ]);
    }

    /**
     * Get current attendance stats via AJAX
     */
    public function getStats()
    {
        $today = date('Y-m-d');
        $selectedClass = $this->request->getGet('class') ?? null;

        // Get all students or filtered by class
        $query = $this->studentModel;
        if ($selectedClass) {
            $query = $query->where('class', $selectedClass);
        }
        $students = $query->findAll();

        // Get today's attendance
        $todayAttendance = $this->attendanceModel
            ->where('date', $today)
            ->whereIn('user_id', array_column($students, 'user_id'))
            ->findAll();

        return $this->response->setJSON([
            'ok' => true,
            'stats' => $this->calculateStats($todayAttendance),
        ]);
    }

    /**
     * Get current guru attendance stats via AJAX
     */
    public function getGuruStats()
    {
        $today = date('Y-m-d');
        $userModel = new UserModel();

        // Get all teachers (users with role='guru')
        $teachers = $userModel->where('role', 'guru')->findAll();
        $teacherIds = array_column($teachers, 'id');

        // Get today's attendance for teachers
        $todayAttendance = $this->attendanceModel
            ->where('date', $today)
            ->whereIn('user_id', $teacherIds)
            ->findAll();

        return $this->response->setJSON([
            'ok' => true,
            'stats' => $this->calculateStats($todayAttendance),
        ]);
    }

    /**
     * Export attendance report to Excel
     */
    public function exportExcel()
    {
        $attModel = new AttendanceModel();
        $studentModel = new StudentModel();

        // Get filter params (same as report)
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-d', strtotime('-7 days'));
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');
        $class = $this->request->getGet('class') ?? null;
        $search = $this->request->getGet('search') ?? null;

        // Query builder
        $query = $attModel->select('attendances.*, students.nis, students.full_name, students.class')
            ->join('students', 'attendances.user_id = students.user_id', 'left')
            ->where('attendances.date >=', $dateFrom)
            ->where('attendances.date <=', $dateTo);

        if ($class) {
            $query->where('students.class', $class);
        }

        if ($search) {
            $query->groupStart()
                ->like('students.nis', $search)
                ->orLike('students.full_name', $search)
                ->groupEnd();
        }

        $attendance = $query->orderBy('attendances.date', 'ASC')
            ->orderBy('students.class', 'ASC')
            ->orderBy('students.full_name', 'ASC')
            ->findAll();

        // Calculate stats
        $stats = $this->calculateStats($attendance);

        // Generate Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getProperties()
            ->setCreator('Presensi Sekolah')
            ->setTitle('Laporan Absensi')
            ->setSubject('Laporan Absensi Siswa');

        $sheet->setTitle('Laporan Absensi');

        // Title and info
        $sheet->setCellValue('A1', 'LAPORAN ABSENSI SISWA');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Filter info
        $row = 2;
        $sheet->setCellValue("A{$row}", 'Periode:');
        $sheet->setCellValue("B{$row}", date('d-m-Y', strtotime($dateFrom)) . ' s/d ' . date('d-m-Y', strtotime($dateTo)));
        $row++;
        if ($class) {
            $sheet->setCellValue("A{$row}", 'Kelas:');
            $sheet->setCellValue("B{$row}", $class);
            $row++;
        }
        if ($search) {
            $sheet->setCellValue("A{$row}", 'Pencarian:');
            $sheet->setCellValue("B{$row}", $search);
            $row++;
        }
        $sheet->setCellValue("A{$row}", 'Tanggal Cetak:');
        $sheet->setCellValue("B{$row}", date('d-m-Y H:i:s'));
        $row++;

        // Stats summary
        $row++;
        $sheet->setCellValue("A{$row}", 'RINGKASAN');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
        ]);
        $row++;
        $sheet->setCellValue("A{$row}", 'Total Data:');
        $sheet->setCellValue("B{$row}", $stats['total']);
        $row++;
        $sheet->setCellValue("A{$row}", 'Tepat Waktu:');
        $sheet->setCellValue("B{$row}", $stats['on_time']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('16A34A');
        $row++;
        $sheet->setCellValue("A{$row}", 'Terlambat:');
        $sheet->setCellValue("B{$row}", $stats['late']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('EA580C');
        $row++;
        $sheet->setCellValue("A{$row}", 'Izin:');
        $sheet->setCellValue("B{$row}", $stats['izin']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('2563EB');
        $row++;
        $sheet->setCellValue("A{$row}", 'Sakit:');
        $sheet->setCellValue("B{$row}", $stats['sakit']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('DC2626');
        $row++;
        $sheet->setCellValue("A{$row}", 'Alpha:');
        $sheet->setCellValue("B{$row}", $stats['alpha']);
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB('9333EA');
        $row++;

        // Table header
        $row++;
        $headerRow = $row;
        $headers = ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Tanggal', 'Status Masuk', 'Jam Masuk', 'Status Pulang', 'Jam Pulang'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $row++;

        // Data rows
        $statusMap = [
            'on_time' => 'Tepat Waktu',
            'late' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
            'unknown' => 'Belum Absen',
        ];

        $no = 1;
        $startDataRow = $row;
        foreach ($attendance as $att) {
            // Calculate pulang_status if pulang_at exists but pulang_status is empty
            $pulang_status = $att['pulang_status'] ?? null;
            if (!empty($att['pulang_at']) && (empty($pulang_status) || $pulang_status === 'unknown')) {
                $pulang_time = date('H:i:s', strtotime($att['pulang_at']));
                $checkout_time = '15:00:00'; // default
                $pulang_status = ($pulang_time >= $checkout_time) ? 'on_time' : 'early';
            } elseif (empty($att['pulang_at'])) {
                $pulang_status = 'unknown';
            }

            // Add 'early' to status map if not exists
            if (!isset($statusMap['early'])) {
                $statusMap['early'] = 'Pulang Awal';
            }

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $att['nis'] ?? '-');
            $sheet->setCellValue("C{$row}", $att['full_name'] ?? '-');
            $sheet->setCellValue("D{$row}", $att['class'] ?? '-');
            $sheet->setCellValue("E{$row}", date('d-m-Y', strtotime($att['date'])));
            $sheet->setCellValue("F{$row}", $statusMap[$att['masuk_status']] ?? '-');
            $sheet->setCellValue("G{$row}", $att['masuk_at'] ? date('H:i', strtotime($att['masuk_at'])) : '-');
            $sheet->setCellValue("H{$row}", $statusMap[$pulang_status] ?? '-');
            $sheet->setCellValue("I{$row}", $att['pulang_at'] ? date('H:i', strtotime($att['pulang_at'])) : '-');

            // Color coding for status masuk
            $masukColor = $this->getStatusColor($att['masuk_status']);
            if ($masukColor) {
                $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB($masukColor);
            }

            // Color coding for status pulang
            $pulangColor = $this->getStatusColor($pulang_status);
            if ($pulangColor) {
                $sheet->getStyle("H{$row}")->getFont()->getColor()->setRGB($pulangColor);
            }

            $row++;
        }

        // Apply borders to data
        if (!empty($attendance)) {
            $endDataRow = $row - 1;
            $sheet->getStyle("A{$startDataRow}:I{$endDataRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'Laporan_Absensi_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Get color code for status
     */
    /**
     * Export attendance rekap to Excel - students in rows, dates in columns
     */
    public function exportExcelRekap()
    {
        $studentModel = new StudentModel();
        $attModel = new AttendanceModel();

        // Get filter params
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');
        $class = $this->request->getGet('class');

        // Get students
        $query = $studentModel->select('students.*, users.username')
            ->join('users', 'students.user_id = users.id', 'left')
            ->orderBy('students.class', 'ASC')
            ->orderBy('students.full_name', 'ASC');

        if ($class) {
            $query->where('students.class', $class);
        }

        $students = $query->findAll();

        // Generate date range
        $dates = [];
        $current = strtotime($dateFrom);
        $end = strtotime($dateTo);
        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        // Get all attendance records
        $attendanceRecords = $attModel
            ->where('date >=', $dateFrom)
            ->where('date <=', $dateTo)
            ->findAll();

        // Map attendance
        $attendanceMap = [];
        foreach ($attendanceRecords as $att) {
            $attendanceMap[$att['user_id']][$att['date']] = $att;
        }

        // Generate Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absensi');

        // Title
        $sheet->setCellValue('A1', 'REKAP ABSENSI SISWA');
        $sheet->mergeCells('A1:' . $this->getColumnLetter(4 + count($dates)) . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Filter info
        $row = 2;
        $sheet->setCellValue("A{$row}", 'Periode: ' . date('d M Y', strtotime($dateFrom)) . ' - ' . date('d M Y', strtotime($dateTo)));
        if ($class) {
            $sheet->setCellValue("A" . ($row + 1), 'Kelas: ' . $class);
            $row++;
        }
        $row += 2;

        // Headers
        $sheet->setCellValue("A{$row}", 'No');
        $sheet->setCellValue("B{$row}", 'NIS');
        $sheet->setCellValue("C{$row}", 'Nama Siswa');
        $sheet->setCellValue("D{$row}", 'Kelas');

        $col = 5; // Column E
        foreach ($dates as $date) {
            $cellCoord = $this->getColumnLetter($col) . $row;
            $sheet->setCellValue($cellCoord, date('d/m', strtotime($date)));
            $col++;
        }

        // Add summary headers
        $summaryCol = $col;
        $sheet->setCellValue($this->getColumnLetter($col) . $row, 'H');
        $col++;
        $sheet->setCellValue($this->getColumnLetter($col) . $row, 'S');
        $col++;
        $sheet->setCellValue($this->getColumnLetter($col) . $row, 'I');
        $col++;
        $sheet->setCellValue($this->getColumnLetter($col) . $row, 'A');
        $col++;
        $sheet->setCellValue($this->getColumnLetter($col) . $row, '%');

        // Style headers
        $headerRange = 'A' . $row . ':' . $this->getColumnLetter($col) . $row;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Data rows
        $row++;
        $startDataRow = $row;
        $no = 1;

        foreach ($students as $student) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $student['nis']);
            $sheet->setCellValue("C{$row}", $student['full_name']);
            $sheet->setCellValue("D{$row}", $student['class']);

            $col = 5;
            foreach ($dates as $date) {
                $att = $attendanceMap[$student['user_id']][$date] ?? null;
                $masuk_status = $att['masuk_status'] ?? null;
                $pulang_status = $att['pulang_status'] ?? null;

                // Helper function for masuk status
                $getStatusAbbr = function ($status) {
                    switch ($status) {
                        case 'on_time':
                            return ['H', '16A34A'];
                        case 'late':
                            return ['T', 'EA580C'];
                        case 'izin':
                            return ['I', '2563EB'];
                        case 'sakit':
                            return ['S', 'DC2626'];
                        case 'alpha':
                            return ['A', '9333EA'];
                        default:
                            return ['-', 'EEEEEE'];
                    }
                };

                // Helper function for pulang status (different from masuk)
                $getPulangAbbr = function ($status) {
                    switch ($status) {
                        case 'on_time':
                            return ['P', '16A34A'];  // P = Pulang Tepat Waktu
                        case 'early':
                            return ['E', '06B6D4'];  // E = Pulang Lebih Awal
                        case 'izin':
                            return ['I', '2563EB'];
                        case 'sakit':
                            return ['S', 'DC2626'];
                        case 'alpha':
                            return ['A', '9333EA'];
                        default:
                            return ['-', 'EEEEEE'];
                    }
                };

                // Get status values - check if pulang_at exists and status is not 'unknown'
                [$masuk_abbr, $masuk_color] = $getStatusAbbr($masuk_status);

                if ($att && $att['pulang_at'] && $pulang_status !== 'unknown') {
                    [$pulang_abbr, $pulang_color] = $getPulangAbbr($pulang_status);
                } else {
                    [$pulang_abbr, $pulang_color] = ['-', 'EEEEEE'];
                }

                // Format: "M/P" (Masuk/Pulang)
                $cellValue = $masuk_abbr . '/' . $pulang_abbr;
                // Use masuk color primarily
                $bgColor = $masuk_color;

                $cellCoord = $this->getColumnLetter($col) . $row;
                $sheet->setCellValue($cellCoord, $cellValue);

                // Apply background color
                $sheet->getStyle($cellCoord)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                ]);

                $col++;
            }

            // Add summary columns
            $hadir = 0;
            $sakit = 0;
            $izin = 0;
            $alpha = 0;
            $hari_efektif = 0;

            foreach ($dates as $date) {
                $att = $attendanceMap[$student['user_id']][$date] ?? null;
                if ($att) {
                    $hari_efektif++;
                    switch ($att['masuk_status']) {
                        case 'on_time':
                        case 'late':
                            $hadir++;
                            break;
                        case 'sakit':
                            $sakit++;
                            break;
                        case 'izin':
                            $izin++;
                            break;
                        case 'alpha':
                            $alpha++;
                            break;
                    }
                }
            }

            // Calculate percentage based on effective days only
            $percentage = $hari_efektif > 0 ? round(($hadir / $hari_efektif) * 100) : 0;

            // Write summary columns
            $sheet->setCellValue($this->getColumnLetter($col) . $row, $hadir);
            $col++;
            $sheet->setCellValue($this->getColumnLetter($col) . $row, $sakit);
            $col++;
            $sheet->setCellValue($this->getColumnLetter($col) . $row, $izin);
            $col++;
            $sheet->setCellValue($this->getColumnLetter($col) . $row, $alpha);
            $col++;
            $sheet->setCellValue($this->getColumnLetter($col) . $row, $percentage . '%');

            $row++;
        }

        // Borders for data
        $finalCol = 4 + count($dates) + 5; // 4 summary columns + 1 percentage
        $dataRange = 'A' . $startDataRow . ':' . $this->getColumnLetter($finalCol) . ($row - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(10);
        for ($i = 5; $i <= (4 + count($dates)); $i++) {
            $sheet->getColumnDimensionByColumn($i)->setWidth(7);
        }
        // Summary columns width
        for ($i = (4 + count($dates) + 1); $i <= $finalCol; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setWidth(6);
        }

        // Legend
        $row += 2;
        $sheet->setCellValue("A{$row}", 'Keterangan:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'Format: Masuk/Pulang');
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
        $row++;

        $legends = [
            ['H', 'Hadir Masuk', '16A34A'],
            ['P', 'Pulang Tepat Waktu', '16A34A'],
            ['T', 'Terlambat', 'EA580C'],
            ['E', 'Pulang Lebih Awal', '06B6D4'],
            ['I', 'Izin', '2563EB'],
            ['S', 'Sakit', 'DC2626'],
            ['A', 'Alpha', '9333EA'],
            ['-', 'Belum', 'EEEEEE'],
        ];

        foreach ($legends as $legend) {
            $sheet->setCellValue("A{$row}", $legend[0]);
            $sheet->setCellValue("B{$row}", $legend[1]);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $legend[2]]],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }

        // Export
        $filename = 'Rekap_Absensi_' . date('Y-m-d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Helper to get column letter from index (1=A, 2=B, etc)
     */
    private function getColumnLetter($index)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
    }

    private function getStatusColor($status)
    {
        $colors = [
            'on_time' => '16A34A',   // green
            'late' => 'EA580C',      // orange
            'early' => 'EAB308',     // yellow (pulang lebih awal)
            'izin' => '2563EB',      // blue
            'sakit' => 'DC2626',     // red
            'alpha' => '9333EA',     // purple
        ];
        return $colors[$status] ?? null;
    }

    /**
     * Send notifications for manual attendance update
     */
    /**
     * Send notifications for manual attendance update
     */
    private function sendManualNotification($userId, $date, $type, $status, $time)
    {
        log_message('info', "sendManualNotification called: UserId=$userId, Date=$date, Type=$type, Status=$status");

        // Import necessary models/helpers manually if not in use already
        $studentModel = new StudentModel();
        // Join walikelas to get wa_group_id
        $student = $studentModel->select('students.*, walikelas.wa_group_id')
            ->join('walikelas', 'walikelas.id = students.wali_kelas_id', 'left')
            ->where('user_id', $userId)
            ->first();
        
        $templateModel = new \App\Models\NotificationTemplateModel();
        $telegramModel = new \App\Models\TelegramNotificationModel();
        $waModel = new \App\Models\WhatsAppNotificationModel();
        $settingsModel = new \App\Models\SettingsModel();

        if ($student) {
            // SISWA NOTIFICATIONS
            $data = [
                'name' => $student['full_name'],
                'time' => substr($time, 0, 5),
                'status_label' => \App\Helpers\NotificationTemplateHelper::getStatusLabel($status, 'text'),
                'date' => date('d/m/Y', strtotime($date)),
                'type' => ucfirst($type) 
            ];

            // Telegram (Unchanged)
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

            /*
            // STUDENT NOTIFICATIONS REMOVED AS REQUESTED
            // Do NOT send to student's personal number.
            */

            // Android Push (Unchanged)
            $tokenModel = new StudentDeviceTokenModel();
            $npsn = getenv('SCHOOL_NPSN');
            $deviceTokens = $tokenModel->getActiveTokensByStudent($student['id'], $npsn);
            
            if (!empty($deviceTokens)) {
                $androidModel = new AndroidNotificationModel();
                $androidTitle = 'Absensi ' . ucfirst($type);
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

        } else {
            // Check if Teacher (Unchanged logic, just ensuring recipient_type)
            $teacherModel = new \App\Models\TeacherModel();
            $teacher = $teacherModel->where('user_id', $userId)->first();
            
            if ($teacher) {
                 // GURU NOTIFICATIONS
                 $data = [
                    'name' => $teacher['full_name'],
                    'time' => substr($time, 0, 5),
                    'status_label' => \App\Helpers\NotificationTemplateHelper::getStatusLabel($status, 'text'),
                    'date' => date('d/m/Y', strtotime($date)),
                    'type' => ucfirst($type) 
                ];

                // Telegram
                if (!empty($teacher['telegram_chat_id'])) {
                    $template = $templateModel->getTemplate('tele_manual_update');
                    $message = $template 
                        ? \App\Helpers\NotificationTemplateHelper::replaceVariables($template['content'], $data)
                        : "📝 Absensi " . ucfirst($type) . " (Manual)\nNama: {$teacher['full_name']}\nJam: " . substr($time, 0, 5) . "\nTanggal: {$date}\nStatus: {$data['status_label']}";
                    
                    $telegramModel->insert([
                        'chat_id' => $teacher['telegram_chat_id'],
                        'message' => $message,
                        'payload' => json_encode(['type' => $type, 'status' => $status, 'time' => substr($time, 0, 5), 'date' => $date, 'recipient' => 'guru']),
                        'status' => 'pending',
                        'attempts' => 0,
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // WhatsApp
                if (!empty($teacher['phone_number'])) {
                     $message = "📱 Absensi " . ucfirst($type) . " Guru (Manual)\nNama: {$teacher['full_name']}\nJam: " . substr($time, 0, 5) . "\nTanggal: {$date}\nStatus: {$data['status_label']}";
                    
                    $waModel->insert([
                        'phone_number' => $teacher['phone_number'],
                        'message' => $message,
                        'payload' => json_encode(['type' => $type, 'status' => $status, 'time' => substr($time, 5), 'date' => $date, 'recipient' => 'guru', 'recipient_type' => 'individual']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
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
}
