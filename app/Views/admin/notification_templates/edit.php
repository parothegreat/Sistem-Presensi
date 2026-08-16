<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Template Notifikasi</h1>
            <a href="<?= base_url('admin/notification-templates') ?>" class="text-gray-600 hover:text-gray-900">&larr; Kembali</a>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-gray-100 text-gray-800 mr-3">
                        <?= $template['channel'] ?>
                    </span>
                    <h2 class="text-xl font-semibold text-gray-800"><?= esc($template['name']) ?></h2>
                </div>
                <p class="text-sm text-gray-600">Code: <span class="font-mono bg-gray-100 px-1"><?= esc($template['code']) ?></span></p>
            </div>

            <form action="<?= base_url('admin/notification-templates/' . $template['id']) ?>" method="POST" class="p-6">
                <?= csrf_field() ?>

                <!-- Content Field -->
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Isi Pesan Template</label>
                    <textarea name="content" id="content" rows="6" 
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-2 border-slate-400 rounded-md font-mono p-2"
                        required><?= esc(old('content', $template['content'])) ?></textarea>
                    <p class="mt-2 text-sm text-gray-500">Gunakan variabel di bawah ini untuk data dinamis.</p>
                </div>

                <!-- Variable Cheat Sheet -->
                <div class="mb-6 bg-blue-50 p-4 rounded-md border border-blue-100">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">📌 Variabel Tersedia</h3>
                    <p class="text-sm text-blue-600 mb-2"><?= esc($template['description']) ?></p>
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 font-mono">
                        <div>{name} : Nama Siswa/Guru</div>
                        <div>{time} : Jam (HH:MM)</div>
                        <div>{date} : Tanggal (DD-MM-YYYY)</div>
                        <div>{status_label} : Status (Tepat Waktu/Terlambat/dll)</div>
                        <div>{school_name} : Nama Sekolah</div>
                    </div>
                </div>

                <!-- Is Active Toggle -->
                <div class="mb-8">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox h-5 w-5 text-indigo-600 rounded" <?= ($template['is_active']) ? 'checked' : '' ?>>
                        <span class="ml-2 text-gray-700">Aktifkan Notifikasi Ini</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="<?= base_url('admin/notification-templates') ?>" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
