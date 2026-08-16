<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Scanner - Presensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <!-- RFID Display -->
                    <div class="bg-gray-900 rounded-none aspect-video flex items-center justify-center p-6 relative">
                        <div class="text-center">
                            <div id="rfidIcon" class="text-indigo-400 text-6xl mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <p id="rfidStatus" class="text-indigo-400 text-xl font-bold">Menunggu kartu RFID...</p>
                        </div>
                    </div>

                    <!-- RFID Input (Hidden visually but focusable - untuk menerima input dari USB reader) -->
                    <input type="text" id="rfidInput" style="opacity: 0; position: absolute; z-index: -1;" autocomplete="off" />

                    <!-- Manual Input Section -->
                    <div class="p-6 bg-white border-t">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Input RFID Manual</label>
                        <div class="flex gap-2">
                            <input type="text" id="rfidManualInput" placeholder="Masukkan RFID ID..." class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-3 font-mono focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-200">
                            <button type="button" onclick="submitRfid()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-3 rounded-lg transition">
                                Scan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Right) - Two Columns for Student and Teacher Attendance -->
            <div class="lg:col-span-2 flex flex-col h-full">
                <!-- Attendance Mode Toggle -->
                <div class="bg-white rounded-xl shadow-2xl overflow-hidden mb-6">
                    <div class="flex">
                        <button type="button" onclick="switchMode('masuk')" id="modeBtn-masuk" class="flex-1 bg-green-600 text-white font-bold py-3 rounded-l-lg transition hover:bg-green-700">
                            Masuk
                        </button>
                        <button type="button" onclick="switchMode('pulang')" id="modeBtn-pulang" class="flex-1 bg-gray-300 text-gray-700 font-bold py-3 rounded-r-lg transition hover:bg-gray-400">
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
    <a href="<?= base_url('/') ?>" class="text-white hover:text-gray-200 underline">← Kembali ke Beranda</a>
    </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white px-8 py-6 rounded-lg shadow-2xl text-center max-w-sm">
            <div class="mb-4">
                <div class="animate-spin inline-block w-10 h-10 border-4 border-slate-300 border-t-blue-600 rounded-full"></div>
            </div>
            <p class="text-slate-700 font-medium text-lg">Memproses...</p>
        </div>
    </div>

    <script>
        let currentMode = 'masuk';
        let lastRfidInput = '';
        let hasAnybodyCheckedIn = false; // Track if anyone has checked in
        let isProcessing = false; // Prevent double submission

        // Play beep sound
        function playBeep() {
            try {
                const audioContext = new(window.AudioContext || window.webkitAudioContext)();
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

        // Play thank you beep sound from audio file
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

        // Digital Clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('digitalClock').textContent = `${hours}:${minutes}:${seconds}`;

            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dayName = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            document.getElementById('digitalDate').textContent = `${dayName}, ${date} ${month} ${year}`;
        }

        updateClock();
        setInterval(updateClock, 1000);

        // Global RFID Scanner Handler (No focus needed)
        let rfidBuffer = '';
        let scanTimer;

        document.addEventListener('keydown', function(e) {
            // Ignore if user is typing in the manual input or other inputs
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
                return;
            }

            // Reset timer on every key
            clearTimeout(scanTimer);

            if (e.key === 'Enter') {
                // Handle Enter key (Common for scanners)
                if (rfidBuffer.length > 5) { // Valid RFID length assumption
                    processRfid(rfidBuffer);
                }
                rfidBuffer = '';
            } else if (e.key.length === 1) { // Append printable characters
                rfidBuffer += e.key;

                // Auto-submit if silence for 200ms (Matches previous logic & supports scanners without Enter)
                scanTimer = setTimeout(() => {
                    if (rfidBuffer.length > 5) {
                        processRfid(rfidBuffer);
                    }
                    rfidBuffer = ''; // Clear buffer
                }, 200);
            }
        });

        // Manual RFID Submit
        function submitRfid() {
            const rfidId = document.getElementById('rfidManualInput').value.trim();
            if (!rfidId) {
                alert('Masukkan RFID ID');
                return;
            }
            processRfid(rfidId);
            document.getElementById('rfidManualInput').value = '';
            document.getElementById('rfidManualInput').focus();
        }

        // Process RFID
        function processRfid(rfidId) {
            if (isProcessing) return; // Block double submission
            isProcessing = true;

            const modal = document.getElementById('loadingModal');
            modal.classList.remove('hidden');

            const payload = JSON.stringify({
                rfid_id: rfidId,
                mode: currentMode
            });

            fetch('<?= base_url('/api/rfid-scan') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: payload
                })
                .then(response => response.json())
                .then(data => {
                    modal.classList.add('hidden');
                    isProcessing = false; // Release lock

                    if (data.success) {
                        playThankYouBeep();
                        showSuccess(data);
                        updateAttendanceDisplay();
                    } else {
                        playBeep();
                        showError(data);
                    }

                    // Refocus - not needed anymore with global listener
                    // document.getElementById('rfidInput').focus();
                })
                .catch(error => {
                    modal.classList.add('hidden');
                    isProcessing = false; // Release lock
                    playBeep();
                    alert('Error: ' + error.message);
                    // document.getElementById('rfidInput').focus();
                });
        }

        function showSuccess(data) {
            const statusEl = document.getElementById('rfidStatus');
            const iconEl = document.getElementById('rfidIcon');

            statusEl.textContent = data.message;
            statusEl.className = 'text-green-400 text-xl font-bold';
            iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>';

            setTimeout(() => {
                statusEl.textContent = 'Menunggu kartu RFID...';
                statusEl.className = 'text-indigo-400 text-xl font-bold';
                iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>';
            }, 3000);
        }

        function showError(data) {
            const statusEl = document.getElementById('rfidStatus');
            const iconEl = document.getElementById('rfidIcon');

            statusEl.textContent = data.message;
            statusEl.className = 'text-red-400 text-xl font-bold';
            iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>';

            setTimeout(() => {
                statusEl.textContent = 'Menunggu kartu RFID...';
                statusEl.className = 'text-indigo-400 text-xl font-bold';
                iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>';
            }, 3000);
        }

        function switchMode(mode) {
            // Validate: can only switch to pulang if someone has checked in
            if (mode === 'pulang' && !hasAnybodyCheckedIn) {
                playBeep();
                alert('Tidak ada yang sudah check-in. Lakukan check-in terlebih dahulu.');
                return;
            }

            currentMode = mode;
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
            updateAttendanceDisplay();
        }

        // Update pulang button state (enabled/disabled based on check-in status)
        function updatePulangButtonState() {
            const pulangBtn = document.getElementById('modeBtn-pulang');
            if (hasAnybodyCheckedIn) {
                pulangBtn.disabled = false;
                pulangBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                pulangBtn.style.cursor = 'pointer';
            } else {
                pulangBtn.disabled = true;
                pulangBtn.classList.add('opacity-50', 'cursor-not-allowed');
                pulangBtn.style.cursor = 'not-allowed';
            }
        }

        function updateAttendanceDisplay() {
            // Load attendance today
            fetch('<?= base_url('/api/attendance/today') ?>?mode=' + currentMode)
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

                    // Check if anyone has checked in (masuk)
                    hasAnybodyCheckedIn = students.length > 0 || teachers.length > 0;

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
                    
                    // Update pulang button state based on check-in status
                    updatePulangButtonState();
                })
                .catch(error => console.error('Error loading attendance:', error));
        }

        // Load attendance on page load
        window.addEventListener('load', updateAttendanceDisplay);

        // Focus on RFID input - Not needed
        // document.getElementById('rfidInput').focus();
        document.getElementById('rfidManualInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitRfid();
            }
        });
    </script>
</body>

</html>