<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800"><i class="fas fa-calendar-check mr-3"></i>Kelola Kegiatan</h1>
            <p class="text-slate-500 text-sm mt-1">Manage kegiatan dan absensi khusus</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= base_url('/admin/activities/create') ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center gap-2 font-medium">
                <i class="fas fa-plus"></i> Tambah Kegiatan
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6 flex items-start">
            <i class="fas fa-check-circle mr-3 mt-0.5 flex-shrink-0"></i>
            <div><?= session()->getFlashdata('success') ?></div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6 flex items-start">
            <i class="fas fa-exclamation-circle mr-3 mt-0.5 flex-shrink-0"></i>
            <div><?= session()->getFlashdata('error') ?></div>
        </div>
    <?php endif; ?>

    <!-- Activities Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table id="activitiesTable" class="w-full display align-middle">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Kegiatan</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Waktu</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Target</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-left uppercase text-xs tracking-wider">Status</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 text-right uppercase text-xs tracking-wider pr-8">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($activities as $activity): ?>
                        <tr class="hover:bg-slate-50 transition duration-150">
                            <!-- Kegiatan Column -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900"><?= esc($activity['name']) ?></div>
                                <div class="text-xs text-slate-500 mt-1 line-clamp-2"><?= esc(substr($activity['description'] ?? '', 0, 100)) ?></div>
                            </td>

                            <!-- Waktu Column -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center text-sm font-medium text-slate-700">
                                        <i class="fas fa-calendar-alt text-slate-400 mr-1.5 w-4"></i>
                                        <?= date('d M Y', strtotime($activity['start_time'])) ?>
                                    </span>
                                    <span class="inline-flex items-center text-xs text-slate-500 font-mono">
                                        <i class="fas fa-clock text-slate-400 mr-1.5 w-4"></i>
                                        <?= date('H:i', strtotime($activity['start_time'])) ?> - <?= date('H:i', strtotime($activity['end_time'])) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Target Peserta Column -->
                            <td class="px-6 py-4">
                                <?php
                                $target = json_decode($activity['target_audience'] ?? '{}', true);
                                $hasReligion = !empty($target['religion']);
                                $hasClass = !empty($target['class']);

                                if (!$hasReligion && !$hasClass) {
                                    echo '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-500 italic">Semua / Tanpa Filter</span>';
                                } else {
                                    echo '<div class="flex flex-col gap-1.5">';

                                    if ($hasReligion) {
                                        echo '<div class="flex flex-wrap gap-1">';
                                        foreach ($target['religion'] as $r) {
                                            echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">' . $r . '</span>';
                                        }
                                        echo '</div>';
                                    }

                                    if ($hasClass) {
                                        echo '<div class="flex flex-wrap gap-1">';
                                        $countClass = count($target['class']);
                                        if ($countClass > 3) {
                                            echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-50 text-gray-600 border border-gray-200" title="' . implode(', ', $target['class']) . '">' . $countClass . ' Kelas</span>';
                                        } else {
                                            foreach ($target['class'] as $c) {
                                                echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-50 text-gray-600 border border-gray-200">' . $c . '</span>';
                                            }
                                        }
                                        echo '</div>';
                                    }

                                    echo '</div>';
                                }
                                ?>
                            </td>

                            <!-- Status Column -->
                            <td class="px-6 py-4">
                                <?php
                                $now = time();
                                $start = strtotime($activity['start_time']);
                                $end = strtotime($activity['end_time']);

                                $displayStatus = $activity['status'];
                                $displayLabel = ucfirst($activity['status']);

                                // Dynamic Status Calculation
                                if ($activity['status'] === 'scheduled') {
                                    if ($now < $start) {
                                        $displayStatus = 'scheduled';
                                        $displayLabel = 'Terjadwal';
                                    } elseif ($now >= $start && $now <= $end) {
                                        $displayStatus = 'ongoing';
                                        $displayLabel = 'Berlangsung';
                                    } else {
                                        $displayStatus = 'completed';
                                        $displayLabel = 'Selesai';
                                    }
                                } elseif ($activity['status'] === 'ongoing') {
                                    $displayLabel = 'Berlangsung';
                                } elseif ($activity['status'] === 'completed') {
                                    $displayLabel = 'Selesai';
                                } elseif ($activity['status'] === 'cancelled') {
                                    $displayLabel = 'Dibatalkan';
                                }

                                $statusClass = 'bg-slate-100 text-slate-800 border-slate-200';
                                if ($displayStatus === 'ongoing') $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200 animate-pulse';
                                if ($displayStatus === 'completed') $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                if ($displayStatus === 'cancelled') $statusClass = 'bg-rose-100 text-rose-800 border-rose-200';
                                if ($displayStatus === 'scheduled') $statusClass = 'bg-amber-100 text-amber-800 border-amber-200';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $statusClass ?>">
                                    <?= $displayLabel ?>
                                </span>
                            </td>

                            <!-- Aksi Column -->
                            <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="<?= base_url('/admin/activities/' . $activity['id'] . '/edit') ?>"
                                        class="text-slate-400 hover:text-amber-600 transition-colors p-1"
                                        title="Edit">
                                        <i class="fas fa-edit fa-lg"></i>
                                    </a>
                                    <a href="<?= base_url('/admin/activities/' . $activity['id']) ?>"
                                        class="text-slate-400 hover:text-blue-600 transition-colors p-1"
                                        title="Detail">
                                        <i class="fas fa-eye fa-lg"></i>
                                    </a>
                                    <a href="<?= base_url('/admin/activities/' . $activity['id'] . '/delete') ?>"
                                        class="text-slate-400 hover:text-red-600 transition-colors p-1"
                                        onclick="return confirm('Yakin hapus kegiatan ini?')"
                                        title="Hapus">
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

<!-- DataTables CSS & JS (Same as student index) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<style>
    /* Reuse DataTables styling from admin layout/student index */
    .dataTables_wrapper {
        padding: 1.5rem 0;
    }

    .dataTables_length,
    .dataTables_filter {
        margin-bottom: 1rem;
        padding: 0 1.5rem;
    }

    .dataTables_wrapper input,
    .dataTables_wrapper select {
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
    }
</style>

<script>
    $(document).ready(function() {
        $('#activitiesTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            pageLength: 10,
            columnDefs: [{
                targets: -1,
                orderable: false,
                searchable: false
            }]
        });
    });
</script>
<?php $this->endSection() ?>