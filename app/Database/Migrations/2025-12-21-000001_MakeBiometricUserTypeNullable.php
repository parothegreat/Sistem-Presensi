<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeBiometricUserTypeNullable extends Migration
{
    public function up()
    {
        // Modify user_type column to be nullable
        // Note: We need to redefine the enum and set null => true
        $this->forge->modifyColumn('biometric_logs', [
            'user_type' => [
                'type'       => 'ENUM',
                'constraint' => ['siswa', 'guru'],
                'default'    => null, // Remove default 'siswa' if desired, or keep it. Let's start with null default.
                'null'       => true,
                'comment'    => 'Tipe user: siswa atau guru. Null jika tidak dikenali.',
            ],
        ]);
    }

    public function down()
    {
        // Revert back to not null
        // BE CAREFUL: If there are null values, this will fail or truncate.
        // For 'down', we honestly might just leave it or force a default.
        // Let's attempt to revert to strict.
        
        // First delete or fix nulls? No, just try to revert definition.
        $this->forge->modifyColumn('biometric_logs', [
            'user_type' => [
                'type'       => 'ENUM',
                'constraint' => ['siswa', 'guru'],
                'default'    => 'siswa',
                'null'       => false,
            ],
        ]);
    }
}
