<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="mx-auto px-2 sm:px-4 py-4 max-w-7xl">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-chalkboard-user text-indigo-600 text-2xl"></i>
            <h1 class="text-2xl font-bold text-slate-800"><?= $title ?></h1>
        </div>
        <p class="text-slate-600">Monitor dan analisis kehadiran guru</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-slate-600">Total</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800"><?= $stats['total'] ?></p>
                </div>
                <i class="fas fa-users text-3xl text-slate-400"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-green-600">Tepat Waktu</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-600"><?= $stats['on_time'] ?></p>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-200"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-orange-600">Terlambat</p>
                    <p class="text-xl sm:text-2xl font-bold text-orange-600"><?= $stats['late'] ?></p>
                </div>
                <i class="fas fa-clock text-3xl text-orange-200"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-blue-600">Izin</p>
                    <p class="text-xl sm:text-2xl font-bold text-blue-600"><?= $stats['izin'] ?></p>
                </div>
                <i class="fas fa-envelope text-3xl text-blue-200"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-red-600">Sakit</p>
                    <p class="text-xl sm:text-2xl font-bold text-red-600"><?= $stats['sakit'] ?></p>
                </div>
                <i class="fas fa-heartbeat text-3xl text-red-200"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-purple-600">Alpha</p>
                    <p class="text-xl sm:text-2xl font-bold text-purple-600"><?= $stats['alpha'] ?></p>
                </div>
                <i class="fas fa-ban text-3xl text-purple-200"></i>
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
        </div>
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="<?= $dateFrom ?>" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?= $dateTo ?>" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cari Nama/NIP</label>
                <input type="text" name="search" placeholder="Nama atau NIP guru..." value="<?= $search ?>" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="sm:col-span-3 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="<?= base_url('/admin/attendance/guru-riwayat') ?>" class="px-4 py-2 bg-slate-300 text-slate-700 rounded-lg hover:bg-slate-400 flex items-center gap-2">
                    <i class="fas fa-redo"></i> Reset
                </a>
                <a href="<?= base_url('/admin/attendance/export-guru-riwayat?' . http_build_query([
                                'date_from' => $dateFrom,
                                'date_to' => $dateTo,
                                'search' => $search
                            ])) ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> Download Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($attendance)): ?>
            <div class="p-6 text-center text-slate-500">
                <i class="fas fa-inbox text-4xl mb-2 block opacity-50"></i>
                Tidak ada data absensi untuk periode ini
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Nama Guru</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Username</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Jam Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Jam Pulang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Status Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Status Pulang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php $no = 1;
                        foreach ($attendance as $att): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm text-slate-700"><?= $no++ ?></td>
                                <td class="px-4 py-3 text-sm text-slate-800 font-medium"><?= $att['full_name'] ?></td>
                                <td class="px-4 py-3 text-sm text-slate-600"><?= $att['username'] ?></td>
                                <td class="px-4 py-3 text-sm text-slate-600"><?= date('d/m/Y', strtotime($att['date'])) ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <?php if ($att['masuk_at'] ?? null): ?>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs"><?= substr($att['masuk_at'], 11, 5) ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <?php if ($att['pulang_at'] ?? null): ?>
                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs"><?= substr($att['pulang_at'], 11, 5) ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <?php
                                    $status = $att['masuk_status'] ?? 'alpha';
                                    $statusBadge = [
                                        'on_time' => ['bg-green-100', 'text-green-800', 'Tepat Waktu'],
                                        'late' => ['bg-orange-100', 'text-orange-800', 'Terlambat'],
                                        'izin' => ['bg-blue-100', 'text-blue-800', 'Izin'],
                                        'sakit' => ['bg-red-100', 'text-red-800', 'Sakit'],
                                        'alpha' => ['bg-purple-100', 'text-purple-800', 'Alpha'],
                                    ];
                                    $badge = $statusBadge[$status] ?? ['bg-gray-100', 'text-gray-800', 'Unknown'];
                                    ?>
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?= $badge[0] ?> <?= $badge[1] ?>"><?= $badge[2] ?></span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <?php
                                    $statusPulang = $att['pulang_status'] ?? '-';
                                    $statusBadgePulang = [
                                        'on_time' => ['bg-green-100', 'text-green-800', 'Tepat'],
                                        'early' => ['bg-blue-100', 'text-blue-800', 'Awal'],
                                        'late' => ['bg-orange-100', 'text-orange-800', 'Terlalu Lama'],
                                        'none' => ['bg-gray-100', 'text-gray-800', 'Belum'],
                                    ];
                                    $badgePulang = $statusBadgePulang[$statusPulang] ?? ['bg-gray-100', 'text-gray-800', '-'];
                                    ?>
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?= $badgePulang[0] ?> <?= $badgePulang[1] ?>"><?= $badgePulang[2] ?></span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600"><?= $att['note'] ?? '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>