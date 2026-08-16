<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PetugasUserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new \App\Models\UserModel();

        // Check if user already exists
        $existingUser = $userModel->where('username', 'petugas')->first();

        if (!$existingUser) {
            $data = [
                'username'      => 'petugas',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'role'          => 'petugas',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            $userModel->insert($data);
            echo "User 'petugas' created successfully.\n";
        } else {
            echo "User 'petugas' already exists.\n";
        }
    }
}
