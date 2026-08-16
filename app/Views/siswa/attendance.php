<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Riwayat Absensi</h1>
            <p class="text-slate-600 mt-1">Tampilkan daftar kehadiran Anda. Gunakan filter bulan di dashboard untuk tampilan bulanan.</p>
        </div>
        <a href="<?= base_url('/siswa/dashboard') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">Kembali</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-lg font-bold text-slate-800">Daftar Absensi</h2>
        </div>

        <?php if (empty($attendance)): ?>
            <div class="p-6 text-center text-slate-600">Belum ada data absensi.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Tanggal</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Jam Masuk</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Jam Pulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($attendance as $att): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-600"><?= esc(date('d M Y', strtotime($att['date']))) ?></td>
                                <?php
                                $statusMap = [
                                    'on_time' => ['Hadir', 'bg-green-100 text-green-800'],
                                    'late' => ['Terlambat', 'bg-yellow-100 text-yellow-800'],
                                    'izin' => ['Izin', 'bg-blue-100 text-blue-800'],
                                    'sakit' => ['Sakit', 'bg-orange-100 text-orange-800'],
                                    'alpha' => ['Alpha', 'bg-red-100 text-red-800'],
                                ];
                                [$label, $cls] = $statusMap[$att['masuk_status']] ?? ['-', 'bg-gray-100 text-gray-800'];
                                ?>
                                <td class="px-6 py-4 text-sm"><span class="inline-block <?= $cls ?> px-3 py-1 rounded-full text-sm font-medium"><?= esc($label) ?></span></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?= $att['masuk_at'] ? esc(date('H:i', strtotime($att['masuk_at']))) : '-' ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?= $att['pulang_at'] ? esc(date('H:i', strtotime($att['pulang_at']))) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>