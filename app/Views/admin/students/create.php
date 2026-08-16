<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">Tambah Profil Siswa</h1>

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
        <form action="<?= base_url('/admin/students') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- User Selection -->
            <div class="mb-6">
                <label for="user_id" class="block text-sm font-semibold text-slate-700 mb-2">Pilih Akun Siswa</label>
                <select id="user_id" name="user_id" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="">-- Pilih User Siswa --</option>
                    <?php foreach ($availableSiswa as $siswa): ?>
                        <option value="<?= $siswa['id'] ?>" <?= old('user_id') == $siswa['id'] ? 'selected' : '' ?>>
                            <?= $siswa['username'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- NIS -->
            <div class="mb-6">
                <label for="nis" class="block text-sm font-semibold text-slate-700 mb-2">NIS (Nomor Induk Siswa) <span class="text-red-500">*</span></label>
                <input type="text" id="nis" name="nis" value="<?= esc(old('nis')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: S123456" required>
            </div>

            <!-- Full Name -->
            <div class="mb-6">
                <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" value="<?= esc(old('full_name')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Masukkan nama lengkap" required>
            </div>

            <!-- Religion -->
            <div class="mb-6">
                <label for="religion" class="block text-sm font-semibold text-slate-700 mb-2">Agama</label>
                <select id="religion" name="religion" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Agama --</option>
                    <option value="Islam" <?= old('religion') == 'Islam' ? 'selected' : '' ?>>Islam</option>
                    <option value="Kristen" <?= old('religion') == 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                    <option value="Katolik" <?= old('religion') == 'Katolik' ? 'selected' : '' ?>>Katolik</option>
                    <option value="Hindu" <?= old('religion') == 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                    <option value="Buddha" <?= old('religion') == 'Buddha' ? 'selected' : '' ?>>Buddha</option>
                    <option value="Khonghucu" <?= old('religion') == 'Khonghucu' ? 'selected' : '' ?>>Khonghucu</option>
                </select>
            </div>

            <!-- Class -->
            <div class="mb-6">
                <label for="class" class="block text-sm font-semibold text-slate-700 mb-2">Kelas</label>
                <select id="class" name="class" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" required onchange="updateWaliKelasId()">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($walikelas as $wk): ?>
                        <option value="<?= esc($wk['class_name']) ?>" data-wali-id="<?= $wk['id'] ?>" <?= old('class') == $wk['class_name'] ? 'selected' : '' ?>>
                            <?= esc($wk['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Wali Kelas ID (hidden) -->
            <input type="hidden" id="wali_kelas_id" name="wali_kelas_id" value="<?= esc(old('wali_kelas_id')) ?>">

            <!-- Shift -->
            <div class="mb-6">
                <label for="shift_id" class="block text-sm font-semibold text-slate-700 mb-2">Shift</label>
                <select id="shift_id" name="shift_id" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Shift --</option>
                    <?php foreach ($shifts as $shift): ?>
                        <?php if ($shift['is_active']): ?>
                            <option value="<?= $shift['id'] ?>" <?= old('shift_id') == $shift['id'] ? 'selected' : '' ?>>
                                <?= esc($shift['name']) ?> (<?= date('H:i', strtotime($shift['start_time'])) ?> - <?= date('H:i', strtotime($shift['end_time'])) ?>)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-slate-500 mt-2">Pilih shift sesuai dengan jam belajar siswa</p>
            </div>

            <!-- Telegram Chat ID -->
            <div class="mb-6">
                <label for="telegram_chat_id" class="block text-sm font-semibold text-slate-700 mb-2">Telegram Chat ID (Wali Murid)</label>
                <input type="text" id="telegram_chat_id" name="telegram_chat_id" value="<?= esc(old('telegram_chat_id')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: 123456789 (chat id wali) - optional">
                <p class="text-sm text-slate-500 mt-2">Isi jika ingin mengirim notifikasi ke wali melalui Telegram.</p>
            </div>

            <!-- Phone Number (Siswa) -->
            <div class="mb-6">
                <label for="phone_number" class="block text-sm font-semibold text-slate-700 mb-2">Nomor WhatsApp Siswa</label>
                <input type="text" id="phone_number" name="phone_number" value="<?= esc(old('phone_number')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: 62812345678 (tanpa +)">
                <p class="text-sm text-slate-500 mt-2">Nomor WhatsApp siswa untuk menerima notifikasi absensi.</p>
            </div>

            <!-- Guardian Name -->
            <div class="mb-6">
                <label for="guardian_name" class="block text-sm font-semibold text-slate-700 mb-2">Nama Wali</label>
                <input type="text" id="guardian_name" name="guardian_name" value="<?= esc(old('guardian_name')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Masukkan nama wali/orang tua">
                <p class="text-sm text-slate-500 mt-2">Nama orang tua/wali siswa.</p>
            </div>

            <!-- Guardian Phone -->
            <div class="mb-6">
                <label for="guardian_phone" class="block text-sm font-semibold text-slate-700 mb-2">Nomor WhatsApp Wali</label>
                <input type="text" id="guardian_phone" name="guardian_phone" value="<?= esc(old('guardian_phone')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: 62812345678 (tanpa +)">
                <p class="text-sm text-slate-500 mt-2">Nomor WhatsApp wali untuk menerima notifikasi absensi siswa.</p>
            </div>

            <!-- RFID ID (IoT Integration) -->
            <div class="mb-6">
                <label for="rfid_id" class="block text-sm font-semibold text-slate-700 mb-2">RFID Tag ID</label>
                <input type="text" id="rfid_id" name="rfid_id" value="<?= esc(old('rfid_id')) ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: RF12345ABC (unique RFID tag)">
                <p class="text-sm text-slate-500 mt-2">ID tag RFID untuk integrasi IoT reader. Harus unik per siswa.</p>
            </div>

            <!-- Photo Upload -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Foto Siswa</label>
                <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg hover:border-indigo-500 transition-colors relative bg-slate-50">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-slate-600 justify-center">
                            <label for="photo" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 px-2">
                                <span>Upload file</span>
                                <input id="photo" name="photo" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                            </label>
                            <p class="pl-1">atau drag and drop</p>
                        </div>
                        <p class="text-xs text-slate-500">PNG, JPG, GIF up to 2MB</p>
                    </div>

                    <!-- Preview Image -->
                    <img id="imagePreview" src="" class="hidden absolute inset-0 w-full h-full object-contain bg-white p-2 rounded-lg cursor-pointer" onclick="document.getElementById('photo').click()" title="Klik untuk ganti foto" />
                </div>
            </div>

            <script>
                function previewImage(input) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.getElementById('imagePreview');
                            preview.src = e.target.result;
                            preview.classList.remove('hidden');
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                }
            </script>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Simpan
                </button>
                <a href="<?= base_url('/admin/students') ?>" class="bg-slate-300 text-slate-700 px-6 py-2 rounded hover:bg-slate-400">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function updateWaliKelasId() {
        const select = document.getElementById('class');
        const option = select.options[select.selectedIndex];
        const waliId = option.getAttribute('data-wali-id');
        document.getElementById('wali_kelas_id').value = waliId || '';
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', updateWaliKelasId);
</script>
<?php $this->endSection() ?>