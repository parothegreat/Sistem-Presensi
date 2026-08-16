<?php

namespace App\Controllers;

use App\Models\SchoolHolidayModel;
use CodeIgniter\Controller;

class AdminHoliday extends Controller
{
    /**
     * Display list of holidays
     */
    public function index()
    {
        // Require login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $holidayModel = new SchoolHolidayModel();
        $holidays = $holidayModel->orderBy('date_from', 'ASC')->findAll();

        return view('admin/holidays/index', [
            'holidays' => $holidays,
        ]);
    }

    /**
     * Show create holiday form
     */
    public function create()
    {
        // Require login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        return view('admin/holidays/form', [
            'holiday' => null,
            'title' => 'Tambah Hari Libur Baru',
        ]);
    }

    /**
     * Store new holiday
     */
    public function store()
    {
        // Require login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $holidayModel = new SchoolHolidayModel();

        $data = [
            'date_from' => $this->request->getPost('date_from'),
            'date_to' => $this->request->getPost('date_to'),
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'description' => $this->request->getPost('description') ?? null,
        ];

        // Validate
        $rules = [
            'date_from' => 'required|valid_date',
            'date_to' => 'required|valid_date',
            'name' => 'required|min_length[3]|max_length[255]',
            'type' => 'required|in_list[national_holiday,school_activity,special]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check for overlapping dates
        $overlapping = $holidayModel->checkOverlap($data['date_from'], $data['date_to']);
        if (!empty($overlapping)) {
            $message = $holidayModel->getOverlapMessage($overlapping);
            return redirect()->back()->withInput()->with('error', $message);
        }

        if ($holidayModel->insert($data)) {
            return redirect()->to('/admin/holidays')->with('success', 'Hari libur berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan hari libur');
    }

    /**
     * Show edit holiday form
     */
    public function edit($id)
    {
        // Require login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $holidayModel = new SchoolHolidayModel();
        $holiday = $holidayModel->find($id);

        if (!$holiday) {
            return redirect()->to('/admin/holidays')->with('error', 'Hari libur tidak ditemukan');
        }

        return view('admin/holidays/form', [
            'holiday' => $holiday,
            'title' => 'Edit Hari Libur',
        ]);
    }

    /**
     * Update holiday
     */
    public function update($id)
    {
        // Require login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $holidayModel = new SchoolHolidayModel();
        $holiday = $holidayModel->find($id);

        if (!$holiday) {
            return redirect()->to('/admin/holidays')->with('error', 'Hari libur tidak ditemukan');
        }

        $data = [
            'date_from' => $this->request->getPost('date_from'),
            'date_to' => $this->request->getPost('date_to'),
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'description' => $this->request->getPost('description') ?? null,
        ];

        // Validate
        $rules = [
            'date_from' => 'required|valid_date',
            'date_to' => 'required|valid_date',
            'name' => 'required|min_length[3]|max_length[255]',
            'type' => 'required|in_list[national_holiday,school_activity,special]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check for overlapping dates (exclude current holiday)
        $overlapping = $holidayModel->checkOverlap($data['date_from'], $data['date_to'], $id);
        if (!empty($overlapping)) {
            $message = $holidayModel->getOverlapMessage($overlapping);
            return redirect()->back()->withInput()->with('error', $message);
        }

        if ($holidayModel->update($id, $data)) {
            return redirect()->to('/admin/holidays')->with('success', 'Hari libur berhasil diperbarui');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui hari libur');
    }

    /**
     * Delete holiday
     */
    public function delete($id)
    {
        // Require login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Require admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/');
        }

        $holidayModel = new SchoolHolidayModel();
        $holiday = $holidayModel->find($id);

        if (!$holiday) {
            return redirect()->to('/admin/holidays')->with('error', 'Hari libur tidak ditemukan');
        }

        if ($holidayModel->delete($id)) {
            return redirect()->to('/admin/holidays')->with('success', 'Hari libur berhasil dihapus');
        }

        return redirect()->to('/admin/holidays')->with('error', 'Gagal menghapus hari libur');
    }
}
