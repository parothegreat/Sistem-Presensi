<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="mx-auto px-2 sm:px-4 py-4 max-w-full">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-calendar-alt text-indigo-600 text-2xl"></i>
            <h1 class="text-2xl font-bold text-slate-800"><?= $title ?></h1>
        </div>
        <p class="text-slate-600">Rekap kehadiran guru per tanggal</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800 flex items-center gap-2">
                <i class="fas fa-filter text-indigo-600"></i>
                Filter Rekap
            </h2>
            <div class="flex gap-2">
                <a href="<?= base_url('/admin/attendance/guru-riwayat') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm font-medium">
                    <i class="fas fa-list"></i>
                    Riwayat Detail
                </a>
                <a href="<?= base_url('/admin/attendance/export-guru-rekap?' . http_build_query([
                                'date_from' => $dateFrom,
                                'date_to' => $dateTo
                            ])) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                    <i class="fas fa-file-excel"></i>
                    Download Excel
                </a>
            </div>
        </div>
        <form method="get" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="<?= $dateFrom ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?= $dateTo ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                <thead class="bg-purple-600 text-white sticky top-0 z-10">
                    <tr>
                        <th class="px-2 py-3 text-left font-semibold border border-purple-700 sticky left-0 bg-purple-600 z-20" style="min-width: 40px;">#</th>
                        <th class="px-3 py-3 text-left font-semibold border border-purple-700 sticky left-[40px] bg-purple-600 z-20" style="min-width: 180px;">Nama Guru</th>
                        <th class="px-3 py-3 text-left font-semibold border border-purple-700 sticky left-[220px] bg-purple-600 z-20" style="min-width: 120px;">Username</th>
                        <?php foreach ($dates as $date): ?>
                            <th class="px-1 py-3 text-center font-semibold border border-purple-700" style="min-width: 65px;">
                                <div><?= date('d', strtotime($date)) ?></div>
                                <div class="text-xs font-normal"><?= date('D', strtotime($date)) ?></div>
                            </th>
                        <?php endforeach; ?>
                        <th class="px-3 py-3 text-center font-semibold border border-purple-700 sticky right-0 bg-purple-600 z-20" style="min-width: 140px;">Ringkasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="<?= 3 + count($dates) ?>" class="px-4 py-6 text-center text-slate-500">
                                <i class="fas fa-chalkboard-user text-4xl text-slate-300 mb-2"></i>
                                <p>Tidak ada data guru</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1;
                        foreach ($teachers as $teacher): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-2 py-3 text-slate-700 border sticky left-0 bg-white z-10"><?= $no++ ?></td>
                                <td class="px-3 py-3 text-slate-700 border sticky left-[40px] bg-white z-10 font-medium"><?= $teacher['full_name'] ?></td>
                                <td class="px-3 py-3 text-slate-700 border sticky left-[220px] bg-white z-10"><?= $teacher['username'] ?></td>
                                <?php
                                $hadir = 0;
                                $terlambat = 0;
                                $izin = 0;
                                $sakit = 0;
                                $alpha = 0;
                                foreach ($dates as $date):
                                    $att = $attendanceMap[$teacher['id']][$date] ?? null;
                                    $masuk_status = $att['masuk_status'] ?? null;
                                    $pulang_status = $att['pulang_status'] ?? null;

                                    // Helper function to get status display
                                    $getStatusDisplay = function ($status, $pulangStatus = null) {
                                        switch ($status) {
                                            case 'on_time':
                                                $masuk = 'H';
                                                $masukBg = 'bg-green-500';
                                                break;
                                            case 'late':
                                                $masuk = 'T';
                                                $masukBg = 'bg-orange-500';
                                                break;
                                            case 'izin':
                                                $masuk = 'I';
                                                $masukBg = 'bg-blue-500';
                                                break;
                                            case 'sakit':
                                                $masuk = 'S';
                                                $masukBg = 'bg-red-500';
                                                break;
                                            case 'alpha':
                                                $masuk = 'A';
                                                $masukBg = 'bg-purple-500';
                                                break;
                                            default:
                                                $masuk = '-';
                                                $masukBg = 'bg-slate-300';
                                        }

                                        switch ($pulangStatus) {
                                            case 'on_time':
                                                $pulang = 'P';
                                                $pulangBg = 'bg-green-500';
                                                break;
                                            case 'early':
                                                $pulang = 'E';
                                                $pulangBg = 'bg-cyan-500';
                                                break;
                                            case 'late':
                                                $pulang = 'L';
                                                $pulangBg = 'bg-orange-500';
                                                break;
                                            default:
                                                $pulang = '-';
                                                $pulangBg = 'bg-slate-300';
                                        }

                                        return [$masuk, $pulang, $masukBg, $pulangBg];
                                    };

                                    $display = $getStatusDisplay($masuk_status, $pulang_status);

                                    // Count for summary
                                    if ($masuk_status === 'on_time') $hadir++;
                                    elseif ($masuk_status === 'late') $terlambat++;
                                    elseif ($masuk_status === 'izin') $izin++;
                                    elseif ($masuk_status === 'sakit') $sakit++;
                                    elseif ($masuk_status === 'alpha') $alpha++;
                                ?>
                                    <td class="px-1 py-3 text-center border text-xs">
                                        <div class="flex gap-0.5 justify-center">
                                            <span class="w-5 h-5 rounded flex items-center justify-center text-white font-bold text-xs <?= $display[2] ?>"><?= $display[0] ?></span>
                                            <span class="w-5 h-5 rounded flex items-center justify-center text-white font-bold text-xs <?= $display[3] ?>"><?= $display[1] ?></span>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                                <td class="px-3 py-3 text-slate-700 border sticky right-0 bg-white z-10">
                                    <div class="text-center">
                                        <div class="text-xs font-semibold">
                                            <span class="inline-block px-2 py-1 rounded bg-green-100 text-green-700 mr-1">H:<?= $hadir ?></span>
                                            <span class="inline-block px-2 py-1 rounded bg-orange-100 text-orange-700 mr-1">T:<?= $terlambat ?></span>
                                            <span class="inline-block px-2 py-1 rounded bg-blue-100 text-blue-700 mr-1">I:<?= $izin ?></span>
                                            <span class="inline-block px-2 py-1 rounded bg-red-100 text-red-700 mr-1">S:<?= $sakit ?></span>
                                            <span class="inline-block px-2 py-1 rounded bg-purple-100 text-purple-700">A:<?= $alpha ?></span>
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
</div>
<?= $this->endSection() ?>