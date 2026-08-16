<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWalikelasTable extends Migration
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
            'teacher_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'class_name' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'academic_year' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
            ],
            'semester' => [
                'type' => 'ENUM',
                'constraint' => ['1', '2'],
                'default' => '1',
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addForeignKey('teacher_id', 'teachers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['class_name', 'academic_year', 'semester'], 'unique_class_per_year');
        $this->forge->createTable('walikelas', true);
    }

    public function down()
    {
        $this->forge->dropTable('walikelas', true);
    }
}
