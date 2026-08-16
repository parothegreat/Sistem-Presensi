<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TeacherScheduleSeeder extends Seeder
{
    public function run()
    {
        // Get user IDs (assuming they exist from UserSeeder)
        $db = \Config\Database::connect();

        // Get first guru user
        $guruUser = $db->table('users')
            ->where('role', 'guru')
            ->limit(1)
            ->get()
            ->getRow();

        // Get first karyawan user
        $karyawanUser = $db->table('users')
            ->where('role', 'karyawan')
            ->limit(1)
            ->get()
            ->getRow();

        $data = [];

        // Schedule for Guru (if exists)
        if ($guruUser) {
            // Senin: 07:00 - 14:30
            $data[] = [
                'user_id' => $guruUser->id,
                'role' => 'guru',
                'hari' => 1, // Senin
                'jam_masuk' => '07:00',
                'jam_pulang' => '14:30',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Selasa: 09:00 - 16:00
            $data[] = [
                'user_id' => $guruUser->id,
                'role' => 'guru',
                'hari' => 2, // Selasa
                'jam_masuk' => '09:00',
                'jam_pulang' => '16:00',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Rabu: 07:00 - 13:00
            $data[] = [
                'user_id' => $guruUser->id,
                'role' => 'guru',
                'hari' => 3, // Rabu
                'jam_masuk' => '07:00',
                'jam_pulang' => '13:00',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Kamis: 08:00 - 16:00
            $data[] = [
                'user_id' => $guruUser->id,
                'role' => 'guru',
                'hari' => 4, // Kamis
                'jam_masuk' => '08:00',
                'jam_pulang' => '16:00',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Jumat: 07:00 - 12:00
            $data[] = [
                'user_id' => $guruUser->id,
                'role' => 'guru',
                'hari' => 5, // Jumat
                'jam_masuk' => '07:00',
                'jam_pulang' => '12:00',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Sabtu: no schedule (null)
            // Minggu: no schedule (null)
        }

        // Schedule for Karyawan (if exists) - Fixed 07:00 - 16:00 every day
        if ($karyawanUser) {
            for ($i = 1; $i <= 7; $i++) {
                if ($i !== 7) { // Skip Sunday (optional)
                    $data[] = [
                        'user_id' => $karyawanUser->id,
                        'role' => 'karyawan',
                        'hari' => $i,
                        'jam_masuk' => '07:00',
                        'jam_pulang' => '16:00',
                        'status' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        // Batch insert
        if (!empty($data)) {
            $this->db->table('teacher_schedules')->insertBatch($data);
            echo "TeacherScheduleSeeder: " . count($data) . " records inserted\n";
        }
    }
}
