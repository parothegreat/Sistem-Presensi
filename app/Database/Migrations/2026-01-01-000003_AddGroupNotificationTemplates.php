<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGroupNotificationTemplates extends Migration
{
    public function up()
    {
        // Insert default templates for group notifications
        $data = [
            [
                'code' => 'wa_checkin_group',
                'name' => 'WA Absensi Masuk (Group)',
                'channel' => 'whatsapp',
                'content' => "✅ *ABSENSI MASUK*\n\nSiswa: *{name}*\nTanggal: {date}\nWaktu: {time}\nStatus: {status_label}\n\n_{school_name}_",
                'description' => 'Template notifikasi WhatsApp ke Group Kelas saat siswa absen masuk',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'wa_checkout_group',
                'name' => 'WA Absensi Pulang (Group)',
                'channel' => 'whatsapp',
                'content' => "👋 *ABSENSI PULANG*\n\nSiswa: *{name}*\nTanggal: {date}\nWaktu: {time}\nStatus: {status_label}\n\n_{school_name}_",
                'description' => 'Template notifikasi WhatsApp ke Group Kelas saat siswa absen pulang',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'wa_manual_update_group',
                'name' => 'WA Absensi Manual (Group)',
                'channel' => 'whatsapp',
                'content' => "📝 *ABSENSI MANUAL*\n\nSiswa: *{name}*\nTanggal: {date}\nWaktu: {time}\nStatus: {status_label}\nInfo: {type}\n\n_{school_name}_",
                'description' => 'Template notifikasi WhatsApp ke Group Kelas saat absen diinput manual oleh admin',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('notification_templates')->insertBatch($data);
    }

    public function down()
    {
        $this->db->table('notification_templates')
            ->whereIn('code', ['wa_checkin_group', 'wa_checkout_group', 'wa_manual_update_group'])
            ->delete();
    }
}
