<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">
                <?= isset($holiday) ? 'Edit Hari Libur' : 'Tambah Hari Libur' ?>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                <?= isset($holiday) ? 'Perbarui informasi hari libur' : 'Tambahkan hari libur baru ke dalam sistem' ?>
            </p>
        </div>
        <a href="<?= base_url('/admin/holidays') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded">
            Kembali
        </a>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <h4 class="font-semibold mb-2">Error:</h4>
            <p><?= nl2br(session('error')) ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->has('errors')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <h4 class="font-semibold mb-2">Validasi Gagal:</h4>
            <ul class="list-disc list-inside">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="<?= isset($holiday) ? base_url('/admin/holidays/' . $holiday['id']) : base_url('/admin/holidays') ?>" method="POST">
            <?= csrf_field() ?>

            <!-- Nama Hari Libur -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                    Nama Hari Libur <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= esc(old('name', $holiday['name'] ?? '')) ?>"
                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                    placeholder="Contoh: Hari Raya Idul Fitri">
            </div>

            <!-- Periode: Tanggal Mulai & Selesai -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tanggal Mulai <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="date"
                        id="date_from"
                        name="date_from"
                        value="<?= esc(old('date_from', $holiday['date_from'] ?? '')) ?>"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tanggal Selesai <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="date"
                        id="date_to"
                        name="date_to"
                        value="<?= esc(old('date_to', $holiday['date_to'] ?? '')) ?>"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                </div>
            </div>

            <!-- Jenis Hari Libur -->
            <div class="mb-6">
                <label for="type" class="block text-sm font-semibold text-slate-700 mb-2">
                    Jenis <span class="text-red-600">*</span>
                </label>
                <select
                    id="type"
                    name="type"
                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                    <option value="">-- Pilih Jenis --</option>
                    <option value="national_holiday" <?= (old('type', $holiday['type'] ?? '') === 'national_holiday') ? 'selected' : '' ?>>
                        Libur Nasional
                    </option>
                    <option value="school_activity" <?= (old('type', $holiday['type'] ?? '') === 'school_activity') ? 'selected' : '' ?>>
                        Kegiatan Sekolah
                    </option>
                    <option value="special" <?= (old('type', $holiday['type'] ?? '') === 'special') ? 'selected' : '' ?>>
                        Libur Khusus
                    </option>
                </select>
            </div>

            <!-- Keterangan -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">
                    Keterangan
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                    placeholder="Contoh: Hari Raya Idul Fitri 1446 H"><?= esc(old('description', $holiday['description'] ?? '')) ?></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 border-t">
                <a href="<?= base_url('/admin/holidays') ?>" class="px-4 py-2 border border-slate-300 text-slate-700 rounded hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    <?= isset($holiday) ? 'Perbarui' : 'Simpan' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Validasi: date_to tidak boleh kurang dari date_from
    document.getElementById('date_to').addEventListener('change', function() {
        const dateFrom = document.getElementById('date_from').value;
        const dateTo = this.value;

        if (dateFrom && dateTo && dateTo < dateFrom) {
            alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai!');
            this.value = dateFrom;
        }
    });
</script>
<?php $this->endSection() ?>