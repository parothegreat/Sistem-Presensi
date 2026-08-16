<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTelegramLinkConfigTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pin' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('telegram_link_config', true);
    }

    public function down()
    {
        $this->forge->dropTable('telegram_link_config', true);
    }
}
