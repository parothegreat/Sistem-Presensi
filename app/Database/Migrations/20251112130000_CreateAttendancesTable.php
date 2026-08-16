<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'date' => ['type' => 'DATE'],
            'masuk_at' => ['type' => 'DATETIME', 'null' => true],
            "masuk_status" => ['type' => 'ENUM', 'constraint' => ['on_time', 'late', 'izin', 'sakit', 'alpha', 'unknown'], 'default' => 'unknown'],
            'pulang_at' => ['type' => 'DATETIME', 'null' => true],
            "pulang_status" => ['type' => 'ENUM', 'constraint' => ['on_time', 'early', 'izin', 'sakit', 'alpha', 'unknown'], 'default' => 'unknown'],
            'device_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'note' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'date'], false, true); // unique
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('attendances');
    }

    public function down()
    {
        $this->forge->dropTable('attendances');
    }
}
