<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StudentModel;
use App\Models\AttendanceModel;

class Admin extends Controller
{
    public function dashboard()
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            // Simple 403 response for unauthorized access
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $attModel = new AttendanceModel();
        $studentModel = new StudentModel();
        $userModel = new \App\Models\UserModel();

        // Get current month and year
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Get attendance stats for current month
        $monthStart = $currentYear . '-' . $currentMonth . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        // Query attendance data for the month with user role info
        $db = \Config\Database::connect();
        $query = $db->table('attendances')
            ->select('attendances.date, attendances.masuk_status, attendances.user_id, users.role')
            ->join('users', 'users.id = (SELECT user_id FROM students WHERE user_id = attendances.user_id LIMIT 1) OR users.id = (SELECT user_id FROM teachers WHERE user_id = attendances.user_id LIMIT 1)', 'left')
            ->where('attendances.date >=', $monthStart)
            ->where('attendances.date <=', $monthEnd)
            ->get();

        $attendance = $query->getResultArray();

        // Separate data for students and teachers
        $studentData = [];
        $guruData = [];

        foreach ($attendance as $row) {
            $date = $row['date'];
            $status = $row['masuk_status'] ?? 'unknown';

            // Check if it's a student or teacher
            $user = $userModel->find($row['user_id']);
            $isTeacher = $user && $user['role'] === 'guru';

            if ($isTeacher) {
                if (!isset($guruData[$date])) {
                    $guruData[$date] = ['on_time' => 0, 'late' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'unknown' => 0];
                }
                $guruData[$date][$status]++;
            } else {
                if (!isset($studentData[$date])) {
                    $studentData[$date] = ['on_time' => 0, 'late' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'unknown' => 0];
                }
                $studentData[$date][$status]++;
            }
        }

        // Calculate stats for students
        $studentStats = ['on_time' => 0, 'late' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
        foreach ($studentData as $data) {
            foreach ($studentStats as $status => &$count) {
                $count += $data[$status];
            }
        }

        // Calculate stats for teachers
        $guruStats = ['on_time' => 0, 'late' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
        foreach ($guruData as $data) {
            foreach ($guruStats as $status => &$count) {
                $count += $data[$status];
            }
        }

        // Get basic stats
        $studentCount = $studentModel->countAll();
        $teacherCount = $db->table('teachers')->countAllResults();
        $studentToday = $attModel->select('DISTINCT user_id')->where('date', date('Y-m-d'))->join('users', 'users.id = attendances.user_id')->where('users.role', 'siswa')->countAllResults();
        $guruToday = $attModel->select('DISTINCT user_id')->where('date', date('Y-m-d'))->join('users', 'users.id = attendances.user_id')->where('users.role', 'guru')->countAllResults();

        $stats = [
            'student' => [
                'total' => $studentCount,
                'today' => $studentToday,
                'on_time_month' => $studentStats['on_time'],
                'late_month' => $studentStats['late'],
                'izin_month' => $studentStats['izin'],
                'sakit_month' => $studentStats['sakit'],
                'alpha_month' => $studentStats['alpha'],
            ],
            'guru' => [
                'total' => $teacherCount,
                'today' => $guruToday,
                'on_time_month' => $guruStats['on_time'],
                'late_month' => $guruStats['late'],
                'izin_month' => $guruStats['izin'],
                'sakit_month' => $guruStats['sakit'],
                'alpha_month' => $guruStats['alpha'],
            ]
        ];

        // Prepare chart data for students
        ksort($studentData);
        $studentDates = array_keys($studentData);
        $studentOnTime = [];
        $studentLate = [];
        $studentIzin = [];
        $studentSakit = [];
        $studentAlpha = [];

        foreach ($studentDates as $date) {
            $studentOnTime[] = $studentData[$date]['on_time'];
            $studentLate[] = $studentData[$date]['late'];
            $studentIzin[] = $studentData[$date]['izin'];
            $studentSakit[] = $studentData[$date]['sakit'];
            $studentAlpha[] = $studentData[$date]['alpha'];
        }

        // Prepare chart data for teachers
        ksort($guruData);
        $guruDates = array_keys($guruData);
        $guruOnTime = [];
        $guruLate = [];
        $guruIzin = [];
        $guruSakit = [];
        $guruAlpha = [];

        foreach ($guruDates as $date) {
            $guruOnTime[] = $guruData[$date]['on_time'];
            $guruLate[] = $guruData[$date]['late'];
            $guruIzin[] = $guruData[$date]['izin'];
            $guruSakit[] = $guruData[$date]['sakit'];
            $guruAlpha[] = $guruData[$date]['alpha'];
        }

        // If no data, create sample dates
        if (empty($studentDates)) {
            $daysToShow = min(10, date('j'));
            for ($i = 1; $i <= $daysToShow; $i++) {
                $date = $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $studentDates[] = $date;
                $studentOnTime[] = 0;
                $studentLate[] = 0;
                $studentIzin[] = 0;
                $studentSakit[] = 0;
                $studentAlpha[] = 0;
            }
        }

        if (empty($guruDates)) {
            $daysToShow = min(10, date('j'));
            for ($i = 1; $i <= $daysToShow; $i++) {
                $date = $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $guruDates[] = $date;
                $guruOnTime[] = 0;
                $guruLate[] = 0;
                $guruIzin[] = 0;
                $guruSakit[] = 0;
                $guruAlpha[] = 0;
            }
        }

        return view('admin/dashboard', [
            'stats' => $stats,
            'studentDates' => json_encode($studentDates),
            'studentOnTime' => json_encode($studentOnTime),
            'studentLate' => json_encode($studentLate),
            'studentIzin' => json_encode($studentIzin),
            'studentSakit' => json_encode($studentSakit),
            'studentAlpha' => json_encode($studentAlpha),
            'guruDates' => json_encode($guruDates),
            'guruOnTime' => json_encode($guruOnTime),
            'guruLate' => json_encode($guruLate),
            'guruIzin' => json_encode($guruIzin),
            'guruSakit' => json_encode($guruSakit),
            'guruAlpha' => json_encode($guruAlpha),
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
        ]);
    }

    public function profile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        return view('admin/profile', [
            'username' => $user['username'] ?? '',
            'role' => session()->get('role'),
        ]);
    }

    public function editProfile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        helper('form');

        $data = [
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/profile_edit', $data);
    }

    public function updateProfile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            session()->setFlashdata('error', 'User tidak ditemukan');
            return redirect()->to('/admin/profile');
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]|max_length[255]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            helper('form');
            return view('admin/profile_edit', [
                'validation' => \Config\Services::validation(),
            ]);
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Verify current password
        if (! password_verify($currentPassword, $user['password_hash'])) {
            session()->setFlashdata('error', 'Password saat ini tidak sesuai');
            helper('form');
            return view('admin/profile_edit', [
                'validation' => \Config\Services::validation(),
            ]);
        }

        // Update password
        $data = [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ];

        $userModel->update($userId, $data);

        session()->setFlashdata('success', 'Password berhasil diperbarui');
        return redirect()->to('/admin/profile');
    }
}
