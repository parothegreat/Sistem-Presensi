<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTelegramNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'chat_id' => [
                'type' => 'VARCHAR',
                'constraint' => '64',
                'null' => false,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed'],
                'default' => 'pending',
            ],
            'attempts' => [
                'type' => 'TINYINT',
                'constraint' => 3,
                'default' => 0,
            ],
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('telegram_notifications', true);
    }

    public function down()
    {
        $this->forge->dropTable('telegram_notifications', true);
    }
}
