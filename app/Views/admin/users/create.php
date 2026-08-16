<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">Tambah User Baru</h1>

    <?php if (isset($validation)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($validation->getErrors() as $field => $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="<?= base_url('/admin/users') ?>" method="post">
            <?= csrf_field() ?>

            <!-- Username -->
            <div class="mb-6">
                <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Masukkan username" required>
                <?php if (isset($validation) && $validation->hasError('username')): ?>
                    <p class="text-red-600 text-sm mt-1"><?= $validation->getError('username') ?></p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                <input type="password" id="password" name="password"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Masukkan password (min 6 karakter)" required>
                <?php if (isset($validation) && $validation->hasError('password')): ?>
                    <p class="text-red-600 text-sm mt-1"><?= $validation->getError('password') ?></p>
                <?php endif; ?>
            </div>

            <!-- Role -->
            <div class="mb-6">
                <label for="role" class="block text-sm font-semibold text-slate-700 mb-2">Role</label>
                <select id="role" name="role"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="guru" <?= old('role') === 'guru' ? 'selected' : '' ?>>Guru</option>
                    <option value="petugas" <?= old('role') === 'petugas' ? 'selected' : '' ?>>Petugas</option>
                    <option value="siswa" <?= old('role') === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                </select>
                <?php if (isset($validation) && $validation->hasError('role')): ?>
                    <p class="text-red-600 text-sm mt-1"><?= $validation->getError('role') ?></p>
                <?php endif; ?>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Simpan
                </button>
                <a href="<?= base_url('/admin/users') ?>" class="bg-slate-300 text-slate-700 px-6 py-2 rounded hover:bg-slate-400">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
<?php $this->endSection() ?>