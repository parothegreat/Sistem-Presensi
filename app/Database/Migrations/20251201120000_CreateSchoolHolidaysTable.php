<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchoolHolidaysTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'date_from' => ['type' => 'DATE'],
            'date_to' => ['type' => 'DATE'],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'type' => ['type' => 'ENUM', 'constraint' => ['national_holiday', 'school_activity', 'special'], 'default' => 'school_activity'],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('date_from', false);
        $this->forge->addKey('date_to', false);
        $this->forge->createTable('school_holidays');
    }

    public function down()
    {
        $this->forge->dropTable('school_holidays');
    }
}
