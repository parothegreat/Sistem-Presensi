<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Petugas extends Controller
{
    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard Petugas',
            'user' => session()->get()
        ];

        return view('petugas/dashboard', $data);
    }
}
