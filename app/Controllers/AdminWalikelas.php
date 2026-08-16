<?php

namespace App\Controllers;

use App\Models\WalikelasModel;
use App\Models\TeacherModel;
use CodeIgniter\Controller;

class AdminWalikelas extends Controller
{
    protected $walikelasModel;
    protected $teacherModel;

    public function __construct()
    {
        $this->walikelasModel = new WalikelasModel();
        $this->teacherModel = new TeacherModel();
    }

    public function index()
    {
        if (! session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Kelola Wali Kelas',
            'walikelas' => $this->walikelasModel->getAllActive(),
        ];

        return view('admin/walikelas/index', $data);
    }

    public function create()
    {
        if (! session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Buat Wali Kelas',
            'teachers' => $this->teacherModel->getTeachersWithUser(),
        ];

        return view('admin/walikelas/create', $data);
    }

    public function store()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/walikelas');
        }

        $teacher_id = $this->request->getPost('teacher_id');
        $class_name = $this->request->getPost('class_name');

        $rules = [
            'teacher_id' => 'required|integer',
            'class_name' => 'required|string|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check if teacher already assigned as wali kelas (one teacher -> one class)
        $existingTeacher = $this->walikelasModel
            ->where('teacher_id', $teacher_id)
            ->where('is_active', true)
            ->first();

        if ($existingTeacher) {
            return redirect()->back()->withInput()->with('error', 'Guru ini sudah menjadi wali kelas untuk kelas lain');
        }

        // Get current academic year and semester
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');
        $academicYear = $currentMonth >= 7 ? ($currentYear . '/' . ($currentYear + 1)) : (($currentYear - 1) . '/' . $currentYear);
        $semester = $currentMonth >= 7 || $currentMonth <= 6 ? ($currentMonth >= 7 ? 1 : 2) : 1;

        // Check for duplicate class in same academic year and semester
        $existingClass = $this->walikelasModel
            ->where('class_name', $class_name)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->first();

        if ($existingClass) {
            return redirect()->back()->withInput()->with('error', "Kelas $class_name sudah ada untuk Tahun Ajaran $academicYear Semester $semester");
        }

        $payload = [
            'teacher_id' => $teacher_id,
            'class_name' => $class_name,
            'academic_year' => $academicYear,
            'semester' => $semester,
            'is_active' => 1,
            'wa_group_id' => $this->request->getPost('wa_group_id'),
        ];

        if ($id = $this->walikelasModel->insert($payload)) {
            // Auto-sync students: Update all students in this class to have this wali_kelas_id
            $this->teacherModel->db->table('students')
                ->where('class', $class_name)
                ->update(['wali_kelas_id' => $id]);

            return redirect()->to('/admin/walikelas')->with('success', 'Wali kelas berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan wali kelas');
        }
    }

    public function edit($id)
    {
        // ... (unchanged)
        if (! session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $item = $this->walikelasModel->find($id);
        if (! $item) return redirect()->to('/admin/walikelas')->with('error', 'Data tidak ditemukan');

        $data = [
            'title' => 'Edit Wali Kelas',
            'item' => $item,
            'teachers' => $this->teacherModel->getTeachersWithUser(),
        ];

        return view('admin/walikelas/edit', $data);
    }

    public function update($id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/walikelas');
        }

        $teacher_id = $this->request->getPost('teacher_id');
        $class_name = $this->request->getPost('class_name');

        $rules = [
            'teacher_id' => 'required|integer',
            'class_name' => 'required|string|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check if teacher already assigned as wali kelas (one teacher -> one class), exclude current record
        $existingTeacher = $this->walikelasModel
            ->where('teacher_id', $teacher_id)
            ->where('id !=', $id)
            ->first();

        if ($existingTeacher) {
            return redirect()->back()->withInput()->with('error', 'Guru ini sudah menjadi wali kelas untuk kelas lain');
        }

        // Check for duplicate class in same academic year and semester (excluding current record)
        $currentRecord = $this->walikelasModel->find($id);
        if ($currentRecord) {
            $duplicateClass = $this->walikelasModel
                ->where('class_name', $class_name)
                ->where('academic_year', $currentRecord['academic_year'])
                ->where('semester', $currentRecord['semester'])
                ->where('id !=', $id)
                ->first();

            if ($duplicateClass) {
                return redirect()->back()->withInput()->with('error', "Kelas $class_name sudah ada untuk Tahun Ajaran {$currentRecord['academic_year']} Semester {$currentRecord['semester']}");
            }
        }

        $payload = [
            'teacher_id' => $teacher_id,
            'class_name' => $class_name,
            'wa_group_id' => $this->request->getPost('wa_group_id'),
        ];

        if ($this->walikelasModel->update($id, $payload)) {
            // Auto-sync students: Update all students in this class to have this wali_kelas_id
            // First, reset any students who might have had this wali_kelas_id but are not in the new class (if class name changed)
            // But realistically, we just want to claim the students in the new class name.
            $this->teacherModel->db->table('students')
                ->where('class', $class_name)
                ->update(['wali_kelas_id' => $id]);

            return redirect()->to('/admin/walikelas')->with('success', 'Wali kelas berhasil diupdate');
        }
    }

    public function delete($id)
    {
        if (! session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        // Check if there are students in this class
        $studentCount = $this->teacherModel->db->table('students')
            ->where('wali_kelas_id', $id)
            ->countAllResults();

        if ($studentCount > 0) {
            return redirect()->to('/admin/walikelas')->with('error', "Gagal menghapus: Masih ada $studentCount siswa di kelas ini. Silakan pindahkan siswa terlebih dahulu.");
        }

        if (! $this->walikelasModel->delete($id)) {
            return redirect()->to('/admin/walikelas')->with('error', 'Gagal menghapus data');
        }

        return redirect()->to('/admin/walikelas')->with('success', 'Wali kelas berhasil dihapus');
    }
}
