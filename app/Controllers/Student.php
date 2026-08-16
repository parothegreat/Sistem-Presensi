<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StudentModel;
use App\Models\UserModel;
use App\Models\WalikelasModel;
use App\Models\ShiftModel;

class Student extends Controller
{
    protected $studentModel;
    protected $userModel;
    protected $walikelasModel;
    protected $shiftModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->userModel = new UserModel();
        $this->walikelasModel = new WalikelasModel();
        $this->shiftModel = new ShiftModel();
    }

    /**
     * List all students
     */
    public function index()
    {
        $students = $this->studentModel->getStudentsWithUser();

        return view('admin/students/index', [
            'students' => $students,
            'title' => 'Kelola Siswa'
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Get available siswa users (those with role 'siswa' but no profile yet)
        $siswaUsers = $this->userModel->where('role', 'siswa')->findAll();
        $linkedSiswaIds = $this->studentModel->select('user_id')->findAll();
        $linkedIds = array_column($linkedSiswaIds, 'user_id');
        $availableSiswa = array_filter($siswaUsers, function ($u) use ($linkedIds) {
            return !in_array($u['id'], $linkedIds);
        });

        return view('admin/students/create', [
            'availableSiswa' => $availableSiswa,
            'walikelas' => $this->walikelasModel->findAll(),
            'shifts' => $this->shiftModel->findAll(),
            'title' => 'Tambah Profil Siswa'
        ]);
    }

    /**
     * Store new student profile
     */
    public function store()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'user_id' => 'required|is_unique[students.user_id]',
            'nis' => 'required|max_length[50]|is_unique[students.nis]',
            'full_name' => 'required|max_length[150]',
            'class' => 'required|max_length[50]',
            'religion' => 'permit_empty|max_length[50]',
            'rfid_id' => 'permit_empty|is_unique[students.rfid_id]',
            'photo' => 'permit_empty|is_image[photo]|max_size[photo,2048]|mime_in[photo,image/jpg,image/jpeg,image/png]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->studentModel->insert([
            'user_id' => $this->request->getPost('user_id'),
            'nis' => $this->request->getPost('nis'),
            'full_name' => $this->request->getPost('full_name'),
            'religion' => $this->request->getPost('religion') ?: null,
            'telegram_chat_id' => $this->request->getPost('telegram_chat_id') ?? null,
            'class' => $this->request->getPost('class'),
            'wali_kelas_id' => $this->request->getPost('wali_kelas_id') ?? null,
            'shift_id' => $this->request->getPost('shift_id') ?? null,
            'phone_number' => $this->request->getPost('phone_number') ?: null,
            'guardian_name' => $this->request->getPost('guardian_name') ?: null,
            'guardian_phone' => $this->request->getPost('guardian_phone') ?: null,
            'rfid_id' => $this->request->getPost('rfid_id') ?: null,
            'photo' => $this->_uploadPhoto(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/students')->with('success', 'Profil siswa berhasil ditambahkan');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Siswa tidak ditemukan");
        }

        return view('admin/students/edit', [
            'student' => $student,
            'walikelas' => $this->walikelasModel->findAll(),
            'shifts' => $this->shiftModel->findAll(),
            'title' => 'Edit Profil Siswa'
        ]);
    }

    /**
     * Update student
     */
    public function update($id)
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Siswa tidak ditemukan");
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'full_name' => 'required|max_length[150]',
            'class' => 'required|max_length[50]',
            'religion' => 'permit_empty|max_length[50]',
            'nis' => "required|max_length[50]|is_unique[students.nis,id,{$id}]",
            // Unique check but ignore current record
            // Unique check but ignore current record
            'rfid_id' => "permit_empty|is_unique[students.rfid_id,id,{$id}]",
            'photo' => 'permit_empty|is_image[photo]|max_size[photo,2048]|mime_in[photo,image/jpg,image/jpeg,image/png]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->studentModel->update($id, [
            'nis' => $this->request->getPost('nis'),
            'full_name' => $this->request->getPost('full_name'),
            'religion' => $this->request->getPost('religion') ?: null,
            'telegram_chat_id' => $this->request->getPost('telegram_chat_id') ?? null,
            'class' => $this->request->getPost('class'),
            'wali_kelas_id' => $this->request->getPost('wali_kelas_id') ?? null,
            'shift_id' => $this->request->getPost('shift_id') ?? null,
            'phone_number' => $this->request->getPost('phone_number') ?: null,
            'guardian_name' => $this->request->getPost('guardian_name') ?: null,
            'guardian_phone' => $this->request->getPost('guardian_phone') ?: null,
            'rfid_id' => $this->request->getPost('rfid_id') ?: null,
            'photo' => $this->_uploadPhoto($student['photo']),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/students')->with('success', 'Profil siswa berhasil diperbarui');
    }

    /**
     * Delete student
     */
    public function delete($id)
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Siswa tidak ditemukan");
        }

        // Delete User as well to keep DB clean
        if (!empty($student['user_id'])) {
            $this->userModel->delete($student['user_id']);
        }
        $this->studentModel->delete($id);

        return redirect()->to('/admin/students')->with('success', 'Profil siswa dan akun user berhasil dihapus');
    }

    /**
     * Bulk Delete Students
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->to('/admin/students')->with('error', 'Tidak ada siswa yang dipilih');
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            $student = $this->studentModel->find($id);
            if ($student) {
                // Delete User first (or after, depending on foreign keys. usually safe if no strict cascade prevent)
                // Ideally delete student first if foreign key refers to user, or user first if cascade.
                // Let's delete student first then user.
                $this->studentModel->delete($id);

                if (!empty($student['user_id'])) {
                    $this->userModel->delete($student['user_id']);
                }
                $deletedCount++;
            }
        }

        return redirect()->to('/admin/students')->with('success', "$deletedCount data siswa beserta akun user berhasil dihapus");
    }

    /**
     * Generate a one-time link token for parent to /link with bot
     */
    public function generateLink($id)
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan');
        }

        // Use global PIN config if available (single PIN for all students).
        $configModel = new \App\Models\TelegramLinkConfigModel();
        $config = $configModel->orderBy('id', 'DESC')->first();

        if (!$config || empty($config['pin'])) {
            // create numeric PIN (6 digits) and store as global PIN
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $configModel->insert([
                'pin' => $pin,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $pin = $config['pin'];
        }

        // Show instructions to admin. Use NIS + PIN format for security.
        $nis = $student['nis'] ?? $student['id'];
        return redirect()->to('/admin/students/' . $id . '/edit')->with('success', "Global PIN: <code>$pin</code>. Minta wali kirim ke bot: /link $nis $pin");
    }

    /**
     * Show import page
     */
    public function importPage()
    {
        // Get recent imports (if tracking exists)
        $recentImports = [];

        // Get Reference Data
        $walikelas = $this->walikelasModel->getAllActive();
        $shifts = $this->shiftModel->findAll();

        return view('admin/students/import', [
            'title' => 'Import Siswa',
            'recentImports' => $recentImports,
            'walikelas' => $walikelas,
            'shifts' => $shifts
        ]);
    }

    /**
     * Handle CSV/Excel import
     */
    public function import()
    {
        // Check if file was uploaded
        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak ditemukan atau tidak valid');
        }

        // Get file extension
        $extension = strtolower($file->getClientExtension());
        $allowedExtensions = ['csv', 'xlsx', 'xls'];

        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->with('error', 'File harus berformat CSV, XLSX, atau XLS');
        }

        // Parse file based on type
        try {
            if ($extension === 'csv') {
                $data = $this->parseCSV($file);
            } else {
                $data = $this->parseExcel($file);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (empty($data)) {
            return redirect()->back()->with('error', 'File kosong atau hanya memiliki header');
        }

        // Get header from first row
        $header = array_shift($data);
        $requiredColumns = ['nis', 'full_name', 'class', 'username', 'password', 'shift_id'];

        // Validate header
        foreach ($requiredColumns as $col) {
            if (!in_array($col, $header)) {
                return redirect()->back()->with('error', "Kolom '$col' tidak ditemukan di file CSV (wajib)");
            }
        }

        // Process data
        $columnIndices = array_flip($header);
        $successCount = 0;
        $errors = [];
        $db = \Config\Database::connect();

        foreach ($data as $lineNum => $row) {
            $rowNum = $lineNum + 2; // +2 because header is row 1

            // Skip empty rows
            if (empty(array_filter($row)))
                continue;

            // Extract data (with trim to handle whitespace and quotes)
            $nis = isset($columnIndices['nis']) ? trim($row[$columnIndices['nis']] ?? '') : '';
            $fullName = isset($columnIndices['full_name']) ? trim($row[$columnIndices['full_name']] ?? '') : '';
            $class = isset($columnIndices['class']) ? trim($row[$columnIndices['class']] ?? '') : '';
            $username = isset($columnIndices['username']) ? trim($row[$columnIndices['username']] ?? '') : '';
            $password = isset($columnIndices['password']) ? trim($row[$columnIndices['password']] ?? '') : '';
            $waliKelasId = isset($columnIndices['wali_kelas_id']) ? trim($row[$columnIndices['wali_kelas_id']] ?? '') : '';
            $shiftId = isset($columnIndices['shift_id']) ? trim($row[$columnIndices['shift_id']] ?? '') : '';
            $phoneNumber = isset($columnIndices['phone_number']) ? trim($row[$columnIndices['phone_number']] ?? '') : '';
            $guardianName = isset($columnIndices['guardian_name']) ? trim($row[$columnIndices['guardian_name']] ?? '') : '';
            $guardianPhone = isset($columnIndices['guardian_phone']) ? trim($row[$columnIndices['guardian_phone']] ?? '') : '';
            $rfidId = isset($columnIndices['rfid_id']) ? trim($row[$columnIndices['rfid_id']] ?? '') : '';

            // Handle Religion (Allow 'religion' or 'agama' header)
            $religion = '';
            if (isset($columnIndices['religion'])) {
                $religion = trim($row[$columnIndices['religion']] ?? '');
            } elseif (isset($columnIndices['agama'])) {
                $religion = trim($row[$columnIndices['agama']] ?? '');
            }

            // Validate required fields
            if (empty($nis) || empty($fullName) || empty($class) || empty($username) || empty($password)) {
                $errors[] = "Baris $rowNum: Data tidak lengkap (nis, full_name, class, username, password wajib diisi)";
                continue;
            }

            $existingUser = $this->userModel->where('username', trim($username))->first();
            if ($existingUser) {
                $errors[] = "Baris $rowNum: Username '$username' sudah digunakan";
                continue;
            }

            // ... (validations skipped for brevity in replace block, keep existing) ...

            // Start transaction
            $db->transStart();

            try {
                // Create user account with role 'siswa'
                $userId = $this->userModel->insert([
                    'username' => trim($username),
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'role' => 'siswa',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                if (!$userId) {
                    throw new \Exception("Gagal membuat user untuk baris $rowNum: " . implode(', ', $this->userModel->errors()));
                }

                // Create student profile
                $studentData = [
                    'user_id' => $userId,
                    'nis' => trim($nis),
                    'full_name' => trim($fullName),
                    'class' => trim($class),
                    'religion' => !empty($religion) ? trim($religion) : null,
                    'wali_kelas_id' => !empty($waliKelasId) ? intval($waliKelasId) : null,
                    'shift_id' => intval($shiftId),
                    'phone_number' => !empty($phoneNumber) ? trim($phoneNumber) : null,
                    'guardian_name' => !empty($guardianName) ? trim($guardianName) : null,
                    'guardian_phone' => !empty($guardianPhone) ? trim($guardianPhone) : null,
                    'rfid_id' => !empty($rfidId) ? trim($rfidId) : null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                // INSERT RETURNS THE ID (or false)
                $studentId = $this->studentModel->insert($studentData);

                if (!$studentId) {
                    // Check if error is from DB or validation
                    $errors = $this->studentModel->errors();
                    $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Gagal menyimpan data siswa (Cek duplikasi NIS/RFID)';
                    throw new \Exception("Gagal data siswa baris $rowNum: " . $errorMsg);
                }

                // Generate QR code using the correct Student ID
                $this->studentModel->generateQrCode($studentId);

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception("Gagal commit database untuk baris $rowNum (Rollback occurred)");
                }

                $successCount++;
            } catch (\Exception $e) {
                // transComplete already calls rollback if status is false, but an exception might occur before
                // calling transRollback is safe to ensure it's clean (though if transStarted, we should rollback)
                // Just to be safe, if transStatus is not false yet (exception before complete), rollback.
                $db->transRollback();
                $errors[] = $e->getMessage();
            }
        }

        $db->transComplete();

        // Prepare response
        if ($successCount > 0) {
            session()->setFlashdata('success', 'Import siswa berhasil!');
            session()->setFlashdata('imported_count', $successCount);
        }

        if (!empty($errors)) {
            session()->setFlashdata('error', 'Beberapa baris mengalami kesalahan:');
            session()->setFlashdata('errors', $errors);
            return redirect()->to('/admin/students/import');
        }

        if ($successCount === 0) {
            return redirect()->to('/admin/students/import')->with('error', 'Tidak ada siswa yang berhasil diimport');
        }

        return redirect()->to('/admin/students')->with('success', "Import siswa berhasil! Total: $successCount siswa");
    }

    /**
     * Parse CSV file
     */
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

    /**
     * Parse Excel file (XLSX/XLS)
     */
    private function parseExcel($file)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();

            $data = [];
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $value = $cell->getValue();
                    // Convert to string and trim
                    $rowData[] = trim((string) $value);
                }

                // Skip empty rows
                if (!empty(array_filter($rowData))) {
                    $data[] = $rowData;
                }
            }

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Helper to upload photo
     */
    private function _uploadPhoto($oldPhoto = null)
    {
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old photo if exists
            if ($oldPhoto && file_exists(FCPATH . $oldPhoto)) {
                unlink(FCPATH . $oldPhoto);
            }

            $newName = 'student_' . time() . '_' . $file->getRandomName();
            if (!is_dir(FCPATH . 'uploads/students')) {
                mkdir(FCPATH . 'uploads/students', 0777, true);
            }
            $file->move(FCPATH . 'uploads/students', $newName);

            return 'uploads/students/' . $newName;
        }

        return $oldPhoto;
    }
}
