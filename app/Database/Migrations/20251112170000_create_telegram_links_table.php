<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTelegramLinksTable extends Migration
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
            ],
            'token' => [
                'type' => 'VARCHAR',
                'constraint' => '64',
                'null' => false,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'consumed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('telegram_links', true);
    }

    public function down()
    {
        $this->forge->dropTable('telegram_links', true);
    }
}
