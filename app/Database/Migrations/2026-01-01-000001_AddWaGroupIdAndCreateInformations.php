<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWaGroupIdAndCreateInformations extends Migration
{
    public function up()
    {
        // Add wa_group_id to walikelas
        $this->forge->addColumn('walikelas', [
            'wa_group_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'class_name'
            ]
        ]);

        // Create informations table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'content' => [
                'type' => 'TEXT',
            ],
            'target_classes' => [
                'type' => 'TEXT', // JSON array of class IDs
                'null' => true,
            ],
            'send_via_wa' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('informations');
    }

    public function down()
    {
        $this->forge->dropTable('informations');
        $this->forge->dropColumn('walikelas', 'wa_group_id');
    }
}
