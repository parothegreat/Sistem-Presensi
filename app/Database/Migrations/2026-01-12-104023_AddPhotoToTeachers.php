<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToTeachers extends Migration
{
    public function up()
    {
        $fields = [
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'subject', // Place it after subject column for better organization
            ],
        ];

        // Check if field exists before adding to avoid errors
        if (!$this->db->fieldExists('photo', 'teachers')) {
            $this->forge->addColumn('teachers', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('photo', 'teachers')) {
            $this->forge->dropColumn('teachers', 'photo');
        }
    }
}
