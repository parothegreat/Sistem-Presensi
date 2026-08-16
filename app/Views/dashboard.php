<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Presensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white bg-opacity-10 backdrop-blur-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-3">
                        <div class="bg-white rounded-lg p-2">
                            <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-white">Sistem Presensi</h1>
                    </div>
                    <div class="text-white text-sm">
                        <div id="currentTime" class="font-semibold">--:--:--</div>
                        <div id="currentDate" class="text-xs opacity-75">--</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
            <!-- Welcome Section -->
            <div class="mb-12 text-center">
                <h2 class="text-4xl font-bold text-white mb-2">Selamat Datang</h2>
                <p class="text-lg text-white text-opacity-80">Pilih metode absensi yang ingin Anda gunakan</p>
            </div>

            <!-- Feature Cards Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                <!-- QR Code Scanner Card -->
                <a href="<?= base_url('/scanner') ?>" class="group">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 hover:scale-105 h-full">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 h-32 flex items-center justify-center">
                            <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-qrcode mr-2"></i>QR Code Scanner
                            </h3>
                            <p class="text-gray-600 mb-4">Scan QR code untuk melakukan absensi masuk dan pulang</p>
                            <div class="flex items-center text-blue-600 group-hover:text-blue-700 font-semibold">
                                <span>Mulai Scan</span>
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- RFID Scanner Card -->
                <a href="<?= base_url('/rfid-scanner') ?>" class="group">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 hover:scale-105 h-full">
                        <div class="bg-gradient-to-br from-purple-500 to-purple-600 h-32 flex items-center justify-center">
                            <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-id-card mr-2"></i>RFID Scanner
                            </h3>
                            <p class="text-gray-600 mb-4">Tap kartu RFID USB reader untuk absensi otomatis</p>
                            <div class="flex items-center text-purple-600 group-hover:text-purple-700 font-semibold">
                                <span>Mulai Scan</span>
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Manual Entry Card -->
                <a href="<?= base_url('/attendance/manual') ?>" class="group">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 hover:scale-105 h-full">
                        <div class="bg-gradient-to-br from-green-500 to-green-600 h-32 flex items-center justify-center">
                            <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-keyboard mr-2"></i>Input Manual
                            </h3>
                            <p class="text-gray-600 mb-4">Masukkan NIS/NIP secara manual untuk absensi</p>
                            <div class="flex items-center text-green-600 group-hover:text-green-700 font-semibold">
                                <span>Input Data</span>
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Features Section -->
            <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-2xl p-8 mb-12 border border-white border-opacity-20">
                <h3 class="text-3xl font-bold text-white mb-8">Fitur Unggulan</h3>
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Feature 1 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-white mb-2">Notifikasi Real-time</h4>
                            <p class="text-white text-opacity-80">Penerima notifikasi otomatis via Telegram, WhatsApp, dan aplikasi mobile untuk setiap absensi</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                            <i class="fas fa-smartphone"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-white mb-2">Mobile App Integration</h4>
                            <p class="text-white text-opacity-80">Push notification ke aplikasi mobile Android untuk notifikasi real-time</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-white mb-2">Validasi Mode</h4>
                            <p class="text-white text-opacity-80">Sistem otomatis memastikan check-in sebelum check-out, mencegah duplikasi absensi</p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-white mb-2">Dual User Support</h4>
                            <p class="text-white text-opacity-80">Mendukung siswa (NIS) dan guru (NIP) dengan logika jadwal yang berbeda</p>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                            <i class="fas fa-sound"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-white mb-2">Audio Feedback</h4>
                            <p class="text-white text-opacity-80">Suara beep untuk error dan thank-you untuk sukses membantu pengguna</p>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-md bg-pink-500 text-white">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-white mb-2">Status Otomatis</h4>
                            <p class="text-white text-opacity-80">Sistem secara otomatis menentukan status (Tepat Waktu, Terlambat, Lebih Awal)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin & Teacher Options -->
            <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-2xl p-8 border border-white border-opacity-20">
                <h3 class="text-3xl font-bold text-white mb-8">Akses Lainnya</h3>
                <div class="grid md:grid-cols-3 gap-6">
                    <a href="<?= base_url('/attendance') ?>" class="bg-white bg-opacity-5 hover:bg-opacity-10 rounded-lg p-6 transition border border-white border-opacity-10 hover:border-opacity-30">
                        <div class="flex items-center space-x-3 text-white mb-3">
                            <i class="fas fa-list-check text-2xl"></i>
                            <h4 class="text-lg font-semibold">Daftar Presensi</h4>
                        </div>
                        <p class="text-white text-opacity-70 text-sm">Lihat daftar presensi harian</p>
                    </a>

                    <a href="<?= base_url('/admin') ?>" class="bg-white bg-opacity-5 hover:bg-opacity-10 rounded-lg p-6 transition border border-white border-opacity-10 hover:border-opacity-30">
                        <div class="flex items-center space-x-3 text-white mb-3">
                            <i class="fas fa-cog text-2xl"></i>
                            <h4 class="text-lg font-semibold">Panel Admin</h4>
                        </div>
                        <p class="text-white text-opacity-70 text-sm">Kelola data siswa, guru, dan pengaturan</p>
                    </a>

                    <a href="<?= base_url('/profile') ?>" class="bg-white bg-opacity-5 hover:bg-opacity-10 rounded-lg p-6 transition border border-white border-opacity-10 hover:border-opacity-30">
                        <div class="flex items-center space-x-3 text-white mb-3">
                            <i class="fas fa-user text-2xl"></i>
                            <h4 class="text-lg font-semibold">Profil Saya</h4>
                        </div>
                        <p class="text-white text-opacity-70 text-sm">Lihat dan ubah profil pengguna</p>
                    </a>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-black bg-opacity-20 backdrop-blur-md mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
                <p class="text-white text-opacity-70 text-sm">
                    © <?= date('Y') ?> Sistem Presensi. Semua hak cipta dilindungi.
                </p>
                <p class="text-white text-opacity-50 text-xs mt-2">
                    Build with <i class="fas fa-heart text-red-500"></i> using CodeIgniter 4
                </p>
            </div>
        </footer>
    </div>

    <script>
        // Update clock and date
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const dateStr = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            document.getElementById('currentTime').textContent = timeStr;
            document.getElementById('currentDate').textContent = dateStr;
        }

        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>

</html>