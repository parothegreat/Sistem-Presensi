<?php
$db = \Config\Database::connect();
$settings = $db->table('settings')->get()->getResultArray();
$appSettings = [];
foreach ($settings as $s) {
    $appSettings[$s['key']] = $s['value'];
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? $title . ' - Guru' : 'Panel Guru' ?></title>
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
                            <div class="text-xs text-slate-500">Panel Guru</div>
                        </div>
                    </a>
                </div>

                <?php $uri = uri_string();
                // Determine if current user is wali kelas
                // This is passed from controller or we detect from menu context
                $isWaliKelas = isset($isWaliKelas) ? $isWaliKelas : (strpos($uri, 'guru/attendance') === 0);
                ?>
                <!-- Desktop Nav - Centered -->
                <nav class="hidden lg:flex items-center gap-2 text-sm flex-1 justify-center px-4">
                    <a href="<?= base_url('/guru/dashboard') ?>" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= $uri === 'guru/dashboard' ? 'bg-slate-100 font-semibold' : '' ?>">
                        <i class="fas fa-chart-line text-slate-600"></i>
                        <span>Dashboard</span>
                    </a>
                    <?php if ($isWaliKelas): ?>
                        <a href="<?= base_url('/guru/attendance') ?>" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= $uri === 'guru/attendance' ? 'bg-slate-100 font-semibold' : '' ?>">
                            <i class="fas fa-clipboard-list text-slate-600"></i>
                            <span>Absensi Kelas</span>
                        </a>
                        <a href="<?= base_url('/guru/permission') ?>" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= str_contains($uri, 'guru/permission') ? 'bg-slate-100 font-semibold' : '' ?>">
                            <i class="fas fa-envelope-open-text text-slate-600"></i>
                            <span>Persetujuan Izin</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('/guru/attendance-history') ?>" class="flex items-center gap-1 px-2 xl:px-3 py-2 rounded hover:bg-slate-50 whitespace-nowrap <?= $uri === 'guru/attendance-history' ? 'bg-slate-100 font-semibold' : '' ?>">
                        <i class="fas fa-user-check text-slate-600"></i>
                        <span>Kehadiran Saya</span>
                    </a>
                </nav>

                <!-- Right Section -->
                <div class="flex items-center gap-2 ml-auto">
                    <div class="relative">
                        <button id="userBtn" class="px-2 sm:px-3 py-2 rounded bg-slate-100 text-xs sm:text-sm whitespace-nowrap">Halo, <strong><?= session()->get('full_name') ?? session()->get('username') ?? 'Guru' ?></strong> ▾</button>
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-40 bg-white border rounded shadow z-50">
                            <a href="<?= base_url('/guru/profile') ?>" class="block px-3 py-2 text-sm hover:bg-slate-50"><i class="fas fa-user mr-2"></i>Profil</a>
                            <hr class="my-1">
                            <a href="<?= base_url('/logout') ?>" class="block px-3 py-2 text-sm text-red-600 hover:bg-slate-50"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </div>
                    <!-- Mobile menu button -->
                    <button id="mobileMenuBtn" class="lg:hidden p-2 rounded bg-slate-100 flex-shrink-0">
                        <i class="fas fa-bars h-5 w-5 text-slate-700"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile nav -->
            <div id="mobileMenu" class="lg:hidden hidden border-t mt-2">
                <div class="px-2 py-2 flex flex-col gap-1 text-sm">
                    <a href="<?= base_url('/guru/dashboard') ?>" class="px-2 py-2 rounded hover:bg-slate-50"><i class="fas fa-chart-line mr-2 w-4"></i>Dashboard</a>
                    <?php if ($isWaliKelas): ?>
                        <a href="<?= base_url('/guru/attendance') ?>" class="px-2 py-2 rounded hover:bg-slate-50"><i class="fas fa-clipboard-list mr-2 w-4"></i>Absensi Kelas</a>
                        <a href="<?= base_url('/guru/permission') ?>" class="px-2 py-2 rounded hover:bg-slate-50"><i class="fas fa-envelope-open-text mr-2 w-4"></i>Persetujuan Izin</a>
                    <?php endif; ?>
                    <a href="<?= base_url('/guru/attendance-history') ?>" class="px-2 py-2 rounded hover:bg-slate-50"><i class="fas fa-user-check mr-2 w-4"></i>Kehadiran Saya</a>
                    <hr class="my-1">
                    <a href="<?= base_url('/logout') ?>" class="px-2 py-2 rounded text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt mr-2 w-4"></i>Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="p-6">
        <?= $this->renderSection('content') ?>
    </main>

    <script>
        (function() {
            var userBtn = document.getElementById('userBtn');
            var userMenu = document.getElementById('userMenu');
            var mobileMenuBtn = document.getElementById('mobileMenuBtn');
            var mobileMenu = document.getElementById('mobileMenu');

            if (userBtn && userMenu) {
                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                });
            }
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            document.addEventListener('click', function() {
                if (userMenu) userMenu.classList.add('hidden');
            });
        })();
    </script>
</body>

</html>