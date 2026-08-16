<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEndDateToPermissionsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('permissions', [
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'date',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('permissions', 'end_date');
    }
}
