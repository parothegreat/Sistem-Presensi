<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingsModel;

class SettingsController extends BaseController
{
    protected $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Pengaturan Aplikasi',
            'settings' => $this->settingsModel->getAll(),
        ];

        return view('admin/settings/index', $data);
    }

    public function save()
    {
        $rules = [
            'school_name' => 'required|min_length[3]',
            'school_npsn' => 'required|numeric',
            'school_logo' => 'is_image[school_logo]|max_size[school_logo,2048]|mime_in[school_logo,image/jpg,image/jpeg,image/png]',
            'school_favicon' => 'max_size[school_favicon,1024]|ext_in[school_favicon,ico,png,gif]',
            'school_address' => 'permit_empty|string',
            'school_phone' => 'permit_empty|string',
            'school_email' => 'permit_empty|valid_email',
            'card_header_text' => 'permit_empty|string',
            'card_back_text' => 'permit_empty|string',
            'card_back_bg_color' => 'permit_empty|string',
            'card_back_text_color' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Save text settings
        $this->settingsModel->saveSetting('school_name', $this->request->getPost('school_name'));
        $this->settingsModel->saveSetting('school_npsn', $this->request->getPost('school_npsn'));
        $this->settingsModel->saveSetting('school_address', $this->request->getPost('school_address'));
        $this->settingsModel->saveSetting('school_phone', $this->request->getPost('school_phone'));
        $this->settingsModel->saveSetting('school_email', $this->request->getPost('school_email'));
        $this->settingsModel->saveSetting('card_header_text', $this->request->getPost('card_header_text'));
        $this->settingsModel->saveSetting('card_back_text', $this->request->getPost('card_back_text'));
        $this->settingsModel->saveSetting('card_back_bg_color', $this->request->getPost('card_back_bg_color'));
        $this->settingsModel->saveSetting('card_back_text_color', $this->request->getPost('card_back_text_color'));
        $this->settingsModel->saveSetting('wa_notification_target', $this->request->getPost('wa_notification_target'));

        // Handle logo upload
        $logo = $this->request->getFile('school_logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newName = 'logo_sekolah.' . $logo->getExtension();
            $logo->move(FCPATH . 'uploads/logo', $newName, true); // Overwrite existing
            $this->settingsModel->saveSetting('school_logo', 'uploads/logo/' . $newName, 'general', 'image');
        }

        // Handle favicon upload
        $favicon = $this->request->getFile('school_favicon');
        if ($favicon && $favicon->isValid() && !$favicon->hasMoved()) {
            $newNameFav = 'favicon.' . $favicon->getExtension();
            $favicon->move(FCPATH . 'uploads/logo', $newNameFav, true);
            $this->settingsModel->saveSetting('school_favicon', 'uploads/logo/' . $newNameFav, 'general', 'image');
        }

        return redirect()->to('/admin/settings')->with('success', 'Pengaturan berhasil disimpan');
    }

    public function backup()
    {
        // Load database utilities
        $db = \Config\Database::connect();
        // Custom backup logic using mysqldump if available, or manual select

        $filename = 'backup_absensi_' . date('Y-m-d_H-i-s') . '.sql';

        // Set headers for download
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Simple backup: iterate tables and export
        // Note: For a robust solution, mysqldump via exec() is better but might not work on all hosts.
        // We will try to use a simple CodeIgniter-like export logic or just a raw SQL dump wrapper.

        echo "-- Database Backup\n";
        echo "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        $tables = $db->listTables();
        foreach ($tables as $table) {
            echo "-- Table: $table\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";

            // Create Table
            $createTable = $db->query("SHOW CREATE TABLE `$table`")->getRowArray();
            echo $createTable['Create Table'] . ";\n\n";

            // Insert Data
            $rows = $db->table($table)->get()->getResultArray();
            foreach ($rows as $row) {
                $keys = array_map(fn($k) => "`$k`", array_keys($row));
                $values = array_map(function ($v) use ($db) {
                    return $v === null ? "NULL" : $db->escape($v);
                }, array_values($row));

                echo "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            echo "\n";
        }

        exit;
    }
}
