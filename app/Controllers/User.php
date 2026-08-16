<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class User extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * List all users with role filter
     */
    public function index()
    {
        $role = $this->request->getGet('role');

        if ($role) {
            $users = $this->userModel->where('role', $role)->findAll();
            $title = ucfirst($role);
        } else {
            $users = $this->userModel->findAll();
            $title = 'Semua User';
        }

        return view('admin/users/index', [
            'users' => $users,
            'title' => $title,
            'currentRole' => $role
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin/users/create', [
            'title' => 'Tambah User'
        ]);
    }

    /**
     * Store new user
     */
    public function store()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|is_unique[users.username]|min_length[3]',
            'password' => 'required|min_length[6]',
            'role' => 'required|in_list[admin,guru,petugas,siswa]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return view('admin/users/create', [
                'validation' => $validation,
                'title' => 'Tambah User'
            ]);
        }

        $password_hash = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);

        $this->userModel->insert([
            'username' => $this->request->getPost('username'),
            'password_hash' => $password_hash,
            'role' => $this->request->getPost('role'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/users')->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("User tidak ditemukan");
        }

        return view('admin/users/edit', [
            'user' => $user,
            'title' => 'Edit User'
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("User tidak ditemukan");
        }

        $validation = \Config\Services::validation();

        // Build unique rule excluding current user
        $uniqueRule = "is_unique[users.username,id,{$id}]";

        $validation->setRules([
            'username' => "required|{$uniqueRule}|min_length[3]",
            'role' => 'required|in_list[admin,guru,petugas,siswa]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return view('admin/users/edit', [
                'user' => $user,
                'validation' => $validation,
                'title' => 'Edit User'
            ]);
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'role' => $this->request->getPost('role'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Only update password if provided
        $password = $this->request->getPost('password');
        if ($password) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);

        return redirect()->to('/admin/users')->with('success', 'User berhasil diperbarui');
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("User tidak ditemukan");
        }

        // Prevent deleting self
        if ($user['id'] == session()->get('user_id')) {
            return redirect()->to('/admin/users')->with('error', 'Anda tidak dapat menghapus diri sendiri');
        }

        $this->userModel->delete($id);

        return redirect()->to('/admin/users')->with('success', 'User berhasil dihapus');
    }
}
