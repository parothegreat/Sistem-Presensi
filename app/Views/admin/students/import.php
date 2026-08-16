<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Import Siswa</h1>
        <p class="text-slate-500 text-sm mt-1">Upload file CSV atau Excel untuk menambah data siswa secara massal</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
            <div class="font-semibold"><?= session()->getFlashdata('success') ?></div>
            <?php if (session()->getFlashdata('imported_count')): ?>
                <div class="text-sm mt-2">Total siswa berhasil diimport: <strong><?= session()->getFlashdata('imported_count') ?></strong></div>
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
            <div class="font-semibold text-yellow-900 mb-3"><i class="fas fa-exclamation-triangle mr-2"></i>Beberapa baris mengalami kesalahan:</div>
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
        <!-- Form Upload -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="<?= base_url('/admin/students/import') ?>" method="POST" enctype="multipart/form-data">
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
                        Import Siswa
                    </button>
                </form>
            </div>

            <!-- Reference Tables -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Walikelas Reference -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-slate-800 mb-3 text-sm border-b pb-2">Referensi Walikelas (ID)</h3>
                    <div class="overflow-x-auto max-h-60 overflow-y-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-500 font-semibold sticky top-0">
                                <tr>
                                    <th class="px-3 py-2">ID</th>
                                    <th class="px-3 py-2">Kelas</th>
                                    <th class="px-3 py-2">Guru</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (!empty($walikelas)): ?>
                                    <?php foreach ($walikelas as $wk): ?>
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-indigo-600 font-bold"><?= $wk['id'] ?></td>
                                            <td class="px-3 py-2"><?= esc($wk['class_name']) ?></td>
                                            <td class="px-3 py-2 text-slate-600"><?= esc($wk['teacher_name']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-3 py-2 text-center text-slate-500">Tidak ada data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Shift Reference -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-slate-800 mb-3 text-sm border-b pb-2">Referensi Shift (ID)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-500 font-semibold">
                                <tr>
                                    <th class="px-3 py-2">ID</th>
                                    <th class="px-3 py-2">Nama Shift</th>
                                    <th class="px-3 py-2">Jam Masuk - Pulang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (!empty($shifts)): ?>
                                    <?php foreach ($shifts as $shift): ?>
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-indigo-600 font-bold"><?= $shift['id'] ?></td>
                                            <td class="px-3 py-2 font-semibold"><?= esc($shift['name']) ?></td>
                                            <td class="px-3 py-2 text-slate-600"><?= substr($shift['start_time'], 0, 5) ?> - <?= substr($shift['end_time'], 0, 5) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-3 py-2 text-center text-slate-500">Tidak ada data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold text-blue-900 mb-3">Format File</h3>
            <p class="text-sm text-blue-800 mb-4">File harus memiliki kolom berikut (dengan header di baris pertama):</p>

            <div class="bg-white rounded p-3 text-xs font-mono text-slate-700 overflow-x-auto mb-4">
                <div>nis,full_name,class,username,password,agama,wali_kelas_id,shift_id,phone_number,guardian_name,guardian_phone,rfid_id</div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="font-semibold text-blue-900">nis</span>
                    <p class="text-blue-800">Nomor Induk Siswa (required)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">full_name</span>
                    <p class="text-blue-800">Nama lengkap siswa (required)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">class</span>
                    <p class="text-blue-800">Kelas, misal: X A, XI B (required)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">username</span>
                    <p class="text-blue-800">Username untuk login (required, unique)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">password</span>
                    <p class="text-blue-800">Password (required, min 6 karakter)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">agama</span>
                    <p class="text-blue-800">Agama siswa (optional, bisa menggunakan header 'agama' atau 'religion')</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">wali_kelas_id</span>
                    <p class="text-blue-800">ID walikelas (optional)</p>
                </div>
                <div class="bg-yellow-50 p-2 rounded border border-yellow-200">
                    <span class="font-semibold text-yellow-900">shift_id</span>
                    <p class="text-yellow-800">ID shift (WAJIB) - Format: 1 atau 2</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">phone_number</span>
                    <p class="text-blue-800">Nomor WhatsApp siswa (optional) - Format: 62812345678 (tanpa +)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">guardian_name</span>
                    <p class="text-blue-800">Nama wali/orang tua (optional)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">guardian_phone</span>
                    <p class="text-blue-800">Nomor WhatsApp wali (optional) - Format: 62812345678 (tanpa +)</p>
                </div>
                <div>
                    <span class="font-semibold text-blue-900">rfid_id</span>
                    <p class="text-blue-800">ID tag RFID untuk IoT reader (optional, unique) - Format: RF12345ABC</p>
                </div>
            </div>

            <hr class="my-4 border-blue-200">

            <h4 class="font-semibold text-blue-900 mb-2">Contoh Data:</h4>
            <div class="bg-white rounded p-3 text-xs overflow-x-auto space-y-1">
                <div class="font-mono text-slate-600 text-xs">nis,full_name,class,username,password,agama,wali_kelas_id,shift_id,phone_number,guardian_name,guardian_phone,rfid_id</div>
                <div class="font-mono text-slate-600 text-xs">S001,Ahmad Rizki,X A,ahmad_rizki,password123,Islam,1,1,6281234567890,Bapak Rizki,6289876543210,RF001A123</div>
                <div class="font-mono text-slate-600 text-xs">S002,Budi Santoso,X A,budi_santoso,password123,Kristen,1,1,6282234567891,Ibu Santoso,6289876543211,RF002B456</div>
                <div class="font-mono text-slate-600 text-xs">S003,Citra Dewi,X B,citra_dewi,password123,Hindu,2,2,6283234567892,Bapak Dewi,6289876543212,RF003C789</div>
            </div>

            <div class="mt-4 flex gap-2">
                <a href="<?= base_url('/files/students_template.xlsx') ?>" class="inline-block bg-green-600 text-white px-3 py-2 rounded text-xs hover:bg-green-700 font-medium">
                    <i class="fas fa-file-excel mr-1"></i> Download Template Excel
                </a>
                <a href="<?= base_url('/files/students_template.csv') ?>" class="inline-block bg-blue-600 text-white px-3 py-2 rounded text-xs hover:bg-blue-700 font-medium">
                    <i class="fas fa-file-csv mr-1"></i> Download Template CSV
                </a>
            </div>


        </div>

        <!-- Recent Imports -->
        <?php if (!empty($recentImports)): ?>
            <div class="mt-8">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Riwayat Import Terakhir</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 border-b">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">Tanggal</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">File</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">Jumlah</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentImports as $import): ?>
                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-700"><?= date('d M Y H:i', strtotime($import['created_at'])) ?></td>
                                    <td class="px-4 py-3 text-slate-700"><?= esc($import['filename'] ?? 'N/A') ?></td>
                                    <td class="px-4 py-3 text-slate-700"><?= $import['total_count'] ?? '-' ?></td>
                                    <td class="px-4 py-3">
                                        <?php if (($import['success_count'] ?? 0) == ($import['total_count'] ?? 0)): ?>
                                            <span class="inline-block bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">Sukses</span>
                                        <?php else: ?>
                                            <span class="inline-block bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-medium">Sebagian</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function showFileName(input) {
            const fileName = input.files[0]?.name || '';
            document.getElementById('fileName').textContent = fileName ? '✓ ' + fileName : '';
        }

        // Drag and drop
        const dropZone = document.querySelector('[onclick*="csvFile"]');
        if (dropZone) {
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-indigo-400', 'bg-indigo-50');
            });
            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
            });
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    document.getElementById('csvFile').files = files;
                    showFileName(document.getElementById('csvFile'));
                }
            });
        }
    </script>

    <?php $this->endSection() ?>