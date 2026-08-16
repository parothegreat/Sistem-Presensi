<?php $this->extend('layouts/admin'); ?>

<?php $this->section('content'); ?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Dashboard Admin</h1>
        <p class="text-slate-600">Ringkasan data presensi siswa dan guru</p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <a href="/scanner" class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg hover:shadow-xl transition-all group">
            <div class="p-3 bg-white/20 rounded-full mr-4">
                <i class="fas fa-qrcode text-2xl text-white"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-lg group-hover:underline">QR Scanner</h3>
                <p class="text-blue-100 text-sm">Buka pemindai QR Code untuk presensi</p>
            </div>
            <i class="fas fa-arrow-right text-white ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
        </a>
        
        <a href="/rfid-scanner" class="flex items-center p-4 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg hover:shadow-xl transition-all group">
            <div class="p-3 bg-white/20 rounded-full mr-4">
                <i class="fas fa-id-card text-2xl text-white"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-lg group-hover:underline">RFID Scanner</h3>
                <p class="text-purple-100 text-sm">Buka pemindai kartu RFID untuk presensi</p>
            </div>
            <i class="fas fa-arrow-right text-white ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
        </a>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
        <button onclick="switchTab('siswa')" id="tab-siswa" class="px-6 py-2 rounded-lg font-semibold bg-indigo-600 text-white flex items-center gap-2">
            <i class="fas fa-book"></i> Siswa
        </button>
        <button onclick="switchTab('guru')" id="tab-guru" class="px-6 py-2 rounded-lg font-semibold bg-slate-300 text-slate-700 hover:bg-slate-400 flex items-center gap-2">
            <i class="fas fa-chalkboard-user"></i> Guru
        </button>
    </div>

    <!-- SISWA TAB -->
    <div id="siswa-content">
        <!-- Stats Cards -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Total Siswa</div>
                <div class="mt-2 text-2xl font-bold text-indigo-600"><?= $stats['student']['total'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Absensi Hari Ini</div>
                <div class="mt-2 text-2xl font-bold text-blue-600"><?= $stats['student']['today'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Bulan Ini - Tepat Waktu</div>
                <div class="mt-2 text-2xl font-bold text-green-600"><?= $stats['student']['on_time_month'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Bulan Ini - Terlambat</div>
                <div class="mt-2 text-2xl font-bold text-yellow-600"><?= $stats['student']['late_month'] ?></div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid gap-6 sm:grid-cols-3 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Izin</div>
                <div class="mt-2 text-2xl font-bold text-blue-600"><?= $stats['student']['izin_month'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Sakit</div>
                <div class="mt-2 text-2xl font-bold text-orange-600"><?= $stats['student']['sakit_month'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Alpha</div>
                <div class="mt-2 text-2xl font-bold text-red-600"><?= $stats['student']['alpha_month'] ?></div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h3 class="text-lg font-semibold mb-4">Grafik Kehadiran Siswa - <?= date('F Y', strtotime($currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-01')) ?></h3>
            <div class="relative h-96">
                <canvas id="studentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- GURU TAB -->
    <div id="guru-content" class="hidden">
        <!-- Stats Cards -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Total Guru</div>
                <div class="mt-2 text-2xl font-bold text-purple-600"><?= $stats['guru']['total'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Absensi Hari Ini</div>
                <div class="mt-2 text-2xl font-bold text-blue-600"><?= $stats['guru']['today'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Bulan Ini - Tepat Waktu</div>
                <div class="mt-2 text-2xl font-bold text-green-600"><?= $stats['guru']['on_time_month'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Bulan Ini - Terlambat</div>
                <div class="mt-2 text-2xl font-bold text-yellow-600"><?= $stats['guru']['late_month'] ?></div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid gap-6 sm:grid-cols-3 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Izin</div>
                <div class="mt-2 text-2xl font-bold text-blue-600"><?= $stats['guru']['izin_month'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Sakit</div>
                <div class="mt-2 text-2xl font-bold text-orange-600"><?= $stats['guru']['sakit_month'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-slate-500">Alpha</div>
                <div class="mt-2 text-2xl font-bold text-red-600"><?= $stats['guru']['alpha_month'] ?></div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h3 class="text-lg font-semibold mb-4">Grafik Kehadiran Guru - <?= date('F Y', strtotime($currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-01')) ?></h3>
            <div class="relative h-96">
                <canvas id="guruChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function switchTab(tab) {
        // Hide all tabs
        document.getElementById('siswa-content').classList.add('hidden');
        document.getElementById('guru-content').classList.add('hidden');

        // Remove active styling
        document.getElementById('tab-siswa').classList.remove('bg-indigo-600', 'text-white');
        document.getElementById('tab-siswa').classList.add('bg-slate-300', 'text-slate-700', 'hover:bg-slate-400');
        document.getElementById('tab-guru').classList.remove('bg-indigo-600', 'text-white');
        document.getElementById('tab-guru').classList.add('bg-slate-300', 'text-slate-700', 'hover:bg-slate-400');

        // Show selected tab
        if (tab === 'siswa') {
            document.getElementById('siswa-content').classList.remove('hidden');
            document.getElementById('tab-siswa').classList.remove('bg-slate-300', 'text-slate-700', 'hover:bg-slate-400');
            document.getElementById('tab-siswa').classList.add('bg-indigo-600', 'text-white');

            // Draw student chart
            setTimeout(drawStudentChart, 100);
        } else {
            document.getElementById('guru-content').classList.remove('hidden');
            document.getElementById('tab-guru').classList.remove('bg-slate-300', 'text-slate-700', 'hover:bg-slate-400');
            document.getElementById('tab-guru').classList.add('bg-indigo-600', 'text-white');

            // Draw guru chart
            setTimeout(drawGuruChart, 100);
        }
    }

    function drawStudentChart() {
        const chartDates = <?= $studentDates ?>;
        const onTimeData = <?= $studentOnTime ?>;
        const lateData = <?= $studentLate ?>;
        const izinData = <?= $studentIzin ?>;
        const sakitData = <?= $studentSakit ?>;
        const alphaData = <?= $studentAlpha ?>;

        const canvasElement = document.getElementById('studentChart');
        if (!canvasElement) return;

        // Destroy existing chart if any
        if (window.studentChartInstance) {
            window.studentChartInstance.destroy();
        }

        const ctx = canvasElement.getContext('2d');
        window.studentChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartDates.map(date => {
                    const d = new Date(date + 'T00:00:00');
                    return d.toLocaleDateString('id-ID', {
                        weekday: 'short',
                        day: 'numeric',
                        month: 'short'
                    });
                }),
                datasets: [{
                        label: 'Tepat Waktu',
                        data: onTimeData,
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 1
                    },
                    {
                        label: 'Terlambat',
                        data: lateData,
                        backgroundColor: '#f59e0b',
                        borderColor: '#d97706',
                        borderWidth: 1
                    },
                    {
                        label: 'Izin',
                        data: izinData,
                        backgroundColor: '#3b82f6',
                        borderColor: '#1d4ed8',
                        borderWidth: 1
                    },
                    {
                        label: 'Sakit',
                        data: sakitData,
                        backgroundColor: '#ec4899',
                        borderColor: '#be185d',
                        borderWidth: 1
                    },
                    {
                        label: 'Alpha',
                        data: alphaData,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    function drawGuruChart() {
        const chartDates = <?= $guruDates ?>;
        const onTimeData = <?= $guruOnTime ?>;
        const lateData = <?= $guruLate ?>;
        const izinData = <?= $guruIzin ?>;
        const sakitData = <?= $guruSakit ?>;
        const alphaData = <?= $guruAlpha ?>;

        const canvasElement = document.getElementById('guruChart');
        if (!canvasElement) return;

        // Destroy existing chart if any
        if (window.guruChartInstance) {
            window.guruChartInstance.destroy();
        }

        const ctx = canvasElement.getContext('2d');
        window.guruChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartDates.map(date => {
                    const d = new Date(date + 'T00:00:00');
                    return d.toLocaleDateString('id-ID', {
                        weekday: 'short',
                        day: 'numeric',
                        month: 'short'
                    });
                }),
                datasets: [{
                        label: 'Tepat Waktu',
                        data: onTimeData,
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 1
                    },
                    {
                        label: 'Terlambat',
                        data: lateData,
                        backgroundColor: '#f59e0b',
                        borderColor: '#d97706',
                        borderWidth: 1
                    },
                    {
                        label: 'Izin',
                        data: izinData,
                        backgroundColor: '#3b82f6',
                        borderColor: '#1d4ed8',
                        borderWidth: 1
                    },
                    {
                        label: 'Sakit',
                        data: sakitData,
                        backgroundColor: '#ec4899',
                        borderColor: '#be185d',
                        borderWidth: 1
                    },
                    {
                        label: 'Alpha',
                        data: alphaData,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Draw student chart on page load
    document.addEventListener('DOMContentLoaded', function() {
        drawStudentChart();
    });
</script>
<?php $this->endSection(); ?>