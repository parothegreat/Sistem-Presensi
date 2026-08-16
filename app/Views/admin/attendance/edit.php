<?php $this->extend('layouts/admin'); ?>

<?php $this->section('content'); ?>

<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Edit Absensi</h1>
        <p class="text-slate-600">
            <strong><?= esc($attendance['nis']) ?></strong> -
            <?= esc($attendance['full_name']) ?>
            (<?= esc($attendance['class']) ?>)
        </p>
        <p class="text-sm text-slate-500">Tanggal: <?= date('d-m-Y', strtotime($attendance['date'])) ?></p>
    </div>

    <!-- Form -->
    <form method="post" action="<?= base_url('/admin/attendance/' . $attendance['id']) ?>" class="bg-white rounded-lg shadow p-6">
        <?= csrf_field() ?>

        <!-- Masuk Section -->
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Data Masuk</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Waktu Masuk -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Waktu Masuk</label>
                    <input type="datetime-local" name="masuk_at"
                        value="<?= $attendance['masuk_at'] ? date('Y-m-d\TH:i', strtotime($attendance['masuk_at'])) : '' ?>"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Status Masuk -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Masuk</label>
                    <select name="masuk_status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Pilih Status</option>
                        <option value="on_time" <?= $attendance['masuk_status'] === 'on_time' ? 'selected' : '' ?>>Tepat Waktu</option>
                        <option value="late" <?= $attendance['masuk_status'] === 'late' ? 'selected' : '' ?>>Terlambat</option>
                        <option value="izin" <?= $attendance['masuk_status'] === 'izin' ? 'selected' : '' ?>>Izin</option>
                        <option value="sakit" <?= $attendance['masuk_status'] === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                        <option value="alpha" <?= $attendance['masuk_status'] === 'alpha' ? 'selected' : '' ?>>Alpha</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Pulang Section -->
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Data Pulang</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Waktu Pulang -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Waktu Pulang</label>
                    <input type="datetime-local" name="pulang_at"
                        value="<?= $attendance['pulang_at'] ? date('Y-m-d\TH:i', strtotime($attendance['pulang_at'])) : '' ?>"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Status Pulang -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Pulang</label>
                    <select name="pulang_status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Pilih Status</option>
                        <option value="on_time" <?= ($attendance['pulang_status'] ?? '') === 'on_time' ? 'selected' : '' ?>>Tepat</option>
                        <option value="early" <?= ($attendance['pulang_status'] ?? '') === 'early' ? 'selected' : '' ?>>Lebih Awal</option>
                        <option value="izin" <?= ($attendance['pulang_status'] ?? '') === 'izin' ? 'selected' : '' ?>>Izin</option>
                        <option value="sakit" <?= ($attendance['pulang_status'] ?? '') === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                        <option value="alpha" <?= ($attendance['pulang_status'] ?? '') === 'alpha' ? 'selected' : '' ?>>Alpha</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan</label>
            <textarea name="note" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Catatan atau keterangan tambahan...">
<?= esc($attendance['note'] ?? '') ?></textarea>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                Simpan Perubahan
            </button>
            <a href="<?= base_url('/admin/attendance') ?>" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 font-medium">
                Batal
            </a>
        </div>
    </form>
</div>

<?php $this->endSection(); ?>