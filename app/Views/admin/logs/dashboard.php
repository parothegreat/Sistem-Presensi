<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800"><i class="fas fa-chart-bar mr-2 text-indigo-600"></i>Dashboard Logs</h1>
        <p class="text-slate-600">Monitor push dari mesin finger dan notifikasi semua channel</p>
    </div>

    <!-- Statistics Cards - 4 columns -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Biometric Push Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600 font-medium">Biometric Push</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2"><?= $biometricStats['today'] ?? 0 ?></p>
                    <p class="text-xs text-slate-500 mt-1">Hari ini</p>
                </div>
                <div class="text-4xl text-indigo-100"><i class="fas fa-microchip text-indigo-300"></i></div>
            </div>
        </div>

        <!-- Android Notifications Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600 font-medium">Android Pending</p>
                    <p class="text-3xl font-bold text-green-600 mt-2"><?= $notificationStats['android']['pending'] ?? 0 ?></p>
                    <p class="text-xs text-slate-500 mt-1">Belum dikirim</p>
                </div>
                <div class="text-4xl text-green-100"><i class="fab fa-android text-green-300"></i></div>
            </div>
        </div>

        <!-- Telegram Notifications Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600 font-medium">Telegram Pending</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2"><?= $notificationStats['telegram']['pending'] ?? 0 ?></p>
                    <p class="text-xs text-slate-500 mt-1">Belum dikirim</p>
                </div>
                <div class="text-4xl text-blue-100"><i class="fab fa-telegram text-blue-300"></i></div>
            </div>
        </div>

        <!-- WhatsApp Notifications Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-emerald-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600 font-medium">WhatsApp Pending</p>
                    <p class="text-3xl font-bold text-emerald-600 mt-2"><?= $notificationStats['whatsapp']['pending'] ?? 0 ?></p>
                    <p class="text-xs text-slate-500 mt-1">Belum dikirim</p>
                </div>
                <div class="text-4xl text-emerald-100"><i class="fab fa-whatsapp text-emerald-300"></i></div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4"><i class="fas fa-link mr-2 text-slate-600"></i>Akses Log Detail</h3>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="<?= base_url('/admin/logs/biometric') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg p-4 transition flex items-center gap-3 group">
                <i class="fas fa-microchip text-2xl group-hover:scale-110 transition"></i>
                <span class="font-medium">Biometric Logs</span>
            </a>
            <a href="<?= base_url('/admin/logs/android') ?>" class="bg-green-600 hover:bg-green-700 text-white rounded-lg p-4 transition flex items-center gap-3 group">
                <i class="fab fa-android text-2xl group-hover:scale-110 transition"></i>
                <span class="font-medium">Android Push</span>
            </a>
            <a href="<?= base_url('/admin/logs/telegram') ?>" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg p-4 transition flex items-center gap-3 group">
                <i class="fab fa-telegram text-2xl group-hover:scale-110 transition"></i>
                <span class="font-medium">Telegram</span>
            </a>
            <a href="<?= base_url('/admin/logs/whatsapp') ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg p-4 transition flex items-center gap-3 group">
                <i class="fab fa-whatsapp text-2xl group-hover:scale-110 transition"></i>
                <span class="font-medium">WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Recent Biometric Logs -->
    <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-white"><i class="fas fa-history mr-2"></i>Biometric Push Terbaru</h3>
        </div>
        <div class="p-6">
            <?php if (!empty($recentBiometricLogs)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="text-left py-3 px-4 font-semibold text-slate-700">Waktu</th>
                                <th class="text-left py-3 px-4 font-semibold text-slate-700">Device</th>
                                <th class="text-left py-3 px-4 font-semibold text-slate-700">User</th>
                                <th class="text-center py-3 px-4 font-semibold text-slate-700">Type</th>
                                <th class="text-center py-3 px-4 font-semibold text-slate-700">Status</th>
                                <th class="text-center py-3 px-4 font-semibold text-slate-700">Processed</th>
                                <th class="text-left py-3 px-4 font-semibold text-slate-700">Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBiometricLogs as $log): ?>
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="py-3 px-4 text-slate-600">
                                        <div class="font-medium"><?= date('H:i:s', strtotime($log['timestamp'] ?? $log['created_at'])) ?></div>
                                        <div class="text-xs text-slate-400"><?= date('d M', strtotime($log['timestamp'] ?? $log['created_at'])) ?></div>
                                    </td>
                                    <td class="py-3 px-4"><code class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-600"><?= $log['device_id'] ?? 'N/A' ?></code></td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($log['user_name'])): ?>
                                            <div class="font-medium text-indigo-700"><?= esc($log['user_name']) ?></div>
                                            <div class="text-xs text-slate-500"><?= ucfirst($log['user_role'] ?? 'User') ?></div>
                                        <?php else: ?>
                                            <span class="text-slate-400 font-mono text-xs"><?= $log['user_id'] ?? 'N/A' ?></span>
                                            <div class="text-xs text-slate-400 italic">Unidentified</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="bg-slate-100 text-slate-500 px-2 py-1 rounded text-xs">
                                            <?= strtoupper($log['biometric_type'] ?? 'FINGER') ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php
                                        $statusClass = match ($log['status'] ?? 'checkin') {
                                            'checkin' => 'bg-green-100 text-green-800',
                                            'checkout' => 'bg-orange-100 text-orange-800',
                                            'breakout' => 'bg-yellow-100 text-yellow-800',
                                            'breakin' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-slate-100 text-slate-800'
                                        };
                                        ?>
                                        <span class="<?= $statusClass ?> px-3 py-1 rounded-full text-xs font-medium">
                                            <?= ucfirst($log['status'] ?? 'Checkin') ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($log['processed']): ?>
                                            <i class="fas fa-check-circle text-green-500 text-lg" title="Processed"></i>
                                        <?php else: ?>
                                            <i class="fas fa-clock text-slate-300 text-lg" title="Pending"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-red-600">
                                        <?= !empty($log['process_error']) ? esc($log['process_error']) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-slate-500 text-center py-8">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Notifications - 2 columns -->
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Android Notifications -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-white"><i class="fab fa-android mr-2"></i>Android Notifikasi Terbaru</h3>
            </div>
            <div class="p-6">
                <?php if (!empty($recentAndroidNotifs)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Waktu</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentAndroidNotifs, 0, 5) as $notif): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                                        <td class="py-3 px-4 text-slate-600"><?= date('H:i:s', strtotime($notif['created_at'])) ?></td>
                                        <td class="py-3 px-4">
                                            <?php
                                            $statusClass = match ($notif['notification_status'] ?? 'pending') {
                                                'sent' => 'bg-green-100 text-green-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                default => 'bg-yellow-100 text-yellow-800'
                                            };
                                            ?>
                                            <span class="<?= $statusClass ?> px-3 py-1 rounded-full text-xs font-medium">
                                                <?= ucfirst($notif['notification_status'] ?? 'Pending') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">Tidak ada data</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Telegram Notifications -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-white"><i class="fab fa-telegram mr-2"></i>Telegram Notifikasi Terbaru</h3>
            </div>
            <div class="p-6">
                <?php if (!empty($recentTelegramNotifs)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Waktu</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentTelegramNotifs, 0, 5) as $notif): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                                        <td class="py-3 px-4 text-slate-600"><?= date('H:i:s', strtotime($notif['created_at'])) ?></td>
                                        <td class="py-3 px-4">
                                            <?php
                                            $statusClass = match ($notif['status'] ?? 'pending') {
                                                'sent' => 'bg-green-100 text-green-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                default => 'bg-yellow-100 text-yellow-800'
                                            };
                                            ?>
                                            <span class="<?= $statusClass ?> px-3 py-1 rounded-full text-xs font-medium">
                                                <?= ucfirst($notif['status'] ?? 'Pending') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">Tidak ada data</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>