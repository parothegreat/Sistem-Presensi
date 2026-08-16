<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToStudents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('students', [
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'class'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('students', 'photo');
    }
}
