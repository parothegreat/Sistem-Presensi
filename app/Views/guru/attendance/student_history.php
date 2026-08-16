<?= $this->extend('layouts/guru') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Riwayat Absensi Siswa</h1>
            <p class="text-slate-600 mt-1">
                <strong><?= esc($student['full_name']) ?></strong>
                | NIS: <strong><?= esc($student['nis']) ?></strong>
                | Kelas: <strong><?= esc($waliKelas['class_name']) ?></strong>
            </p>
        </div>
        <a href="/guru/attendance" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-slate-500">
            <p class="text-xs text-slate-600">Total Hari</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['total'] ?></h3>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-green-500">
            <p class="text-xs text-slate-600">Tepat Waktu</p>
            <h3 class="text-2xl font-bold text-green-600 mt-1"><?= $stats['on_time'] ?></h3>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-yellow-500">
            <p class="text-xs text-slate-600">Terlambat</p>
            <h3 class="text-2xl font-bold text-yellow-600 mt-1"><?= $stats['late'] ?></h3>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-blue-500">
            <p class="text-xs text-slate-600">Izin</p>
            <h3 class="text-2xl font-bold text-blue-600 mt-1"><?= $stats['izin'] ?></h3>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-orange-500">
            <p class="text-xs text-slate-600">Sakit</p>
            <h3 class="text-2xl font-bold text-orange-600 mt-1"><?= $stats['sakit'] ?></h3>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-red-500">
            <p class="text-xs text-slate-600">Alpha</p>
            <h3 class="text-2xl font-bold text-red-600 mt-1"><?= $stats['alpha'] ?></h3>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-lg font-bold text-slate-800">Riwayat 30 Hari Terakhir</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Tanggal</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Hari</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Waktu Masuk</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Waktu Pulang</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (! empty($attendanceHistory)): ?>
                        <?php foreach ($attendanceHistory as $attendance): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                    <?= date('d M Y', strtotime($attendance['date'])) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?= date('l', strtotime($attendance['date'])) ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php
                                    $statusMap = [
                                        'on_time' => ['Tepat Waktu', 'bg-green-100 text-green-800'],
                                        'late' => ['Terlambat', 'bg-yellow-100 text-yellow-800'],
                                        'izin' => ['Izin', 'bg-blue-100 text-blue-800'],
                                        'sakit' => ['Sakit', 'bg-orange-100 text-orange-800'],
                                        'alpha' => ['Alpha', 'bg-red-100 text-red-800'],
                                        'unknown' => ['Belum Diisi', 'bg-gray-100 text-gray-800'],
                                    ];
                                    $status = $attendance['masuk_status'] ?? 'unknown';
                                    [$label, $class] = $statusMap[$status] ?? ['Unknown', 'bg-gray-100 text-gray-800'];
                                    ?>
                                    <span class="inline-block <?= $class ?> px-3 py-1 rounded-full text-xs font-medium">
                                        <?= $label ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php if ($attendance['masuk_at']): ?>
                                        <?= date('H:i', strtotime($attendance['masuk_at'])) ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php if ($attendance['pulang_at']): ?>
                                        <?= date('H:i', strtotime($attendance['pulang_at'])) ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?= esc($attendance['note'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                Belum ada riwayat absensi
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>