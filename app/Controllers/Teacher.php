<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\TeacherModel;
use App\Models\UserModel;

class Teacher extends Controller
{
    protected $teacherModel;
    protected $userModel;

    public function __construct()
    {
        $this->teacherModel = new TeacherModel();
        $this->userModel = new UserModel();
    }

    /**
     * List all teachers
     */
    public function index()
    {
        $teachers = $this->teacherModel->getTeachersWithUser();

        return view('admin/teachers/index', [
            'teachers' => $teachers,
            'title' => 'Kelola Guru'
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Get available guru users (those with role 'guru' but no profile yet)
        $guruUsers = $this->userModel->where('role', 'guru')->findAll();
        $linkedGuruIds = $this->teacherModel->select('user_id')->findAll();
        $linkedIds = array_column($linkedGuruIds, 'user_id');
        $availableGuru = array_filter($guruUsers, function ($u) use ($linkedIds) {
            return !in_array($u['id'], $linkedIds);
        });

        return view('admin/teachers/create', [
            'availableGuru' => $availableGuru,
            'title' => 'Tambah Profil Guru'
        ]);
    }

    /**
     * Store new teacher profile
     */
    public function store()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'user_id' => 'required|is_unique[teachers.user_id]',
            'nip' => 'permit_empty|max_length[50]',
            'full_name' => 'required|max_length[150]',
            'subject' => 'required|max_length[100]',
            'phone_number' => 'permit_empty|max_length[20]',
            'telegram_chat_id' => 'permit_empty|max_length[50]',
            'rfid_id' => 'permit_empty|max_length[50]',
            'photo' => 'permit_empty|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png]|max_size[photo,2048]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Handle photo upload
        $photoFile = $this->request->getFile('photo');
        $photoPath = null;
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $newName = $photoFile->getRandomName();
            $photoFile->move(FCPATH . 'uploads/teachers', $newName);
            $photoPath = 'uploads/teachers/' . $newName;
        }

        $this->teacherModel->insert([
            'user_id' => $this->request->getPost('user_id'),
            'nip' => $this->request->getPost('nip'),
            'full_name' => $this->request->getPost('full_name'),
            'subject' => $this->request->getPost('subject'),
            'photo' => $photoPath,
            'phone_number' => $this->request->getPost('phone_number'),
            'telegram_chat_id' => $this->request->getPost('telegram_chat_id'),
            'rfid_id' => $this->request->getPost('rfid_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/teachers')->with('success', 'Profil guru berhasil ditambahkan');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $teacher = $this->teacherModel->find($id);
        if (! $teacher) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Guru tidak ditemukan");
        }

        return view('admin/teachers/edit', [
            'teacher' => $teacher,
            'title' => 'Edit Profil Guru',
            // 'errors' => []
        ]);
    }

    /**
     * Update teacher
     */
    public function update($id)
    {
        $teacher = $this->teacherModel->find($id);
        if (! $teacher) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Guru tidak ditemukan");
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'full_name' => 'required|max_length[150]',
            'subject' => 'required|max_length[100]',
            'nip' => 'permit_empty|max_length[50]',
            'phone_number' => 'permit_empty|max_length[20]',
            'telegram_chat_id' => 'permit_empty|max_length[50]',
            'rfid_id' => 'permit_empty|max_length[50]',
            'photo' => 'permit_empty|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png]|max_size[photo,2048]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Handle photo upload
        $photoFile = $this->request->getFile('photo');
        $updateData = [
            'nip' => $this->request->getPost('nip'),
            'full_name' => $this->request->getPost('full_name'),
            'subject' => $this->request->getPost('subject'),
            'phone_number' => $this->request->getPost('phone_number'),
            'telegram_chat_id' => $this->request->getPost('telegram_chat_id'),
            'rfid_id' => $this->request->getPost('rfid_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            // Delete old photo
            if ($teacher['photo'] && file_exists(FCPATH . $teacher['photo'])) {
                unlink(FCPATH . $teacher['photo']);
            }
            // Move new photo
            $newName = $photoFile->getRandomName();
            $photoFile->move(FCPATH . 'uploads/teachers', $newName);
            $updateData['photo'] = 'uploads/teachers/' . $newName;
        }

        $this->teacherModel->update($id, $updateData);

        return redirect()->to('/admin/teachers')->with('success', 'Profil guru berhasil diperbarui');
    }

    /**
     * Delete teacher
     */
    public function delete($id)
    {
        $teacher = $this->teacherModel->find($id);
        if (! $teacher) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Guru tidak ditemukan");
        }

        $this->teacherModel->delete($id);

        return redirect()->to('/admin/teachers')->with('success', 'Profil guru berhasil dihapus');
    }

    /**
     * Import page for teachers
     */
    public function importPage()
    {
        return view('admin/teachers/import', [
            'title' => 'Import Guru'
        ]);
    }

    /**
     * Handle CSV/Excel import for teachers
     */
    public function import()
    {
        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak ditemukan atau tidak valid');
        }

        $extension = strtolower($file->getClientExtension());
        $allowedExtensions = ['csv', 'xlsx', 'xls'];
        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->with('error', 'File harus berformat CSV, XLSX, atau XLS');
        }

