<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGuardianAndPhoneFields extends Migration
{
    public function up()
    {
        // Check if columns already exist
        try {
            $this->forge->addColumn('students', [
                'guardian_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'comment' => 'Nama wali siswa'
                ],
                'guardian_phone' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'comment' => 'Nomor WhatsApp wali'
                ]
            ]);
        } catch (\Exception $e) {
            // Columns already exist, skip
        }
    }

    public function down()
    {
        $this->forge->dropColumn('students', ['guardian_name', 'guardian_phone']);
    }
}
