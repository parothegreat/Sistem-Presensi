<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">Dashboard Siswa</h1>



    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Kelas -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Kelas</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?= esc($classInfo['class_name'] ?? 'Belum Ditentukan') ?></h3>
                </div>
                <i class="fas fa-school text-indigo-500 text-5xl opacity-50"></i>
            </div>
        </div>

        <!-- Kehadiran Hari Ini -->
        <div class="bg-white rounded-lg shadow p-6">
            <?php
            $status = $todayAttendance['masuk_status'] ?? 'unknown';
            $time = $todayAttendance['masuk_at'] ?? null;
            $statusMap = [
                'on_time' => ['Hadir', 'bg-green-100 text-green-800'],
                'late' => ['Terlambat', 'bg-yellow-100 text-yellow-800'],
                'izin' => ['Izin', 'bg-blue-100 text-blue-800'],
                'sakit' => ['Sakit', 'bg-orange-100 text-orange-800'],
                'alpha' => ['Alpha', 'bg-red-100 text-red-800'],
                'unknown' => ['Belum Diisi', 'bg-gray-100 text-gray-800'],
            ];
            [$label, $badgeClass] = $statusMap[$status] ?? ['Unknown', 'bg-gray-100 text-gray-800'];
            ?>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Status Hari Ini</p>
                    <div class="mt-2">
                        <span class="inline-block <?= $badgeClass ?> px-3 py-1 rounded-full text-sm font-medium"><?= $label ?></span>
                    </div>
                    <?php if ($time): ?>
                        <p class="text-sm text-slate-500 mt-2">Waktu: <?= date('H:i', strtotime($time)) ?></p>
                    <?php endif; ?>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>

            <div class="mt-4 flex gap-3">
                <a href="/siswa/attendance" class="bg-indigo-600 text-white px-2 py-2 rounded hover:bg-indigo-700 text-xs text-center flex-1">Riwayat Absensi</a>
                <a href="/siswa/profile" class="bg-slate-100 text-slate-800 px-2 py-2 rounded hover:bg-slate-200 text-xs text-center flex-1">Profil Saya</a>
            </div>
        </div>

        <!-- Informasi (New 3rd Card) -->
        <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col h-full">
             <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-indigo-50/50">
                <h2 class="text-base font-bold text-slate-800 flex items-center">
                    <i class="fas fa-bullhorn mr-2 text-indigo-600"></i>
                    Pengumuman
                </h2>
                <span class="text-xs font-medium bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full"><?= !empty($informations) ? count($informations) : 0 ?></span>
            </div>
            
            <div class="flex-1 overflow-y-auto max-h-60 p-0">
                <?php if (!empty($informations)): ?>
                    <div class="divide-y divide-slate-100">
                        <?php foreach ($informations as $index => $info): ?>
                            <details class="group" <?= $index === 0 ? 'open' : '' ?>>
                                <summary class="flex justify-between items-start p-3 cursor-pointer hover:bg-slate-50 transition-colors list-none">
                                    <div class="flex items-start gap-2">
                                        <div class="text-indigo-600 mt-1 transition-transform group-open:rotate-90">
                                            <i class="fas fa-chevron-right text-[10px]"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-sm text-slate-700 group-open:text-indigo-700 block line-clamp-1"><?= esc($info['title']) ?></span>
                                            <span class="text-[10px] text-slate-400 block mt-0.5"><?= date('d M', strtotime($info['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </summary>
                                <div class="px-3 pb-3 pl-7">
                                    <div class="text-xs text-slate-600 bg-slate-50 p-2 rounded border border-slate-100 whitespace-pre-line">
                                        <?php 
                                            $content = $info['content'];
                                            $content = str_replace('{nama}', esc($student['full_name']), $content);
                                            $content = str_replace('{kelas}', esc($classInfo['class_name'] ?? '-'), $content);
                                            echo esc($content); 
                                        ?>
                                    </div>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-slate-500 text-sm">
                        Tidak ada pengumuman terbaru
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ringkasan Absensi Bulan Ini -->
    <div class="mb-8">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Ringkasan Bulan: <?= date('F Y', strtotime($month . '-01')) ?></h2>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <!-- Total Masuk -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Total Masuk</p>
                        <p class="text-3xl font-bold text-green-600 mt-1"><?= ($monthlyStats['total_masuk'] ?? 0) + ($monthlyStats['total_terlambat'] ?? 0) ?></p>
                        <p class="text-xs text-slate-400 mt-1"><?= $monthlyStats['total_masuk'] ?? 0 ?> tepat waktu</p>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-3xl opacity-50"></i>
                </div>
            </div>

            <!-- Terlambat -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Terlambat</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-1"><?= $monthlyStats['total_terlambat'] ?? 0 ?></p>
                    </div>
                    <i class="fas fa-clock text-yellow-500 text-3xl opacity-50"></i>
                </div>
            </div>

            <!-- Izin -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Izin</p>
                        <p class="text-3xl font-bold text-blue-600 mt-1"><?= $monthlyStats['total_izin'] ?? 0 ?></p>
                    </div>
                    <i class="fas fa-file-alt text-blue-500 text-3xl opacity-50"></i>
                </div>
            </div>

            <!-- Sakit -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Sakit</p>
                        <p class="text-3xl font-bold text-orange-600 mt-1"><?= $monthlyStats['total_sakit'] ?? 0 ?></p>
                    </div>
                    <i class="fas fa-hospital text-orange-500 text-3xl opacity-50"></i>
                </div>
            </div>

            <!-- Alpha -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Alpha</p>
                        <p class="text-3xl font-bold text-red-600 mt-1"><?= $monthlyStats['total_alpha'] ?? 0 ?></p>
                    </div>
                    <i class="fas fa-ban text-red-500 text-3xl opacity-50"></i>
                </div>
            </div>

            <!-- Total Pulang -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-cyan-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Total Pulang</p>
                        <p class="text-3xl font-bold text-cyan-600 mt-1"><?= $monthlyStats['total_pulang'] ?? 0 ?></p>
                    </div>
                    <i class="fas fa-sign-out-alt text-cyan-500 text-3xl opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Absensi Bulan Ini / Pilih Bulan -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">Riwayat Absensi - <?= date('F Y', strtotime($month . '-01')) ?></h2>
            <div class="flex items-center gap-2">
                <a href="?month=<?= $prevMonth ?>" class="px-3 py-1 bg-slate-100 rounded hover:bg-slate-200">&larr; <?= date('M Y', strtotime($prevMonth . '-01')) ?></a>
                <a href="?month=<?= $nextMonth ?>" class="px-3 py-1 bg-slate-100 rounded hover:bg-slate-200"><?= date('M Y', strtotime($nextMonth . '-01')) ?> &rarr;</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Tanggal</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Waktu Masuk</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Waktu Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (! empty($attendanceHistory)): ?>
                        <?php foreach ($attendanceHistory as $att): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-600"><?= date('d M Y', strtotime($att['date'])) ?></td>
                                <?php
                                $map = [
                                    'on_time' => ['Hadir', 'bg-green-100 text-green-800'],
                                    'late' => ['Terlambat', 'bg-yellow-100 text-yellow-800'],
                                    'izin' => ['Izin', 'bg-blue-100 text-blue-800'],
                                    'sakit' => ['Sakit', 'bg-orange-100 text-orange-800'],
                                    'alpha' => ['Alpha', 'bg-red-100 text-red-800'],
                                ];
                                [$lbl, $cls] = $map[$att['masuk_status']] ?? ['Belum Diisi', 'bg-gray-100 text-gray-800'];
                                ?>
                                <td class="px-6 py-4 text-sm"><span class="inline-block <?= $cls ?> px-3 py-1 rounded-full text-sm font-medium"><?= $lbl ?></span></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?= $att['masuk_at'] ? date('H:i', strtotime($att['masuk_at'])) : '-' ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?= $att['pulang_at'] ? date('H:i', strtotime($att['pulang_at'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-slate-500">Belum ada data absensi untuk bulan ini</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Notifications removed - focusing on attendance -->
</div>
<?= $this->endSection() ?>