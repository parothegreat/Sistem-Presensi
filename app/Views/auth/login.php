<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?= esc($settings['school_name'] ?? 'Panel Admin') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f3f4f6;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239ca3af' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
    <link rel="icon" href="<?= !empty($settings['school_favicon']) ? base_url($settings['school_favicon']) : base_url('favicon.ico') ?>">
</head>

<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-indigo-600 p-8 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-700"></div>
            <!-- Decorative circles -->
            <div class="absolute -top-4 -left-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute bottom-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>

            <div class="relative z-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-xl shadow-lg mb-4 ring-4 ring-white/30">
                    <?php if (!empty($settings['school_logo'])): ?>
                        <img src="<?= base_url($settings['school_logo']) ?>" alt="Logo" class="w-full h-full object-contain p-2">
                    <?php else: ?>
                        <div class="text-indigo-600 font-bold text-2xl">PS</div>
                    <?php endif; ?>
                </div>
                <h2 class="text-white font-bold text-xl leading-tight"><?= esc($settings['school_name'] ?? 'Presensi Sekolah') ?></h2>
                <p class="text-indigo-200 text-sm mt-1">Silakan login untuk melanjutkan</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="p-8">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-6 flex items-start gap-3 p-3 bg-red-50 text-red-700 rounded-lg text-sm border border-red-100">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <span><?= session()->getFlashdata('error') ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('/login') ?>" method="post" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 ml-1">Username / NIS / NIP</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-slate-400"></i>
                        </div>
                        <input name="username" type="text" required placeholder="Masukkan ID Pengguna"
                            class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white"
                            autocomplete="username">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 ml-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-slate-400"></i>
                        </div>
                        <input name="password" type="password" required placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white"
                            autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg shadow transition-all active:scale-95 flex items-center justify-center gap-2 group">
                    <span>Masuk Aplikasi</span>
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="<?= base_url('/') ?>" class="text-sm text-slate-500 hover:text-indigo-600 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i> Kembali ke Portal Utama
                </a>
            </div>
        </div>

         <!-- Footer Credit -->
         <div class="bg-slate-50 p-3 text-center border-t border-slate-100">
            <p class="text-xs text-slate-400">
                © <?= date('Y') ?> Presensi • Dibuat oleh <a href="https://raeniofficial.blogspot.com" target="_blank" class="text-indigo-500 hover:text-indigo-700 hover:underline">SMK Mitra Industri</a>
            </p>
        </div>
    </div>
</body>

</html>