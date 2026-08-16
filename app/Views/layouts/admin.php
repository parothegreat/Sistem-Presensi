<?php
$db = \Config\Database::connect();
$settings = $db->table('settings')->get()->getResultArray();
$appSettings = [];
foreach ($settings as $s) {
    $appSettings[$s['key']] = $s['value'];
}
$role = session()->get('role') ?? null;
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? $title . ' - Admin' : 'Admin Panel' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html {
            scroll-behavior: smooth
        }
    </style>
    <link rel="icon" href="<?= !empty($appSettings['school_favicon']) ? base_url($appSettings['school_favicon']) : base_url('favicon.ico') ?>">
</head>

<body class="bg-slate-100 min-h-screen">
    <!-- Top navbar -->
    <header class="bg-white border-b sticky top-0 z-40">
        <div class="mx-auto px-2 sm:px-4 py-2 sm:py-3">
            <div class="flex items-center justify-between gap-2 sm:gap-4">
                <!-- Logo Section -->
                <div class="flex items-center gap-2 min-w-fit">
                    <a href="<?= base_url('/') ?>" class="flex items-center gap-2">
                        <?php if (!empty($appSettings['school_logo'])): ?>
                            <img src="<?= base_url($appSettings['school_logo']) ?>" alt="Logo" class="h-8 w-auto sm:h-9 object-contain">
                        <?php else: ?>
                            <div class="h-8 w-8 sm:h-9 sm:w-9 bg-indigo-600 rounded flex items-center justify-center text-white font-bold text-xs sm:text-sm">PS</div>
                        <?php endif; ?>
                        <div class="hidden md:block">
                            <div class="font-semibold text-slate-800 text-sm"><?= esc($appSettings['school_name'] ?? 'Presensi Sekolah') ?></div>
                            <div class="text-xs text-slate-500">Panel Admin</div>
                        </div>
                    </a>
                </div>

                <?php $uri = uri_string(); ?>
                <!-- Desktop Nav - Centered -->
                <nav class="hidden lg:flex items-center gap-2 text-sm flex-1 justify-center px-4">
                    <a href="<?= base_url('/admin/dashboard') ?>" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= $uri === 'admin/dashboard' ? 'bg-slate-100 font-semibold' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-600" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3 3h7v7H3V3zM10 3h7v4h-7V3zM3 10h4v7H3v-7zM9 10h8v7H9v-7z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    <div class="relative" id="navDropdown">
                        <button id="dropdownBtn" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3 4a1 1 0 011-1h3v12H4a1 1 0 01-1-1V4zM9 3h7a1 1 0 011 1v10a1 1 0 01-1 1H9V3z" />
                            </svg>
                            <span>Master</span>
                            <svg class="h-3 w-3 text-slate-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div id="dropdownMenu" class="hidden absolute left-1/2 transform -translate-x-1/2 mt-2 w-60 bg-white border rounded shadow z-50 transition ease-out duration-150 text-sm">
                            <a href="<?= base_url('/admin/users') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-users mr-2"></i>Kelola User</a>
                            <a href="<?= base_url('/admin/teachers') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-chalkboard-user mr-2"></i>Guru</a>
                            <a href="<?= base_url('/admin/walikelas') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-person-chalkboard mr-2"></i>Wali Kelas</a>
                            <a href="<?= base_url('/admin/shifts') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-clock mr-2"></i>Shift Masuk</a>
                            <a href="<?= base_url('/admin/students') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-graduation-cap mr-2"></i>Siswa</a>
                            <a href="<?= base_url('/admin/holidays') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-calendar-days mr-2"></i>Hari Libur</a>
                            <a href="<?= base_url('/admin/teacher-schedule') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-calendar-alt mr-2"></i>Jadwal Guru</a>
                            <hr class="my-1">
                            <a href="<?= base_url('/admin/settings') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50 text-indigo-700 bg-indigo-50"><i class="fas fa-cog mr-2"></i>Pengaturan Aplikasi</a>
                            <hr class="my-1">
                            <a href="<?= base_url('/admin/notification-templates') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-bell mr-2"></i>Template Notifikasi</a>
                            <hr class="my-1">
                            <a href="<?= base_url('/admin/telegram-settings') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-key mr-2"></i>Telegram Link PIN</a>
                            <a href="<?= base_url('/admin/telegram-webhook') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-globe mr-2"></i>Registrasi Webhook</a>
                        </div>
                    </div>
                    <div class="relative" id="navAttendanceDropdown">
                        <button id="attendanceDropdownBtn" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= strpos($uri, 'admin/attendance') === 0 ? 'bg-slate-100 font-semibold' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 3H5a2 2 0 00-2 2v14l4-3 4 3 4-3 4 3V5a2 2 0 00-2-2z" />
                            </svg>
                            <span>Absensi</span>
                            <svg class="h-3 w-3 text-slate-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div id="attendanceDropdownMenu" class="hidden absolute left-1/2 transform -translate-x-1/2 mt-2 w-48 bg-white border rounded shadow z-50 transition ease-out duration-150 text-sm">
                            <a href="<?= base_url('/admin/attendance') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-clipboard-check mr-2"></i>Insert Presensi</a>
                            <a href="<?= base_url('/admin/attendance/insert-guru') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-chalkboard mr-2"></i>Insert Guru</a>
                            <a href="<?= base_url('/admin/activities') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50 text-indigo-600 font-medium"><i class="fas fa-calendar-check mr-2"></i>Kelola Kegiatan</a>
                            <hr class="my-1">
                            <a href="<?= base_url('/admin/attendance/laporan') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-list mr-2"></i>Riwayat Siswa</a>
                            <a href="<?= base_url('/admin/attendance/rekap') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-calendar-alt mr-2"></i>Rekap Siswa</a>
                            <hr class="my-1">
                            <a href="<?= base_url('/admin/attendance/guru-riwayat') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-book mr-2"></i>Riwayat Guru</a>
                            <a href="<?= base_url('/admin/attendance/guru-rekap') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-chart-bar mr-2"></i>Rekap Guru</a>
                            <hr class="my-1">
                            <a href="<?= base_url('/admin/qrcode/print-cards') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-qrcode mr-2"></i>QR Code Siswa</a>
                            <a href="<?= base_url('/admin/qrcode/print-cards-guru') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-id-badge mr-2"></i>QR Code Guru</a>
                            <a href="<?= base_url('/admin/card-template') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-id-card mr-2"></i>Template Kartu</a>
                        </div>
                    </div>
                    <a href="<?= base_url('/admin/logs') ?>" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= strpos($uri, 'admin/logs') === 0 ? 'bg-slate-100 font-semibold' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        <span>Logs</span>
                    </a>
                    <a href="<?= base_url('/admin/help') ?>" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= $uri === 'admin/help' ? 'bg-slate-100 font-semibold' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span>Help</span>
                    </a>
                    <div class="relative" id="navInfoDropdown">
                        <button id="infoDropdownBtn" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= strpos($uri, 'admin/information') === 0 ? 'bg-slate-100 font-semibold' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <span>Informasi</span>
                            <svg class="h-3 w-3 text-slate-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div id="infoDropdownMenu" class="hidden absolute left-1/2 transform -translate-x-1/2 mt-2 w-48 bg-white border rounded shadow z-50 transition ease-out duration-150 text-sm">
                            <a href="<?= base_url('/admin/information/student') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-user-graduate mr-2"></i>Info Siswa</a>
                            <a href="<?= base_url('/admin/information/teacher') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-chalkboard-user mr-2"></i>Info Guru</a>
                        </div>
                    </div>
                </nav>

                <!-- Right Section -->
                <div class="flex items-center gap-2 ml-auto">
                    <div class="relative">
                        <button id="userBtn" class="px-2 sm:px-3 py-2 rounded bg-slate-100 text-xs sm:text-sm whitespace-nowrap">Halo, <strong><?= session()->get('full_name') ?? session()->get('username') ?? 'Admin' ?></strong> ▾</button>
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-40 bg-white border rounded shadow z-50">
                            <a href="<?= base_url('/admin/profile') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-user mr-2"></i>Profil</a>
                            <hr class="my-1">
                            <a href="<?= base_url('/logout') ?>" class="block px-3 py-2 text-sm text-red-600 hover:bg-slate-50"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </div>
                    <!-- Mobile menu button -->
                    <button id="mobileMenuBtn" class="lg:hidden p-2 rounded bg-slate-100 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile nav -->
            <div id="mobileMenu" class="lg:hidden hidden border-t mt-2">
                <div class="px-2 py-2 flex flex-col gap-1 text-sm">
                    <a href="<?= base_url('/admin/dashboard') ?>" class="px-2 py-2 rounded hover:bg-slate-50"><i class="fas fa-chart-line mr-2 w-4"></i>Dashboard</a>
                    <button id="mobileMasterBtn" class="w-full text-left px-2 py-2 rounded hover:bg-slate-50 flex items-center justify-between">
                        <span><i class="fas fa-folder mr-2 w-4"></i>Master</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <div id="mobileMasterMenu" class="hidden pl-4 flex flex-col gap-1 bg-slate-50 rounded">
                        <a href="<?= base_url('/admin/users') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-users mr-2 w-3"></i>Kelola User</a>
                        <a href="<?= base_url('/admin/teachers') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-chalkboard-user mr-2 w-3"></i>Guru</a>
                        <a href="<?= base_url('/admin/walikelas') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-person-chalkboard mr-2 w-3"></i>Wali Kelas</a>
                        <a href="<?= base_url('/admin/shifts') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-clock mr-2 w-3"></i>Shift Masuk</a>
                        <a href="<?= base_url('/admin/students') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-graduation-cap mr-2 w-3"></i>Siswa</a>
                        <a href="<?= base_url('/admin/holidays') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-calendar-days mr-2 w-3"></i>Hari Libur</a>
                        <a href="<?= base_url('/admin/teacher-schedule') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-calendar-alt mr-2 w-3"></i>Jadwal Guru</a>
                        <a href="<?= base_url('/admin/notification-templates') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-bell mr-2 w-3"></i>Template Notifikasi</a>
                        <a href="<?= base_url('/admin/telegram-settings') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-key mr-2 w-3"></i>Telegram Link PIN</a>
                        <a href="<?= base_url('/admin/telegram-webhook') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-globe mr-2 w-3"></i>Registrasi Webhook</a>
                    </div>
                    <button id="mobileAttendanceBtn" class="w-full text-left px-2 py-2 rounded hover:bg-slate-50 flex items-center justify-between">
                        <span><i class="fas fa-calendar-check mr-2 w-4"></i>Absensi</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <div id="mobileAttendanceMenu" class="hidden pl-4 flex flex-col gap-1 bg-slate-50 rounded">
                        <a href="<?= base_url('/admin/attendance') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-clipboard-check mr-2 w-3"></i>Insert Presensi</a>
                        <a href="<?= base_url('/admin/attendance/insert-guru') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-chalkboard mr-2 w-3"></i>Insert Guru</a>
                        <a href="<?= base_url('/admin/activities') ?>" class="px-2 py-1 rounded text-sm text-indigo-700 font-medium"><i class="fas fa-calendar-check mr-2 w-3"></i>Kelola Kegiatan</a>
                        <a href="<?= base_url('/admin/attendance/rekap') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-calendar-alt mr-2 w-3"></i>Rekap Absensi</a>
                        <a href="<?= base_url('/admin/attendance/laporan') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-list mr-2 w-3"></i>Riwayat Detail</a>
                        <a href="<?= base_url('/admin/qrcode/print-cards') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-qrcode mr-2 w-3"></i>QR Code Siswa</a>
                        <a href="<?= base_url('/admin/qrcode/print-cards-guru') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-id-badge mr-2 w-3"></i>QR Code Guru</a>
                    </div>
                    <a href="<?= base_url('/admin/logs') ?>" class="px-2 py-2 rounded hover:bg-slate-50"><i class="fas fa-chart-line mr-2 w-4"></i>Logs Monitor</a>
                    <button id="mobileInfoBtn" class="w-full text-left px-2 py-2 rounded hover:bg-slate-50 flex items-center justify-between">
                        <span><i class="fas fa-info-circle mr-2 w-4"></i>Informasi</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <div id="mobileInfoMenu" class="hidden pl-4 flex flex-col gap-1 bg-slate-50 rounded">
                        <a href="<?= base_url('/admin/information/student') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-user-graduate mr-2 w-3"></i>Info Siswa</a>
                        <a href="<?= base_url('/admin/information/teacher') ?>" class="px-2 py-1 rounded text-sm"><i class="fas fa-chalkboard-user mr-2 w-3"></i>Info Guru</a>
                    </div>
                    <a href="<?= base_url('/admin/help') ?>" class="px-2 py-2 rounded hover:bg-slate-50"><i class="fas fa-question-circle mr-2 w-4"></i>Help & Tutorial</a>
                    <hr class="my-1">
                    <a href="<?= base_url('/logout') ?>" class="px-2 py-2 rounded text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt mr-2 w-4"></i>Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="p-6">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-white border-t py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-xs text-slate-400">
                © <?= date('Y') ?> Presensi Sekolah •
                Dibuat oleh <a href="https://raeniofficial.blogspot.com" target="_blank" class="text-indigo-500 hover:text-indigo-700 hover:underline">SMK Mitra Industri</a>
            </p>
        </div>
    </footer>

    <script>
        // Dropdowns and mobile menu - attach listeners only if elements exist
        (function() {
            var dropdownBtn = document.getElementById('dropdownBtn');
            var dropdownMenu = document.getElementById('dropdownMenu');
            var attendanceDropdownBtn = document.getElementById('attendanceDropdownBtn');
            var attendanceDropdownMenu = document.getElementById('attendanceDropdownMenu');
            var userBtn = document.getElementById('userBtn');
            var userMenu = document.getElementById('userMenu');
            var mobileMenuBtn = document.getElementById('mobileMenuBtn');
            var mobileMenu = document.getElementById('mobileMenu');
            var mobileMasterBtn = document.getElementById('mobileMasterBtn');
            var mobileMasterMenu = document.getElementById('mobileMasterMenu');
            var mobileAttendanceMenu = document.getElementById('mobileAttendanceMenu');
            var infoDropdownBtn = document.getElementById('infoDropdownBtn');
            var infoDropdownMenu = document.getElementById('infoDropdownMenu');
            var mobileInfoBtn = document.getElementById('mobileInfoBtn');
            var mobileInfoMenu = document.getElementById('mobileInfoMenu');

            if (dropdownBtn && dropdownMenu) {
                dropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('hidden');
                    if (userMenu) userMenu.classList.add('hidden');
                    if (attendanceDropdownMenu) attendanceDropdownMenu.classList.add('hidden');
                    if (infoDropdownMenu) infoDropdownMenu.classList.add('hidden');
                });
            }

            if (attendanceDropdownBtn && attendanceDropdownMenu) {
                attendanceDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    attendanceDropdownMenu.classList.toggle('hidden');
                    if (userMenu) userMenu.classList.add('hidden');
                    if (dropdownMenu) dropdownMenu.classList.add('hidden');
                    if (infoDropdownMenu) infoDropdownMenu.classList.add('hidden');
                });
            }

            if (infoDropdownBtn && infoDropdownMenu) {
                infoDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    infoDropdownMenu.classList.toggle('hidden');
                    if (userMenu) userMenu.classList.add('hidden');
                    if (dropdownMenu) dropdownMenu.classList.add('hidden');
                    if (attendanceDropdownMenu) attendanceDropdownMenu.classList.add('hidden');
                });
            }

            if (userBtn && userMenu) {
                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                    if (dropdownMenu) dropdownMenu.classList.add('hidden');
                    if (attendanceDropdownMenu) attendanceDropdownMenu.classList.add('hidden');
                    if (infoDropdownMenu) infoDropdownMenu.classList.add('hidden');
                });
            }

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            if (mobileMasterBtn && mobileMasterMenu) {
                mobileMasterBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileMasterMenu.classList.toggle('hidden');
                });
            }

            if (mobileAttendanceBtn && mobileAttendanceMenu) {
                mobileAttendanceBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileAttendanceMenu.classList.toggle('hidden');
                });
            }

            if (mobileInfoBtn && mobileInfoMenu) {
                mobileInfoBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileInfoMenu.classList.toggle('hidden');
                });
            }

            // Close when clicking outside
            document.addEventListener('click', function() {
                if (dropdownMenu) dropdownMenu.classList.add('hidden');
                if (attendanceDropdownMenu) attendanceDropdownMenu.classList.add('hidden');
                if (infoDropdownMenu) infoDropdownMenu.classList.add('hidden');
                if (userMenu) userMenu.classList.add('hidden');
                if (mobileMasterMenu) mobileMasterMenu.classList.add('hidden');
                if (mobileAttendanceMenu) mobileAttendanceMenu.classList.add('hidden');
                if (mobileInfoMenu) mobileInfoMenu.classList.add('hidden');
            });
        })();
    </script>
</body>

</html>
