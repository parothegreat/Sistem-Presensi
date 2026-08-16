<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsappNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'student_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'payload' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'status' => [
                'type' => "ENUM('pending', 'sent', 'failed')",
                'default' => 'pending',
            ],
            'attempts' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'max_attempts' => [
                'type' => 'INT',
                'default' => 3,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', false, true);
        $this->forge->addKey('status');
        $this->forge->addKey('student_id');
        $this->forge->createTable('whatsapp_notifications');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_notifications');
    }
}
