<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTelegramChatIdToStudents extends Migration
{
    public function up()
    {
        $fields = [
            'telegram_chat_id' => [
                'type' => 'VARCHAR',
                'constraint' => '64',
                'null' => true,
                'after' => 'class',
            ],
        ];

        $this->forge->addColumn('students', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('students', 'telegram_chat_id');
    }
}
