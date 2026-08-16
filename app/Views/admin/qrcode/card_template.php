<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Template Kartu Absensi</h1>
            <p class="text-sm text-slate-500 mt-1">Atur desain dan tata letak kartu absensi siswa</p>
        </div>
        <a href="<?= base_url('/admin/dashboard') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">Kembali</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 p-3 bg-green-50 border border-green-100 text-green-700 rounded"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('/admin/card-template/save') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Settings Column -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Background & Card Size -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Background & Ukuran (mm)</h3>
                    <div class="mb-4">
                        <label class="block text-sm text-slate-600 mb-2">Upload Template (Background)</label>
                        <input type="file" name="background_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-slate-600 mb-2">Upload Logo (Optional)</label>
                        <input type="file" name="logo_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Lebar Kartu (mm)</label>
                            <input type="number" step="0.01" name="card_width" value="<?= $config['card_width'] ?? '85.60' ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Tinggi Kartu (mm)</label>
                            <input type="number" step="0.01" name="card_height" value="<?= $config['card_height'] ?? '53.98' ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Standar ID Card (CR80) Landscape: 85.60 x 53.98 mm</p>
                    <p class="text-xs text-slate-400">Standar Portrait: 53.98 x 85.60 mm</p>
                </div>

                <!-- QR Code Settings -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">QR Code (mm)</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="qr_visible" value="1" <?= ($config['qr']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="qr_x" value="<?= $config['qr']['x'] ?? 5 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="qr_y" value="<?= $config['qr']['y'] ?? 15 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Ukuran (mm)</label>
                            <input type="number" step="0.1" name="qr_size" value="<?= $config['qr']['size'] ?? 25 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Text Settings: Name -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">Nama Siswa</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="name_visible" value="1" <?= ($config['name']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="name_x" value="<?= $config['name']['x'] ?? 42 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="name_y" value="<?= $config['name']['y'] ?? 10 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Ukuran Font (pt)</label>
                            <input type="number" step="0.1" name="name_size" value="<?= $config['name']['size'] ?? 12 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Warna</label>
                            <input type="color" name="name_color" value="<?= $config['name']['color'] ?? '#000000' ?>" class="w-full border h-8 p-0 rounded" onchange="updatePreview()">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Perataan Teks</label>
                            <select name="name_align" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                                <option value="left" <?= ($config['name']['align'] ?? 'center') == 'left' ? 'selected' : '' ?>>Rata Kiri</option>
                                <option value="center" <?= ($config['name']['align'] ?? 'center') == 'center' ? 'selected' : '' ?>>Rata Tengah</option>
                                <option value="right" <?= ($config['name']['align'] ?? 'center') == 'right' ? 'selected' : '' ?>>Rata Kanan</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Label (Prefix)</label>
                            <input type="text" name="name_label" value="<?= $config['name']['label'] ?? '' ?>" class="w-full border rounded px-2 py-1" placeholder="Contoh: Nama: " oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Text Settings: NIS -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">NIS</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="nis_visible" value="1" <?= ($config['nis']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="nis_x" value="<?= $config['nis']['x'] ?? 42 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="nis_y" value="<?= $config['nis']['y'] ?? 15 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Ukuran Font (pt)</label>
                            <input type="number" step="0.1" name="nis_size" value="<?= $config['nis']['size'] ?? 10 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Warna</label>
                            <input type="color" name="nis_color" value="<?= $config['nis']['color'] ?? '#666666' ?>" class="w-full border h-8 p-0 rounded" onchange="updatePreview()">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Perataan Teks</label>
                            <select name="nis_align" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                                <option value="left" <?= ($config['nis']['align'] ?? 'center') == 'left' ? 'selected' : '' ?>>Rata Kiri</option>
                                <option value="center" <?= ($config['nis']['align'] ?? 'center') == 'center' ? 'selected' : '' ?>>Rata Tengah</option>
                                <option value="right" <?= ($config['nis']['align'] ?? 'center') == 'right' ? 'selected' : '' ?>>Rata Kanan</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Label (Prefix)</label>
                            <input type="text" name="nis_label" value="<?= $config['nis']['label'] ?? '' ?>" class="w-full border rounded px-2 py-1" placeholder="Contoh: NIS: " oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Text Settings: Class -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">Kelas</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="class_visible" value="1" <?= ($config['class']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="class_x" value="<?= $config['class']['x'] ?? 42 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="class_y" value="<?= $config['class']['y'] ?? 20 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Ukuran Font (pt)</label>
                            <input type="number" step="0.1" name="class_size" value="<?= $config['class']['size'] ?? 8 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Warna</label>
                            <input type="color" name="class_color" value="<?= $config['class']['color'] ?? '#666666' ?>" class="w-full border h-8 p-0 rounded" onchange="updatePreview()">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Perataan Teks</label>
                            <select name="class_align" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                                <option value="left" <?= ($config['class']['align'] ?? 'center') == 'left' ? 'selected' : '' ?>>Rata Kiri</option>
                                <option value="center" <?= ($config['class']['align'] ?? 'center') == 'center' ? 'selected' : '' ?>>Rata Tengah</option>
                                <option value="right" <?= ($config['class']['align'] ?? 'center') == 'right' ? 'selected' : '' ?>>Rata Kanan</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Label (Prefix)</label>
                            <input type="text" name="class_label" value="<?= $config['class']['label'] ?? '' ?>" class="w-full border rounded px-2 py-1" placeholder="Contoh: Kelas: " oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Photo Settings -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">Foto Siswa (3x4)</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="photo_visible" value="1" <?= ($config['photo']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="photo_x" value="<?= $config['photo']['x'] ?? 65 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="photo_y" value="<?= $config['photo']['y'] ?? 15 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Lebar (mm)</label>
                            <input type="number" step="0.1" name="photo_width" value="<?= $config['photo']['width'] ?? 15 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Tinggi (mm)</label>
                            <input type="number" step="0.1" name="photo_height" value="<?= $config['photo']['height'] ?? 20 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Header Settings -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">Header (Kartu Pelajar)</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="header_visible" value="1" <?= ($config['header']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="header_x" value="<?= $config['header']['x'] ?? 42 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="header_y" value="<?= $config['header']['y'] ?? 5 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Ukuran Font (pt)</label>
                            <input type="number" step="0.1" name="header_size" value="<?= $config['header']['size'] ?? 10 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Warna</label>
                            <input type="color" name="header_color" value="<?= $config['header']['color'] ?? '#000000' ?>" class="w-full border h-8 p-0 rounded" onchange="updatePreview()">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Perataan Teks</label>
                            <select name="header_align" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                                <option value="left" <?= ($config['header']['align'] ?? 'center') == 'left' ? 'selected' : '' ?>>Rata Kiri</option>
                                <option value="center" <?= ($config['header']['align'] ?? 'center') == 'center' ? 'selected' : '' ?>>Rata Tengah</option>
                                <option value="right" <?= ($config['header']['align'] ?? 'center') == 'right' ? 'selected' : '' ?>>Rata Kanan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- School Name Settings -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">Nama Sekolah</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="school_name_visible" value="1" <?= ($config['school_name']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="school_name_x" value="<?= $config['school_name']['x'] ?? 42 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="school_name_y" value="<?= $config['school_name']['y'] ?? 10 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Ukuran Font (pt)</label>
                            <input type="number" step="0.1" name="school_name_size" value="<?= $config['school_name']['size'] ?? 14 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Warna</label>
                            <input type="color" name="school_name_color" value="<?= $config['school_name']['color'] ?? '#000000' ?>" class="w-full border h-8 p-0 rounded" onchange="updatePreview()">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Perataan Teks</label>
                            <select name="school_name_align" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                                <option value="left" <?= ($config['school_name']['align'] ?? 'center') == 'left' ? 'selected' : '' ?>>Rata Kiri</option>
                                <option value="center" <?= ($config['school_name']['align'] ?? 'center') == 'center' ? 'selected' : '' ?>>Rata Tengah</option>
                                <option value="right" <?= ($config['school_name']['align'] ?? 'center') == 'right' ? 'selected' : '' ?>>Rata Kanan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- School Info Settings -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">Info Sekolah (Alamat)</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="school_info_visible" value="1" <?= ($config['school_info']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="school_info_x" value="<?= $config['school_info']['x'] ?? 42 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="school_info_y" value="<?= $config['school_info']['y'] ?? 50 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Ukuran Font (pt)</label>
                            <input type="number" step="0.1" name="school_info_size" value="<?= $config['school_info']['size'] ?? 6 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Warna</label>
                            <input type="color" name="school_info_color" value="<?= $config['school_info']['color'] ?? '#000000' ?>" class="w-full border h-8 p-0 rounded" onchange="updatePreview()">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Perataan Teks</label>
                            <select name="school_info_align" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                                <option value="left" <?= ($config['school_info']['align'] ?? 'center') == 'left' ? 'selected' : '' ?>>Rata Kiri</option>
                                <option value="center" <?= ($config['school_info']['align'] ?? 'center') == 'center' ? 'selected' : '' ?>>Rata Tengah</option>
                                <option value="right" <?= ($config['school_info']['align'] ?? 'center') == 'right' ? 'selected' : '' ?>>Rata Kanan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Logo Settings -->
                <div class="bg-white p-6 rounded-lg shadow border">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-slate-800">Logo (Logo Sekolah)</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="logo_visible" value="1" <?= ($config['logo']['visible'] ?? true) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-2 text-sm font-medium text-gray-500">Tampil</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi X (mm)</label>
                            <input type="number" step="0.1" name="logo_x" value="<?= $config['logo']['x'] ?? 5 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Posisi Y (mm)</label>
                            <input type="number" step="0.1" name="logo_y" value="<?= $config['logo']['y'] ?? 5 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Lebar (mm)</label>
                            <input type="number" step="0.1" name="logo_width" value="<?= $config['logo']['width'] ?? 10 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Tinggi (mm)</label>
                            <input type="number" step="0.1" name="logo_height" value="<?= $config['logo']['height'] ?? 10 ?>" class="w-full border rounded px-2 py-1" onchange="updatePreview()">
                        </div>
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button type="button" onclick="resetDefaults()" class="w-1/2 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 rounded-lg shadow-lg">Reset Default</button>
                        <button type="submit" class="w-1/2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg shadow-lg">Simpan</button>
                    </div>
                </div>
            </div>

            <!-- Preview Column -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-lg shadow border sticky top-24">
                    <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Live Preview</h3>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <span class="font-bold">Elemen tidak muncul?</span><br>
                                    Kemungkinan nilai Posisi X/Y anda masih dalam <b>Pixel</b> (angka besar).<br>
                                    Sistem sekarang menggunakan <b>Millimeter (mm)</b>.<br>
                                    Silakan kecilkan angkanya (misal: 100px &rarr; 26mm) atau klik tombol <b>Reset Default</b>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center bg-slate-100 p-8 rounded border overflow-visible">
                        <!-- Container for scaling -->
                        <div style="transform: scale(1.5); transform-origin: top center; margin-bottom: 200px;">
                            <div id="cardPreview" class="relative bg-white shadow-lg overflow-visible" style="width: 85.6mm; height: 53.98mm; background-size: cover; background-position: center;">
                                <?php if (!empty($config['background_image'])): ?>
                                    <img src="<?= $config['background_image'] ?>" class="absolute inset-0 w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600"></div>
                                <?php endif; ?>

                                <!-- Logo Placeholder -->
                                <div id="previewLogo" data-target="logo" class="absolute cursor-move hover:border-blue-500 hover:border-2 z-10 flex items-center justify-center">
                                    <?php if (!empty($config['logo']['path'])): ?>
                                        <img src="<?= $config['logo']['path'] ?>" class="w-full h-full object-contain">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-white/50 border border-dashed border-slate-500 flex items-center justify-center">Logo</div>
                                    <?php endif; ?>
                                </div>

                                <!-- QR Placeholder -->
                                <div id="previewQR" data-target="qr" class="absolute bg-white border border-gray-200 flex items-center justify-center cursor-move hover:border-blue-500 hover:border-2 z-10">
                                    <i class="fas fa-qrcode text-xl text-gray-800"></i>
                                </div>

                                <!-- Name Placeholder -->
                                <div id="previewName" data-target="name" class="absolute font-bold text-center w-full px-1 cursor-move hover:text-blue-600 border border-transparent hover:border-blue-300 border-dashed z-10" style="transform: translateX(-50%); white-space: nowrap;">
                                    SISWA CONTOH
                                </div>

                                <!-- NIS Placeholder -->
                                <div id="previewNIS" data-target="nis" class="absolute font-semibold text-center w-full px-1 cursor-move hover:text-blue-600 border border-transparent hover:border-blue-300 border-dashed z-10" style="transform: translateX(-50%); white-space: nowrap;">
                                    NIS: 123456
                                </div>

                                <!-- Class Placeholder -->
                                <div id="previewClass" data-target="class" class="absolute text-center w-full px-1 cursor-move hover:text-blue-600 border border-transparent hover:border-blue-300 border-dashed z-10" style="transform: translateX(-50%); white-space: nowrap;">
                                    KELAS X-A
                                </div>

                                <!-- Photo Placeholder -->
                                <div id="previewPhoto" data-target="photo" class="absolute bg-gray-200 border border-gray-400 cursor-move hover:border-blue-500 hover:border-2 z-10 flex items-center justify-center overflow-hidden">
                                    <i class="fas fa-user text-gray-400 text-2xl"></i>
                                </div>

                                <!-- Header Placeholder -->
                                <div id="previewHeader" data-target="header" class="absolute font-bold text-center w-full px-1 cursor-move hover:text-blue-600 border border-transparent hover:border-blue-300 border-dashed z-10" style="transform: translateX(-50%); white-space: nowrap;">
                                    KARTU PELAJAR
                                </div>

                                <!-- School Name Placeholder -->
                                <div id="previewSchoolName" data-target="school_name" class="absolute font-bold text-center w-full px-1 cursor-move hover:text-blue-600 border border-transparent hover:border-blue-300 border-dashed z-10" style="transform: translateX(-50%); white-space: nowrap;">
                                    NAMA SEKOLAH
                                </div>

                                <!-- School Info Placeholder -->
                                <div id="previewSchoolInfo" data-target="school_info" class="absolute text-center w-full px-1 cursor-move hover:text-blue-600 border border-transparent hover:border-blue-300 border-dashed z-10" style="transform: translateX(-50%); white-space: nowrap;">
                                    Alamat Sekolah | Telp | Email
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function resetDefaults() {
        if (!confirm('Reset pengaturan ke posisi default (Standar ID Card)?')) return;

        // Helper to set value
        const set = (name, val) => document.querySelector(`[name="${name}"]`).value = val;

        set('card_width', '85.60');
        set('card_height', '53.98');

        set('qr_x', '5');
        set('qr_y', '15');
        set('qr_size', '25');

        set('name_x', '42');
        set('name_y', '10');
        set('name_size', '12');
        set('name_color', '#000000'); // Assuming default color
        set('name_align', 'center');

        set('nis_x', '42');
        set('nis_y', '15');
        set('nis_size', '10');
        set('nis_color', '#666666');
        set('nis_align', 'center');

        set('class_x', '42');
        set('class_y', '35');
        set('class_size', '8');
        set('class_color', '#666666');
        set('class_align', 'center');

        set('photo_x', '65');
        set('photo_y', '15');
        set('photo_width', '15');
        set('photo_height', '20');

        set('header_x', '42');
        set('header_y', '5');
        set('header_size', '10');
        set('header_color', '#000000');
        set('header_align', 'center');

        set('school_name_x', '42');
        set('school_name_y', '10');
        set('school_name_size', '14');
        set('school_name_color', '#000000');
        set('school_name_align', 'center');

        set('school_info_x', '42');
        set('school_info_y', '50');
        set('school_info_size', '6');
        set('school_info_color', '#000000');
        set('school_info_align', 'center');

        set('logo_x', '5');
        set('logo_y', '5');
        set('logo_width', '10');
        set('logo_height', '10');

        // Reset visibility
        document.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = true);
        document.querySelectorAll('input[name$="_label"]').forEach(el => el.value = '');

        // Reset specific labels if needed default
        set('nis_label', 'NIS: ');

        updatePreview();
    }

    // Scale factor to map input pixels (based on typical card size) to the preview box
    // Assuming 'Standard' card width is around 300px in preview.
    // Let's treat inputs as relative to a 300px width card for now, or raw pixels.
    // IMPORTANT: The backend saves RAW pixels. The preview should respect that. 
    // If the print output uses a specific width (e.g. 190mm ~ 718px at 96dpi), the inputs should be large.
    // For simplicity, let's assume inputs are 1:1 with the preview for now (300px width).

    function updatePreview() {
        // Elements
        const qr = document.getElementById('previewQR');
        const name = document.getElementById('previewName');
        const nis = document.getElementById('previewNIS');
        const cls = document.getElementById('previewClass');
        const box = document.getElementById('cardPreview');
        const logo = document.getElementById('previewLogo');

        // Helper to get value
        const val = (name) => {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return '';
            if (el.type === 'checkbox') return el.checked;
            return el.value;
        };

        // Update Box Size (using mm)
        box.style.width = val('card_width') + 'mm';
        box.style.height = val('card_height') + 'mm';

        // Update QR
        qr.style.left = val('qr_x') + 'mm';
        qr.style.top = val('qr_y') + 'mm';
        qr.style.width = val('qr_size') + 'mm';
        qr.style.height = val('qr_size') + 'mm';
        qr.style.display = val('qr_visible') ? 'flex' : 'none';

        // Update Logo
        logo.style.left = val('logo_x') + 'mm';
        logo.style.top = val('logo_y') + 'mm';
        logo.style.width = val('logo_width') + 'mm';
        logo.style.height = val('logo_height') + 'mm';
        logo.style.display = val('logo_visible') ? 'block' : 'none';

        // Helper to update text style
        const updateText = (el, prefix) => {
            el.style.left = val(prefix + '_x') + 'mm';
            el.style.top = val(prefix + '_y') + 'mm';
            el.style.fontSize = val(prefix + '_size') + 'pt';
            el.style.color = val(prefix + '_color');
            const align = val(prefix + '_align');
            el.style.textAlign = align;
            if (align === 'center') {
                el.style.transform = 'translateX(-50%)';
            } else if (align === 'right') {
                el.style.transform = 'translateX(-100%)';
            } else {
                el.style.transform = 'none';
            }

            // Updates label content if exists
            const labelInput = document.querySelector(`[name="${prefix}_label"]`);
            if (labelInput) {
                const label = labelInput.value;
                // Preserve the placeholder text depending on the element
                let contentText = '';
                if (prefix === 'name') contentText = 'SISWA CONTOH';
                if (prefix === 'nis') contentText = '123456';
                if (prefix === 'class') contentText = 'KELAS X-A';

                el.innerText = label + contentText;
            }

            // Update visibility
            el.style.display = val(prefix + '_visible') ? 'block' : 'none';
        };

        // Update Name
        updateText(name, 'name');

        // Update NIS
        updateText(nis, 'nis');

        // Update Class
        updateText(cls, 'class');

        // Update Photo
        const photo = document.getElementById('previewPhoto');
        photo.style.left = val('photo_x') + 'mm';
        photo.style.top = val('photo_y') + 'mm';
        photo.style.width = val('photo_width') + 'mm';
        photo.style.height = val('photo_height') + 'mm';
        photo.style.display = val('photo_visible') ? 'flex' : 'none';

        // Update Header
        updateText(document.getElementById('previewHeader'), 'header');

        // Update School Name
        updateText(document.getElementById('previewSchoolName'), 'school_name');

        // Update School Info
        updateText(document.getElementById('previewSchoolInfo'), 'school_info');
    }

    // Drag and Drop Logic
    document.addEventListener('DOMContentLoaded', () => {
        updatePreview(); // Initial call

        const draggables = document.querySelectorAll('.cursor-move');
        const container = document.getElementById('cardPreview');
        const SCALE = 1.5;
        let isDragging = false;
        let currentElement = null;
        let startX, startY, startLeft, startTop;

        draggables.forEach(el => {
            el.addEventListener('mousedown', (e) => {
                isDragging = true;
                currentElement = el;
                startX = e.clientX;
                startY = e.clientY;

                // Get current position in pixels relative to container
                startLeft = el.offsetLeft;
                startTop = el.offsetTop;

                e.preventDefault(); // Prevent text selection
            });
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging || !currentElement) return;

            const dx = (e.clientX - startX) / SCALE;
            const dy = (e.clientY - startY) / SCALE;

            currentElement.style.left = (startLeft + dx) + 'px';
            currentElement.style.top = (startTop + dy) + 'px';
        });

        document.addEventListener('mouseup', () => {
            if (!isDragging || !currentElement) return;

            // Calculate new position in mm
            // 1. Get container width in px and mm
            const containerW_px = container.offsetWidth;
            const containerW_mm = parseFloat(document.querySelector('[name="card_width"]').value);

            // 2. Calculate conversion ratio
            const mmPerPx = containerW_mm / containerW_px;

            // 3. Get final element position in px
            const finalLeft_px = currentElement.offsetLeft;
            const finalTop_px = currentElement.offsetTop;

            // 4. Convert to mm and round
            const finalLeft_mm = (finalLeft_px * mmPerPx).toFixed(1);
            const finalTop_mm = (finalTop_px * mmPerPx).toFixed(1);

            // 5. Update inputs
            const target = currentElement.dataset.target; // 'qr', 'name', 'nis', 'class'
            if (target) {
                document.querySelector(`[name="${target}_x"]`).value = finalLeft_mm;
                document.querySelector(`[name="${target}_y"]`).value = finalTop_mm;
            }

            isDragging = false;
            currentElement = null;

            // Force update preview to snap to strict mm values
            updatePreview();
        });
    });
</script>
<?= $this->endSection() ?>