<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPayloadToAndroidNotifications extends Migration
{
    public function up()
    {
        // Add payload column to store notification data
        $this->forge->addColumn('android_notifications', [
            'payload' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'message',
                'comment' => 'JSON payload for APK direct integration'
            ]
        ]);

        // Add npsn column for multi-school support
        $this->forge->addColumn('android_notifications', [
            'npsn' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'nis',
                'comment' => 'School NPSN for multi-school support'
            ]
        ]);

        // Add index on npsn
        $this->db->query('ALTER TABLE android_notifications ADD INDEX idx_npsn (npsn)');
    }

    public function down()
    {
        // Remove index
        try {
            $this->db->query('ALTER TABLE android_notifications DROP INDEX idx_npsn');
        } catch (\Exception $e) {
            // Ignore
        }

        // Remove columns
        $this->forge->dropColumn('android_notifications', 'payload');
        $this->forge->dropColumn('android_notifications', 'npsn');
    }
}
