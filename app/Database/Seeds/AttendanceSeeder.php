<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // Get all students from class X TKJ
        $students = $this->db->table('students')
            ->select('students.user_id, students.nis, students.full_name')
            ->where('students.class', 'X TKJ')
            ->get()
            ->getResultArray();

        if (empty($students)) {
            echo "No students found in class X TKJ\n";
            return;
        }

        echo "Found " . count($students) . " students in X TKJ\n";

        // Generate dates for Monday to Saturday (this week)
        // Get current week's Monday
        $today = new \DateTime();
        $mondayDate = clone $today;
        $mondayDate->modify('monday this week');

        $dates = [];
        for ($i = 0; $i < 6; $i++) {
            $date = clone $mondayDate;
            $date->modify("+$i day");
            $dates[] = $date->format('Y-m-d');
        }

        // Status options
        $masuks = ['on_time', 'late', 'izin', 'sakit', 'alpha'];
        $pulangs = ['on_time', 'early', 'izin', 'sakit', 'alpha', 'unknown'];

        $data = [];
        $now = date('Y-m-d H:i:s');

        foreach ($students as $student) {
            foreach ($dates as $date) {
                // Random masuk status (80% on_time/late, 20% other)
                $rand = mt_rand(1, 100);
                if ($rand <= 70) {
                    $masuk_status = mt_rand(1, 100) <= 80 ? 'on_time' : 'late';
                    $masuk_at = $date . ' ' . (mt_rand(1, 100) <= 80 ? '07:15:00' : '07:45:00');
                } else if ($rand <= 85) {
                    $masuk_status = 'izin';
                    $masuk_at = null;
                } else if ($rand <= 95) {
                    $masuk_status = 'sakit';
                    $masuk_at = null;
                } else {
                    $masuk_status = 'alpha';
                    $masuk_at = null;
                }

                // Random pulang status (if masuk is on_time/late)
                if ($masuk_status === 'on_time' || $masuk_status === 'late') {
                    $rand_pulang = mt_rand(1, 100);
                    if ($rand_pulang <= 80) {
                        $pulang_status = 'on_time';
                        $pulang_at = $date . ' ' . (mt_rand(1, 100) <= 70 ? '15:30:00' : '15:15:00');
                    } else if ($rand_pulang <= 95) {
                        $pulang_status = 'early';
                        $pulang_at = $date . ' ' . (mt_rand(1, 100) <= 70 ? '14:30:00' : '14:45:00');
                    } else {
                        $pulang_status = 'unknown';
                        $pulang_at = null;
                    }
                } else {
                    $pulang_status = 'unknown';
                    $pulang_at = null;
                }

                $data[] = [
                    'user_id' => $student['user_id'],
                    'date' => $date,
                    'masuk_at' => $masuk_at,
                    'masuk_status' => $masuk_status,
                    'pulang_at' => $pulang_at,
                    'pulang_status' => $pulang_status,
                    'device_id' => null,
                    'note' => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert data in batches
        $batchSize = 100;
        for ($i = 0; $i < count($data); $i += $batchSize) {
            $batch = array_slice($data, $i, $batchSize);
            // Skip if already exists (on unique constraint)
            foreach ($batch as $record) {
                try {
                    $this->db->table('attendances')->insert($record);
                } catch (\Exception $e) {
                    // Skip duplicates
                }
            }
        }

        echo "Inserted " . count($data) . " attendance records\n";
        echo "Date range: " . $dates[0] . " to " . end($dates) . "\n";
    }
}
