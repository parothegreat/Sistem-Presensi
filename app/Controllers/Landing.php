<?php

namespace App\Controllers;

use CodeIgniter\Controller;

use App\Models\SettingsModel;

class Landing extends Controller
{
    public function index()
    {
        $settingsModel = new SettingsModel();
        $data = [
            'settings' => $settingsModel->getAll()
        ];
        return view('landing', $data);
    }
}
