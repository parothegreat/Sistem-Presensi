<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="<?= base_url('/admin/activities') ?>" class="text-slate-500 hover:text-slate-700 flex items-center gap-2 mb-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <h1 class="text-3xl font-bold text-slate-800"><?= esc($activity['name']) ?></h1>
            <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-600">
                <span><i class="fas fa-clock mr-1"></i> Mulai: <strong><?= date('d M Y H:i', strtotime($activity['start_time'])) ?></strong></span>
                <span><i class="fas fa-flag-checkered mr-1"></i> Selesai: <strong><?= date('d M Y H:i', strtotime($activity['end_time'])) ?></strong></span>
                <span>
                    Status:
                    <?php
                    $statusClass = 'bg-slate-100 text-slate-800';
                    if ($activity['status'] === 'ongoing') $statusClass = 'bg-green-100 text-green-800 animate-pulse';
                    if ($activity['status'] === 'completed') $statusClass = 'bg-blue-100 text-blue-800';
                    ?>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold <?= $statusClass ?>">
                        <?= ucfirst($activity['status']) ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Actions & Filter -->
        <div class="flex flex-col sm:flex-row gap-2">
            <form method="get" class="flex gap-2">
                <select name="filter_class" class="rounded border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?= $cls ?>" <?= ($filterClass == $cls) ? 'selected' : '' ?>><?= $cls ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <a href="<?= base_url("/admin/activities/{$activity['id']}/print?filter_class=" . ($filterClass ?? '')) ?>" target="_blank" class="bg-slate-700 text-white px-4 py-2 rounded hover:bg-slate-800 text-sm font-medium flex items-center justify-center gap-2">
                <i class="fas fa-print"></i> Cetak
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Summary Stats (Filtered) -->
        <div class="lg:col-span-3 grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Stats Calculation for Filtered Data -->
            <?php
            // Filter attendance based on visible participants
            $participantIds = array_column($participants, 'student_id');
            $filteredAttendance = array_filter($attendance, function ($a) use ($participantIds) {
                return in_array($a['student_id'], $participantIds);
            });

            $present = array_filter($filteredAttendance, function ($a) {
                return in_array($a['status'], ['present']);
            });
            $late = array_filter($filteredAttendance, function ($a) {
                return $a['status'] === 'late';
            });
            $permission = array_filter($filteredAttendance, function ($a) {
                return $a['status'] === 'permission';
            });
            $sick = array_filter($filteredAttendance, function ($a) {
                return $a['status'] === 'sick';
            });
            $absent = count($participants) - count($present) - count($late) - count($permission) - count($sick);
            // Safety measure if absent < 0 due to some sync issue
            if ($absent < 0) $absent = 0;
            ?>

            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <div class="text-sm text-slate-500">Total Peserta</div>
                <div class="text-2xl font-bold text-slate-800"><?= count($participants) ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <div class="text-sm text-slate-500">Hadir (Tepat)</div>
                <div class="text-2xl font-bold text-slate-800"><?= count($present) ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <div class="text-sm text-slate-500">Terlambat</div>
                <div class="text-2xl font-bold text-slate-800"><?= count($late) ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
                <div class="text-sm text-slate-500">Belum Hadir / Alpha</div>
                <div class="text-2xl font-bold text-slate-800"><?= $absent ?></div>
            </div>
        </div>

        <!-- Attendance List -->
        <div class="lg:col-span-3 bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b flex justify-between items-center bg-slate-50">
                <h2 class="text-lg font-bold text-slate-800">Daftar Kehadiran <?= $filterClass ? "($filterClass)" : "" ?></h2>
            </div>
            <div class="overflow-x-auto">
                <table id="participantTable" class="w-full text-sm text-left">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3">Nama Siswa</th>
                            <th class="px-6 py-3">Kelas</th>
                            <th class="px-6 py-3">Check In</th>
                            <th class="px-6 py-3">Check Out</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Metode</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        // Map attendance by student_id for easy lookup
                        $attMap = [];
                        foreach ($attendance as $a) $attMap[$a['student_id']] = $a;

                        foreach ($participants as $p):
                            $att = $attMap[$p['student_id']] ?? null;
                            $status = $att ? $att['status'] : 'absent';
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 font-medium text-slate-900"><?= esc($p['full_name']) ?></td>
                                <td class="px-6 py-3"><?= esc($p['class']) ?></td>
                                <td class="px-6 py-3 font-mono text-slate-600">
                                    <?= ($att && $att['check_in_time']) ? date('H:i', strtotime($att['check_in_time'])) : '-' ?>
                                </td>
                                <td class="px-6 py-3 font-mono text-slate-600">
                                    <?= ($att && $att['check_out_time']) ? date('H:i', strtotime($att['check_out_time'])) : '-' ?>
                                </td>
                                <td class="px-6 py-3">
                                    <?php
                                    $sColor = 'text-slate-500';
                                    $sLabel = 'Belum Hadir';
                                    if ($status == 'present') {
                                        $sColor = 'text-green-600 font-bold';
                                        $sLabel = 'Hadir';
                                    } elseif ($status == 'late') {
                                        $sColor = 'text-yellow-600 font-bold';
                                        $sLabel = 'Terlambat';
                                    } elseif ($status == 'permission') {
                                        $sColor = 'text-blue-600 font-bold';
                                        $sLabel = 'Izin';
                                    } elseif ($status == 'sick') {
                                        $sColor = 'text-purple-600 font-bold';
                                        $sLabel = 'Sakit';
                                    } elseif ($status == 'absent' && $att) {
                                        $sColor = 'text-red-600 font-bold';
                                        $sLabel = 'Alpha';
                                    } // manual mark alpha
                                    ?>
                                    <span class="<?= $sColor ?>"><?= $sLabel ?></span>
                                </td>
                                <td class="px-6 py-3 text-slate-500 text-xs">
                                    <?= $att ? esc($att['method']) : '-' ?>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <!-- Edit Status Button -->
                                        <button onclick="openStatusModal('<?= $p['student_id'] ?>', '<?= $p['full_name'] ?>', '<?= $status ?>')"
                                            class="text-blue-600 hover:bg-blue-100 p-1.5 rounded" title="Ubah Status">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <?php if ($att): ?>
                                            <!-- Delete Attendance (Reset to absent) -->
                                            <form action="<?= base_url("/admin/activities/{$activity['id']}/attendance/delete") ?>" method="post" onsubmit="return confirm('Reset status kehadiran siswa ini?');" class="inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="student_id" value="<?= $p['student_id'] ?>">
                                                <button type="submit" class="text-orange-600 hover:bg-orange-100 p-1.5 rounded" title="Reset Kehadiran">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Delete Participant (Remove from list) -->
                                        <form action="<?= base_url("/admin/activities/{$activity['id']}/participant/delete") ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus siswa ini dari kegiatan? Data kehadiran juga akan terhapus.');" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="student_id" value="<?= $p['student_id'] ?>">
                                            <button type="submit" class="text-red-600 hover:bg-red-100 p-1.5 rounded" title="Hapus Peserta">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform transition-all">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-bold text-slate-800">Ubah Status Kehadiran</h3>
        </div>
        <form action="<?= base_url("/admin/activities/{$activity['id']}/attendance/update-status") ?>" method="post">
            <?= csrf_field() ?>
            <div class="p-6">
                <input type="hidden" name="student_id" id="modalStudentId">
                <p class="text-slate-600 mb-4">Siswa: <strong id="modalStudentName"></strong></p>

                <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                <select name="status" id="modalStatus" class="w-full px-3 py-2 rounded border border-slate-400 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="present">Hadir</option>
                    <option value="late">Terlambat</option>
                    <option value="permission">Izin</option>
                    <option value="sick">Sakit</option>
                    <option value="absent">Alpha</option>
                </select>
                <p class="text-xs text-slate-400 mt-2">Perubahan status manual akan tercatat sebagai metode 'manual_update'.</p>
            </div>
            <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3 rounded-b-lg">
                <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border rounded text-slate-600 hover:bg-slate-100 font-medium text-sm">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 font-medium text-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#participantTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            pageLength: 50,
            columnDefs: [{
                orderable: false,
                targets: -1
            }] // Disable sort on actions column
        });
    });

    function openStatusModal(studentId, studentName, currentStatus) {
        document.getElementById('modalStudentId').value = studentId;
        document.getElementById('modalStudentName').innerText = studentName;

        // If status is 'absent' but no record, it's basically 'absent'
        document.getElementById('modalStatus').value = currentStatus;

        document.getElementById('statusModal').classList.remove('hidden');
        document.getElementById('statusModal').classList.add('flex');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
        document.getElementById('statusModal').classList.remove('flex');
    }

    // Close modal on outside click
    document.getElementById('statusModal').addEventListener('click', function(e) {
        if (e.target === this) closeStatusModal();
    });
</script>
<?php $this->endSection() ?>