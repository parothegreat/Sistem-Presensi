<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWaliKelasToStudents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('students', [
            'wali_kelas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'class',
            ],
        ]);

        $this->forge->addForeignKey('wali_kelas_id', 'walikelas', 'id', 'CASCADE', 'SET_NULL', 'students');
    }

    public function down()
    {
        $this->forge->dropForeignKey('students', 'students_wali_kelas_id_foreign');
        $this->forge->dropColumn('students', 'wali_kelas_id');
    }
}
