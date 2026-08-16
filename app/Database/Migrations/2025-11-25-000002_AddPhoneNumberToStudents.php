<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhoneNumberToStudents extends Migration
{
    public function up()
    {
        // Check if phone_number column already exists
        try {
            $this->forge->addColumn('students', [
                'phone_number' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'comment' => 'Student phone number for WhatsApp notifications'
                ],
            ]);
        } catch (\Exception $e) {
            // Column already exists, skip
        }
    }

    public function down()
    {
        $this->forge->dropColumn('students', 'phone_number');
    }
}
