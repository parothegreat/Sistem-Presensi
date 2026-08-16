<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityTables extends Migration
{
    public function up()
    {
        // 1. Add religion to students table if not exists
        if (!$this->db->fieldExists('religion', 'students')) {
            $this->forge->addColumn('students', [
                'religion' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'class'
                ]
            ]);
        }

        // 2. Create activities table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'start_time' => [
                'type' => 'DATETIME',
            ],
            'end_time' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['scheduled', 'ongoing', 'completed', 'cancelled'],
                'default' => 'scheduled',
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
        $this->forge->createTable('activities', true);

        // 3. Create activity_participants table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'activity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('activity_id', 'activities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        // Prevent duplicate participants
        $this->forge->addUniqueKey(['activity_id', 'student_id']);
        $this->forge->createTable('activity_participants', true);

        // 4. Create activity_attendances table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'activity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'check_in_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'check_out_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['present', 'late', 'absent', 'permission'],
                'default' => 'absent',
            ],
            'method' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true, // rfid, qrcode, fingerprint, manual
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
        $this->forge->addForeignKey('activity_id', 'activities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['activity_id', 'student_id']);
        $this->forge->createTable('activity_attendances', true);
    }

    public function down()
    {
        $this->forge->dropTable('activity_attendances', true);
        $this->forge->dropTable('activity_participants', true);
        $this->forge->dropTable('activities', true);

        if ($this->db->fieldExists('religion', 'students')) {
            $this->forge->dropColumn('students', 'religion');
        }
    }
}
