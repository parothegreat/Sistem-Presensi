<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TeacherScheduleModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AdminTeacherSchedule extends BaseController
{
    protected $teacherScheduleModel;
    protected $userModel;

    public function __construct()
    {
        $this->teacherScheduleModel = new TeacherScheduleModel();
        $this->userModel = new UserModel();
    }

    /**
     * List all teacher schedules
     */
    public function index()
    {
        $data = [
            'title' => 'Jadwal Guru & Karyawan',
            'schedules' => $this->teacherScheduleModel->getAllSchedulesWithUsers(),
        ];

        return view('admin/teacher-schedule/index', $data);
    }

    /**
     * Show form to select user (Step 1)
     */
    public function create()
    {
        // Get all users dengan role guru atau karyawan yang sudah punya record di teachers table
        $users = $this->userModel
            ->select('users.id, users.username, users.role, teachers.full_name')
            ->join('teachers', 'teachers.user_id = users.id', 'inner')  // INNER JOIN agar hanya yang punya relasi
            ->whereIn('users.role', ['guru', 'karyawan'])
            ->orderBy('teachers.full_name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Set Jadwal - Pilih Guru/Karyawan',
            'users' => $users,
        ];

        return view('admin/teacher-schedule/select-user', $data);
    }

    /**
     * Show form to set schedule per day (Step 2)
     */
    public function setSchedule($userId = null)
    {
        if (!$userId) {
            return redirect()->back()->with('error', 'User ID tidak valid');
        }

        // Validate user exists and is guru or karyawan
        $user = $this->userModel->find($userId);
        if (!$user || !in_array($user['role'], ['guru', 'karyawan'])) {
            return redirect()->back()->with('error', 'Guru/Karyawan tidak ditemukan');
        }

        // Get teacher info with full_name
        $teacherModel = new \App\Models\TeacherModel();
        $teacher = $teacherModel->where('user_id', $userId)->first();
        $fullName = $teacher['full_name'] ?? $user['username'];

        // Get existing schedules for this user
        $existingSchedules = $this->teacherScheduleModel->getActiveSchedules($userId);
        $schedulesByDay = [];
        foreach ($existingSchedules as $schedule) {
            $schedulesByDay[$schedule['hari']] = $schedule;
        }

        $data = [
            'title' => 'Set Jadwal - ' . $fullName,
            'user' => array_merge($user, ['full_name' => $fullName]),
            'schedulesByDay' => $schedulesByDay,
            'days' => [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
                7 => 'Minggu',
            ],
        ];

        return view('admin/teacher-schedule/set-schedule', $data);
    }

    /**
     * Save schedules for a user
     */
    public function saveSchedule($userId = null)
    {
        if (!$userId) {
            return redirect()->back()->with('error', 'User ID tidak valid');
        }

        // Validate user exists
        $user = $this->userModel->find($userId);
        if (!$user || !in_array($user['role'], ['guru', 'karyawan'])) {
            return redirect()->back()->with('error', 'Guru/Karyawan tidak ditemukan');
        }

        // Delete all existing schedules for this user
        $this->teacherScheduleModel->where('user_id', $userId)->delete();

        // Process each day's schedule
        $successCount = 0;
        for ($hari = 1; $hari <= 7; $hari++) {
            // Check if this day is marked as active
            $isActive = $this->request->getPost("hari_{$hari}_active") === 'on';

            if ($isActive) {
                $jamMasuk = $this->request->getPost("hari_{$hari}_jam_masuk");
                $jamPulang = $this->request->getPost("hari_{$hari}_jam_pulang");

                // Validate times if day is active
                if (empty($jamMasuk) || empty($jamPulang)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Waktu masuk dan pulang harus diisi untuk hari yang aktif');
                }

                $scheduleData = [
                    'user_id' => $userId,
                    'role' => $user['role'],
                    'hari' => $hari,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'status' => 'aktif',
                ];

                if ($this->teacherScheduleModel->insert($scheduleData)) {
                    $successCount++;
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal menyimpan jadwal untuk hari ke-' . $hari);
                }
            }
        }

        if ($successCount > 0) {
            // Get teacher info for message
            $teacherModel = new \App\Models\TeacherModel();
            $teacher = $teacherModel->where('user_id', $userId)->first();
            $fullName = $teacher['full_name'] ?? $user['username'];

            return redirect()->to(base_url('admin/teacher-schedule'))
                ->with('success', "Jadwal untuk {$fullName} berhasil disimpan ($successCount hari)");
        } else {
            return redirect()->back()
                ->with('info', 'Tidak ada jadwal yang disimpan. Pilih minimal satu hari.');
        }
    }

    /**
     * Edit schedule for a user (Show step 2 form with existing data)
     */
    public function edit($userId = null)
    {
        if (!$userId) {
            return redirect()->back()->with('error', 'User ID tidak valid');
        }

        // Validate user exists
        $user = $this->userModel->find($userId);
        if (!$user || !in_array($user['role'], ['guru', 'karyawan'])) {
            return redirect()->back()->with('error', 'Guru/Karyawan tidak ditemukan');
        }

        // Get teacher info with full_name
        $teacherModel = new \App\Models\TeacherModel();
        $teacher = $teacherModel->where('user_id', $userId)->first();
        $fullName = $teacher['full_name'] ?? $user['username'];

        // Get existing schedules for this user
        $existingSchedules = $this->teacherScheduleModel->getActiveSchedules($userId);
        $schedulesByDay = [];
        foreach ($existingSchedules as $schedule) {
            $schedulesByDay[$schedule['hari']] = $schedule;
        }

        $data = [
            'title' => 'Edit Jadwal - ' . $fullName,
            'user' => array_merge($user, ['full_name' => $fullName]),
            'schedulesByDay' => $schedulesByDay,
            'isEdit' => true,
            'days' => [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
                7 => 'Minggu',
            ],
        ];

        return view('admin/teacher-schedule/set-schedule', $data);
    }

    /**
     * Delete a user's schedule
     */
    public function delete($userId = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID tidak valid']);
        }

        // Validate user exists
        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan']);
        }

        // Delete all schedules for this user
        $deleted = $this->teacherScheduleModel->where('user_id', $userId)->delete();

        if ($deleted) {
            // Get teacher info for message
            $teacherModel = new \App\Models\TeacherModel();
            $teacher = $teacherModel->where('user_id', $userId)->first();
            $fullName = $teacher['full_name'] ?? $user['username'];

            return $this->response->setJSON([
                'success' => true,
                'message' => "Jadwal untuk {$fullName} berhasil dihapus",
                'csrf_token' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus jadwal'
            ]);
        }
    }
}
