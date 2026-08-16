<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationTemplatesTable extends Migration
{
    public function up()
    {
        // Create table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'channel' => [
                'type' => 'ENUM',
                'constraint' => ['whatsapp', 'telegram', 'android'],
            ],
            'content' => [
                'type' => 'TEXT',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('notification_templates');

        // Seed Default Templates
        $data = [
            // === WHATSAPP STUDENT ===
            [
                'code' => 'wa_student_checkin',
                'name' => 'WhatsApp Siswa - Check In',
                'channel' => 'whatsapp',
                'content' => "📱 Absensi Masuk\nNama: {name}\nJam: {time}\nStatus: {status_label}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}, {school_name}',
            ],
            [
                'code' => 'wa_student_checkout',
                'name' => 'WhatsApp Siswa - Check Out',
                'channel' => 'whatsapp',
                'content' => "👋 Absensi Pulang\nNama: {name}\nJam: {time}\nStatus: {status_label}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}, {school_name}',
            ],
            
            // === WHATSAPP PARENT ===
            [
                'code' => 'wa_guardian_checkin',
                'name' => 'WhatsApp Wali - Check In',
                'channel' => 'whatsapp',
                'content' => "📱 Absensi Masuk Siswa\nNama: {name}\nJam: {time}\nStatus: {status_label}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}, {school_name}',
            ],
            [
                'code' => 'wa_guardian_checkout',
                'name' => 'WhatsApp Wali - Check Out',
                'channel' => 'whatsapp',
                'content' => "👋 Absensi Pulang Siswa\nNama: {name}\nJam: {time}\nStatus: {status_label}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}, {school_name}',
            ],

            // === TELEGRAM STUDENT ===
            [
                'code' => 'tele_student_checkin',
                'name' => 'Telegram Siswa - Check In',
                'channel' => 'telegram',
                'content' => "✓ Absensi Masuk\n\nNama: {name}\nWaktu: {time}\nStatus: {status_label}\nTanggal: {date}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}, {school_name}',
            ],
            [
                'code' => 'tele_student_checkout',
                'name' => 'Telegram Siswa - Check Out',
                'channel' => 'telegram',
                'content' => "⟲ Absensi Pulang\n\nNama: {name}\nWaktu: {time}\nStatus: {status_label}\nTanggal: {date}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}, {school_name}',
            ],

            // === TELEGRAM TEACHER ===
             [
                'code' => 'tele_teacher_checkin',
                'name' => 'Telegram Guru - Check In',
                'channel' => 'telegram',
                'content' => "✓ {name} masuk pada {time}\nTanggal: {date}\nStatus: {status_label}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}',
            ],
            [
                'code' => 'tele_teacher_checkout',
                'name' => 'Telegram Guru - Check Out',
                'channel' => 'telegram',
                'content' => "⟲ {name} pulang pada {time}\nTanggal: {date}\nStatus: {status_label}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}',
            ],

            // === ANDROID STUDENT ===
            [
                'code' => 'android_student_checkin_title',
                'name' => 'Android Title - Check In',
                'channel' => 'android',
                'content' => "Absensi Masuk",
                'description' => 'Judul Notifikasi. Variabel: {name}, {time}',
            ],
            [
                'code' => 'android_student_checkin_body',
                'name' => 'Android Body - Check In',
                'channel' => 'android',
                'content' => "Nama: {name}\nJam: {time}\nStatus: {status_label}",
                'description' => 'Isi Notifikasi. Variabel: {name}, {time}, {status_label}',
            ],
            [
                'code' => 'android_student_checkout_title',
                'name' => 'Android Title - Check Out',
                'channel' => 'android',
                'content' => "Absensi Pulang",
                'description' => 'Judul Notifikasi. Variabel: {name}, {time}',
            ],
            [
                'code' => 'android_student_checkout_body',
                'name' => 'Android Body - Check Out',
                'channel' => 'android',
                'content' => "Nama: {name}\nJam: {time}\nStatus: {status_label}",
                'description' => 'Isi Notifikasi. Variabel: {name}, {time}, {status_label}',
            ],
             // === MANUAL UPDATE ===
             [
                'code' => 'tele_manual_update',
                'name' => 'Telegram - Update Manual',
                'channel' => 'telegram',
                'content' => "📝 Perubahan Status Absensi (Guru)\n\nNama: {name}\nStatus Baru: {status_label}\nWaktu Update: {time}\nTanggal: {date}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {date}',
            ],
             [
                'code' => 'wa_manual_update_student',
                'name' => 'WhatsApp Siswa - Update Manual',
                'channel' => 'whatsapp',
                'content' => "📱 Absensi {type} (Manual)\nNama: {name}\nJam: {time}\nStatus: {status_label}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {type}',
            ],
             [
                'code' => 'wa_manual_update_guardian',
                'name' => 'WhatsApp Wali - Update Manual',
                'channel' => 'whatsapp',
                'content' => "📱 Absensi {type} Siswa (Manual)\nNama: {name}\nJam: {time}\nStatus: {status_label}",
                'description' => 'Variabel: {name}, {time}, {status_label}, {type}',
            ],
        ];

        // Batch insert
        $db = \Config\Database::connect();
        $date = date('Y-m-d H:i:s');
        foreach ($data as &$row) {
            $row['created_at'] = $date;
            $row['updated_at'] = $date;
            $row['is_active'] = 1;
        }
        $db->table('notification_templates')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('notification_templates');
    }
}
