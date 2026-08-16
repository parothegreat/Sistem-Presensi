<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="mx-auto px-2 sm:px-4 py-4 max-w-full">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-calendar-alt text-indigo-600 text-2xl"></i>
            <h1 class="text-2xl font-bold text-slate-800"><?= $title ?></h1>
        </div>
        <p class="text-slate-600">Rekap kehadiran siswa per tanggal</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800 flex items-center gap-2">
                <i class="fas fa-filter text-indigo-600"></i>
                Filter Rekap
            </h2>
            <div class="flex gap-2">
                <a href="<?= base_url('/admin/attendance/laporan') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm font-medium">
                    <i class="fas fa-list"></i>
                    Riwayat Detail
                </a>
                <a href="<?= base_url('/admin/attendance/export-excel-rekap?' . http_build_query([
                                'date_from' => $dateFrom,
                                'date_to' => $dateTo,
                                'class' => $class
                            ])) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                    <i class="fas fa-file-excel"></i>
                    Download Excel
                </a>
            </div>
        </div>
        <form method="get" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
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
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-700">
                <p class="font-semibold mb-1">Keterangan Status (Format: Masuk / Pulang):</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-2 text-xs">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span> H = Hadir Masuk
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span> P = Pulang Tepat
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-orange-500"></span> T = Terlambat
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-cyan-500"></span> E = Pulang Awal
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span> I = Izin
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span> S = Sakit
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-purple-500"></span> A = Alpha
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-pink-500"></span> TAM = Tanpa Absen Masuk
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span> TAP = Tanpa Absen Pulang
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-slate-300"></span> - = Belum
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm border-collapse">
                <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                    <tr>
                        <th class="px-2 py-3 text-left font-semibold border border-indigo-700 sticky left-0 bg-indigo-600 z-20" style="min-width: 40px;">#</th>
                        <th class="px-3 py-3 text-left font-semibold border border-indigo-700 sticky left-[40px] bg-indigo-600 z-20" style="min-width: 100px;">NIS</th>
                        <th class="px-3 py-3 text-left font-semibold border border-indigo-700 sticky left-[140px] bg-indigo-600 z-20" style="min-width: 180px;">Nama Siswa</th>
                        <th class="px-3 py-3 text-left font-semibold border border-indigo-700 sticky left-[320px] bg-indigo-600 z-20" style="min-width: 80px;">Kelas</th>
                        <?php foreach ($dates as $date): ?>
                            <th class="px-1 py-3 text-center font-semibold border border-indigo-700" style="min-width: 65px;">
                                <div><?= date('d', strtotime($date)) ?></div>
                                <div class="text-xs font-normal"><?= date('D', strtotime($date)) ?></div>
                            </th>
                        <?php endforeach; ?>
                        <th class="px-3 py-3 text-center font-semibold border border-indigo-700 sticky right-0 bg-indigo-600 z-20" style="min-width: 140px;">Ringkasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="<?= 4 + count($dates) ?>" class="px-4 py-6 text-center text-slate-500">
                                <i class="fas fa-users text-4xl text-slate-300 mb-2"></i>
                                <p>Tidak ada data siswa</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1;
                        foreach ($students as $student): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-2 py-3 text-slate-700 border sticky left-0 bg-white z-10"><?= $no++ ?></td>
                                <td class="px-3 py-3 text-slate-700 border sticky left-[40px] bg-white z-10"><?= $student['nis'] ?></td>
                                <td class="px-3 py-3 text-slate-700 border sticky left-[140px] bg-white z-10"><?= $student['full_name'] ?></td>
                                <td class="px-3 py-3 text-slate-700 border sticky left-[320px] bg-white z-10"><?= $student['class'] ?></td>
                                <?php foreach ($dates as $date): ?>
                                    <?php
                                    $att = $attendanceMap[$student['user_id']][$date] ?? null;
                                    $masuk_status = $att['masuk_status'] ?? null;
                                    $pulang_status = $att['pulang_status'] ?? null;

                                    // Helper function to get status display for masuk
                                    $getStatusDisplay = function ($status, $time = null) {
                                        switch ($status) {
                                            case 'on_time':
                                                return ['H', 'bg-green-500', 'text-white'];
                                            case 'late':
                                                return ['T', 'bg-orange-500', 'text-white'];
                                            case 'izin':
                                                return ['I', 'bg-blue-500', 'text-white'];
                                            case 'sakit':
                                                return ['S', 'bg-red-500', 'text-white'];
                                            case 'alpha':
                                                return ['A', 'bg-purple-500', 'text-white'];
                                            default:
                                                return ['-', 'bg-slate-100', 'text-slate-400'];
                                        }
                                    };

                                    // Helper function to get status display for pulang
                                    $getPulangDisplay = function ($status, $time = null) {
                                        switch ($status) {
                                            case 'on_time':
                                                return ['P', 'bg-green-500', 'text-white'];  // P = Pulang Tepat Waktu
                                            case 'early':
                                                return ['E', 'bg-cyan-500', 'text-white'];  // E = Pulang Lebih Awal
                                            case 'izin':
                                                return ['I', 'bg-blue-500', 'text-white'];
                                            case 'sakit':
                                                return ['S', 'bg-red-500', 'text-white'];
                                            case 'alpha':
                                                return ['A', 'bg-purple-500', 'text-white'];
                                            default:
                                                return ['-', 'bg-slate-100', 'text-slate-400'];
                                        }
                                    };

                                    // Determine display for masuk
                                    if (!$att || !$masuk_status) {
                                        $masuk_display = '-';
                                        $masuk_bgColor = 'bg-slate-100';
                                        $masuk_textColor = 'text-slate-400';
                                        $masuk_title = 'Belum masuk';
                                    } else {
                                        [$masuk_display, $masuk_bgColor, $masuk_textColor] = $getStatusDisplay($masuk_status);
                                        $masuk_title = $masuk_display . ' Masuk: ' . ($att['masuk_at'] ? date('H:i', strtotime($att['masuk_at'])) : '-');
                                    }

                                    // Determine display for pulang - check if pulang_at exists and status is not 'unknown'
                                    if (!$att || !$att['pulang_at'] || $pulang_status === 'unknown') {
                                        $pulang_display = '-';
                                        $pulang_bgColor = 'bg-slate-100';
                                        $pulang_textColor = 'text-slate-400';
                                        $pulang_title = 'Belum pulang';
                                    } else {
                                        [$pulang_display, $pulang_bgColor, $pulang_textColor] = $getPulangDisplay($pulang_status);
                                        $pulang_title = $pulang_display . ' Pulang: ' . ($att['pulang_at'] ? date('H:i', strtotime($att['pulang_at'])) : '-');
                                    }
                                    ?>
                                    <td class="px-1 py-2 text-center border">
                                        <div class="flex flex-col gap-1 items-center">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-sm <?= $masuk_bgColor ?> <?= $masuk_textColor ?> font-bold text-xs" title="<?= $masuk_title ?>">
                                                <?= $masuk_display ?>
                                            </span>
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-sm <?= $pulang_bgColor ?> <?= $pulang_textColor ?> font-bold text-xs" title="<?= $pulang_title ?>">
                                                <?= $pulang_display ?>
                                            </span>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                                <?php
                                // Calculate summary for this student
                                $hadir = 0;
                                $sakit = 0;
                                $izin = 0;
                                $alpha = 0;
                                $terlambat = 0;
                                $tanpa_absen_masuk = 0; // Check-out exists but check-in missing
                                $tanpa_absen_pulang = 0; // Check-in exists but check-out missing
                                $pulang_cepat = 0;
                                $hari_efektif = 0; // Count only days with attendance data

                                foreach ($dates as $date) {
                                    $att = $attendanceMap[$student['user_id']][$date] ?? null;
                                    if ($att) {
                                        $hari_efektif++;

                                        $hasMasuk = !empty($att['masuk_at']);
                                        $hasPulang = !empty($att['pulang_at']);

                                        // Count Late
                                        if (($att['masuk_status'] ?? '') === 'late') {
                                            $terlambat++;
                                        }

                                        // Count Early Leave
                                        if (($att['pulang_status'] ?? '') === 'early') {
                                            $pulang_cepat++;
                                        }

                                        // Count Missing Check-in/out
                                        if ($hasMasuk && !$hasPulang) {
                                            $tanpa_absen_pulang++;
                                        }
                                        if (!$hasMasuk && $hasPulang) {
                                            $tanpa_absen_masuk++;
                                        }

                                        switch ($att['masuk_status'] ?? '') {
                                            case 'on_time':
                                            case 'late':
                                                $hadir++;
                                                break;
                                            case 'sakit':
                                                $sakit++;
                                                break;
                                            case 'izin':
                                                $izin++;
                                                break;
                                            case 'alpha':
                                                $alpha++;
                                                break;
                                        }
                                    }
                                }
                                // Calculate percentage based on effective days only
                                $percentage = $hari_efektif > 0 ? round(($hadir / $hari_efektif) * 100) : 0;
                                ?>
                                <td class="px-2 py-2 text-center border sticky right-0 bg-white z-10 text-xs font-medium">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex flex-wrap justify-center gap-1.5 text-[0.65rem]">
                                            <span class="px-1 py-0.5 bg-green-100 text-green-700 rounded" title="Hadir">H:<?= $hadir ?></span>
                                            <span class="px-1 py-0.5 bg-orange-100 text-orange-700 rounded" title="Terlambat">T:<?= $terlambat ?></span>
                                            <span class="px-1 py-0.5 bg-cyan-100 text-cyan-700 rounded" title="Pulang Cepat">PC:<?= $pulang_cepat ?></span>
                                            <span class="px-1 py-0.5 bg-red-100 text-red-700 rounded" title="Sakit">S:<?= $sakit ?></span>
                                            <span class="px-1 py-0.5 bg-blue-100 text-blue-700 rounded" title="Izin">I:<?= $izin ?></span>
                                            <span class="px-1 py-0.5 bg-purple-100 text-purple-700 rounded" title="Alpha">A:<?= $alpha ?></span>
                                            <span class="px-1 py-0.5 bg-pink-100 text-pink-700 rounded" title="Tanpa Absen Masuk">TAM:<?= $tanpa_absen_masuk ?></span>
                                            <span class="px-1 py-0.5 bg-emerald-100 text-emerald-700 rounded" title="Tanpa Absen Pulang">TAP:<?= $tanpa_absen_pulang ?></span>
                                        </div>
                                        <div class="font-bold text-sm mt-0.5">
                                            <span class="<?= $percentage >= 80 ? 'text-green-600' : ($percentage >= 60 ? 'text-orange-600' : 'text-red-600') ?>">
                                                <?= $percentage ?>%
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary -->
    <?php if (!empty($students)): ?>
        <div class="mt-6 bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-slate-800 mb-3">Ringkasan</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-slate-600">Total Siswa:</span>
                    <span class="font-bold text-slate-800 ml-2"><?= count($students) ?></span>
                </div>
                <div>
                    <span class="text-slate-600">Periode:</span>
                    <span class="font-bold text-slate-800 ml-2"><?= count($dates) ?> hari</span>
                </div>
                <div>
                    <span class="text-slate-600">Dari:</span>
                    <span class="font-bold text-slate-800 ml-2"><?= date('d M Y', strtotime($dateFrom)) ?></span>
                </div>
                <div>
                    <span class="text-slate-600">Sampai:</span>
                    <span class="font-bold text-slate-800 ml-2"><?= date('d M Y', strtotime($dateTo)) ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Sticky columns for horizontal scroll */
    @media (max-width: 1024px) {
        table {
            font-size: 0.75rem;
        }
    }
</style>
<?= $this->endSection() ?>