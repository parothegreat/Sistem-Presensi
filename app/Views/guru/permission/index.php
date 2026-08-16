<?= $this->extend('layouts/guru') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Daftar Pengajuan Izin</h1>
        <div class="text-sm text-slate-500">
            Kelas: <span class="font-semibold text-indigo-600"><?= esc($teacher['full_name']) ?> (Wali Kelas)</span>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-slate-50 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800">Menunggu Persetujuan</h2>
            <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded"><?= count($permissions) ?> Pending</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Bukti</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(!empty($permissions)): ?>
                        <?php foreach($permissions as $perm): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap">
                                    <?php 
                                    echo date('d M Y', strtotime($perm['date']));
                                    if (!empty($perm['end_date'])) {
                                        echo ' - ' . date('d M Y', strtotime($perm['end_date']));
                                    }
                                    ?>
                                    <div class="text-xs text-slate-400 mt-0.5">Diajukan: <?= date('d M H:i', strtotime($perm['created_at'])) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-slate-900"><?= esc($perm['full_name']) ?></div>
                                    <div class="text-xs text-slate-500">NIS: <?= esc($perm['nis']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="capitalize font-medium <?= $perm['status'] == 'sakit' ? 'text-orange-600' : 'text-blue-600' ?>"><?= $perm['status'] ?></span>
                                    <p class="text-xs text-slate-500 max-w-xs mt-1" title="<?= esc($perm['reason']) ?>"><?= esc($perm['reason']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if($perm['evidence']): ?>
                                        <a href="/uploads/permissions/<?= $perm['evidence'] ?>" target="_blank" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <i class="fas fa-image mr-1.5"></i> Lihat
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-2 whitespace-nowrap">
                                    <button onclick="confirmAction('/guru/permission/approve/<?= $perm['id'] ?>', 'Setujui izin ini? Absensi akan otomatis tercatat.')" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded-md text-xs font-semibold transition">
                                        <i class="fas fa-check mr-1"></i> Terima
                                    </button>
                                    <button onclick="confirmAction('/guru/permission/reject/<?= $perm['id'] ?>', 'Tolak pengajuan izin ini?')" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded-md text-xs font-semibold transition">
                                        <i class="fas fa-times mr-1"></i> Tolak
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <i class="fas fa-check-circle text-4xl text-slate-200 mb-3 block"></i>
                                <p>Tidak ada pengajuan izin yang menunggu persetujuan.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmAction(url, message) {
    if (confirm(message)) {
        window.location.href = url;
    }
}
</script>
<?= $this->endSection() ?>
