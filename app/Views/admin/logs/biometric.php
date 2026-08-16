<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800"><i class="fas fa-fingerprint mr-2 text-indigo-600"></i>Biometric Logs</h1>
            <p class="text-slate-600">Monitor data absensi dari mesin finger</p>
        </div>
        <a href="<?= base_url('/admin/logs') ?>" class="flex items-center gap-2 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg transition">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4"><i class="fas fa-filter mr-2"></i>Filter</h3>
        <form method="GET" class="grid gap-4 sm:grid-cols-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-slate-700 mb-1">Dari:</label>
                <input type="date" name="date_from" id="date_from" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    value="<?= $filters['date_from'] ?? '' ?>">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-slate-700 mb-1">Sampai:</label>
                <input type="date" name="date_to" id="date_to" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    value="<?= $filters['date_to'] ?? '' ?>">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status:</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    <option value="">-- Semua --</option>
                    <option value="checkin" <?= ($filters['status'] ?? '') === 'checkin' ? 'selected' : '' ?>>Checkin</option>
                    <option value="checkout" <?= ($filters['status'] ?? '') === 'checkout' ? 'selected' : '' ?>>Checkout</option>
                </select>
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Cari (User/Device):</label>
                <input type="text" name="search" id="search" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    value="<?= $filters['search'] ?? '' ?>" placeholder="ID/Nama/SN...">
            </div>
            <div class="flex items-end gap-2 col-span-4 justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="<?= base_url('/admin/logs/biometric') ?>" class="bg-slate-300 hover:bg-slate-400 text-slate-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center"><i class="fas fa-history mr-2"></i>Daftar Absensi Masuk</h3>
        </div>
        <div class="p-0">
            <?php if (!empty($logs)): ?>
                <div class="overflow-x-auto">
                    <table id="biometricTable" class="w-full display align-middle">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Waktu</th>
                                <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Device</th>
                                <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">User</th>
                                <th class="px-6 py-4 font-semibold text-slate-700 text-center uppercase text-xs tracking-wider">Type</th>
                                <th class="px-6 py-4 font-semibold text-slate-700 text-center uppercase text-xs tracking-wider">Status</th>
                                <th class="px-6 py-4 font-semibold text-slate-700 text-center uppercase text-xs tracking-wider">Proc</th>
                                <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Error</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="text-center py-12 bg-white">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                        <i class="fas fa-fingerprint text-3xl text-slate-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900">Tidak ada data logs</h3>
                    <p class="text-slate-500 mt-1">Belum ada data absensi biometric yang terekam/sesuai filter.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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

<style>
    .dt-button {
        background-color: #4f46e5 !important;
        color: white !important;
        border: none !important;
        padding: 0.5rem 1rem !important;
        border-radius: 0.5rem !important;
        margin-right: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        cursor: pointer !important;
        transition: background-color 0.2s !important;
    }

    .dt-button:hover {
        background-color: #4338ca !important;
    }
</style>

<script>
    $(document).ready(function() {
        var table = $('#biometricTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('admin/logs/biometric-json') ?>',
                data: function(d) {
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                    d.status = $('#status').val();
                    d.custom_search = $('#search').val();
                }
            },
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            },
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100], // Removed 'All' because specific length is safer for server-side
                [10, 25, 50, 100]
            ],
            order: [
                [0, 'desc']
            ],
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'copy',
                    text: '<i class="fas fa-copy" style="margin-right: 0.5rem;"></i>Salin',
                    className: 'dt-button'
                },
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv" style="margin-right: 0.5rem;"></i>CSV',
                    className: 'dt-button'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel" style="margin-right: 0.5rem;"></i>Excel',
                    className: 'dt-button'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf" style="margin-right: 0.5rem;"></i>PDF',
                    className: 'dt-button',
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print" style="margin-right: 0.5rem;"></i>Print',
                    className: 'dt-button'
                }
            ],
            columns: [{
                    data: 'created_at',
                    render: function(data, type, row) {
                        if (!data) return '-';
                        // Split date and time
                        var dateObj = new Date(data);
                        var time = dateObj.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        });
                        var date = dateObj.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });

                        // Fallback manual parsing if Date parsing is inconsistent across browsers for SQL string
                        if (data.indexOf(' ') > 0) {
                            var parts = data.split(' ');
                            time = parts[1];
                            var d = new Date(parts[0]);
                            date = d.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
                        }

                        return `<div class="flex flex-col gap-1">
                                    <span class="font-bold text-slate-800 text-sm">${time}</span>
                                    <span class="text-xs text-slate-500 font-mono">${date}</span>
                                </div>`;
                    }
                },
                {
                    data: 'device_id',
                    render: function(data) {
                        return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 font-mono border border-slate-200">
                                    ${data || 'N/A'}
                                </span>`;
                    }
                },
                {
                    data: 'user_name',
                    render: function(data, type, row) {
                        if (data) {
                            var role = (row.user_role || 'User').charAt(0).toUpperCase() + (row.user_role || 'User').slice(1);
                            return `<div class="font-semibold text-slate-900 text-sm">${data}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">${role} <span class="text-slate-300">|</span> ID: ${row.user_id}</div>`;
                        } else {
                            return `<div class="text-slate-400 italic text-sm">Unidentified</div>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5">ID: ${row.user_id || 'N/A'}</div>`;
                        }
                    }
                },
                {
                    data: 'biometric_type',
                    className: 'text-center',
                    render: function(data) {
                        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                    ${(data || 'FINGER').toUpperCase()}
                                </span>`;
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        var status = data || 'checkin';
                        var classes = 'bg-slate-100 text-slate-800 border-slate-200';
                        if (status === 'checkin') classes = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                        else if (status === 'checkout') classes = 'bg-amber-100 text-amber-800 border-amber-200';
                        else if (status === 'breakout') classes = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                        else if (status === 'breakin') classes = 'bg-blue-100 text-blue-800 border-blue-200';

                        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${classes}">
                                    ${status.charAt(0).toUpperCase() + status.slice(1)}
                                </span>`;
                    }
                },
                {
                    data: 'processed',
                    className: 'text-center',
                    render: function(data) {
                        if (data == 1 || data == true) {
                            return `<div class="flex justify-center">
                                        <i class="fas fa-check-circle text-emerald-500 text-lg" title="Processed to Attendance"></i>
                                    </div>`;
                        } else {
                            return `<div class="flex justify-center">
                                        <i class="fas fa-clock text-slate-300 text-lg" title="Pending Processing"></i>
                                    </div>`;
                        }
                    }
                },
                {
                    data: 'process_error',
                    render: function(data) {
                        if (data) {
                            // Trim manually if needed or just styling
                            return `<span class="text-xs text-red-600 font-medium bg-red-50 px-2 py-1 rounded border border-red-100 inline-block max-w-[200px] truncate" title="${data}">
                                        ${data}
                                    </span>`;
                        } else {
                            return `<span class="text-slate-300 text-xs">-</span>`;
                        }
                    }
                }
            ],
            columnDefs: [{
                targets: -1,
                orderable: false, // Error column
                searchable: false
            }]
        });

        // Prevent form submit and reload table instead
        $('form').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        // Handle reset button to just reset inputs and reload
        $('a[href*="reset"]').on('click', function(e) {
            e.preventDefault();
            $('form')[0].reset();
            table.ajax.reload();
        });
    });
</script>

<?= $this->endSection() ?>