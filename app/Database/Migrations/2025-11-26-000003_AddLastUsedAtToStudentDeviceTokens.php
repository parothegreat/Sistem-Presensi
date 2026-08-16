<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastUsedAtToStudentDeviceTokens extends Migration
{
    public function up()
    {
        // Add last_used_at column to track device token usage
        $this->forge->addColumn('student_device_tokens', [
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'app_version',
                'comment' => 'Last time this device token was used'
            ]
        ]);

        // Add index on last_used_at for faster queries
        $this->db->query('ALTER TABLE student_device_tokens ADD INDEX idx_last_used_at (last_used_at)');
    }

    public function down()
    {
        // Remove index
        try {
            $this->db->query('ALTER TABLE student_device_tokens DROP INDEX idx_last_used_at');
        } catch (\Exception $e) {
            // Ignore if index doesn't exist
        }

        // Remove last_used_at column
        $this->forge->dropColumn('student_device_tokens', 'last_used_at');
    }
}
