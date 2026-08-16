<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">Edit Profil Guru</h1>

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
        <form action="<?= base_url('/admin/teachers/' . $teacher['id']) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- Full Name -->
            <div class="mb-6">
                <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" value="<?= esc(old('full_name', $teacher['full_name'])) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Masukkan nama lengkap" required>
            </div>

            <!-- NIP -->
            <div class="mb-6">
                <label for="nip" class="block text-sm font-semibold text-slate-700 mb-2">NIP (Nomor Induk Pegawai)</label>
                <input type="text" id="nip" name="nip" value="<?= esc(old('nip', $teacher['nip'])) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: 1987654321">
            </div>

            <!-- Subject -->
            <div class="mb-6">
                <label for="subject" class="block text-sm font-semibold text-slate-700 mb-2">Mata Pelajaran</label>
                <input type="text" id="subject" name="subject" value="<?= esc(old('subject', $teacher['subject'])) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: Matematika" required>
            </div>

            <!-- Photo -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Foto Profil</label>
                <?php if (!empty($teacher['photo'])): ?>
                    <div class="mb-2">
                        <img src="<?= base_url($teacher['photo']) ?>" alt="Current Photo" class="h-24 w-24 object-cover rounded-full border">
                    </div>
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*" class="w-full border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500 text-sm text-slate-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100">
                <p class="text-xs text-slate-500 mt-1">Biarkan kosong jika tidak ingin mengubah foto. Format: JPG, PNG. Maks: 2MB.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Phone Number -->
                <div>
                    <label for="phone_number" class="block text-sm font-semibold text-slate-700 mb-2">Nomor WhatsApp</label>
                    <input type="text" id="phone_number" name="phone_number" value="<?= esc(old('phone_number', $teacher['phone_number'])) ?>"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="628xxxxxxxxxx">
                    <p class="text-xs text-gray-500 mt-1">Gunakan format 628xxx</p>
                </div>

                <!-- Telegram Chat ID -->
                <div>
                    <label for="telegram_chat_id" class="block text-sm font-semibold text-slate-700 mb-2">Telegram Chat ID</label>
                    <input type="text" id="telegram_chat_id" name="telegram_chat_id" value="<?= esc(old('telegram_chat_id', $teacher['telegram_chat_id'])) ?>"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Contoh: 123456789">
                </div>
            </div>

            <!-- RFID ID -->
            <div class="mb-6">
                <label for="rfid_id" class="block text-sm font-semibold text-slate-700 mb-2">RFID Card ID</label>
                <input type="text" id="rfid_id" name="rfid_id" value="<?= esc(old('rfid_id', $teacher['rfid_id'])) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Scan kartu RFID atau input ID manual">
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Simpan Perubahan
                </button>
                <a href="<?= base_url('/admin/teachers') ?>" class="bg-slate-300 text-slate-700 px-6 py-2 rounded hover:bg-slate-400">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
<?php $this->endSection() ?>