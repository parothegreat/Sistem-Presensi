<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceEventsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'attendance_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'event_time' => ['type' => 'DATETIME'],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'device_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('attendance_id', 'attendances', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('attendance_events');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_events');
    }
}
