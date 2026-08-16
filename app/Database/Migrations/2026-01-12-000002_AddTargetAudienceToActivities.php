<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetAudienceToActivities extends Migration
{
    public function up()
    {
        $this->forge->addColumn('activities', [
            'target_audience' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'description'
            ]
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('target_audience', 'activities')) {
            $this->forge->dropColumn('activities', 'target_audience');
        }
    }
}
