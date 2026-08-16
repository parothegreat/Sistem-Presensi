<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Buat Wali Kelas</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php $errors = session()->getFlashdata('errors') ?? [] ?>
    <?php if (! empty($errors)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <ul class="text-sm text-red-700">
                <?php foreach ($errors as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('/admin/walikelas') ?>" method="post" class="bg-white rounded-lg shadow p-6">
        <?= csrf_field() ?>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">Guru</label>
            <select name="teacher_id" class="mt-1 block w-full rounded border px-3 py-2">
                <option value="">-- Pilih Guru --</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= esc($t['full_name']) ?> (<?= esc($t['username'] ?? '') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">Nama Kelas</label>
            <input type="text" name="class_name" class="mt-1 block w-full rounded border px-3 py-2" required />
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">ID Group WhatsApp</label>
            <input type="text" name="wa_group_id" class="mt-1 block w-full rounded border px-3 py-2" placeholder="12345678@g.us" />
            <p class="text-xs text-slate-500 mt-1">ID Group (JID) dari OneSender / WhatsApp Web. Contoh: 12345678@g.us</p>
        </div>

        <div class="flex gap-2">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Simpan</button>
            <a href="<?= base_url('/admin/walikelas') ?>" class="bg-slate-200 px-4 py-2 rounded">Batal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>