<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBiometricLogsTable extends Migration
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
            'device_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'user_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'time' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'timestamp' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'biometric_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'fingerprint',
                'comment' => 'fingerprint, card, face, etc',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'checkin',
                'comment' => 'checkin, checkout, overtime',
            ],
            'processed' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'attendance_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'FK to attendances table',
            ],
            'process_error' => [
                'type' => 'TEXT',
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
        $this->forge->addKey(['user_id', 'date']);
        $this->forge->addKey('device_id');
        $this->forge->addKey('processed');
        $this->forge->addKey('attendance_id');

        $this->forge->createTable('biometric_logs');
    }

    public function down()
    {
        $this->forge->dropTable('biometric_logs');
    }
}
