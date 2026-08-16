<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\InformationModel;
use App\Models\BiometricLogModel;
use CodeIgniter\Controller;

class Lobby extends Controller
{
    /**
     * Display the TV Lobby Interface
     */
    public function index()
    {
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getAll();

        return view('lobby/index', [
            'school_name' => $settings['school_name'] ?? getenv('SCHOOL_NAME') ?? 'Sekolah Kami',
            'school_logo' => isset($settings['school_logo']) ? base_url($settings['school_logo']) : base_url('assets/img/logo.png'), 
            // Favicon logic: check setting, else default
            'school_favicon' => isset($settings['school_favicon']) ? base_url($settings['school_favicon']) : base_url('favicon.ico'),
        ]);
    }

    /**
     * Get real-time updates for JSON polling
     */
    public function getUpdates()
    {
        $attendanceModel = new AttendanceModel();
        $informationModel = new InformationModel();
        
        $today = date('Y-m-d');

        // 1. STATS: Count attendance by status today
        // We need counts for: Hadir (on_time + late), Sakit, Izin, Alpha
        // Note: 'masuk_status' can be: on_time, late, izin, sakit, alpha
        
        $stats = [
            'total_present' => 0,
            'late' => 0,
            'permit' => 0, // Izin + Sakit
            'alpha' => 0,
        ];

        // Fetch today's attendance records
        $todayRecords = $attendanceModel->where('date', $today)->findAll();
        
        foreach ($todayRecords as $att) {
            $status = $att['masuk_status'];
            if ($status == 'on_time' || $status == 'late') {
                $stats['total_present']++;
            }
            if ($status == 'late') {
                $stats['late']++;
            }
            if ($status == 'sakit' || $status == 'izin') {
                $stats['permit']++;
            }
            if ($status == 'alpha') {
                $stats['alpha']++;
            }
        }

        // 2. RECENT SCANS: Get last 5 check-ins/check-outs from biometric logs/attendance
        // We'll use attendance table 'updated_at' or 'masuk_at'/'pulang_at'?
        // Better to use biometric_log for "live" feel, but attendance has properly mapped user data.
        // Let's use AttendanceModel with join to User/Student for names.
        // Ordering by updated_at DESC.
        
        $db = \Config\Database::connect();
        $recentScans = $db->table('attendances')
            ->select('attendances.*, students.full_name as student_name, students.nis, students.class as class_name, teachers.full_name as teacher_name')
            ->join('users', 'users.id = attendances.user_id')
            ->join('students', 'students.user_id = users.id', 'left') // Join student
            ->join('teachers', 'teachers.user_id = users.id', 'left') // Join teacher
            ->where('attendances.date', $today)
            // Filter: Only Students (teachers have null student_name usually, but better check student join)
            ->where('students.id IS NOT NULL')
            // Filter: Only Masuk (on_time) or Late
            ->whereIn('attendances.masuk_status', ['on_time', 'late'])
            // We want latest activity. either masuk_at or pulang_at. 
            // A simple logic: sort by updated_at is good enough for "recent activity"
            ->orderBy('attendances.updated_at', 'DESC')
            ->limit(7)
            ->get()
            ->getResultArray();
            
        $formattedScans = [];
        foreach ($recentScans as $scan) {
            // Determine if it was check-in or check-out based on times vs updated_at
            // If pulang_at is close to updated_at, it's checkout. Else checkin.
            // Simplified: Just show the latest time
            $isCheckout = !empty($scan['pulang_at']) && strtotime($scan['updated_at']) >= strtotime($scan['pulang_at']);
            
            $time = $isCheckout 
                ? ($scan['pulang_at'] ? date('H:i', strtotime($scan['pulang_at'])) : '-')
                : ($scan['masuk_at'] ? date('H:i', strtotime($scan['masuk_at'])) : '-');

            $statusLabel = $isCheckout ? 'Pulang' : 'Masuk';
            $statusColor = $isCheckout ? 'text-orange-400' : 'text-green-400';
            
            // Name (Student or Teacher)
            $name = $scan['student_name'] ?? $scan['teacher_name'] ?? 'Unknown';
            $roleLabel = $scan['student_name'] ? ($scan['class_name'] ?? 'Siswa') : 'Guru';
            
            // Photo URL (Assuming standard path or placeholder)
            // You might need a helper for actual photo paths
            $photo = base_url('assets/img/avatars/default.png'); 

            $formattedScans[] = [
                'name' => $name,
                'role' => $roleLabel,
                'time' => $time,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'photo' => $photo 
            ];
        }

        // 3. ANNOUNCEMENTS: Active running text
        $announcements = $informationModel
            // ->where('is_active', 1) // Column not exists yet
            ->orderBy('created_at', 'DESC')
            ->findAll(5);
            
        $runningText = [];
        if (empty($announcements)) {
            $runningText[] = "Selamat Datang di " . (getenv('SCHOOL_NAME') ?: 'Sekolah Kami') . ". Tetap Semangat!";
        } else {
            foreach ($announcements as $info) {
                $runningText[] = $info['title'] . ": " . strip_tags($info['content']);
            }
        }

        return $this->response->setJSON([
            'stats' => $stats,
            'scans' => $formattedScans,
            'running_text' => implode("   |   ", $runningText)
        ]);
    }
}
