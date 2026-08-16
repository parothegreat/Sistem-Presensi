<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    public function login()
    {
        helper(['form', 'url']);
        $settingsModel = new \App\Models\SettingsModel();
        $data = [
            'settings' => $settingsModel->getAll()
        ];
        return view('auth/login', $data);
    }

    public function authenticate()
    {
        helper('url');
        $session = session();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Use UserModel to check credentials
        $userModel = new \App\Models\UserModel();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            $sessionData = [
                'isLoggedIn' => true,
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
            ];

            // For siswa, also store full_name from students table
            if ($user['role'] === 'siswa') {
                $studentModel = new \App\Models\StudentModel();
                $student = $studentModel->where('user_id', $user['id'])->first();
                if ($student) {
                    $sessionData['full_name'] = $student['full_name'];
                }
            } elseif ($user['role'] === 'guru') {
                // For guru, store full_name from teachers table
                $teacherModel = new \App\Models\TeacherModel();
                $teacher = $teacherModel->where('user_id', $user['id'])->first();
                if ($teacher) {
                    $sessionData['full_name'] = $teacher['full_name'];
                }
            } elseif ($user['role'] === 'admin') {
                // For admin, store full_name from teachers table (if admin is also a teacher)
                // Otherwise use username as fallback
                $teacherModel = new \App\Models\TeacherModel();
                $teacher = $teacherModel->where('user_id', $user['id'])->first();
                if ($teacher) {
                    $sessionData['full_name'] = $teacher['full_name'];
                } else {
                    $sessionData['full_name'] = $user['username'];
                }
            }

            $session->set($sessionData);

            // Redirect users to their role-specific dashboard to avoid role filter 403
            switch ($user['role']) {
                case 'guru':
                    return redirect()->to('/guru/dashboard');
                case 'siswa':
                    return redirect()->to('/siswa/dashboard');
                case 'petugas':
                    return redirect()->to('/petugas/dashboard');
                case 'admin':
                default:
                    return redirect()->to('/admin/dashboard');
            }
        }

        $session->setFlashdata('error', 'Username atau password salah');
        return redirect()->to('/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }

    public function dashboard()
    {
        // Deprecated: admin dashboard moved to Admin::dashboard
        // Keep method for backward compatibility but redirect to new controller
        return redirect()->to('/admin/dashboard');
    }
}
