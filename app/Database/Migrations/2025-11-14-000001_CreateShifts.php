<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShifts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'start_time' => [
                'type' => 'TIME',
                'comment' => 'Jam mulai shift (contoh: 07:00)',
            ],
            'end_time' => [
                'type' => 'TIME',
                'comment' => 'Jam akhir shift (contoh: 13:00)',
            ],
            'checkin_deadline' => [
                'type' => 'TIME',
                'comment' => 'Batas masuk tepat waktu (contoh: 07:30)',
            ],
            'checkout_earliest' => [
                'type' => 'TIME',
                'comment' => 'Mulai bisa pulang (contoh: 15:00)',
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('shifts');
    }

    public function down()
    {
        $this->forge->dropTable('shifts');
    }
}
