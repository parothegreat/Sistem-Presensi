<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Akun Saya</h1>
        </div>
        <a href="<?= base_url('/admin/dashboard') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 p-3 bg-green-50 border border-green-100 text-green-700 rounded"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-700 rounded"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="bg-white border rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Informasi Akun</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-slate-600 mb-6">
            <div>
                <span class="text-slate-500">Username</span>
                <div class="font-medium text-slate-800"><?= esc($username) ?></div>
            </div>
            <div>
                <span class="text-slate-500">Role</span>
                <div class="font-medium text-slate-800"><?= esc(ucfirst($role)) ?></div>
            </div>
        </div>

        <hr class="my-6">

        <div>
            <a href="<?= base_url('/admin/profile/edit') ?>" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Ubah Password</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>