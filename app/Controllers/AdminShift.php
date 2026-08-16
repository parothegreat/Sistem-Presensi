<?php

namespace App\Controllers;

use App\Models\ShiftModel;
use CodeIgniter\Controller;

class AdminShift extends Controller
{
    /**
     * Display list of shifts
     */
    public function index()
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $shiftModel = new ShiftModel();
        $shifts = $shiftModel->orderBy('id', 'ASC')->findAll();

        return view('admin/shifts/index', [
            'shifts' => $shifts,
        ]);
    }

    /**
     * Show create shift form
     */
    public function create()
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        return view('admin/shifts/form', [
            'shift' => null,
            'title' => 'Tambah Shift Baru',
        ]);
    }

    /**
     * Store new shift
     */
    public function store()
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $shiftModel = new ShiftModel();

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'checkin_deadline' => $this->request->getPost('checkin_deadline'),
            'checkout_earliest' => $this->request->getPost('checkout_earliest'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Validate
        if (! $shiftModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $shiftModel->errors());
        }

        $shiftModel->insert($data);

        return redirect()->to('/admin/shifts')->with('success', 'Shift berhasil ditambahkan');
    }

    /**
     * Show edit shift form
     */
    public function edit($id)
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $shiftModel = new ShiftModel();
        $shift = $shiftModel->find($id);

        if (! $shift) {
            return redirect()->to('/admin/shifts')->with('error', 'Shift tidak ditemukan');
        }

        return view('admin/shifts/form', [
            'shift' => $shift,
            'title' => 'Edit Shift',
        ]);
    }

    /**
     * Update shift
     */
    public function update($id)
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $shiftModel = new ShiftModel();
        $shift = $shiftModel->find($id);

        if (! $shift) {
            return redirect()->to('/admin/shifts')->with('error', 'Shift tidak ditemukan');
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'checkin_deadline' => $this->request->getPost('checkin_deadline'),
            'checkout_earliest' => $this->request->getPost('checkout_earliest'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Validate
        if (! $shiftModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $shiftModel->errors());
        }

        $shiftModel->update($id, $data);

        return redirect()->to('/admin/shifts')->with('success', 'Shift berhasil diperbarui');
    }

    /**
     * Delete shift
     */
    public function delete($id)
    {
        // Require login
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $shiftModel = new ShiftModel();
        $shift = $shiftModel->find($id);

        if (! $shift) {
            return redirect()->to('/admin/shifts')->with('error', 'Shift tidak ditemukan');
        }

        // Check if any students are using this shift
        $studentModel = new \App\Models\StudentModel();
        $studentCount = $studentModel->where('shift_id', $id)->countAllResults();

        if ($studentCount > 0) {
            return redirect()->to('/admin/shifts')->with('error', 'Tidak bisa menghapus shift karena masih digunakan oleh ' . $studentCount . ' siswa');
        }

        $shiftModel->delete($id);

        return redirect()->to('/admin/shifts')->with('success', 'Shift berhasil dihapus');
    }
}
