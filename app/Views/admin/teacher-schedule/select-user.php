<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Set Jadwal Guru & Karyawan</h1>
            <p class="text-gray-600">Langkah 1 dari 2: Pilih Guru atau Karyawan</p>
        </div>

        <!-- Form -->
        <form id="selectUserForm" action="" method="GET" class="bg-white rounded-lg shadow-md p-6">
            <?= csrf_field() ?>

            <!-- Alert -->
            <?php if (session()->has('error')) : ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <!-- User Selection -->
            <div class="mb-6">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-2"></i>Pilih Guru atau Karyawan
                </label>
                <select
                    id="user_id"
                    name="user_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    required>
                    <option value="">-- Pilih --</option>

                    <?php if (!empty($users)) : ?>
                        <?php
                        $currentRole = '';
                        foreach ($users as $user) :
                            // Add optgroup separator
                            if ($user['role'] !== $currentRole) :
                                if ($currentRole !== '') : ?>
                                    </optgroup>
                                <?php endif;
                                $currentRole = $user['role'];
                                $roleLabel = ucfirst($user['role']); ?>
                                <optgroup label="<?= $roleLabel ?>">
                                <?php endif; ?>

                                <option value="<?= $user['id'] ?>">
                                    <?= esc($user['full_name'] ?? 'User #' . $user['id']) ?> (<?= ucfirst($user['role']) ?>)
                                </option>
                            <?php endforeach;
                        if ($currentRole !== '') : ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endif; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button
                    type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-right mr-2"></i>Lanjutkan
                </button>
                <a
                    href="<?= base_url('admin/teacher-schedule') ?>"
                    class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-center">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
            </div>
        </form>

        <!-- Info Card -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-bold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-2"></i>Informasi
            </h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Pilih guru atau karyawan dari daftar di atas</li>
                <li>• Setiap guru dapat memiliki jadwal berbeda untuk setiap hari</li>
                <li>• Karyawan biasanya memiliki jadwal tetap (07:00-16:00) setiap hari</li>
                <li>• Anda dapat menambah atau mengubah jadwal kapan saja</li>
            </ul>
        </div>

        <!-- Warning Card -->
        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="text-sm font-bold text-yellow-900 mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>⚠️ Perhatian Penting
            </h3>
            <ul class="text-sm text-yellow-800 space-y-1">
                <li>• Jika Anda membuat jadwal baru untuk guru/karyawan yang sudah ada, <strong>semua jadwal lama akan dihapus</strong></li>
                <li>• Jadwal yang dipilih akan sepenuhnya diganti dengan jadwal baru yang Anda set</li>
                <li>• Pastikan Anda yakin sebelum melanjutkan ke tahap berikutnya</li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.getElementById('selectUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const userId = document.getElementById('user_id').value;
        if (!userId) {
            alert('Pilih guru atau karyawan terlebih dahulu');
            return;
        }
        window.location.href = '<?= base_url("admin/teacher-schedule/set-schedule") ?>/' + userId;
    });
</script>

<?= $this->endSection() ?>