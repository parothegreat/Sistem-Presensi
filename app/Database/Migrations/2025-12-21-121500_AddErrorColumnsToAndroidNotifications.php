<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddErrorColumnsToAndroidNotifications extends Migration
{
    public function up()
    {
        $fields = [
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'attempts',
            ],
            'last_error_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'last_error',
            ],
            'failed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'sent_at',
            ],
        ];

        $this->forge->addColumn('android_notifications', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('android_notifications', ['last_error', 'last_error_code', 'failed_at']);
    }
}
