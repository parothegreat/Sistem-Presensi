<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800"><i class="fas fa-bullhorn mr-2 text-indigo-600"></i>Kelola Informasi <?= $type == 'teacher' ? 'Guru' : 'Siswa' ?></h1>
            <p class="text-slate-600">Kirim pengumuman ke <?= $type == 'teacher' ? 'guru' : 'siswa dan wali murid' ?></p>
        </div>
        <a href="<?= base_url('/admin/information/' . $type . '/create') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition shadow-md">
            <i class="fas fa-plus mr-2"></i>Buat Informasi
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Konten</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Via WA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($informations)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                <i class="fas fa-inbox text-4xl mb-3 text-slate-300"></i>
                                <p>Belum ada informasi yang dibuat</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($informations as $info): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-800">
                                    <?= esc($info['title']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-600 line-clamp-2 w-64"><?= esc($info['content']) ?></p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    <?php 
                                        $targets = json_decode($info['target_classes'], true); 
                                        $label = (isset($type) && $type == 'teacher') ? 'Guru' : 'Kelas';
                                        echo count($targets) . ' ' . $label;
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($info['send_via_wa']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Ya
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800">
                                            Tidak
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    <?= date('d M Y H:i', strtotime($info['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
