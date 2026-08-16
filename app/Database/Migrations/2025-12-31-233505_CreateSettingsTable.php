<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{    public function up()
    {
        $this->forge->addField([
            'key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'group' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'general',
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'string', // string, boolean, integer, image
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

        $this->forge->addKey('key', true);
        $this->forge->addKey('group');
        $this->forge->createTable('settings');

        // Insert default settings
        $data = [
            [
                'key' => 'school_name',
                'value' => 'SMK Mitra Industri',
                'group' => 'general',
                'type' => 'string',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'school_npsn',
                'value' => '12345678',
                'group' => 'general',
                'type' => 'string',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'school_logo',
                'value' => null,
                'group' => 'general',
                'type' => 'image',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('settings')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
