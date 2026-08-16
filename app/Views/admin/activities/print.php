<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Absensi - <?= esc($activity['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table {
                font-size: 12px;
            }

            h1,
            h2 {
                color: black !important;
            }
        }
    </style>
</head>

<body class="bg-white p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= esc($activity['name']) ?></h1>
                <p class="text-gray-600 mt-1"><?= esc($activity['description']) ?></p>
            </div>
            <div class="text-right text-sm text-gray-600">
                <p>Waktu: <strong><?= date('d M Y H:i', strtotime($activity['start_time'])) ?></strong></p>
                <p>Filter Kelas: <strong><?= $filterClass ?: 'Semua Kelas' ?></strong></p>
            </div>
        </div>

        <div class="mb-4">
            <button onclick="window.print()" class="no-print bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-medium text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Halaman
            </button>
        </div>

        <!-- Table -->
        <table class="w-full border-collapse border border-gray-300 text-left text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-3 py-2 w-10 text-center">No</th>
                    <th class="border border-gray-300 px-3 py-2">Nama Siswa</th>
                    <th class="border border-gray-300 px-3 py-2 w-24">Kelas</th>
                    <th class="border border-gray-300 px-3 py-2 w-24 text-center">Masuk</th>
                    <th class="border border-gray-300 px-3 py-2 w-24 text-center">Keluar</th>
                    <th class="border border-gray-300 px-3 py-2 w-24 text-center">Status</th>
                    <th class="border border-gray-300 px-3 py-2 w-32">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Map attendance by student_id
                $attMap = [];
                foreach ($attendance as $a) $attMap[$a['student_id']] = $a;

                $no = 1;
                foreach ($participants as $p):
                    $att = $attMap[$p['student_id']] ?? null;
                    $status = $att ? $att['status'] : '';

                    $statusLabel = '';
                    if ($status == 'present') $statusLabel = 'Hadir';
                    elseif ($status == 'late') $statusLabel = 'Terlambat';
                    elseif ($status == 'permission') $statusLabel = 'Izin';
                    elseif ($status == 'sick') $statusLabel = 'Sakit';
                    elseif ($status == 'absent') $statusLabel = 'Alpha';
                ?>
                    <tr>
                        <td class="border border-gray-300 px-3 py-2 text-center"><?= $no++ ?></td>
                        <td class="border border-gray-300 px-3 py-2 font-medium"><?= esc($p['full_name']) ?></td>
                        <td class="border border-gray-300 px-3 py-2"><?= esc($p['class']) ?></td>
                        <td class="border border-gray-300 px-3 py-2 text-center text-xs font-mono">
                            <?= ($att && $att['check_in_time']) ? date('H:i', strtotime($att['check_in_time'])) : '-' ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-center text-xs font-mono">
                            <?= ($att && $att['check_out_time']) ? date('H:i', strtotime($att['check_out_time'])) : '-' ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-center font-semibold">
                            <?= $statusLabel ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-xs text-gray-500">
                            <!-- Helper column for manual signature or notes -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="mt-12 flex justify-end">
            <div class="text-center w-64">
                <p class="mb-16">Mengetahui,</p>
                <div class="border-b border-black w-full mb-2"></div>
                <p class="font-bold">Penanggung Jawab</p>
            </div>
        </div>
    </div>
</body>

</html>