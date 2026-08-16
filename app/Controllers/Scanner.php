<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Scanner extends Controller
{
    public function index()
    {
        return view('scanner/index');
    }

    public function testApi()
    {
        return view('test_api');
    }
}
