<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SchoolHolidaySeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $holidays = [
            // 2025
            [
                'date_from' => '2025-12-25',
                'date_to' => '2026-01-05',
                'name' => 'Libur Akhir Tahun & Tahun Baru',
                'type' => 'national_holiday',
                'description' => 'Libur Natal & Tahun Baru 2025-2026',
                'created_at' => $now,
            ],

            // 2026
            [
                'date_from' => '2026-01-30',
                'date_to' => '2026-02-02',
                'name' => 'Isra dan Mi\'raj',
                'type' => 'national_holiday',
                'description' => null,
                'created_at' => $now,
            ],
            [
                'date_from' => '2026-03-28',
                'date_to' => '2026-04-04',
                'name' => 'Libur Ramadan & Idul Fitri',
                'type' => 'national_holiday',
                'description' => 'Periode libur Ramadan dan Lebaran',
                'created_at' => $now,
            ],
            [
                'date_from' => '2026-04-16',
                'date_to' => '2026-04-18',
                'name' => 'Idul Adha',
                'type' => 'national_holiday',
                'description' => null,
                'created_at' => $now,
            ],
            [
                'date_from' => '2026-05-01',
                'date_to' => '2026-05-01',
                'name' => 'Hari Buruh Internasional',
                'type' => 'national_holiday',
                'description' => null,
                'created_at' => $now,
            ],
            [
                'date_from' => '2026-05-14',
                'date_to' => '2026-05-14',
                'name' => 'Hari Pendidikan Nasional',
                'type' => 'national_holiday',
                'description' => null,
                'created_at' => $now,
            ],
            [
                'date_from' => '2026-06-01',
                'date_to' => '2026-06-30',
                'name' => 'Libur Akhir Tahun Pelajaran',
                'type' => 'school_activity',
                'description' => 'Liburan sekolah akhir tahun ajaran',
                'created_at' => $now,
            ],
            [
                'date_from' => '2026-08-17',
                'date_to' => '2026-08-17',
                'name' => 'Hari Kemerdekaan Indonesia',
                'type' => 'national_holiday',
                'description' => null,
                'created_at' => $now,
            ],
            [
                'date_from' => '2026-12-25',
                'date_to' => '2027-01-05',
                'name' => 'Libur Akhir Tahun & Tahun Baru',
                'type' => 'national_holiday',
                'description' => 'Libur Natal & Tahun Baru 2026-2027',
                'created_at' => $now,
            ],
        ];

        $this->db->table('school_holidays')->insertBatch($holidays);
        echo "Inserted " . count($holidays) . " holiday records\n";
    }
}
