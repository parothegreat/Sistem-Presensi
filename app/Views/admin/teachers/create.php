<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">Tambah Profil Guru</h1>

    <?php if (isset($errors)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <form action="<?= base_url('/admin/teachers') ?>" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-sm">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- User Selection -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Pilih User Guru</label>
                    <select name="user_id" required class="w-full border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih User --</option>
                        <?php foreach ($availableGuru as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= old('user_id') == $u['id'] ? 'selected' : '' ?>>
                                <?= esc($u['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Hanya menampilkan user dengan role 'guru' yang belum memiliki profil.</p>
                </div>

                <!-- NIP -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">NIP</label>
                    <input type="text" name="nip" value="<?= old('nip') ?>" class="w-full border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: 19800101...">
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="full_name" value="<?= old('full_name') ?>" required class="w-full border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500" placeholder="Nama lengkap dengan gelar">
                </div>

                <!-- Subject -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Mata Pelajaran / Jabatan</label>
                    <input type="text" name="subject" value="<?= old('subject') ?>" required class="w-full border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Matematika / Wali Kelas">
                </div>

                <!-- Photo -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Foto Profil</label>
                    <input type="file" name="photo" accept="image/*" class="w-full border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500 text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100">
                    <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG. Maks: 2MB.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Phone Number -->
                <div>
                    <label for="phone_number" class="block text-sm font-semibold text-slate-700 mb-2">Nomor WhatsApp</label>
                    <input type="text" id="phone_number" name="phone_number" value="<?= esc(old('phone_number')) ?>"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="628xxxxxxxxxx">
                    <p class="text-xs text-gray-500 mt-1">Gunakan format 628xxx</p>
                </div>

                <!-- Telegram Chat ID -->
                <div>
                    <label for="telegram_chat_id" class="block text-sm font-semibold text-slate-700 mb-2">Telegram Chat ID</label>
                    <input type="text" id="telegram_chat_id" name="telegram_chat_id" value="<?= esc(old('telegram_chat_id')) ?>"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Contoh: 123456789">
                </div>
            </div>

            <!-- RFID ID -->
            <div class="mb-6">
                <label for="rfid_id" class="block text-sm font-semibold text-slate-700 mb-2">RFID Card ID</label>
                <input type="text" id="rfid_id" name="rfid_id" value="<?= esc(old('rfid_id')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Scan kartu RFID atau input ID manual">
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Simpan
                </button>
                <a href="<?= base_url('/admin/teachers') ?>" class="bg-slate-300 text-slate-700 px-6 py-2 rounded hover:bg-slate-400">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
<?php $this->endSection() ?>