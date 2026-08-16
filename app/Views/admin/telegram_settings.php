<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold mb-2 text-slate-800">Telegram Link PIN</h1>
    <p class="text-sm text-slate-600 mb-6">Kelola PIN global untuk autentikasi wali saat menautkan chat ke sistem.</p>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
            ✓ <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors) && is_array($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
            <?php foreach ($errors as $error): ?>
                <div>✗ <?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg border border-slate-200 shadow p-6">
        <form method="post" action="<?= base_url('/admin/telegram-settings') ?>">
            <?= csrf_field() ?>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">PIN Aktif Saat Ini</label>
                <div class="p-3 bg-slate-50 border border-slate-300 rounded font-mono text-lg text-slate-800">
                    <?= $pin ? '<code class="text-indigo-600 font-bold">' . esc($pin) . '</code>' : '<span class="text-slate-400">Belum ada PIN</span>' ?>
                </div>
            </div>

            <div class="mb-6">
                <label for="pin" class="block text-sm font-semibold text-slate-700 mb-2">PIN Baru</label>
                <input
                    type="text"
                    id="pin"
                    name="pin"
                    value="<?= esc(old('pin', '')) ?>"
                    placeholder="Contoh: 123456 atau TAMU2025"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    required />
                <p class="text-xs text-slate-500 mt-2">
                    Bisa berupa angka atau kombinasi alphanumeric. Min 4 karakter.
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2">ℹ️ Info</h3>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>PIN ini digunakan oleh semua wali saat melakukan linking via Telegram</li>
                    <li>Wali akan mengirim: <code class="bg-white px-2 py-1 rounded">/link NIS PIN</code></li>
                    <li>Ubah PIN kapan saja, PIN lama akan tidak berlaku</li>
                </ul>
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                    Simpan PIN
                </button>
                <a
                    href="<?= base_url('/admin/dashboard') ?>"
                    class="px-6 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>