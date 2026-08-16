<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner QR Code - Presensi Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <link rel="icon" href="<?= base_url('dukunweb.png') ?>">
</head>

<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 min-h-screen p-4">
    <div class="max-w-6xl mx-auto">
        <!-- Digital Clock Header -->
        <div class="text-center text-white mb-8">
            <div class="text-5xl font-bold mb-2" id="digitalClock">00:00:00</div>
            <div class="text-2xl" id="digitalDate">Senin, 1 Januari 2025</div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-4 gap-6">
            <!-- Scanner Section (50%) -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                    <!-- Camera Feed with Loading Overlay -->
                    <div class="bg-gray-900 aspect-video flex items-center justify-center overflow-hidden relative">
                        <video id="cameraFeed" class="w-full h-full object-cover" playsinline autoplay muted></video>
                        <!-- Loading/Result Overlay -->
                        <div id="loadingOverlay"
                            class="hidden absolute inset-0 bg-black bg-opacity-70 flex flex-col items-center justify-center z-50">
                            <div id="overlayContent"
                                class="bg-white rounded-xl shadow-2xl px-8 py-6 max-w-sm w-full mx-4">
                                <div class="text-center">
                                    <div id="overlayIcon" class="mb-4">
                                        <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>
                                    <p id="overlayText" class="text-gray-800 text-lg font-semibold">Memproses...</p>
                                    <p id="overlayDetail" class="text-gray-600 text-sm mt-2" style="display: none;"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <canvas id="canvas" style="display: none;"></canvas>

                    <!-- Camera Controls -->
                    <div class="p-6 bg-white border-t">
                        <div class="flex gap-3 mb-6">
                            <button type="button" id="startBtn" onclick="startScanning()"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Mulai Scan
                            </button>
                            <button type="button" id="stopBtn" onclick="stopScanning()"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition hidden flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                        clip-rule="evenodd" />
                                </svg>
                                Hentikan
                            </button>
                            <button type="button" id="switchCamBtn" onclick="switchCamera()"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2"
                                title="Ganti Kamera">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v3.25a1 1 0 11-2 0V13.899a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <!-- Manual Input -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Atau Input NIS Manual</label>
                            <div class="flex gap-2">
                                <input type="text" id="nisInput" placeholder="Masukkan NIS siswa..."
                                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                                <button type="button" onclick="submitNis()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                                    Kirim
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Right) - Two Columns for Student and Teacher Attendance -->
            <div class="lg:col-span-2 flex flex-col h-full">
                <!-- Attendance Mode Toggle -->
                <div class="bg-white rounded-xl shadow-2xl overflow-hidden mb-6">
                    <div class="flex">
                        <button type="button" onclick="switchMode('masuk')" id="modeBtn-masuk"
                            class="flex-1 bg-green-600 text-white font-bold py-3 rounded-l-lg transition hover:bg-green-700">
                            Masuk
                        </button>
                        <button type="button" onclick="switchMode('pulang')" id="modeBtn-pulang"
                            class="flex-1 bg-gray-300 text-gray-700 font-bold py-3 rounded-r-lg transition hover:bg-gray-400">
                            Pulang
                        </button>
                    </div>
                </div>

                <!-- Two Column Attendance Display -->
                <div class="grid grid-cols-2 gap-4 flex-1 overflow-hidden">
                    <!-- Student Attendance -->
                    <div class="bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                            <h3 class="text-white font-bold text-sm" id="studentTitle">Siswa Masuk</h3>
                        </div>
                        <div class="p-3 flex-1 overflow-hidden">
                            <div id="studentList" class="space-y-1 h-full overflow-y-auto">
                                <div class="text-xs text-gray-500 text-center py-4">Belum ada</div>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Attendance -->
                    <div class="bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-3">
                            <h3 class="text-white font-bold text-sm" id="teacherTitle">Guru Masuk</h3>
                        </div>
                        <div class="p-3 flex-1 overflow-hidden">
                            <div id="teacherList" class="space-y-1 h-full overflow-y-auto">
                                <div class="text-xs text-gray-500 text-center py-4">Belum ada</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let scanning = false;
        let stream = null;
        let currentMode = 'masuk'; // masuk or pulang
        let currentFacingMode = localStorage.getItem('cameraFacingMode') || 'environment'; // default to rear camera
        const nisInput = document.getElementById('nisInput');

        // Function to switch between masuk and pulang mode
        function switchMode(mode) {
            currentMode = mode;
            console.log('Switched to mode:', mode);

            // Update button styles
            document.getElementById('modeBtn-masuk').classList.toggle('bg-green-600', mode === 'masuk');
            document.getElementById('modeBtn-masuk').classList.toggle('hover:bg-green-700', mode === 'masuk');
            document.getElementById('modeBtn-masuk').classList.toggle('bg-gray-300', mode === 'pulang');
            document.getElementById('modeBtn-masuk').classList.toggle('text-gray-700', mode === 'pulang');
            document.getElementById('modeBtn-masuk').classList.toggle('text-white', mode === 'masuk');

            document.getElementById('modeBtn-pulang').classList.toggle('bg-orange-600', mode === 'pulang');
            document.getElementById('modeBtn-pulang').classList.toggle('hover:bg-orange-700', mode === 'pulang');
            document.getElementById('modeBtn-pulang').classList.toggle('bg-gray-300', mode === 'masuk');
            document.getElementById('modeBtn-pulang').classList.toggle('text-gray-700', mode === 'masuk');
            document.getElementById('modeBtn-pulang').classList.toggle('text-white', mode === 'pulang');

            // Update titles
            const studentTitle = mode === 'masuk' ? 'Siswa Masuk' : 'Siswa Pulang';
            const teacherTitle = mode === 'masuk' ? 'Guru Masuk' : 'Guru Pulang';
            document.getElementById('studentTitle').textContent = studentTitle;
            document.getElementById('teacherTitle').textContent = teacherTitle;

            // Reload attendance list
            loadAttendanceToday();
        }

        // Function to play beep sound
        function playBeep() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 1000; // Hz
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (error) {
                console.error('Error playing beep:', error);
            }
        }

        // Function to play thank you beep sound from audio file
        function playThankYouBeep() {
            try {
                const audio = new Audio('<?= base_url('/thank-you.mp3') ?>');
                audio.play().catch(error => {
                    console.error('Error playing thank you audio:', error);
                    // Fallback to beep sound if audio file fails
                    playBeep();
                });
            } catch (error) {
                console.error('Error creating audio:', error);
                playBeep();
            }
        }

        async function startScanning() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Browser memblokir akses kamera karena koneksi tidak aman (HTTP). Silakan gunakan HTTPS atau Localhost, atau izinkan "Insecure origins" di pengaturan browser.');
                }

                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: currentFacingMode,
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    }
                });

                const video = document.getElementById('cameraFeed');
                video.srcObject = stream;

                scanning = true;
                document.getElementById('startBtn').classList.add('hidden');
                document.getElementById('stopBtn').classList.remove('hidden');

                // Wait for video to load metadata before scanning
                video.onloadedmetadata = function () {
                    video.play();
                    scanQrCode(video);
                };
            } catch (error) {
                alert('Tidak dapat mengakses kamera: ' + error.message);
            }
        }

        function stopScanning() {
            scanning = false;
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            document.getElementById('startBtn').classList.remove('hidden');
            document.getElementById('stopBtn').classList.add('hidden');
            document.getElementById('cameraFeed').srcObject = null;
        }

        function switchCamera() {
            // Toggle facing mode
            currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
            // Save preference
            localStorage.setItem('cameraFacingMode', currentFacingMode);
            
            console.log('Switching camera to:', currentFacingMode);
            
            // Restart scanning if currently scanning
            if (scanning || stream) {
                stopScanning();
                // Add small delay to ensure stream is fully stopped
                setTimeout(() => {
                    startScanning();
                }, 200);
            }
        }

        function pauseScanning() {
            // Pause scanning temporarily without stopping camera stream
            scanning = false;
            console.log('Scanning paused, camera still active');
        }

        function resumeScanning() {
            // Resume scanning if camera is still active
            const video = document.getElementById('cameraFeed');
            if (video && video.srcObject) {
                scanning = true;
                scanQrCode(video);
                console.log('Scanning resumed');
            }
        }

        function scanQrCode(video) {
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');

            const scan = () => {
                if (!scanning) return;

                // Ensure video has dimensions before drawing
                if (video.videoWidth > 0 && video.videoHeight > 0) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    try {
                        ctx.drawImage(video, 0, 0);
                        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const code = jsQR(imageData.data, imageData.width, imageData.height);

                        if (code) {
                            // Extract NIS from QR code data
                            const nis = code.data.trim();
                            if (nis) {
                                pauseScanning();
                                processNis(nis);
                                return;
                            }
                        }
                    } catch (e) {
                        console.error('Error scanning QR:', e);
                    }
                }

                requestAnimationFrame(scan);
            };

            scan();
        }

        function submitNis() {
            const nis = nisInput.value.trim();
            console.log('Submit NIS:', nis);
            if (!nis) {
                alert('Masukkan NIS terlebih dahulu');
                return;
            }
            processNis(nis);
            nisInput.value = '';
            return false;
        }

        function processNis(nis) {
            console.log('Processing NIS/NIP:', nis, 'Mode:', currentMode);
            const loadingOverlay = document.getElementById('loadingOverlay');
            const overlayIcon = document.getElementById('overlayIcon');
            const overlayText = document.getElementById('overlayText');
            const overlayDetail = document.getElementById('overlayDetail');

            // Safety check
            if (!loadingOverlay) {
                console.error('loadingOverlay element not found!');
                alert('Error: Loading overlay not found');
                return;
            }

            // Show loading overlay with loading state
            loadingOverlay.classList.remove('hidden');
            overlayIcon.innerHTML = '<svg class="animate-spin h-12 w-12 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            overlayText.textContent = 'Memproses...';
            overlayDetail.style.display = 'none';

            const apiUrl = '<?= base_url('/api/qrcode/scan') ?>';
            console.log('API URL:', apiUrl);

            // Prepare payload with both NIS and NIP (auto-detect on backend)
            const payload = JSON.stringify({
                nis: nis,
                nip: nis, // Send same value as both NIS and NIP, backend will detect
                mode: currentMode
            });
            console.log('Payload:', payload);

            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text().then(text => {
                        console.log('Response text:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            return {
                                success: false,
                                message: 'Invalid server response: ' + text
                            };
                        }
                    });
                })
                .then(data => {
                    console.log('Response data:', data);

                    // Update overlay with result
                    if (data.success) {
                        // Play thank you beep for success
                        playThankYouBeep();

                        overlayIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-600 mx-auto" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>';
                        overlayText.className = 'text-gray-800 text-lg font-semibold';
                        overlayText.textContent = data.message;
                        overlayDetail.className = 'text-gray-600 text-sm mt-2';
                        overlayDetail.innerHTML = `<strong>${data.name}</strong><br/>NIS/NIP: ${data.nis || data.nip || nis}`;
                        overlayDetail.style.display = 'block';
                    } else {
                        // Play error beep for failed scan
                        playBeep();

                        overlayIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-600 mx-auto" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>';
                        overlayText.className = 'text-red-700 text-lg font-semibold';
                        overlayText.textContent = data.message;
                        overlayDetail.style.display = 'none';
                    }

                    // Close overlay after 2 seconds
                    setTimeout(() => {
                        loadingOverlay.classList.add('hidden');
                        loadAttendanceToday();
                        if (nisInput) {
                            nisInput.focus();
                        }
                    }, 2000);
                })
                .catch(error => {
                    console.error('Fetch Error:', error);

                    overlayIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-600 mx-auto" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>';
                    overlayText.className = 'text-red-700 text-lg font-semibold';
                    overlayText.textContent = 'Network Error';
                    overlayDetail.className = 'text-red-600 text-sm mt-2';
                    overlayDetail.textContent = error.message;
                    overlayDetail.style.display = 'block';

                    setTimeout(() => {
                        loadingOverlay.classList.add('hidden');
                        if (nisInput) {
                            nisInput.focus();
                        }
                    }, 2000);
                });
        }

        function loadAttendanceToday() {
            const url = '<?= base_url('/api/attendance/today') ?>?mode=' + currentMode;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const studentList = document.getElementById('studentList');
                    const teacherList = document.getElementById('teacherList');

                    // Separate students and teachers
                    const students = data.filter(item => item.nis && !item.nip);
                    const teachers = data.filter(item => item.nip && !item.nis);

                    // Determine status label based on mode
                    const statusMap = currentMode === 'masuk' ? {
                        'on_time': 'Tepat Waktu',
                        'late': 'Terlambat'
                    } : {
                        'on_time': 'Tepat Waktu',
                        'early': 'Lebih Awal'
                    };

                    // Render students
                    if (students.length === 0) {
                        studentList.innerHTML = '<div class="text-xs text-gray-500 text-center py-4">Belum ada</div>';
                    } else {
                        studentList.innerHTML = students.map(item => `
                            <div class="flex items-center justify-between text-xs bg-blue-50 p-2 rounded border border-blue-200">
                                <div>
                                    <p class="font-semibold text-gray-900">${item.student_name || item.name}</p>
                                    <p class="text-gray-500">NIS: ${item.nis}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold ${item.status === 'on_time' ? 'text-green-600' : 'text-yellow-600'}">${statusMap[item.status] || item.status}</p>
                                    <p class="text-gray-500 text-xs">${item.time}</p>
                                </div>
                            </div>
                        `).join('');
                    }

                    // Render teachers
                    if (teachers.length === 0) {
                        teacherList.innerHTML = '<div class="text-xs text-gray-500 text-center py-4">Belum ada</div>';
                    } else {
                        teacherList.innerHTML = teachers.map(item => `
                            <div class="flex items-center justify-between text-xs bg-purple-50 p-2 rounded border border-purple-200">
                                <div>
                                    <p class="font-semibold text-gray-900">${item.student_name || item.name}</p>
                                    <p class="text-gray-500">NIP: ${item.nip}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold ${item.status === 'on_time' ? 'text-green-600' : 'text-yellow-600'}">${statusMap[item.status] || item.status}</p>
                                    <p class="text-gray-500 text-xs">${item.time}</p>
                                </div>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => console.error('Error loading attendance:', error));
        }

        // Load attendance on page load
        window.addEventListener('load', loadAttendanceToday);

        // Update digital clock
        function updateClock() {
            const now = new Date();

            // Format time: HH:MM:SS
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('digitalClock').textContent = `${hours}:${minutes}:${seconds}`;

            // Format date: Hari, DD Bulan YYYY
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const dayName = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            document.getElementById('digitalDate').textContent = `${dayName}, ${date} ${month} ${year}`;
        }

        // Update clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);

        // Allow Enter key to submit NIS (prevent default form submission)
        nisInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitNis();
            }
        });

        // Log when page loads
        console.log('Scanner page loaded successfully');
    </script>
</body>

</html>