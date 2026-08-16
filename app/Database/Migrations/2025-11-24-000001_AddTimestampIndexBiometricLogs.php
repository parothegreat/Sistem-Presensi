<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTimestampIndexBiometricLogs extends Migration
{
    public function up()
    {
        // Add index on timestamp column for proper ordering
        $this->forge->addKey('timestamp', false, true, 'idx_timestamp');
        $this->forge->processIndexes('biometric_logs');
    }

    public function down()
    {
        // Drop the index
        $this->forge->dropKey('biometric_logs', 'idx_timestamp');
    }
}
