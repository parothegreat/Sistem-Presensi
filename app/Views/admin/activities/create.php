<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="<?= base_url('/admin/activities') ?>" class="text-slate-500 hover:text-slate-700 flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
        <h1 class="text-3xl font-bold text-slate-800">Tambah Kegiatan Baru</h1>
    </div>

    <!-- MAIN FORM -->
    <form action="<?= base_url('/admin/activities') ?>" method="post" class="bg-white rounded-lg shadow p-6">
        <?= csrf_field() ?>

        <!-- General Info -->
        <h2 class="text-lg font-semibold text-slate-700 mb-4 border-b pb-2">Informasi Kegiatan</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="w-full px-3 py-2 rounded border border-slate-400 focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Contoh: Pengajian Rutin Jumat">
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 rounded border border-slate-400 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Waktu Mulai <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="start_time" class="w-full px-3 py-2 rounded border border-slate-400 focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Waktu Selesai <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="end_time" class="w-full px-3 py-2 rounded border border-slate-400 focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
        </div>

        <!-- Participant Filters -->
        <h2 class="text-lg font-semibold text-slate-700 mb-4 border-b pb-2">Peserta</h2>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4 text-sm">
            <i class="fas fa-info-circle mr-2"></i> Pilih filter di bawah ini untuk menambahkan peserta secara otomatis.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Filter Agama</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($religions as $rel): ?>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="filter_religion[]" value="<?= $rel ?>" class="rounded border border-slate-400 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-slate-600"><?= $rel ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Filter Kelas</label>
                <select name="filter_class[]" multiple class="w-full px-3 py-2 rounded border border-slate-400 focus:border-indigo-500 focus:ring-indigo-500 h-32">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-slate-500 mt-1">Tahan Ctrl/Cmd untuk pilih banyak.</p>
            </div>
        </div>

        <!-- Manual Selection (Optional - Collapsible) -->
        <details class="mb-6 border rounded px-4 py-2">
            <summary class="cursor-pointer font-medium text-slate-700">Pilih Manual Siswa (Opsional)</summary>
            <div class="mt-4 max-h-60 overflow-y-auto">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    <?php foreach ($students as $student): ?>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="students[]" value="<?= $student['id'] ?>" class="rounded border border-slate-400 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-xs text-slate-600 truncate"><?= $student['full_name'] ?> (<?= $student['class'] ?>)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </details>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 font-medium">
                Simpan Kegiatan
            </button>
        </div>
    </form>
</div>
<?php $this->endSection() ?>