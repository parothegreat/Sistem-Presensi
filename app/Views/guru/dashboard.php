<?= $this->extend('layouts/guru') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-800">Dashboard Guru</h1>
        <?php if ($waliKelas): ?>
            <a href="/guru/attendance" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium">
                Lihat Absensi Kelas
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($informations)): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-indigo-50/50">
                <h2 class="text-lg font-bold text-slate-800 flex items-center">
                    <i class="fas fa-bullhorn mr-2 text-indigo-600"></i>
                    Pengumuman Terbaru
                </h2>
                <span class="text-xs font-medium bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full"><?= count($informations) ?> Info</span>
            </div>
            
            <div class="divide-y divide-slate-100">
                <?php foreach ($informations as $index => $info): ?>
                    <details class="group" <?= $index === 0 ? 'open' : '' ?>>
                        <summary class="flex justify-between items-center p-4 cursor-pointer hover:bg-slate-50 transition-colors list-none">
                            <div class="flex items-center gap-3">
                                <div class="text-indigo-600 transition-transform group-open:rotate-90">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                                <span class="font-semibold text-slate-700 group-open:text-indigo-700"><?= esc($info['title']) ?></span>
                            </div>
                            <span class="text-xs text-slate-400 whitespace-nowrap ml-2"><?= date('d M, H:i', strtotime($info['created_at'])) ?></span>
                        </summary>
                        <div class="px-4 pb-4 pl-9">
                            <div class="prose prose-sm max-w-none text-slate-600 bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <?php 
                                    $content = $info['content'];
                                    $content = str_replace('{nama}', esc($teacher['full_name']), $content);
                                    $content = str_replace('{kelas}', 'Guru', $content);
                                    echo nl2br(esc($content)); 
                                ?>
                            </div>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($waliKelas): ?>
        <!-- Wali Kelas Info Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Wali Kelas Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg shadow-md p-6 border-l-4 border-indigo-600">
                <p class="text-sm text-slate-600 font-medium">Wali Kelas</p>
                <h2 class="text-3xl font-bold text-slate-800 mt-2"><?= esc($waliKelas['class_name']) ?></h2>
            </div>

            <!-- Student Count Card -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg shadow-md p-6 border-l-4 border-green-600">
                <p class="text-sm text-slate-600 font-medium">Jumlah Siswa</p>
                <h3 class="text-3xl font-bold text-green-600 mt-2"><?= $studentsCount ?> Siswa</h3>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
            <a href="/guru/attendance" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition border-t-4 border-blue-500">
                <p class="text-slate-500 text-sm mb-2">Absensi Hari Ini</p>
                <div class="flex items-center gap-3">
                    <i class="fas fa-play text-blue-600 text-2xl"></i>
                    <h3 class="text-2xl font-bold text-slate-800">Mulai</h3>
                </div>
            </a>

            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
                <p class="text-slate-500 text-sm mb-2">Total Siswa</p>
                <div class="flex items-center gap-3">
                    <i class="fas fa-users text-green-600 text-2xl"></i>
                    <h3 class="text-2xl font-bold text-slate-800"><?= $studentsCount ?></h3>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-yellow-500">
                <p class="text-slate-500 text-sm mb-2">Status Anda</p>
                <div class="flex items-center gap-3">
                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        Wali Kelas Aktif
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-500">
                <p class="text-slate-500 text-sm mb-2">Jadwal Mata Pelajaran</p>
                <h3 class="text-sm text-slate-700 mt-2"><?= esc($teacher['subject'] ?? '-') ?></h3>
            </div>
        </div>

        <!-- Daftar Siswa Kelas -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-lg font-bold text-slate-800">Daftar Siswa di Kelas <?= esc($waliKelas['class_name']) ?></h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">No</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">NIS</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Username</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (! empty($students)): ?>
                            <?php foreach ($students as $index => $student): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm text-slate-600"><?= $index + 1 ?></td>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-800"><?= esc($student['nis']) ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-700"><?= esc($student['full_name']) ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?= esc($student['username'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="/guru/attendance/student/<?= $student['id'] ?>/history" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            Lihat Riwayat
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                    Belum ada siswa di kelas ini
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- No Wali Kelas Assigned -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 11-2 0 1 1 0 012 0zm5 0a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-yellow-800">
                        Anda belum ditugaskan sebagai wali kelas. Silakan hubungi admin untuk pengaturan lebih lanjut.
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Stats (without wali kelas) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Status Login</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1">Guru</h3>
                    </div>
                    <i class="fas fa-id-card text-indigo-500 text-4xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Tipe Pengguna</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1">Pengajar</h3>
                    </div>
                    <i class="fas fa-chalkboard-user text-blue-500 text-4xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div>
                    <p class="text-slate-500 text-sm">Bantuan</p>
                    <p class="text-sm text-slate-700 mt-2">Hubungi admin untuk bantuan</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>