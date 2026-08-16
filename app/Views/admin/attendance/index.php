<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Absensi Harian</h1>
            <p class="text-slate-600 mt-1">
                <?php if ($selectedClass): ?>
                    Kelas: <strong><?= esc($selectedClass) ?></strong>
                <?php else: ?>
                    Semua Kelas
                <?php endif; ?>
                | Tanggal: <strong><?= date('d M Y', strtotime($today)) ?></strong>
            </p>
        </div>
        <a href="/admin/dashboard" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    <!-- Class Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <label for="class" class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fas fa-filter mr-2 text-indigo-600"></i>Filter Kelas
                </label>
                <select name="class" id="class" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?= esc($cls['class']) ?>" <?= $selectedClass === $cls['class'] ? 'selected' : '' ?>>
                            <?= esc($cls['class']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        <div class="stat-card bg-white rounded-lg shadow p-4 border-t-4 border-slate-500">
            <p class="text-xs text-slate-600">Total Siswa</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['total'] ?></h3>
        </div>
        <div class="stat-card bg-white rounded-lg shadow p-4 border-t-4 border-green-500">
            <p class="text-xs text-slate-600">Tepat Waktu</p>
            <h3 class="text-2xl font-bold text-green-600 mt-1"><?= $stats['on_time'] ?></h3>
        </div>
        <div class="stat-card bg-white rounded-lg shadow p-4 border-t-4 border-yellow-500">
            <p class="text-xs text-slate-600">Terlambat</p>
            <h3 class="text-2xl font-bold text-yellow-600 mt-1"><?= $stats['late'] ?></h3>
        </div>
        <div class="stat-card bg-white rounded-lg shadow p-4 border-t-4 border-blue-500">
            <p class="text-xs text-slate-600">Izin</p>
            <h3 class="text-2xl font-bold text-blue-600 mt-1"><?= $stats['izin'] ?></h3>
        </div>
        <div class="stat-card bg-white rounded-lg shadow p-4 border-t-4 border-orange-500">
            <p class="text-xs text-slate-600">Sakit</p>
            <h3 class="text-2xl font-bold text-orange-600 mt-1"><?= $stats['sakit'] ?></h3>
        </div>
        <div class="stat-card bg-white rounded-lg shadow p-4 border-t-4 border-red-500">
            <p class="text-xs text-slate-600">Alpha</p>
            <h3 class="text-2xl font-bold text-red-600 mt-1"><?= $stats['alpha'] ?></h3>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?= csrf_field() ?>
        <div class="p-6 border-b">
            <h2 class="text-lg font-bold text-slate-800"><i class="fas fa-list mr-2 text-indigo-600"></i>Daftar Absensi Siswa</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">No</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">NIS</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Nama Siswa</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Kelas</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Waktu Masuk</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $index => $student): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-600"><?= $index + 1 ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-800"><?= esc($student['nis']) ?></td>
                                <td class="px-6 py-4 text-sm text-slate-700"><?= esc($student['full_name']) ?></td>
                                <td class="px-6 py-4 text-sm text-slate-700"><?= esc($student['class']) ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <?php
                                    $statusMap = [
                                        'on_time' => ['Tepat Waktu', 'bg-green-100 text-green-800'],
                                        'late' => ['Terlambat', 'bg-yellow-100 text-yellow-800'],
                                        'izin' => ['Izin', 'bg-blue-100 text-blue-800'],
                                        'sakit' => ['Sakit', 'bg-orange-100 text-orange-800'],
                                        'alpha' => ['Alpha', 'bg-red-100 text-red-800'],
                                        'unknown' => ['Belum Diisi', 'bg-gray-100 text-gray-800'],
                                    ];
                                    $status = $student['masuk_status'] ?? 'unknown';
                                    [$label, $class] = $statusMap[$status] ?? ['Unknown', 'bg-gray-100 text-gray-800'];
                                    ?>
                                    <span class="inline-block <?= $class ?> px-3 py-1 rounded-full text-xs font-medium">
                                        <?= $label ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php if ($student['attendance'] && $student['attendance']['masuk_at']): ?>
                                        <?= date('H:i', strtotime($student['attendance']['masuk_at'])) ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex gap-2 flex-wrap">
                                        <button onclick="markAttendance(<?= $student['id'] ?>, 'on_time')" title="Tepat Waktu" class="text-green-600 hover:text-green-900 font-medium text-xs transition">
                                            ✓ Tepat
                                        </button>
                                        <button onclick="markAttendance(<?= $student['id'] ?>, 'late')" title="Terlambat" class="text-yellow-600 hover:text-yellow-900 font-medium text-xs transition">
                                            ⚠ Terlambat
                                        </button>
                                        <button onclick="markAttendance(<?= $student['id'] ?>, 'izin')" title="Izin" class="text-blue-600 hover:text-blue-900 font-medium text-xs transition">
                                            ⊘ Izin
                                        </button>
                                        <button onclick="markAttendance(<?= $student['id'] ?>, 'sakit')" title="Sakit" class="text-orange-600 hover:text-orange-900 font-medium text-xs transition">
                                            ⊕ Sakit
                                        </button>
                                            ✗ Alpha
                                        </button>
                                        <?php if ($student['attendance'] && isset($student['attendance']['id'])): ?>
                                            <a href="/admin/attendance/<?= $student['attendance']['id'] ?>/edit" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs transition">
                                                ✎ Edit
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-400 font-medium text-xs cursor-not-allowed" title="Belum ada data absensi">
                                                ✎ Edit
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                Belum ada siswa
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const statusMap = {
        'on_time': {
            label: 'Tepat Waktu',
            class: 'bg-green-100 text-green-800',
            icon: '✓'
        },
        'late': {
            label: 'Terlambat',
            class: 'bg-yellow-100 text-yellow-800',
            icon: '⚠'
        },
        'izin': {
            label: 'Izin',
            class: 'bg-blue-100 text-blue-800',
            icon: '⊘'
        },
        'sakit': {
            label: 'Sakit',
            class: 'bg-orange-100 text-orange-800',
            icon: '⊕'
        },
        'alpha': {
            label: 'Alpha',
            class: 'bg-red-100 text-red-800',
            icon: '✗'
        }
    };

    function markAttendance(studentId, status) {
        const button = event.target;
        const statusInfo = statusMap[status];

        // Get student name from the row
        const row = button.closest('tr');
        const studentName = row.querySelector('td:nth-child(3)').textContent.trim();

        const formData = new FormData();
        formData.append('student_id', studentId);
        formData.append('status', status);
        formData.append('date', '<?= $today ?>');

        // Get CSRF token
        const csrfToken = document.querySelector('input[name="csrf_test_name"]');
        if (csrfToken) {
            formData.append('csrf_test_name', csrfToken.value);
        }

        // Disable button during request
        button.disabled = true;
        button.style.opacity = '0.5';

        fetch('/admin/attendance/mark-student', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(result => {
                if (result.ok) {
                    // Update status cell
                    const statusCell = row.querySelector('td:nth-child(5)');
                    statusCell.innerHTML = `<span class="inline-block ${statusInfo.class} px-3 py-1 rounded-full text-xs font-medium">${statusInfo.label}</span>`;

                    // Update time cell
                    const timeCell = row.querySelector('td:nth-child(6)');
                    if (status !== 'unknown') {
                        const now = new Date();
                        const hours = String(now.getHours()).padStart(2, '0');
                        const minutes = String(now.getMinutes()).padStart(2, '0');
                        timeCell.textContent = `${hours}:${minutes}`;
                    }

                    // Update CSRF token for next request
                    if (result.csrf_token && csrfToken) {
                        csrfToken.value = result.csrf_token;
                    }

                    // Show success toast with student name
                    showToast(`${studentName} - ${statusInfo.label} berhasil diupdate`, 'success');

                    // Update stats
                    updateStats();
                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan', 'error');
            })
            .finally(() => {
                button.disabled = false;
                button.style.opacity = '1';
            });
    }

    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg text-white text-sm z-50 animate-slide-in transition-all duration-300`;

        if (type === 'success') {
            toast.classList.add('bg-green-500');
        } else if (type === 'error') {
            toast.classList.add('bg-red-500');
        } else {
            toast.classList.add('bg-blue-500');
        }

        toast.textContent = message;
        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function updateStats() {
        // Get current class filter if any
        const classSelect = document.getElementById('class');
        const selectedClass = classSelect ? classSelect.value : '';

        // Build query string
        let query = '/admin/attendance/get-stats';
        if (selectedClass) {
            query += '?class=' + encodeURIComponent(selectedClass);
        }

        fetch(query)
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    // Update stat cards
                    const stats = data.stats;
                    const statValues = [stats.total, stats.on_time, stats.late, stats.izin, stats.sakit, stats.alpha];
                    const statCards = document.querySelectorAll('.stat-card h3');

                    statCards.forEach((card, index) => {
                        card.textContent = statValues[index];
                    });
                }
            })
            .catch(error => console.error('Error updating stats:', error));
    }

    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
    // Check for session flashdata
    <?php if (session()->getFlashdata('success')): ?>
        showToast('<?= session()->getFlashdata('success') ?>', 'success');
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        showToast('<?= session()->getFlashdata('error') ?>', 'error');
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        showToast('<?= session()->getFlashdata('warning') ?>', 'warning');
    <?php endif; ?>
</script>

<?= $this->endSection() ?>