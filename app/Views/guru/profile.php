<?= $this->extend('layouts/guru') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Profil Saya</h1>
        </div>
        <a href="<?= base_url('/guru/dashboard') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">Kembali</a>
    </div>

    <?php if (empty($teacher)): ?>
        <div class="bg-white border rounded-lg shadow p-6 text-center text-slate-600">Profil tidak ditemukan.</div>
    <?php else: ?>
        <div class="bg-white border rounded-lg shadow p-6">
            <div class="flex items-start gap-6">
                <div class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center text-xl font-semibold text-slate-600"><?= esc(substr($teacher['full_name'] ?? '-', 0, 1)) ?></div>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-slate-800"><?= esc($teacher['full_name'] ?? '-') ?></h2>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-slate-600">
                        <div><span class="text-slate-500">NIP</span>
                            <div class="font-medium text-slate-800"><?= esc($teacher['nip'] ?? '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <a href="<?= base_url('/guru/profile/edit') ?>" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit Profil</a>
                <a href="<?= base_url('/guru/password/edit') ?>" class="inline-block px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Ubah Password</a>
                <a href="<?= base_url('/guru/dashboard') ?>" class="inline-block px-4 py-2 bg-slate-100 text-slate-700 rounded hover:bg-slate-200">Lihat Dashboard</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>