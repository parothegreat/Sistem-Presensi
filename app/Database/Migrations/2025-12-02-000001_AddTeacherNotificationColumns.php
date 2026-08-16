<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeacherNotificationColumns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('teachers', [
            'telegram_chat_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Telegram chat ID untuk notifikasi',
                'after' => 'nip',
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Nomor telepon guru untuk WhatsApp',
                'after' => 'telegram_chat_id',
            ],
            'rfid_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'RFID card ID untuk fingerprint machine',
                'after' => 'phone_number',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('teachers', ['telegram_chat_id', 'phone_number', 'rfid_id']);
    }
}
