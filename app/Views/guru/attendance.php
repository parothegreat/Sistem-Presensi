<?= $this->extend('layouts/guru') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto px-2 sm:px-4">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-6">Riwayat Absensi</h1>

    <!-- Filter & Header -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-800"><?= $monthName ?> <?= $year ?></h2>
            <div class="flex gap-2 flex-wrap">
                <a href="?month=<?= $month - 1 ?>&year=<?= $year ?>" class="px-2 sm:px-3 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs sm:text-sm flex-1 sm:flex-none text-center">← Sebelumnya</a>
                <a href="?month=<?= $month + 1 ?>&year=<?= $year ?>" class="px-2 sm:px-3 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs sm:text-sm flex-1 sm:flex-none text-center">Berikutnya →</a>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 sm:gap-3">
            <div class="p-2 sm:p-3 bg-green-50 rounded text-center">
                <p class="text-lg sm:text-xl font-bold text-green-600"><?= $summary['hadir'] ?></p>
                <p class="text-xs text-gray-600">Hadir</p>
            </div>
            <div class="p-2 sm:p-3 bg-yellow-50 rounded text-center">
                <p class="text-lg sm:text-xl font-bold text-yellow-600"><?= $summary['terlambat'] ?></p>
                <p class="text-xs text-gray-600">Terlambat</p>
            </div>
            <div class="p-2 sm:p-3 bg-blue-50 rounded text-center">
                <p class="text-lg sm:text-xl font-bold text-blue-600"><?= $summary['izin'] ?></p>
                <p class="text-xs text-gray-600">Izin</p>
            </div>
            <div class="p-2 sm:p-3 bg-orange-50 rounded text-center">
                <p class="text-lg sm:text-xl font-bold text-orange-600"><?= $summary['sakit'] ?></p>
                <p class="text-xs text-gray-600">Sakit</p>
            </div>
            <div class="p-2 sm:p-3 bg-red-50 rounded text-center">
                <p class="text-lg sm:text-xl font-bold text-red-600"><?= $summary['alpha'] ?></p>
                <p class="text-xs text-gray-600">Alpha</p>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-xs sm:text-sm">
            <thead class="bg-slate-100 border-b sticky top-0">
                <tr>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left font-semibold text-slate-700 whitespace-nowrap">Tanggal</th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left font-semibold text-slate-700 whitespace-nowrap">Hari</th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-center font-semibold text-slate-700 whitespace-nowrap">Status Masuk</th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-center font-semibold text-slate-700 whitespace-nowrap">Jam Masuk</th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-center font-semibold text-slate-700 whitespace-nowrap">Status Pulang</th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-center font-semibold text-slate-700 whitespace-nowrap">Jam Pulang</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (!empty($attendance)) : ?>
                    <?php foreach ($attendance as $att) :
                        $date = new DateTime($att['date']);
                        $dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$date->format('w')];

                        $masukStatusClass = '';
                        $masukStatusText = '';
                        switch ($att['masuk_status']) {
                            case 'on_time':
                                $masukStatusClass = 'bg-green-100 text-green-800';
                                $masukStatusText = 'Hadir';
                                break;
                            case 'late':
                                $masukStatusClass = 'bg-yellow-100 text-yellow-800';
                                $masukStatusText = 'Terlambat';
                                break;
                            case 'alpha':
                                $masukStatusClass = 'bg-red-100 text-red-800';
                                $masukStatusText = 'Alpha';
                                break;
                            default:
                                $masukStatusClass = 'bg-gray-100 text-gray-800';
                                $masukStatusText = 'Unknown';
                        }

                        $pulangStatusClass = '';
                        $pulangStatusText = '';
                        if (!empty($att['pulang_status'])) {
                            switch ($att['pulang_status']) {
                                case 'on_time':
                                    $pulangStatusClass = 'bg-green-100 text-green-800';
                                    $pulangStatusText = 'Tepat';
                                    break;
                                case 'early':
                                    $pulangStatusClass = 'bg-blue-100 text-blue-800';
                                    $pulangStatusText = 'Awal';
                                    break;
                                default:
                                    $pulangStatusClass = 'bg-gray-100 text-gray-800';
                                    $pulangStatusText = 'Unknown';
                            }
                        } else {
                            $pulangStatusClass = 'bg-gray-100 text-gray-800';
                            $pulangStatusText = '-';
                        }
                    ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 sm:px-6 py-2 sm:py-4 text-slate-800 font-medium whitespace-nowrap"><?= $date->format('d/m/Y') ?></td>
                            <td class="px-3 sm:px-6 py-2 sm:py-4 text-slate-600 whitespace-nowrap"><?= $dayName ?></td>
                            <td class="px-3 sm:px-6 py-2 sm:py-4 text-center">
                                <span class="inline-block px-2 sm:px-3 py-1 rounded-full text-xs font-medium <?= $masukStatusClass ?>">
                                    <?= $masukStatusText ?>
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-2 sm:py-4 text-slate-800 text-center font-semibold whitespace-nowrap"><?= $att['masuk_at'] ? date('H:i', strtotime($att['masuk_at'])) : '-' ?></td>
                            <td class="px-3 sm:px-6 py-2 sm:py-4 text-center">
                                <span class="inline-block px-2 sm:px-3 py-1 rounded-full text-xs font-medium <?= $pulangStatusClass ?>">
                                    <?= $pulangStatusText ?>
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-2 sm:py-4 text-slate-800 text-center font-semibold whitespace-nowrap"><?= $att['pulang_at'] ? date('H:i', strtotime($att['pulang_at'])) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            <i class="fas fa-inbox mr-2"></i>Tidak ada data absensi untuk bulan ini
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    function goToDate() {
        const selectedDate = document.getElementById('selectedDate').value;
        if (selectedDate) {
            // Update input link to include date parameter
            const inputLink = document.getElementById('inputLink');
            inputLink.href = '/guru/attendance/input-daily?date=' + selectedDate;

            // Optionally navigate to daily attendance view
            // window.location.href = '/guru/attendance/daily?date=' + selectedDate;
        }
    }

    // Initialize date input with today's date
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('selectedDate').value = today;
    });
</script>

<?= $this->endSection() ?>