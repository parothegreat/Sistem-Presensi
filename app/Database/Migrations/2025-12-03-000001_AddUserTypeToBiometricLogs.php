<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserTypeToBiometricLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('biometric_logs', [
            'user_type' => [
                'type'       => 'ENUM',
                'constraint' => ['siswa', 'guru'],
                'default'    => 'siswa',
                'null'       => false,
                'after'      => 'user_id',
                'comment'    => 'Tipe user: siswa atau guru'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('biometric_logs', 'user_type');
    }
}
