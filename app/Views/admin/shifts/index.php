<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Manajemen Shift</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola jam shift sekolah</p>
        </div>
        <a href="<?= base_url('/admin/shifts/create') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">+ Tambah Shift</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table id="shiftsTable" class="w-full display align-middle">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Nama Shift</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Jam Kerja</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Deadline Masuk</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Jam Pulang</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Status</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-right uppercase text-xs tracking-wider pr-8">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($shifts as $shift): ?>
                        <tr class="hover:bg-slate-50 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900"><?= esc($shift['name']) ?></div>
                                <div class="text-xs text-slate-500 mt-1 line-clamp-1"><?= esc($shift['description']) ?></div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-slate-100 text-slate-800 font-mono border border-slate-200">
                                    <?= date('H:i', strtotime($shift['start_time'])) ?> - <?= date('H:i', strtotime($shift['end_time'])) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-red-50 text-red-700 font-mono border border-red-100">
                                    <?= date('H:i', strtotime($shift['checkin_deadline'])) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-green-50 text-green-700 font-mono border border-green-100">
                                    <?= date('H:i', strtotime($shift['checkout_earliest'])) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <?php if ($shift['is_active']): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="<?= base_url('/admin/shifts/' . $shift['id'] . '/edit') ?>"
                                        class="text-slate-400 hover:text-indigo-600 transition-colors p-1"
                                        title="Edit Shift">
                                        <i class="fas fa-edit fa-lg"></i>
                                    </a>
                                    <a href="<?= base_url('/admin/shifts/' . $shift['id'] . '/delete') ?>"
                                        onclick="return confirm('Yakin hapus shift ini?')"
                                        class="text-slate-400 hover:text-red-600 transition-colors p-1"
                                        title="Hapus Shift">
                                        <i class="fas fa-trash fa-lg"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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

    /* Additional button states */
    .dt-button.buttons-collection {
        background-color: #6366f1 !important;
    }

    .dt-button.buttons-collection:hover:not(.disabled) {
        background-color: #4f46e5 !important;
    }

    /* Table styling */
    #shiftsTable {
        border-collapse: collapse;
    }

    #shiftsTable thead {
        background-color: #f1f5f9;
    }

    #shiftsTable tbody tr {
        border-bottom: 1px solid #e2e8f0;
    }

    #shiftsTable tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Align last column (Aksi) to right */
    #shiftsTable thead th:last-child {
        text-align: right !important;
        padding-right: 2rem !important;
    }

    #shiftsTable tbody td:last-child {
        text-align: right !important;
        padding-right: 2rem !important;
    }

    /* Pagination */
    .dataTables_paginate {
        margin-top: 1rem;
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

    /* Info text */
    .dataTables_info {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 1rem;
    }

    /* Override untuk semua button tag di dalam DataTables */
    .dataTables_wrapper button {
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
    }

    .dataTables_wrapper button:hover {
        background-color: #4338ca !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dataTables_wrapper {
            padding: 0.75rem 0;
        }

        .dt-buttons {
            flex-direction: column;
            gap: 0.5rem;
        }

        .dt-button {
            width: 100%;
            justify-content: center;
        }

        .dataTables_length,
        .dataTables_filter {
            margin-bottom: 0.75rem;
        }

        #shiftsTable {
            font-size: 0.75rem;
        }

        #shiftsTable th {
            padding: 0.5rem 0.25rem !important;
        }

        #shiftsTable td {
            padding: 0.25rem !important;
        }
    }

    @media (max-width: 480px) {
        #shiftsTable {
            font-size: 0.65rem;
        }

        #shiftsTable th,
        #shiftsTable td {
            padding: 0.25rem !important;
        }

        .dt-button {
            padding: 0.375rem 0.5rem !important;
            font-size: 0.65rem !important;
        }
    }
</style>

<script>
    $(document).ready(function() {
        $('#shiftsTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            },
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'Semua']
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
            columnDefs: [{
                targets: -1,
                orderable: false,
                searchable: false
            }]
        });
    });
</script>

<?= $this->endSection() ?>