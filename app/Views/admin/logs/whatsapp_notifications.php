<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800"><i class="fab fa-whatsapp mr-2 text-emerald-600"></i>WhatsApp Notifications Logs</h1>
            <p class="text-slate-600">Monitor push notifikasi ke WhatsApp</p>
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
                <input type="date" name="date_from" id="date_from" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-600"
                    value="<?= $filters['date_from'] ?? '' ?>">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-slate-700 mb-1">Sampai:</label>
                <input type="date" name="date_to" id="date_to" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-600"
                    value="<?= $filters['date_to'] ?? '' ?>">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status:</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-600">
                    <option value="">-- Semua --</option>
                    <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="<?= base_url('/admin/logs/whatsapp') ?>" class="bg-slate-300 hover:bg-slate-400 text-slate-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-white"><i class="fas fa-history mr-2"></i>Daftar WhatsApp Notifications Logs</h3>
        </div>
        <div class="p-6">
            <?php if (!empty($logs)): ?>
                <div class="overflow-x-auto">
                    <table id="whatsappTable" class="w-full display">
                        <thead class="bg-slate-100 border-b">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">Waktu Buat</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">Nomor HP</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">Pesan</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-700">Attempts</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-700">Status</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">Sent At</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-700">Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-6xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500 text-lg">Tidak ada data logs</p>
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
        background-color: #059669 !important;
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
        background-color: #047857 !important;
    }
</style>

<script>
    $(document).ready(function() {
        var table = $('#whatsappTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('admin/logs/whatsapp-json') ?>',
                data: function(d) {
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                    d.status = $('#status').val();
                }
            },
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            },
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
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
                    render: function(data) {
                        return data ? new Date(data).toLocaleString('id-ID') : '-';
                    }
                },
                {
                    data: 'phone_number',
                    render: function(data) {
                        return `<code class="text-xs bg-slate-100 px-2 py-1 rounded">${data || 'N/A'}</code>`;
                    }
                },
                {
                    data: 'message',
                    render: function(data) {
                        return `<div class="max-w-md truncate" title="${data}">${data}</div>`;
                    }
                },
                {
                    data: 'attempts',
                    className: 'text-center'
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        var status = data || 'pending';
                        var classes = 'bg-yellow-100 text-yellow-800';
                        if (status === 'sent') classes = 'bg-green-100 text-green-800';
                        else if (status === 'failed') classes = 'bg-red-100 text-red-800';

                        return `<span class="${classes} px-3 py-1 rounded-full text-xs font-medium">
                                    ${status.charAt(0).toUpperCase() + status.slice(1)}
                                </span>`;
                    }
                },
                {
                    data: 'sent_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleString('id-ID') : '-';
                    }
                },
                {
                    data: 'updated_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleString('id-ID') : '-';
                    }
                }
            ],
            columnDefs: [{
                targets: -1,
                orderable: false,
                searchable: false
            }]
        });

        $('form').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        $('a[href*="whatsapp"]').on('click', function(e) {
            if ($(this).attr('href').endsWith('whatsapp')) {
                e.preventDefault();
                $('form')[0].reset();
                table.ajax.reload();
            }
        });
    });
</script>

<?= $this->endSection() ?>