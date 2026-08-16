<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Jadwal Guru & Karyawan</h1>
            <p class="text-slate-600 mt-1">Kelola jadwal kerja untuk semua guru dan karyawan</p>
        </div>
        <a href="<?= base_url('admin/teacher-schedule/create') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Tambah Jadwal
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->has('success')) : ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i>
            <?= session('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')) : ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('info')) : ?>
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
            <i class="fas fa-info-circle mr-2"></i>
            <?= session('info') ?>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-lg font-bold text-slate-800">Daftar Jadwal</h2>
        </div>
        <table id="scheduleTable" class="w-full display">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Nama</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Role</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Jadwal</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-slate-700 w-32">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($schedules)) :
                    // Group schedules by user
                    $schedulesByUser = [];
                    foreach ($schedules as $schedule) {
                        $userId = $schedule['user_id'];
                        if (!isset($schedulesByUser[$userId])) {
                            $schedulesByUser[$userId] = [
                                'id' => $userId,
                                'full_name' => $schedule['full_name'],
                                'user_role' => $schedule['user_role'],
                                'schedules' => []
                            ];
                        }
                        $schedulesByUser[$userId]['schedules'][] = $schedule;
                    }

                    foreach ($schedulesByUser as $user) :
                        $days = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
                        $fullDays = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

                        // Build schedule string
                        $scheduleStr = [];
                        for ($i = 1; $i <= 7; $i++) {
                            $daySchedule = null;
                            foreach ($user['schedules'] as $sched) {
                                if ($sched['hari'] == $i) {
                                    $daySchedule = $sched;
                                    break;
                                }
                            }
                            if ($daySchedule && $daySchedule['jam_masuk']) {
                                $scheduleStr[] = $fullDays[$i] . ': ' . $daySchedule['jam_masuk'] . '-' . $daySchedule['jam_pulang'];
                            }
                        }
                ?>
                        <tr class="border-t hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                <i class="fas fa-user-tie mr-2 text-blue-600"></i><?= esc($user['full_name']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium"
                                    style="background-color: <?= $user['user_role'] === 'guru' ? '#dcfce7' : '#dbeafe' ?>; color: <?= $user['user_role'] === 'guru' ? '#166534' : '#0c4a6e' ?>;">
                                    <?= ucfirst($user['user_role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if (!empty($scheduleStr)) : ?>
                                    <details class="cursor-pointer group">
                                        <summary class="text-blue-600 hover:text-blue-800 font-medium">
                                            <i class="fas fa-calendar-alt mr-2"></i><?= count($scheduleStr) ?> hari
                                        </summary>
                                        <div class="mt-2 p-3 bg-slate-50 rounded text-sm text-slate-700 space-y-1">
                                            <?php foreach ($scheduleStr as $sched) : ?>
                                                <div><i class="fas fa-check-circle mr-2 text-green-600"></i><?= $sched ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                <?php else : ?>
                                    <span class="text-slate-400 italic">Belum ada jadwal</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-right">
                                <div class="flex gap-2 justify-end">
                                    <a href="<?= base_url('admin/teacher-schedule/edit/' . $user['id']) ?>"
                                        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 rounded transition"
                                        title="Edit jadwal">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded transition delete-btn"
                                        data-user-id="<?= $user['id'] ?>"
                                        data-user-name="<?= esc($user['full_name']) ?>"
                                        title="Hapus jadwal">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-slate-600">
                            <i class="fas fa-inbox mr-2"></i>Belum ada jadwal. <a href="<?= base_url('admin/teacher-schedule/create') ?>" class="text-blue-600 hover:underline">Tambah jadwal sekarang</a>
                        </td>
                    </tr>
                <?php endif; ?>
        </table>
    </div>
</div>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.15.0/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.15.0/vfs_fonts.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* DataTables Styling - Tailwind Consistent */
    .dataTables_wrapper {
        padding: 1.5rem 0;
    }

    .dataTables_length,
    .dataTables_filter {
        margin-bottom: 1rem;
    }

    .dataTables_length label,
    .dataTables_filter label {
        font-size: 0.875rem;
        color: #475569;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-family: inherit;
    }

    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .dt-buttons {
        margin-bottom: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    /* Base button styling */
    .dt-button {
        background-color: #4f46e5 !important;
        color: white !important;
        border: none !important;
        padding: 0.5rem 1rem !important;
        border-radius: 0.375rem !important;
        cursor: pointer !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    .dt-button:hover:not(.disabled) {
        background-color: #4338ca !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    }

    .dt-button:active:not(.disabled) {
        transform: translateY(0) !important;
    }

    .dt-button.disabled,
    .dt-button:disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
    }

    /* Styling untuk button Salin dengan warna hijau */
    .dt-button.buttons-copy {
        background-color: #059669 !important;
    }

    .dt-button.buttons-copy:hover:not(.disabled) {
        background-color: #047857 !important;
        transform: translateY(-1px) !important;
    }

    /* Styling untuk button CSV dengan warna biru */
    .dt-button.buttons-csv {
        background-color: #2563eb !important;
    }

    .dt-button.buttons-csv:hover:not(.disabled) {
        background-color: #1d4ed8 !important;
    }

    /* Styling untuk button Excel dengan warna hijau tua */
    .dt-button.buttons-excel {
        background-color: #16a34a !important;
    }

    .dt-button.buttons-excel:hover:not(.disabled) {
        background-color: #15803d !important;
    }

    /* Styling untuk button PDF dengan warna merah */
    .dt-button.buttons-pdf {
        background-color: #dc2626 !important;
    }

    .dt-button.buttons-pdf:hover:not(.disabled) {
        background-color: #b91c1c !important;
    }

    /* Styling untuk button Print dengan warna orange */
    .dt-button.buttons-print {
        background-color: #ea580c !important;
    }

    .dt-button.buttons-print:hover:not(.disabled) {
        background-color: #c2410c !important;
    }

    .dataTables_info {
        font-size: 0.875rem;
        color: #64748b;
    }

    /* Bottom section - info dan pagination */
    .dataTables_bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }

    /* Pagination */
    .dataTables_paginate {
        margin-top: 0;
    }

    .paginate_button {
        padding: 0.375rem 0.75rem !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.375rem !important;
        margin: 0 0.25rem !important;
        font-size: 0.875rem !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }

    .paginate_button:hover {
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
    }

    .paginate_button.current {
        background-color: #4f46e5 !important;
        color: white !important;
        border-color: #4f46e5 !important;
        font-weight: 600;
    }

    .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable hanya jika ada data
        const tableBody = document.querySelector('#scheduleTable tbody');
        const hasData = tableBody.querySelectorAll('tr').length > 0 &&
            !tableBody.querySelector('tr td[colspan]');

        if (hasData) {
            // Initialize DataTable
            const table = $('#scheduleTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Salin',
                        className: 'dt-button'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'dt-button'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'dt-button'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'dt-button'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'dt-button'
                    }
                ],
                columnDefs: [{
                    orderable: false,
                    targets: 3
                }]
            });
        }

        // Handle delete button
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.dataset.userId;
                const userName = this.dataset.userName;

                Swal.fire({
                    title: 'Hapus Jadwal?',
                    text: `Jadwal untuk ${userName} akan dihapus permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`<?= base_url('admin/teacher-schedule/delete/') ?>${userId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Terhapus!',
                                    data.message,
                                    'success'
                                ).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    data.message || 'Terjadi kesalahan saat menghapus data.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan sistem.',
                                'error'
                            );
                        });
                    }
                });
            });
        });
    });
</script>

<?= $this->endSection() ?>