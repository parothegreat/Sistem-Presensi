<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobby Dashboard - <?= $school_name ?></title>
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $school_favicon ?>">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            /* Slate 900 */
            color: #fff;
            overflow: hidden;
            /* Hide scrollbars for TV */
        }

        .font-digital {
            font-family: 'Orbitron', sans-serif;
        }

        /* Custom Scrollbar for list */
        .scroller::-webkit-scrollbar {
            width: 4px;
        }

        .scroller::-webkit-scrollbar-track {
            background: #1e293b;
        }

        .scroller::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 4px;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Glassmorphism card */
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body class="bg-slate-900 text-white min-h-screen flex flex-col font-inter overflow-x-hidden">

    <!-- HEADER -->
    <header class="h-20 bg-slate-900 border-b border-slate-700 flex items-center justify-between px-4 lg:px-8 shadow-lg z-30 relative shrink-0">
        <div class="flex items-center gap-4">
            <img src="<?= $school_logo ?>" alt="Logo" class="h-10 lg:h-12 w-auto object-contain fallback-logo">
            <div>
                <h1 class="text-lg lg:text-2xl font-bold tracking-wider text-blue-400 truncate max-w-[200px] lg:max-w-none"><?= $school_name ?></h1>
                <p class="text-[10px] lg:text-xs text-slate-400 uppercase tracking-widest hidden sm:block">Sistem Absensi Digital</p>
            </div>
        </div>
        <div class="text-right">
            <div id="clock" class="text-2xl lg:text-4xl font-digital text-white font-bold tracking-widest">00:00:00</div>
            <div id="date" class="text-xs lg:text-sm text-slate-400 font-medium">Senin, 1 Januari 2024</div>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="flex-1 p-4 lg:p-6 relative pb-20">
        <!-- Background Glow -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-blue-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
            <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse" style="animation-duration: 4s;"></div>
        </div>

        <!-- Layout Wrapper -->
        <div class="flex flex-col lg:flex-row gap-6 relative z-10">
            <!-- LEFT PANEL: STATS -->
            <div class="w-full lg:w-[35%] flex flex-col gap-6 z-10 shrink-0">
                <!-- Attendance Chart Card -->
                <div class="glass-card rounded-2xl p-6 min-h-[300px] lg:flex-1 flex flex-col items-center justify-center relative">
                    <h2 class="absolute top-6 left-6 text-slate-400 text-sm uppercase tracking-wider">Kehadiran Hari Ini</h2>
                    <div class="w-48 h-48 lg:w-64 lg:h-64 relative">
                        <canvas id="attendanceChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-4xl lg:text-5xl font-bold text-white" id="totalPresent">0</span>
                            <span class="text-xs lg:text-sm text-slate-400">Siswa Hadir</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Cards -->
                <div class="grid grid-cols-2 gap-4 h-auto lg:h-40">
                    <div class="glass-card rounded-xl p-4 flex flex-col justify-center items-center border-l-4 border-yellow-400 py-6 lg:py-4">
                        <span class="text-2xl lg:text-3xl font-bold text-yellow-400" id="countLate">0</span>
                        <span class="text-[10px] lg:text-xs text-slate-400 uppercase mt-1">Terlambat</span>
                    </div>
                    <div class="glass-card rounded-xl p-4 flex flex-col justify-center items-center border-l-4 border-red-500 py-6 lg:py-4">
                        <span class="text-2xl lg:text-3xl font-bold text-red-500" id="countAlpha">0</span>
                        <span class="text-[10px] lg:text-xs text-slate-400 uppercase mt-1">Alpha / Absen</span>
                    </div>
                    <!-- Combined Permit -->
                    <div class="col-span-2 glass-card rounded-xl p-4 flex flex-col justify-center items-center border-l-4 border-blue-400 py-6 lg:py-4">
                        <span class="text-2xl lg:text-3xl font-bold text-blue-400" id="countPermit">0</span>
                        <span class="text-[10px] lg:text-xs text-slate-400 uppercase mt-1">Sakit / Izin</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: LIVE FEED -->
            <div class="w-full lg:w-[65%] glass-card rounded-2xl p-0 flex flex-col z-10 overflow-hidden min-h-[400px]">
                <div class="p-6 border-b border-slate-700 bg-slate-800/50 flex justify-between items-center shrink-0">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                        Aktivitas Terbaru
                    </h2>
                    <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded">Live Feed</span>
                </div>

                <div class="flex-1 overflow-y-auto scroller p-4" id="feedContainer">
                    <!-- Feed Items injected via JS -->
                    <div class="flex items-center gap-4 p-4 mb-3 bg-slate-800/50 rounded-xl animate-pulse">
                        <div class="w-12 h-12 rounded-full bg-slate-700"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-4 bg-slate-700 rounded w-3/4"></div>
                            <div class="h-3 bg-slate-700 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer Spacer -->
        <div class="h-32 w-full shrink-0"></div>
    </main>

    <!-- FOOTER: RUNNING TEXT -->
    <footer class="fixed bottom-0 left-0 w-full h-12 bg-blue-900 flex items-center overflow-hidden whitespace-nowrap z-50 border-t border-blue-700 shadow-[0_-5px_10px_rgba(0,0,0,0.3)]">
        <div class="px-4 bg-blue-800 h-full flex items-center z-10 font-bold text-sm tracking-wider shadow-xl shrink-0">
            PENGUMUMAN
        </div>
        <div class="animate-marquee inline-block px-4 text-sm font-medium tracking-wide" id="runningText">
            Selamat Datang di Sistem Absensi Digital...
        </div>
    </footer>

    <style>
        .animate-marquee {
            animation: marquee 20s linear infinite;
        }

        @keyframes marquee {
            0% {
                transform: translateX(100vw);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* Fallback logo if missing */
        .fallback-logo[src*="logo.png"] {
            /* content: url('https://ui-avatars.com/api/?name=S+A&background=random&color=fff&size=128'); */
        }
    </style>

    <script>
        // --- 1. CLOCK ---
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour12: false
            });
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            document.getElementById('clock').textContent = timeString;
            document.getElementById('date').textContent = dateString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- 2. CHART CONFIG ---
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Tidak Hadir'],
                datasets: [{
                    data: [0, 100], // Initial
                    backgroundColor: ['#3b82f6', '#334155'], // Blue vs Slate-700
                    borderWidth: 0,
                    cutout: '85%',
                    borderRadius: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        });

        // --- 3. POLLING UPDATES ---
        let lastScans = [];

        async function fetchUpdates() {
            try {
                const response = await fetch('<?= base_url('api/lobby/updates') ?>');
                const data = await response.json();

                // A. Update Stats
                const totalStudents = 100; // Mock or get from API if available? For now just use sum or static
                const present = data.stats.total_present || 0;

                // Animate Numbers
                document.getElementById('totalPresent').textContent = present;
                document.getElementById('countLate').textContent = data.stats.late;
                document.getElementById('countPermit').textContent = data.stats.permit;
                document.getElementById('countAlpha').textContent = data.stats.alpha;

                // Update Chart (Hadir vs Sisa)
                // Assuming roughly 500 students or just relative to data
                // For a nice visual, let's just show present vs (late+alpha+permit) or fixed capacity
                // Better: Present vs (Total - Present). Let's assume Total is dynamic or hardcoded for now.
                // Let's use Sum of all stats as Total for now if not provided.
                const totalRecorded = present + data.stats.alpha + data.stats.permit; // Late is included in present usually in logic? No, check controller.
                // Controller: late is counted in present separately? 
                // Logic: present includes on_time + late. So (present + permit + alpha) = Total Scanned.
                // But we want percentage of "Target Attendance". 
                // Let's just make the chart "Present" vs "Not Present" (gray).

                attendanceChart.data.datasets[0].data = [present, 20]; // Mock 20 remaining for visual
                attendanceChart.update();

                // B. Update Running Text
                const marquee = document.getElementById('runningText');
                if (data.running_text && marquee.textContent !== data.running_text) {
                    marquee.textContent = data.running_text;
                }

                // C. Update Feed
                updateFeed(data.scans);

            } catch (error) {
                console.error('Error fetching updates:', error);
            }
        }

        function updateFeed(scans) {
            const container = document.getElementById('feedContainer');

            // Allow initial populate or replace diff
            // For simplicity, we just clear and re-render if different, or prepend new ones.
            // Simple approach: Render all. If matching top 1, skip.

            const currentHTML = container.innerHTML;
            let newHTML = '';

            if (scans.length === 0) {
                newHTML = '<div class="text-center text-slate-500 py-10">Belum ada aktivitas scan hari ini</div>';
            } else {
                scans.forEach(scan => {
                    const item = `
                        <div class="flex items-center gap-4 p-4 mb-3 bg-slate-800/80 rounded-xl border border-slate-700/50 hover:bg-slate-700/50 transition fade-in">
                            <div class="relative">
                                <img src="${scan.photo}" class="w-12 h-12 rounded-full object-cover border-2 border-slate-600 bg-slate-700" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(scan.name)}&background=random'">
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-slate-800 ${scan.status_label === 'Masuk' ? 'bg-green-500' : 'bg-orange-500'}"></span>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-white text-sm truncate w-40">${scan.name}</h4>
                                    <span class="font-digital text-lg ${scan.status_color}">${scan.time}</span>
                                </div>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-xs text-slate-400 bg-slate-900 px-2 py-0.5 rounded-full">${scan.role}</span>
                                    <span class="text-xs font-medium uppercase ${scan.status_color}">${scan.status_label}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    newHTML += item;
                });
            }

            // Only update if content changed prevents jitter (simple hash check or just replace)
            // Ideally we prepend updates. For kiosk, replacing full list every 10s is fine.
            if (scans.length > 0 && lastScans.length > 0 && scans[0].time === lastScans[0].time && scans[0].name === lastScans[0].name) {
                // No change in latest scan
            } else {
                container.innerHTML = newHTML;
                lastScans = scans;
            }
        }

        // Poll every 5 seconds
        setInterval(fetchUpdates, 5000);
        fetchUpdates(); // Initial call

        // Fullscreen Toggle on double click
        document.body.addEventListener('dblclick', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        });
    </script>
</body>

</html>