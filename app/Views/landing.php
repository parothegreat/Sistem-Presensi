<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($settings['school_name'] ?? 'Presensi Sekolah') ?> - Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#4f46e5">
    <style>
        body {
            background-color: #f3f4f6;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e5e7eb' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
    <link rel="icon" href="<?= !empty($settings['school_favicon']) ? base_url($settings['school_favicon']) : base_url('favicon.ico') ?>">
</head>
<body class="flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl overflow-hidden relative">
        <!-- Decoration Header -->
        <div class="h-32 bg-indigo-600 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-700"></div>
            <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute top-4 left-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
        </div>

        <!-- Logo & Identity -->
        <div class="relative px-8 pt-0 pb-8 text-center -mt-12">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-white p-2 rounded-2xl shadow-lg mb-4 ring-4 ring-white/50">
                <?php if (!empty($settings['school_logo'])): ?>
                    <img src="<?= base_url($settings['school_logo']) ?>" alt="Logo" class="w-full h-full object-contain">
                <?php else: ?>
                    <div class="w-full h-full bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-bold text-3xl">PS</div>
                <?php endif; ?>
            </div>

            <h1 class="text-2xl font-bold text-slate-800 mb-1"><?= esc($settings['school_name'] ?? 'Presensi Sekolah') ?></h1>
            <p class="text-slate-500 text-sm">NPSN: <?= esc($settings['school_npsn'] ?? '-') ?></p>

            <div class="mt-4 inline-block px-4 py-1 bg-slate-100 rounded-full text-sm font-medium text-slate-600" id="clock">
                --:--
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-8 pb-8 space-y-3">
            <a href="<?= base_url('/login') ?>" class="group block w-full bg-indigo-600 hover:bg-indigo-700 text-white p-4 rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sign-in-alt text-lg"></i>
                    </div>
                    <div class="text-left">
                        <div class="font-bold">Login Aplikasi</div>
                        <div class="text-xs text-indigo-200">Admin & Guru</div>
                    </div>
                </div>
                <i class="fas fa-chevron-right opacity-60 group-hover:translate-x-1 transition-transform"></i>
            </a>

            <a href="<?= base_url('/siswa/dashboard') ?>" class="group block w-full bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 text-slate-700 p-4 rounded-xl transition-all active:scale-95 flex items-center justify-between">
                 <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-lg"></i>
                    </div>
                    <div class="text-left">
                        <div class="font-bold">Portal Siswa</div>
                        <div class="text-xs text-slate-500">Cek Kehadiran & Izin</div>
                    </div>
                </div>
                 <i class="fas fa-chevron-right text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
            </a>

            <div class="grid grid-cols-3 gap-2 pt-2">
                <a href="<?= base_url('/scanner') ?>" class="block bg-slate-50 hover:bg-green-50 border border-slate-200 hover:border-green-300 p-3 rounded-xl text-center transition-all group">
                    <i class="fas fa-qrcode text-xl text-green-600 mb-1 group-hover:scale-110 transition-transform"></i>
                    <div class="text-[10px] font-semibold text-slate-700">Scan QR</div>
                </a>
                <a href="<?= base_url('/rfid-scanner') ?>" class="block bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-300 p-3 rounded-xl text-center transition-all group">
                    <i class="fas fa-id-card text-xl text-purple-600 mb-1 group-hover:scale-110 transition-transform"></i>
                    <div class="text-[10px] font-semibold text-slate-700">Scan RFID</div>
                </a>
                <a href="<?= base_url('/lobby') ?>" class="block bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 p-3 rounded-xl text-center transition-all group">
                    <i class="fas fa-tv text-xl text-blue-600 mb-1 group-hover:scale-110 transition-transform"></i>
                    <div class="text-[10px] font-semibold text-slate-700">TV Lobby</div>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 p-4 text-center border-t border-slate-100">
            <p class="text-xs text-slate-400">
                © <?= date('Y') ?> System Absensi
                Dibuat oleh <a href="https://raeniofficial.blogspot.com" target="_blank" class="text-indigo-500 hover:text-indigo-700 hover:underline">SMK Mitra Industri</a>
            </p>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            const dateString = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('clock').innerHTML = `<i class="far fa-clock mr-2"></i>${dateString} • ${timeString}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>