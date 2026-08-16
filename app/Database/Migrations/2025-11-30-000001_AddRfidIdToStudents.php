<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRfidIdToStudents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('students', [
            'rfid_id' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'unique' => true,
                'comment' => 'RFID tag ID for IoT reader integration',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('students', 'rfid_id');
    }
}
