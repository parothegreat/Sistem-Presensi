<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTeacherSchedules extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'guru',
            ],
            'hari' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'comment' => '1=Senin, 2=Selasa, ..., 7=Minggu',
            ],
            'jam_masuk' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'jam_pulang' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=aktif, 0=nonaktif',
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

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['user_id', 'hari']);
        $this->forge->addKey(['user_id', 'role']);
        $this->forge->addKey('hari');

        $this->forge->createTable('teacher_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('teacher_schedules');
    }
}
