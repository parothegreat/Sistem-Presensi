<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAndroidNotificationTables extends Migration
{
    public function up()
    {
        // Create android_notifications table
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nis' => ['type' => 'VARCHAR', 'constraint' => 20],
            'device_token' => ['type' => 'VARCHAR', 'constraint' => 255],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'message' => ['type' => 'TEXT'],
            'notification_status' => ['type' => 'ENUM', 'constraint' => ['pending', 'sent', 'failed'], 'default' => 'pending'],
            'attempts' => ['type' => 'INT', 'default' => 0],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('notification_status');
        $this->forge->addKey('student_id');
        $this->forge->addForeignKey('student_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('android_notifications');

        // Create student_device_tokens table
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'device_token' => ['type' => 'VARCHAR', 'constraint' => 200],
            'device_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'app_version' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('student_id');
        $this->forge->addKey('is_active');
        $this->forge->addUniqueKey('device_token');
        $this->forge->addForeignKey('student_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('student_device_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('android_notifications');
        $this->forge->dropTable('student_device_tokens');
    }
}
