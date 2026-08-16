<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TeacherNotificationTemplateSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'code' => 'wa_teacher_checkin',
                'name' => 'WhatsApp Guru - Check In',
                'channel' => 'whatsapp',
                'content' => "🔔 *Presensi Masuk*\n\nHalo {name},\nAbsensi MASUK Anda telah tercatat.\n\n📅 Tanggal: {date}\n⏰ Waktu: {time}\n\nSelamat bertugas mencerdaskan kehidupan bangsa! 💪",
                'description' => 'Variabel: {name}, {time}, {date}, {status}',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'wa_teacher_checkout',
                'name' => 'WhatsApp Guru - Check Out',
                'channel' => 'whatsapp',
                'content' => "🔔 *Presensi Pulang*\n\nHalo {name},\nAbsensi PULANG Anda telah tercatat.\n\n📅 Tanggal: {date}\n⏰ Waktu: {time}\n\nTerima kasih atas dedikasi Anda hari ini. Selamat beristirahat! 🏠",
                'description' => 'Variabel: {name}, {time}, {date}, {status}',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Using ignore(true) to avoid errors if they already exist (based on unique code)
        $this->db->table('notification_templates')->ignore(true)->insertBatch($data);
    }
}
