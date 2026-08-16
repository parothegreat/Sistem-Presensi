<?php $this->extend('layouts/admin') ?>
<?php $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Import Guru</h1>
        <p class="text-slate-500 text-sm mt-1">Upload file CSV atau Excel untuk menambah data guru secara massal</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
            <div class="font-semibold"><?= session()->getFlashdata('success') ?></div>
            <?php if (session()->getFlashdata('imported_count')): ?>
                <div class="text-sm mt-2">Total guru berhasil diimport: <strong><?= session()->getFlashdata('imported_count') ?></strong></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php $errors = session()->getFlashdata('errors'); ?>
    <?php if (!empty($errors) && is_array($errors)): ?>
        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-6">
            <div class="font-semibold text-yellow-900 mb-3">Beberapa baris mengalami kesalahan:</div>
            <div class="bg-white rounded p-3 max-h-96 overflow-y-auto">
                <ul class="list-disc list-inside text-sm text-yellow-800 space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="<?= base_url('/admin/teachers/import') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Upload File CSV atau Excel</label>
                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition"
                            onclick="document.getElementById('csvFile').click()">
                            <i class="fas fa-file-upload mx-auto text-slate-400" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-sm text-slate-600">
                                <span class="font-semibold">Klik untuk upload</span> atau drag & drop
                            </p>
                            <p class="text-xs text-slate-500 mt-1">CSV (.csv) atau Excel (.xlsx, .xls)</p>
                        </div>
                        <input type="file" id="csvFile" name="csv_file" accept=".csv,.xlsx,.xls" class="hidden" required onchange="showFileName(this)">
                        <p class="mt-2 text-sm text-slate-600" id="fileName"></p>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 font-medium">
                        Import Guru
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold text-blue-900 mb-3">Format File</h3>
            <p class="text-sm text-blue-800 mb-4">File harus memiliki kolom berikut (header di baris pertama):</p>

            <div class="bg-white rounded p-3 text-xs font-mono text-slate-700 overflow-x-auto mb-4">
                <div>full_name,subject,username,password,nip</div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="font-semibold text-blue-900">full_name</span>
                    <p class="text-blue-800">Nama lengkap guru (wajib)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">subject</span>
                    <p class="text-blue-800">Mata pelajaran (wajib)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">username</span>
                    <p class="text-blue-800">Username untuk login (wajib, unik)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">password</span>
                    <p class="text-blue-800">Password (wajib, min 6 karakter)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">nip</span>
                    <p class="text-blue-800">Nomor Induk Pegawai (opsional)</p>
                </div>
            </div>

            <hr class="my-4 border-blue-200">

            <h4 class="font-semibold text-blue-900 mb-2">Contoh Data:</h4>
            <div class="bg-white rounded p-3 text-xs overflow-x-auto space-y-1">
                <div class="font-mono text-slate-600 text-xs">full_name,subject,username,password,nip</div>
                <div class="font-mono text-slate-600 text-xs">Budi Santoso,Matematika,budi_santoso,password123,1987654321</div>
                <div class="font-mono text-slate-600 text-xs">Citra Dewi,Bahasa Indonesia,citra_dewi,password123,</div>
                <div class="font-mono text-slate-600 text-xs">Dimas Pratama,Fisika,dimas_pratama,password123,1234567890</div>
            </div>

            <div class="mt-4 flex gap-2">
                <a href="<?= base_url('/files/teachers_template.xlsx') ?>" class="inline-block bg-green-600 text-white px-3 py-2 rounded text-xs hover:bg-green-700 font-medium">
                    <i class="fas fa-file-excel mr-1"></i> Download Template Excel
                </a>
                <a href="<?= base_url('/files/teachers_template.csv') ?>" class="inline-block bg-blue-600 text-white px-3 py-2 rounded text-xs hover:bg-blue-700 font-medium">
                    <i class="fas fa-file-csv mr-1"></i> Download Template CSV
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function showFileName(input) {
        const fileName = input.files[0]?.name || '';
        document.getElementById('fileName').textContent = fileName ? '✓ ' + fileName : '';
    }
</script>

<?php $this->endSection() ?>