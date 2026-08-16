<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Ubah Password</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui password akun Anda untuk keamanan yang lebih baik.</p>
        </div>
        <a href="<?= base_url('/admin/profile') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">Kembali</a>
    </div>

    <div class="bg-white border rounded-lg shadow p-6">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 p-3 bg-green-50 border border-green-100 text-green-700 rounded"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-700 rounded"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?= form_open('/admin/profile') ?>
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm text-slate-600">Password Saat Ini</label>
                <input type="password" name="current_password" required class="mt-1 block w-full border rounded px-3 py-2" />
                <?php if ($validation && $validation->getError('current_password')): ?>
                    <div class="text-red-600 text-sm mt-1"><?= $validation->getError('current_password') ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm text-slate-600">Password Baru</label>
                <input type="password" name="new_password" required class="mt-1 block w-full border rounded px-3 py-2" />
                <?php if ($validation && $validation->getError('new_password')): ?>
                    <div class="text-red-600 text-sm mt-1"><?= $validation->getError('new_password') ?></div>
                <?php endif; ?>
                <p class="text-xs text-slate-500 mt-1">Minimal 6 karakter</p>
            </div>

            <div>
                <label class="block text-sm text-slate-600">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" required class="mt-1 block w-full border rounded px-3 py-2" />
                <?php if ($validation && $validation->getError('confirm_password')): ?>
                    <div class="text-red-600 text-sm mt-1"><?= $validation->getError('confirm_password') ?></div>
                <?php endif; ?>
            </div>

            <div class="flex gap-3 mt-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Ubah Password</button>
                <a href="<?= base_url('/admin/profile') ?>" class="px-4 py-2 bg-slate-100 text-slate-700 rounded hover:bg-slate-200">Batal</a>
            </div>
        </div>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>