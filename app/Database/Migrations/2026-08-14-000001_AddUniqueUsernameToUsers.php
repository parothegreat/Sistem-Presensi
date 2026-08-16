<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueUsernameToUsers extends Migration
{
    public function up()
    {
        // Buang akun duplikat yang password_hash-nya bukan bcrypt.
        // password_verify() selalu false untuk hash semacam itu, jadi akunnya
        // tidak pernah bisa dipakai login. Yang id-nya paling kecil dipertahankan.
        $sql = <<<'SQL'
            DELETE dup FROM users dup
            JOIN users keep ON keep.username = dup.username AND keep.id < dup.id
            WHERE dup.password_hash NOT LIKE '$2y$%'
            SQL;
        $this->db->query($sql);

        // Sengaja tidak menghapus duplikat yang dua-duanya bcrypt: itu akun asli,
        // dan ALTER di bawah akan gagal keras supaya ditangani manual.
        $this->db->query('ALTER TABLE `users` DROP INDEX `username`, ADD UNIQUE KEY `username` (`username`)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `users` DROP INDEX `username`, ADD KEY `username` (`username`)');
    }
}
