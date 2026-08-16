<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // Admin
        $passwordAdmin = password_hash('admin123', PASSWORD_DEFAULT);
        $this->db->table('users')->insert([
            'username' => 'admin',
            'password_hash' => $passwordAdmin,
            'role' => 'admin',
            'created_at' => $now,
        ]);

        // Guru (teacher)
        $passwordGuru = password_hash('guru123', PASSWORD_DEFAULT);
        $this->db->table('users')->insert([
            'username' => 'guru',
            'password_hash' => $passwordGuru,
            'role' => 'guru',
            'created_at' => $now,
        ]);
        $guruId = $this->db->insertID();
        // insert teacher profile
        $this->db->table('teachers')->insert([
            'user_id' => $guruId,
            'nip' => '1987654321',
            'full_name' => 'Guru Contoh',
            'subject' => 'Matematika',
            'created_at' => $now,
        ]);

        // Siswa (student)
        $passwordSiswa = password_hash('siswa123', PASSWORD_DEFAULT);
        $this->db->table('users')->insert([
            'username' => 'siswa',
            'password_hash' => $passwordSiswa,
            'role' => 'siswa',
            'created_at' => $now,
        ]);
        $siswaId = $this->db->insertID();
        // insert student profile
        $this->db->table('students')->insert([
            'user_id' => $siswaId,
            'nis' => 'S123456',
            'full_name' => 'Siswa Contoh',
            'class' => 'XI-A',
            'created_at' => $now,
        ]);
    }
}
