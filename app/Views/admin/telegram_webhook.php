<?php $this->extend('layouts/admin'); ?>

<?php $this->section('content'); ?>
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Registrasi Webhook Telegram</h1>
        <p class="text-slate-600">Hubungkan bot Telegram dengan aplikasi presensi.</p>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <?php if (strpos(base_url(), 'localhost') !== false || strpos(base_url(), '127.0.0.1') !== false || strpos(base_url(), '::1') !== false): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded relative mb-6">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle mt-1 mr-3"></i>
                <div>
                    <p class="font-bold">Peringatan: Localhost Terdeteksi</p>
                    <p class="text-sm mt-1">
                        Telegram Webhook <strong>tidak dapat bekerja di localhost</strong> karena server Telegram memerlukan URL publik (HTTPS) untuk mengirim data ke aplikasi ini.
                    </p>
                    <p class="text-sm mt-2">
                        Solusi:
                        <ul class="list-disc ml-4 mt-1">
                            <li>Gunakan layanan tunneling seperti <strong>ngrok</strong> (contoh: <code>ngrok http 8080</code>).</li>
                            <li>Deploy aplikasi ke hosting/VPS dengan domain publik dan HTTPS.</li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4 text-slate-700">Status Bot</h2>
            
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-slate-50 rounded border">
                    <span class="text-slate-600 font-medium">Webhook URL Target:</span>
                    <code class="bg-slate-200 px-2 py-1 rounded text-sm text-slate-800 break-all"><?= esc($webhookUrl) ?></code>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-slate-50 rounded border">
                    <span class="text-slate-600 font-medium">Bot Token (di .env):</span>
                    <?php if ($hasToken): ?>
                        <span class="text-green-600 font-mono font-bold flex items-center gap-2">
                            <i class="fas fa-check-circle"></i> Terdeteksi (<?= esc($maskedToken) ?>)
                        </span>
                    <?php else: ?>
                        <span class="text-red-600 font-bold flex items-center gap-2">
                            <i class="fas fa-times-circle"></i> Tidak Ditemukan
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($hasToken): ?>
            <div class="border-t pt-6">
                <p class="text-slate-600 mb-4 text-sm">
                    Klik tombol di bawah untuk mendaftarkan webhook URL ke server Telegram. Pastikan URL aplikasi ini dapat diakses publik (HTTPS).
                </p>
                
                <form action="<?= base_url('admin/telegram-webhook') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors">
                        <i class="fas fa-link"></i>
                        Register Webhook Sekarang
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded text-yellow-800 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Silakan tambahkan <strong>TELEGRAM_BOT_TOKEN</strong> ke file <code>.env</code> Anda terlebih dahulu.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->endSection(); ?>
