<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUniqueFromBiometricLogsTimestamp extends Migration
{
    public function up()
    {
        // Drop the unique index
        $this->forge->dropKey('biometric_logs', 'idx_timestamp');

        // Re-add the index as non-unique
        $this->forge->addKey('timestamp', false, false, 'idx_timestamp');
        $this->forge->processIndexes('biometric_logs');
    }

    public function down()
    {
        // Drop the non-unique index
        $this->forge->dropKey('biometric_logs', 'idx_timestamp');

        // Re-add the index as unique (reverting to previous state)
        $this->forge->addKey('timestamp', false, true, 'idx_timestamp');
        $this->forge->processIndexes('biometric_logs');
    }
}
