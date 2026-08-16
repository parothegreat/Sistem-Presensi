<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800"><i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Buat Informasi Baru</h1>
        <a href="<?= base_url('/admin/information') ?>" class="text-indigo-600 hover:text-indigo-800 text-sm mt-2 inline-block">&larr; Kembali ke Daftar</a>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('/admin/information/' . $type) ?>" method="POST" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <?= csrf_field() ?>
        
        <div class="p-6 space-y-6">
            <!-- Judul -->
            <div>
                <label for="title" class="block text-sm font-medium text-slate-700 mb-1">Judul Informasi</label>
                <input type="text" name="title" id="title" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Pengumuman Libur" required value="<?= esc(old('title')) ?>">
            </div>

            <!-- Konten -->
            <div>
                <label for="content" class="block text-sm font-medium text-slate-700 mb-1">Isi Informasi</label>
                <div class="bg-slate-50 p-2 rounded border mb-2 text-xs text-slate-500">
                    <span class="font-semibold">Tips:</span> Gunakan tag <code>{nama}</code> untuk nama <?= $type == 'teacher' ? 'Guru' : 'Siswa' ?>. <?= $type == 'teacher' ? '' : 'Gunakan <code>{kelas}</code> untuk nama kelas.' ?>
                </div>
                <textarea name="content" id="content" rows="6" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Tulis informasi di sini..." required><?= esc(old('content')) ?></textarea>
                <div class="mt-2 text-xs text-slate-500 space-y-1">
                    <p class="font-semibold">Format Teks WhatsApp:</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div><code>*Tebal*</code> &rarr; <b>Tebal</b></div>
                        <div><code>_Miring_</code> &rarr; <i>Miring</i></div>
                        <div><code>~Coret~</code> &rarr; <strike>Coret</strike></div>
                        <div><code>```Monospace```</code> &rarr; <span class="font-mono">Monospace</span></div>
                    </div>
                </div>
            </div>

            <!-- Target Recipient -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-slate-700">Target <?= $type == 'teacher' ? 'Guru' : 'Kelas' ?></label>
                    <label class="inline-flex items-center cursor-pointer text-xs text-indigo-600 hover:text-indigo-800 select-none">
                        <input type="checkbox" id="selectAll" class="form-checkbox h-3 w-3 mr-1 rounded text-indigo-600">
                        Pilih Semua
                    </label>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 border p-4 rounded-lg max-h-60 overflow-y-auto">
                    <?php if (empty($recipients)): ?>
                        <div class="col-span-3 text-center text-slate-500 py-4 text-sm">Tidak ada data <?= $type == 'teacher' ? 'guru' : 'kelas' ?> aktif.</div>
                    <?php else: ?>
                        <?php foreach ($recipients as $item): ?>
                            <label class="flex items-center space-x-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                                <input type="checkbox" name="recipients[]" value="<?= $item['id'] ?>" class="form-checkbox h-4 w-4 text-indigo-600 rounded recipient-checkbox">
                                <span class="text-sm text-slate-700">
                                    <?= esc($type == 'teacher' ? $item['full_name'] : $item['class_name']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-slate-500 mt-1">Pilih satu atau lebih <?= $type == 'teacher' ? 'guru' : 'kelas' ?>.</p>
            </div>

            <!-- Opsi Kirim -->
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="send_via_wa" value="1" class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-indigo-500" checked>
                    <div>
                        <span class="block text-sm font-semibold text-slate-800">Kirim Notifikasi WhatsApp</span>
                        <span class="block text-xs text-slate-600">Jika dicentang, informasi akan dikirim ke nomor WhatsApp <?= $type == 'teacher' ? 'guru masing-masing.' : 'sesuai pengaturan (Wali/Group/Keduanya).' ?></span>
                        <span class="block text-xs text-amber-600 mt-1 font-medium"><i class="fas fa-info-circle mr-1"></i>Catatan: Variabel <code>{nama}</code> tidak berfungsi spesifik di Group (akan otomatis diganti menjadi 'Siswa/i').</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="bg-slate-50 px-6 py-4 flex justify-end">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg transition shadow-sm">
                <i class="fas fa-paper-plane mr-2"></i>Publikasikan
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const recipientCheckboxes = document.querySelectorAll('.recipient-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                recipientCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                });
            });

            // If any individual checkbox is unchecked, uncheck "Select All"
            recipientCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    } else {
                        // Check if all are checked
                        const allChecked = Array.from(recipientCheckboxes).every(cb => cb.checked);
                        selectAllCheckbox.checked = allChecked;
                    }
                });
            });
        }
    });
</script>
<?= $this->endSection() ?>
