<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeToInformations extends Migration
{
    public function up()
    {
        $fields = [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['student', 'teacher'],
                'default'    => 'student',
                'after'      => 'content',
            ],
        ];
        $this->forge->addColumn('informations', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('informations', 'type');
    }
}
