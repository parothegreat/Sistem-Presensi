<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800"><?= $title ?></h1>
            <p class="text-sm text-slate-500 mt-1">Isi data shift dengan lengkap</p>
        </div>
        <a href="<?= base_url('/admin/shifts') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">Kembali</a>
    </div>

    <?php if (session()->has('errors')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <h4 class="font-semibold mb-2">Validasi Gagal:</h4>
            <ul class="list-disc list-inside">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="<?= $shift ? base_url('/admin/shifts/' . $shift['id']) : base_url('/admin/shifts') ?>" method="POST">
            <?= csrf_field() ?>

            <!-- Nama Shift -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Shift *</label>
                <input type="text" name="name" value="<?= $shift ? esc($shift['name']) : old('name') ?>"
                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    placeholder="Contoh: Pagi, Siang, Malam" required>
            </div>

            <!-- Deskripsi -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    placeholder="Contoh: Shift pagi untuk kelas X"><?= $shift ? esc($shift['description']) : old('description') ?></textarea>
            </div>

            <!-- Grid untuk waktu -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Jam Mulai -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jam Mulai *</label>
                    <input type="time" name="start_time" value="<?= $shift ? $shift['start_time'] : old('start_time') ?>"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        required>
                </div>

                <!-- Jam Selesai -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jam Selesai *</label>
                    <input type="time" name="end_time" value="<?= $shift ? $shift['end_time'] : old('end_time') ?>"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        required>
                </div>

                <!-- Deadline Masuk -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Deadline Masuk (Tepat Waktu) *</label>
                    <input type="time" name="checkin_deadline" value="<?= $shift ? $shift['checkin_deadline'] : old('checkin_deadline') ?>"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        placeholder="Jam berapa dianggap terlambat?"
                        required>
                    <p class="text-xs text-slate-500 mt-1">Siswa masuk setelah jam ini dihitung terlambat</p>
                </div>

                <!-- Jam Pulang Awal -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jam Pulang Awal (Boleh Pulang Dari) *</label>
                    <input type="time" name="checkout_earliest" value="<?= $shift ? $shift['checkout_earliest'] : old('checkout_earliest') ?>"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        placeholder="Jam berapa boleh mulai scan pulang?"
                        required>
                    <p class="text-xs text-slate-500 mt-1">Siswa tidak boleh pulang sebelum jam ini</p>
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" <?= ($shift && $shift['is_active']) ? 'checked' : '' ?>
                        class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-2 focus:ring-blue-200">
                    <span class="text-sm font-semibold text-slate-700">Aktifkan shift ini</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                    <?= $shift ? 'Perbarui' : 'Simpan' ?>
                </button>
                <a href="<?= base_url('/admin/shifts') ?>" class="bg-slate-300 hover:bg-slate-400 text-slate-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Info -->
    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mt-6">
        <h4 class="font-semibold mb-2">💡 Catatan:</h4>
        <ul class="text-sm list-disc list-inside space-y-1">
            <li>Siswa hanya bisa scan masuk antara jam mulai dan jam selesai shift</li>
            <li>Status "Tepat Waktu" jika masuk sebelum atau sama dengan deadline masuk</li>
            <li>Siswa hanya bisa scan pulang mulai dari jam pulang awal sampai jam selesai shift</li>
            <li>Status pulang "Tepat Waktu" jika pulang sesuai atau lebih dari jam pulang awal</li>
        </ul>
    </div>
</div>
<?= $this->endSection() ?>