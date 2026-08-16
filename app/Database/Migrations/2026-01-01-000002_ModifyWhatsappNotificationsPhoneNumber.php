<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyWhatsappNotificationsPhoneNumber extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('whatsapp_notifications', [
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100, // Increased from 20 to 100 to support Group JIDs
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('whatsapp_notifications', [
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
        ]);
    }
}
