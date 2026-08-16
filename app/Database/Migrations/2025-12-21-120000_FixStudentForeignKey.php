<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixStudentForeignKey extends Migration
{
    public function up()
    {
        // Drop existing foreign keys
        // Note: CI4 usually names FKs as table_column_foreign
        // Drop existing foreign keys if they exist
        $dbName = $this->db->getDatabase();

        // Check and drop student_device_tokens FK
        $check1 = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = 'student_device_tokens' AND CONSTRAINT_NAME = 'student_device_tokens_student_id_foreign'")->getRow();
        if ($check1) {
            $this->db->query("ALTER TABLE `student_device_tokens` DROP FOREIGN KEY `student_device_tokens_student_id_foreign`");
        }

        // Check and drop android_notifications FK
        $check2 = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = 'android_notifications' AND CONSTRAINT_NAME = 'android_notifications_student_id_foreign'")->getRow();
        if ($check2) {
            $this->db->query("ALTER TABLE `android_notifications` DROP FOREIGN KEY `android_notifications_student_id_foreign`");
        }

        // Add new foreign keys referencing students table
        $this->db->query("ALTER TABLE `student_device_tokens` ADD CONSTRAINT `student_device_tokens_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
        $this->db->query("ALTER TABLE `android_notifications` ADD CONSTRAINT `android_notifications_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
    }

    public function down()
    {
        // Revert changes
        $this->db->query("ALTER TABLE `student_device_tokens` DROP FOREIGN KEY `student_device_tokens_student_id_foreign`");
        $this->db->query("ALTER TABLE `android_notifications` DROP FOREIGN KEY `android_notifications_student_id_foreign`");

        $this->db->query("ALTER TABLE `student_device_tokens` ADD CONSTRAINT `student_device_tokens_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
        $this->db->query("ALTER TABLE `android_notifications` ADD CONSTRAINT `android_notifications_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
    }
}
