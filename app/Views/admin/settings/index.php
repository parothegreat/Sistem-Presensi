<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-800"><i class="fas fa-cog mr-2 text-indigo-600"></i>Pengaturan Aplikasi</h1>
            <p class="text-slate-600">Atur identitas sekolah dan sistem</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 md:grid-cols-2">
        <!-- School Identity -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 border-b pb-2"><i class="fas fa-school mr-2"></i>Identitas Sekolah</h3>
            <form action="<?= base_url('/admin/settings/save') ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <?= csrf_field() ?>
                    <label for="school_name" class="block text-sm font-medium text-slate-700 mb-1">Nama Sekolah</label>
                    <input type="text" name="school_name" id="school_name" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        value="<?= old('school_name', $settings['school_name'] ?? '') ?>" required>
                </div>
                <div class="mb-4">
                    <label for="school_npsn" class="block text-sm font-medium text-slate-700 mb-1">NPSN</label>
                    <input type="text" name="school_npsn" id="school_npsn" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        value="<?= old('school_npsn', $settings['school_npsn'] ?? '') ?>" required>
                </div>

                <div class="mb-4">
                    <label for="school_address" class="block text-sm font-medium text-slate-700 mb-1">Alamat Sekolah</label>
                    <textarea name="school_address" id="school_address" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"><?= old('school_address', $settings['school_address'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="school_phone" class="block text-sm font-medium text-slate-700 mb-1">Telepon</label>
                        <input type="text" name="school_phone" id="school_phone" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            value="<?= old('school_phone', $settings['school_phone'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="school_email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="school_email" id="school_email" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            value="<?= old('school_email', $settings['school_email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="card_header_text" class="block text-sm font-medium text-slate-700 mb-1">Header Kartu Absensi</label>
                    <input type="text" name="card_header_text" id="card_header_text" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        value="<?= old('card_header_text', $settings['card_header_text'] ?? 'KARTU TANDA PELAJAR') ?>" placeholder="e.g. KARTU TANDA PELAJAR">
                </div>
                <div class="mb-4">
                    <label for="card_back_text" class="block text-sm font-medium text-slate-700 mb-1">Teks Belakang Kartu (Ketentuan)</label>
                    <textarea name="card_back_text" id="card_back_text" rows="5" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" placeholder="Masukkan ketentuan yang akan dicetak di belakang kartu..."><?= old('card_back_text', $settings['card_back_text'] ?? "KETENTUAN:\n\n1. KARTU INI HARUS DIBAWA SAAT SEKOLAH\n2. JIKA MENEMUKAN KARTU INI HARAP DIKEMBALIKAN KE SEKOLAH\n3. DILARANG MENYALAHGUNAKAN KARTU INI") ?></textarea>

                    <div class="mt-3 grid grid-cols-2 gap-4">
                        <div>
                            <label for="card_back_bg_color" class="block text-sm font-medium text-slate-700 mb-1">Warna Background</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="card_back_bg_color" id="card_back_bg_color" class="h-10 w-20 rounded border border-slate-300 p-1 cursor-pointer"
                                    value="<?= old('card_back_bg_color', $settings['card_back_bg_color'] ?? '#ffffff') ?>">
                                <span class="text-xs text-slate-500">Klik untuk pilih</span>
                            </div>
                        </div>
                        <div>
                            <label for="card_back_text_color" class="block text-sm font-medium text-slate-700 mb-1">Warna Teks</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="card_back_text_color" id="card_back_text_color" class="h-10 w-20 rounded border border-slate-300 p-1 cursor-pointer"
                                    value="<?= old('card_back_text_color', $settings['card_back_text_color'] ?? '#333333') ?>">
                                <span class="text-xs text-slate-500">Klik untuk pilih</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Pengaturan ini berlaku untuk tampilan belakang kartu saat menggunakan fitur "Cetak Belakang".</p>
                </div>
                <div class="mb-4">
                    <label for="wa_notification_target" class="block text-sm font-medium text-slate-700 mb-1">Tujuan Notifikasi WhatsApp</label>
                    <select name="wa_notification_target" id="wa_notification_target" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        <?php $target = $settings['wa_notification_target'] ?? 'guardian'; ?>
                        <option value="guardian" <?= $target == 'guardian' ? 'selected' : '' ?>>Wali Murid (Personal)</option>
                        <option value="group" <?= $target == 'group' ? 'selected' : '' ?>>Group Kelas</option>
                        <option value="both" <?= $target == 'both' ? 'selected' : '' ?>>Keduanya</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Pilih kemana notifikasi absensi akan dikirimkan.</p>
                </div>
                <div class="mb-6">
                    <label for="school_logo" class="block text-sm font-medium text-slate-700 mb-1">Logo Sekolah</label>
                    <?php if (!empty($settings['school_logo'])): ?>
                        <div class="mb-2">
                            <img src="<?= base_url($settings['school_logo']) ?>" alt="Logo Sekolah" class="h-20 w-auto object-contain bg-slate-50 p-2 rounded border">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="school_logo" id="school_logo" accept="image/*" class="w-full text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG. Max: 2MB.</p>
                </div>
                <div class="mb-6">
                    <label for="school_favicon" class="block text-sm font-medium text-slate-700 mb-1">Favicon Sekolah</label>
                    <?php if (!empty($settings['school_favicon'])): ?>
                        <div class="mb-2">
                            <img src="<?= base_url($settings['school_favicon']) ?>" alt="Favicon" class="h-8 w-8 object-contain bg-slate-50 p-1 rounded border">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="school_favicon" id="school_favicon" accept="image/x-icon,image/png,image/gif" class="w-full text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-slate-500 mt-1">Format: ICO, PNG, GIF. Max: 1MB.</p>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                </button>
            </form>
        </div>

        <!-- Database Maintenance -->
        <div class="bg-white rounded-lg shadow p-6 h-fit">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 border-b pb-2"><i class="fas fa-database mr-2"></i>Database</h3>
            <p class="text-slate-600 mb-4 text-sm">Download backup database lengkap (semua tabel dan data) dalam format SQL.</p>

            <a href="<?= base_url('/admin/settings/backup') ?>" class="block w-full bg-green-600 hover:bg-green-700 text-white text-center px-4 py-2 rounded-lg transition mb-4">
                <i class="fas fa-download mr-2"></i>Download MySQL Backup (.sql)
            </a>

            <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                <p class="text-xs text-yellow-800"><i class="fas fa-info-circle mr-1"></i> <strong>Note:</strong> Lakukan backup secara berkala untuk mencegah kehilangan data.</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>