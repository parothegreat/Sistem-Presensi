<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNpsnToStudentDeviceTokens extends Migration
{
    public function up()
    {
        // Add NPSN column for multi-school support
        $this->forge->addColumn('student_device_tokens', [
            'npsn' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'after' => 'student_id',
                'comment' => 'School NPSN for multi-school support'
            ]
        ]);

        // Drop existing unique key on device_token (if exists)
        // Note: This might fail if the key doesn't exist, that's OK
        try {
            $this->db->query('ALTER TABLE student_device_tokens DROP INDEX device_token');
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }

        // Add new composite unique key (NPSN + device_token)
        $this->db->query('ALTER TABLE student_device_tokens ADD UNIQUE KEY uk_npsn_device_token (npsn, device_token)');

        // Add index on NPSN for faster queries
        $this->db->query('ALTER TABLE student_device_tokens ADD INDEX idx_npsn (npsn)');
    }

    public function down()
    {
        // Remove the composite unique key
        try {
            $this->db->query('ALTER TABLE student_device_tokens DROP INDEX uk_npsn_device_token');
        } catch (\Exception $e) {
            // Ignore
        }

        // Remove NPSN index
        try {
            $this->db->query('ALTER TABLE student_device_tokens DROP INDEX idx_npsn');
        } catch (\Exception $e) {
            // Ignore
        }

        // Remove NPSN column
        $this->forge->dropColumn('student_device_tokens', 'npsn');
    }
}