        try {
            $rows = $extension === 'csv' ? $this->parseCSV($file) : $this->parseExcel($file);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            return redirect()->back()->with('error', 'File kosong atau hanya memiliki header');
        }

        $header = array_map('trim', $rows[0]);
        $requiredColumns = ['full_name', 'subject', 'username', 'password'];
        foreach ($requiredColumns as $col) {
            if (!in_array($col, $header)) {
                return redirect()->back()->with('error', "Kolom '$col' tidak ditemukan di file (wajib)");
            }
        }

        $columnIndices = array_flip($header);
        $successCount = 0;
        $errors = [];
        $db = \Config\Database::connect();

        foreach (array_slice($rows, 1) as $lineNum => $row) {
            $rowNum = $lineNum + 2;
            if (empty(array_filter($row))) continue;

            $fullName = trim($row[$columnIndices['full_name']] ?? '');
            $subject = trim($row[$columnIndices['subject']] ?? '');
            $username = trim($row[$columnIndices['username']] ?? '');
            $password = trim($row[$columnIndices['password']] ?? '');
            $nip = isset($columnIndices['nip']) ? trim($row[$columnIndices['nip']] ?? '') : '';

            if (empty($fullName) || empty($subject) || empty($username) || empty($password)) {
                $errors[] = "Baris $rowNum: Data tidak lengkap (full_name, subject, username, password wajib)";
                continue;
            }

            if (strlen($password) < 6) {
                $errors[] = "Baris $rowNum: Password minimal 6 karakter";
                continue;
            }

            $existingUser = $this->userModel->where('username', $username)->first();
            if ($existingUser) {
                $errors[] = "Baris $rowNum: Username '$username' sudah digunakan";
                continue;
            }

            $db->transStart();
            try {
                $userId = $this->userModel->insert([
                    'username' => $username,
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'role' => 'guru',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                if (!$userId) {
                    throw new \Exception("Gagal membuat user untuk baris $rowNum");
                }

                $this->teacherModel->insert([
                    'user_id' => $userId,
                    'nip' => $nip ?: null,
                    'full_name' => $fullName,
                    'subject' => $subject,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $db->transComplete();
                $successCount++;
            } catch (\Exception $e) {
                $db->transRollback();
                $errors[] = "Baris $rowNum: " . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            session()->setFlashdata('success', 'Import guru berhasil!');
            session()->setFlashdata('imported_count', $successCount);
        }

        if (!empty($errors)) {
            session()->setFlashdata('error', 'Beberapa baris mengalami kesalahan:');
            session()->setFlashdata('errors', $errors);
            return redirect()->to('/admin/teachers/import');
        }

        if ($successCount === 0) {
            return redirect()->to('/admin/teachers/import')->with('error', 'Tidak ada guru yang berhasil diimport');
        }

        return redirect()->to('/admin/teachers')->with('success', "Import guru berhasil! Total: $successCount guru");
    }

    private function parseCSV($file)
    {
        $content = file_get_contents($file->getTempName());
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        $data = [];
        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                $data[] = str_getcsv($line);
            }
        }
        return $data;
    }

    private function parseExcel($file)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = trim((string) $cell->getValue());
            }
            if (!empty(array_filter($rowData))) {
                $data[] = $rowData;
            }
        }
        return $data;
    }
}
