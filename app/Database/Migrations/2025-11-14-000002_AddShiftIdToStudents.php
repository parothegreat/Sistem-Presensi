<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShiftIdToStudents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('students', [
            'shift_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'class',
                'comment' => 'Shift ID (pagi/siang/malam)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('students', 'shift_id');
    }
}
