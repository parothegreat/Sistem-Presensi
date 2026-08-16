<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Kelola User</h1>
            <p class="text-slate-500 text-sm mt-1">Manage admin, guru, dan siswa</p>
        </div>
        <a href="<?= base_url('/admin/users/create') ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            + Tambah User
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Filter Buttons -->
    <div class="flex gap-2 mb-6">
        <a href="<?= base_url('/admin/users') ?>" class="px-3 py-2 rounded text-sm <?= !$currentRole ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' ?>">
            Semua User
        </a>
        <a href="<?= base_url('/admin/users?role=admin') ?>" class="px-3 py-2 rounded text-sm <?= $currentRole === 'admin' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' ?>">
            Admin
        </a>
        <a href="<?= base_url('/admin/users?role=guru') ?>" class="px-3 py-2 rounded text-sm <?= $currentRole === 'guru' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' ?>">
            Guru
        </a>
        <a href="<?= base_url('/admin/users?role=siswa') ?>" class="px-3 py-2 rounded text-sm <?= $currentRole === 'siswa' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' ?>">
            Siswa
        </a>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table id="usersTable" class="w-full display align-middle">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">User</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Role</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Status</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Dibuat</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-right uppercase text-xs tracking-wider pr-8">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50 transition duration-150">
                            <!-- User Column (Avatar + Username) -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full border border-slate-200" src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=f1f5f9&color=64748b&bold=true" alt="<?= esc($user['username']) ?>">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-slate-900"><?= esc($user['username']) ?></div>
                                        <!-- Subtext could go here if we had email/name -->
                                    </div>
                                </div>
                            </td>

                            <!-- Role Column -->
                            <td class="px-6 py-4">
                                <?php
                                $roleBg = [
                                    'admin' => 'bg-red-100 text-red-800 border-red-200',
                                    'guru' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'siswa' => 'bg-green-100 text-green-800 border-green-200'
                                ];
                                $roleLabel = ucfirst($user['role']);
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $roleBg[$user['role']] ?? 'bg-gray-100 text-gray-800 border-gray-200' ?>">
                                    <?= $roleLabel ?>
                                </span>
                            </td>

                            <!-- Status Column -->
                            <td class="px-6 py-4">
                                <?php if ($user['is_active']): ?>
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

                            <td class="px-6 py-4 text-sm text-slate-500 font-mono">
                                <?= $user['created_at'] ? date('d/m/Y', strtotime($user['created_at'])) : '-' ?>
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="<?= base_url('/admin/users/' . $user['id'] . '/edit') ?>"
                                        class="text-slate-400 hover:text-indigo-600 transition-colors p-1"
                                        title="Edit User">
                                        <i class="fas fa-edit fa-lg"></i>
                                    </a>
                                    <a href="<?= base_url('/admin/users/' . $user['id'] . '/delete') ?>"
                                        class="text-slate-400 hover:text-red-600 transition-colors p-1"
                                        onclick="return confirm('Yakin hapus user ini?')"
                                        title="Hapus User">
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
    #usersTable {
        border-collapse: collapse;
    }

    #usersTable thead {
        background-color: #f1f5f9;
    }

    #usersTable tbody tr {
        border-bottom: 1px solid #e2e8f0;
    }

    #usersTable tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Align last column (Aksi) to right */
    #usersTable thead th:last-child {
        text-align: right !important;
        padding-right: 2rem !important;
    }

    #usersTable tbody td:last-child {
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

        #usersTable {
            font-size: 0.75rem;
        }

        #usersTable th {
            padding: 0.5rem 0.25rem !important;
        }

        #usersTable td {
            padding: 0.25rem !important;
        }
    }

    @media (max-width: 480px) {
        #usersTable {
            font-size: 0.65rem;
        }

        #usersTable th,
        #usersTable td {
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
        $('#usersTable').DataTable({
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

<?php $this->endSection() ?>