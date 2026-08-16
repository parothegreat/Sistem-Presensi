<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueIndexWalikelasTeacher20251113093000 extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Check for duplicate teacher_id entries first
        $query = $db->query("SELECT teacher_id, COUNT(*) AS c FROM walikelas WHERE teacher_id IS NOT NULL GROUP BY teacher_id HAVING c > 1");
        $duplicates = $query->getResultArray();

        if (! empty($duplicates)) {
            $ids = array_map(fn($r) => $r['teacher_id'], $duplicates);
            throw new \Exception('Cannot add UNIQUE index: duplicate teacher_id found for teacher_id(s): ' . implode(', ', $ids));
        }

        // Add unique index on teacher_id
        // Use raw SQL to ensure compatibility with existing table structure
        $db->query("ALTER TABLE `walikelas` ADD UNIQUE KEY `uq_walikelas_teacher_id` (`teacher_id`)");
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // Drop the unique index if it exists
        try {
            $db->query("ALTER TABLE `walikelas` DROP INDEX `uq_walikelas_teacher_id`");
        } catch (\Exception $e) {
            // Ignore errors on rollback if index doesn't exist
        }
    }
}
