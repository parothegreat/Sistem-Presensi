<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Help extends Controller
{
    /**
     * Display help and tutorial page
     */
    public function index()
    {
        // Require admin role
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        return view('admin/help', [
            'title' => 'Help & Tutorial',
            'bot_username' => '@notifpresensi_bot'
        ]);
    }

    /**
     * Display specific tutorial section
     */
    public function tutorial($section = 'telegram')
    {
        // Require admin role
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $sections = ['telegram', 'qrcode', 'scanner', 'attendance'];
        if (!in_array($section, $sections)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/help', [
            'title' => 'Help & Tutorial',
            'section' => $section,
            'bot_username' => '@notifpresensi_bot'
        ]);
    }
}
