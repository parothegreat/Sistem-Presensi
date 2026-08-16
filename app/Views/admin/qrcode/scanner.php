<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Scanner QR Code</h1>
            <p class="text-sm text-slate-500 mt-1">Scan kartu QR code siswa untuk absensi</p>
        </div>
        <a href="<?= base_url('/admin/dashboard') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">Kembali</a>
    </div>

    <!-- Loading modal -->
    <div id="loadingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white px-8 py-6 rounded-lg shadow-2xl text-center max-w-sm">
            <div class="mb-4">
                <div class="animate-spin inline-block w-10 h-10 border-4 border-slate-300 border-t-blue-600 rounded-full"></div>
            </div>
            <p class="text-slate-700 font-medium text-lg" id="loadingText">Memproses...</p>
        </div>
    </div>

    <div class="bg-white border rounded-lg shadow p-6">
        <!-- Scanner area -->
        <div class="mb-6">
            <div id="scannerContainer" class="mb-4">
                <video id="scannerVideo" style="width: 100%; max-height: 400px; background: black;" playsinline></video>
            </div>
            <div class="flex gap-2">
                <button id="startBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Mulai Scan</button>
                <button id="stopBtn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled" disabled>Hentikan</button>
            </div>
        </div>

        <!-- Manual input fallback -->
        <div class="mb-6">
            <label class="block text-sm text-slate-600 mb-2">Atau Input NIS Secara Manual:</label>
            <div class="flex gap-2">
                <input type="text" id="manualNis" placeholder="Masukkan NIS siswa" class="border rounded px-3 py-2 flex-1" />
                <button onclick="submitNis()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Scan</button>
            </div>
        </div>

        <!-- Result -->
        <div id="resultContainer" class="hidden mb-6 p-4 rounded border">
            <div class="mb-3">
                <strong id="resultStatus" class="text-lg"></strong>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-slate-500">Nama:</span>
                    <div class="font-semibold" id="resultName">-</div>
                </div>
                <div>
                    <span class="text-slate-500">NIS/NIP:</span>
                    <div class="font-semibold" id="resultNis">-</div>
                </div>
                <div>
                    <span class="text-slate-500">Kelas/Role:</span>
                    <div class="font-semibold" id="resultClass">-</div>
                </div>
                <div>
                    <span class="text-slate-500">Status:</span>
                    <div class="font-semibold" id="resultAttendanceStatus">-</div>
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold mb-4">Riwayat Absensi Hari Ini</h3>
            <div id="historyContainer" class="text-slate-600">
                <p class="text-sm">Belum ada absensi</p>
            </div>
        </div>
    </div>
</div>

<!-- Include jsQR library for QR code scanning -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
    let scanner = null;
    let isScanning = false;

    // Initialize scanner
    document.getElementById('startBtn').addEventListener('click', async () => {
        const video = document.getElementById('scannerVideo');
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment'
                }
            });
            video.srcObject = stream;
            isScanning = true;
            document.getElementById('startBtn').disabled = true;
            document.getElementById('stopBtn').disabled = false;
            scanQrCode(video);
        } catch (err) {
            alert('Tidak bisa akses kamera: ' + err.message);
        }
    });

    document.getElementById('stopBtn').addEventListener('click', () => {
        const video = document.getElementById('scannerVideo');
        const stream = video.srcObject;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        isScanning = false;
        document.getElementById('startBtn').disabled = false;
        document.getElementById('stopBtn').disabled = true;
    });

    function scanQrCode(video) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        video.addEventListener('play', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const scan = () => {
                if (!isScanning) return;

                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code && code.data) {
                    // Found QR code
                    processNis(code.data);
                    isScanning = false;
                    document.getElementById('startBtn').disabled = false;
                    document.getElementById('stopBtn').disabled = true;
                    const stream = video.srcObject;
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                    }
                    return;
                }

                requestAnimationFrame(scan);
            };

            scan();
        });
    }

    function submitNis() {
        const nis = document.getElementById('manualNis').value.trim();
        if (nis) {
            processNis(nis);
            document.getElementById('manualNis').value = '';
        }
    }

    function processNis(value) {
        // Show loading modal
        document.getElementById('loadingModal').classList.remove('hidden');
        document.getElementById('loadingText').textContent = 'Memproses...';

        // Auto-detect: NIS (student) atau NIP (guru)
        // Don't require CSRF for API since user is already authenticated
        fetch('<?= base_url('/api/qrcode/scan') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    nis: value, // Try as NIS first
                    nip: value // Also send as NIP for auto-detection
                })
            })
            .then(response => response.json())
            .then(data => {
                // Set loading text to show the message
                const prefix = data.success ? '✓ ' : '⚠ ';
                document.getElementById('loadingText').textContent = prefix + data.message;

                setTimeout(() => {
                    document.getElementById('loadingModal').classList.add('hidden');
                    showResult(data);
                    loadHistory();
                }, 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingText').textContent = '⚠ Error: ' + error.message;

                setTimeout(() => {
                    document.getElementById('loadingModal').classList.add('hidden');
                    showResult({
                        success: false,
                        message: 'Error: ' + error.message,
                    });
                }, 1500);
            });
    }

    function showResult(data) {
        const container = document.getElementById('resultContainer');
        const nameEl = document.getElementById('resultName');
        const nisEl = document.getElementById('resultNis');
        const classEl = document.getElementById('resultClass');
        const statusAttendanceEl = document.getElementById('resultAttendanceStatus');

        // Set colors based on success/failure
        if (data.success) {
            container.className = 'p-4 rounded border mb-6 bg-green-50 border-green-200';
        } else {
            container.className = 'p-4 rounded border mb-6 bg-yellow-50 border-yellow-200';
        }

        // Set message from loading modal
        const loadingText = document.getElementById('loadingText').textContent;
        const statusEl = document.getElementById('resultStatus');
        statusEl.textContent = loadingText;
        if (data.success) {
            statusEl.className = 'text-lg text-green-700';
        } else {
            statusEl.className = 'text-lg text-yellow-700';
        }

        // Handle both student and teacher responses
        nameEl.textContent = data.name || '-';
        nisEl.textContent = data.nis || data.nip || '-';
        classEl.textContent = data.class || data.role || '-';
        statusAttendanceEl.textContent = data.status_text || data.time || '-';

        container.classList.remove('hidden');
    }

    function loadHistory() {
        const today = new Date().toISOString().split('T')[0];
        // Fetch today's attendance
        fetch('<?= base_url('/admin/attendance') ?>?date=' + today)
            .then(response => response.text())
            .then(html => {
                // Parse and show summary
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                // Update history container with simple message
                document.getElementById('historyContainer').innerHTML = '<p class="text-sm text-green-600">✓ Absensi diperbarui</p>';
            });
    }

    // Allow Enter key to submit manual input
    document.getElementById('manualNis').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') submitNis();
    });
</script>

<?= $this->endSection() ?>