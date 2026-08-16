<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Pengajuan Izin / Sakit</h1>
        <a href="/siswa/dashboard" class="text-indigo-600 hover:text-indigo-800 font-medium">&larr; Kembali ke Dashboard</a>
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

    <div class="grid grid-cols-1 gap-6">
        <!-- Form Pengajuan -->
        <div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Form Pengajuan</h2>
                <form action="/siswa/permission/submit" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Dari Tanggal</label>
                            <input type="date" name="date" id="date" class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= old('date', date('Y-m-d')) ?>" required>
                            <?php if($validation->hasError('date')): ?>
                                <p class="text-red-500 text-xs mt-1"><?= $validation->getError('date') ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">Sampai Tanggal <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="date" name="end_date" id="end_date" class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= old('end_date') ?>">
                            <p class="text-[10px] text-slate-400 mt-1">Isi jika izin lebih dari 1 hari.</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Keterangan</label>
                        <select name="status" id="status" class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                            <option value="sakit" <?= old('status') == 'sakit' ? 'selected' : '' ?>>Sakit</option>
                            <option value="izin" <?= old('status') == 'izin' ? 'selected' : '' ?>>Izin</option>
                        </select>
                        <?php if($validation->hasError('status')): ?>
                            <p class="text-red-500 text-xs mt-1"><?= $validation->getError('status') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="evidence" class="block text-sm font-medium text-slate-700 mb-1">Bukti Foto / Surat</label>
                        <input type="file" name="evidence" id="evidence" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                        <p class="text-xs text-slate-400 mt-1">Maks. 2MB (JPG/PNG)</p>
                        <?php if($validation->hasError('evidence')): ?>
                            <p class="text-red-500 text-xs mt-1"><?= $validation->getError('evidence') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-6">
                        <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">Alasan</label>
                        <textarea name="reason" id="reason" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Jelaskan alasan izin/sakit..." required><?= old('reason') ?></textarea>
                        <?php if($validation->hasError('reason')): ?>
                            <p class="text-red-500 text-xs mt-1"><?= $validation->getError('reason') ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 transition font-medium">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>

        <!-- Riwayat Pengajuan -->
        <div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b bg-slate-50">
                    <h2 class="text-lg font-bold text-slate-800">Riwayat Pengajuan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Ket.</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(!empty($permissions)): ?>
                                <?php foreach($permissions as $perm): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            <?php 
                                            // Format: 03 Jan - 05 Jan 2026 (Common year merged)
                                            $start = strtotime($perm['date']);
                                            $end = !empty($perm['end_date']) ? strtotime($perm['end_date']) : null;
                                            
                                            if ($end) {
                                                if (date('Y', $start) == date('Y', $end)) {
                                                    echo date('d M', $start) . ' - ' . date('d M Y', $end);
                                                } else {
                                                    echo date('d M Y', $start) . ' - ' . date('d M Y', $end);
                                                }
                                            } else {
                                                echo date('d M Y', $start);
                                            }
                                            ?>
                                            <div class="text-xs text-slate-400 mt-1">Diajukan: <?= date('d M H:i', strtotime($perm['created_at'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="capitalize font-medium <?= $perm['status'] == 'sakit' ? 'text-orange-600' : 'text-blue-600' ?>"><?= $perm['status'] ?></span>
                                            <p class="text-xs text-slate-500 truncate max-w-xs" title="<?= esc($perm['reason']) ?>"><?= esc($perm['reason']) ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?php
                                                $badges = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'approved' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800'
                                                ];
                                                $labels = [
                                                    'pending' => 'Menunggu',
                                                    'approved' => 'Disetujui',
                                                    'rejected' => 'Ditolak'
                                                ];
                                            ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badges[$perm['approval_status']] ?>">
                                                <?= $labels[$perm['approval_status']] ?>
                                            </span>
                                            <?php if($perm['approved_at']): ?>
                                                <div class="text-[10px] text-slate-400 mt-1"><?= date('d M', strtotime($perm['approved_at'])) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?php if($perm['evidence']): ?>
                                                <a href="/uploads/permissions/<?= $perm['evidence'] ?>" target="_blank" class="text-indigo-600 hover:underline text-xs">
                                                    Lihat Foto
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500 text-sm">Belum ada riwayat pengajuan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
