<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="mx-auto px-2 sm:px-4 py-4 max-w-7xl">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 3H5a2 2 0 00-2 2v14l4-3 4 3 4-3 4 3V5a2 2 0 00-2-2z" />
            </svg>
            <h1 class="text-2xl font-bold text-slate-800"><?= $title ?></h1>
        </div>
        <p class="text-slate-600">Monitor dan analisis kehadiran siswa</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-slate-600">Total</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800"><?= $stats['total'] ?></p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-green-600">Tepat Waktu</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-600"><?= $stats['on_time'] ?></p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-200" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-orange-600">Terlambat</p>
                    <p class="text-xl sm:text-2xl font-bold text-orange-600"><?= $stats['late'] ?></p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-200" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-blue-600">Izin</p>
                    <p class="text-xl sm:text-2xl font-bold text-blue-600"><?= $stats['izin'] ?></p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-200" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-red-600">Sakit</p>
                    <p class="text-xl sm:text-2xl font-bold text-red-600"><?= $stats['sakit'] ?></p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-200" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-purple-600">Alpha</p>
                    <p class="text-xl sm:text-2xl font-bold text-purple-600"><?= $stats['alpha'] ?></p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-200" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800 flex items-center gap-2">
                <i class="fas fa-filter text-indigo-600"></i>
                Filter Laporan
            </h2>
            <a href="<?= base_url('/admin/attendance/export-excel?' . http_build_query([
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                            'class' => $class,
                            'search' => $search
                        ])) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                <i class="fas fa-file-excel"></i>
                Download Excel
            </a>
        </div>
        <form method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="<?= $dateFrom ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?= $dateTo ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                <select name="class" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['class'] ?>" <?= $class === $c['class'] ? 'selected' : '' ?>><?= $c['class'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cari (NIS/Nama)</label>
                <input type="text" name="search" value="<?= $search ?>" placeholder="NIS atau Nama Siswa" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">NIS</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Nama Siswa</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Kelas</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Status Masuk</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Jam Masuk</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Status Pulang</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Jam Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($attendance)): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-slate-300" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2V17zm4 0h-2V7h2V17zm4 0h-2v-4h2V17z" />
                                </svg>
                                Tidak ada data absensi
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1;
                        foreach ($attendance as $att): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700"><?= $no++ ?></td>
                                <td class="px-4 py-3 text-slate-700"><?= $att['nis'] ?? '-' ?></td>
                                <td class="px-4 py-3 text-slate-700"><?= $att['full_name'] ?? '-' ?></td>
                                <td class="px-4 py-3 text-slate-700"><?= $att['class'] ?? '-' ?></td>
                                <td class="px-4 py-3 text-slate-700"><?= date('d-m-Y', strtotime($att['date'])) ?></td>
                                <td class="px-4 py-3">
                                    <?php
                                    $status_map = [
                                        'on_time' => ['label' => 'Tepat Waktu', 'color' => 'bg-green-100 text-green-800'],
                                        'late' => ['label' => 'Terlambat', 'color' => 'bg-orange-100 text-orange-800'],
                                        'izin' => ['label' => 'Izin', 'color' => 'bg-blue-100 text-blue-800'],
                                        'sakit' => ['label' => 'Sakit', 'color' => 'bg-red-100 text-red-800'],
                                        'alpha' => ['label' => 'Alpha', 'color' => 'bg-purple-100 text-purple-800'],
                                        'unknown' => ['label' => 'Belum', 'color' => 'bg-slate-100 text-slate-800'],
                                    ];
                                    $status = $att['masuk_status'] ?? 'unknown';
                                    $display = $status_map[$status] ?? $status_map['unknown'];
                                    ?>
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?= $display['color'] ?>">
                                        <?= $display['label'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <?= $att['masuk_at'] ? date('H:i', strtotime($att['masuk_at'])) : '-' ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $pulang_status = $att['pulang_status'] ?? null;

                                    // Jika pulang_at ada tapi pulang_status kosong, hitung statusnya
                                    if (!empty($att['pulang_at']) && (empty($pulang_status) || $pulang_status === 'unknown')) {
                                        $pulang_time = date('H:i:s', strtotime($att['pulang_at']));
                                        $checkout_time = '15:00:00'; // default
                                        $pulang_status = ($pulang_time >= $checkout_time) ? 'on_time' : 'early';
                                    } elseif (empty($att['pulang_at'])) {
                                        $pulang_status = 'unknown';
                                    }

                                    // Handle early status
                                    if (!isset($status_map['early'])) {
                                        $status_map['early'] = ['label' => 'Pulang Awal', 'color' => 'bg-yellow-100 text-yellow-800'];
                                    }

                                    $display = $status_map[$pulang_status] ?? $status_map['unknown'];
                                    ?>
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?= $display['color'] ?>">
                                        <?= $display['label'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <?= $att['pulang_at'] ? date('H:i', strtotime($att['pulang_at'])) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none;
        }
    }
</style>
<?= $this->endSection() ?>