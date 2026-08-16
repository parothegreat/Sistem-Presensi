<?php
$db = \Config\Database::connect();
$settings = $db->table('settings')->get()->getResultArray();
$appSettings = [];
foreach ($settings as $s) {
    $appSettings[$s['key']] = $s['value'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard Petugas' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <link rel="icon" href="<?= !empty($appSettings['school_favicon']) ? base_url($appSettings['school_favicon']) : base_url('favicon.ico') ?>">
</head>

<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <?php if (!empty($appSettings['school_logo'])): ?>
                            <img src="<?= base_url($appSettings['school_logo']) ?>" alt="Logo" class="h-8 w-auto object-contain">
                        <?php else: ?>
                            <div class="h-8 w-8 bg-indigo-600 rounded flex items-center justify-center text-white font-bold text-xs">PS</div>
                        <?php endif; ?>
                        <div>
                            <span class="font-bold text-xl text-indigo-600 block leading-none"><?= esc($appSettings['school_name'] ?? 'Presensi') ?></span>
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider bg-indigo-100 text-indigo-800">Petugas</span>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-sm text-gray-600">
                        Halo, <span class="font-semibold text-gray-900"><?= esc($user['username']) ?></span>
                    </div>
                    <a href="<?= base_url('logout') ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900">Pilih Mode Scanner</h1>
            <p class="mt-2 text-lg text-gray-600">Silakan pilih metode absensi yang akan digunakan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <!-- QR Code Scanner Card -->
            <a href="<?= base_url('scanner') ?>" class="group relative bg-white overflow-hidden rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer border border-gray-100">
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                <div class="p-8 flex flex-col items-center justify-center h-64">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Scan QR Code</h2>
                    <p class="text-gray-500 text-center">Gunakan kamera untuk memindai kode QR siswa atau guru.</p>
                </div>
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-between items-center group-hover:bg-blue-50 transition-colors duration-300">
                    <span class="text-sm font-medium text-blue-600">Buka Scanner</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 group-hover:translate-x-1 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            </a>

            <!-- RFID Scanner Card -->
            <a href="<?= base_url('rfid-scanner') ?>" class="group relative bg-white overflow-hidden rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer border border-gray-100">
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-500 to-pink-600"></div>
                <div class="p-8 flex flex-col items-center justify-center h-64">
                    <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Scan RFID</h2>
                    <p class="text-gray-500 text-center">Gunakan reader USB untuk memindai kartu RFID.</p>
                </div>
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-between items-center group-hover:bg-purple-50 transition-colors duration-300">
                    <span class="text-sm font-medium text-purple-600">Buka Scanner</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 group-hover:translate-x-1 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            </a>
        </div>

        <div class="mt-12 text-center text-sm text-gray-500">
            &copy; <?= date('Y') ?> Presensi Sekolah. All rights reserved.
        </div>
    </main>
</body>

</html>