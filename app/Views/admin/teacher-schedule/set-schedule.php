<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Set Jadwal Guru & Karyawan</h1>
            <p class="text-gray-600">
                Langkah 2 dari 2: Set Jadwal untuk
                <span class="font-bold text-blue-600"><?= esc($user['full_name']) ?></span>
                (<?= ucfirst($user['role']) ?>)
            </p>
        </div>

        <!-- Alert -->
        <?php if (session()->has('error')) : ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= session('error') ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?= base_url('admin/teacher-schedule/save-schedule/' . $user['id']) ?>" method="POST" class="bg-white rounded-lg shadow-md p-6">
            <?= csrf_field() ?>

            <!-- Days Schedule Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <?php for ($hari = 1; $hari <= 7; $hari++) :
                    $dayName = $days[$hari];
                    $existing = $schedulesByDay[$hari] ?? null;
                    $isActive = !empty($existing) && !empty($existing['jam_masuk']);
                ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <!-- Day Header with Checkbox -->
                        <div class="mb-4">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="hari_<?= $hari ?>_active"
                                    class="day-toggle w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                    data-day="<?= $hari ?>"
                                    <?= $isActive ? 'checked' : '' ?>>
                                <span class="ml-3 font-bold text-gray-800 text-lg">
                                    <?= $dayName ?>
                                </span>
                            </label>
                        </div>

                        <!-- Time Inputs -->
                        <div class="space-y-3">
                            <!-- Jam Masuk -->
                            <div>
                                <label for="hari_<?= $hari ?>_jam_masuk" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-clock text-green-600 mr-1"></i>Jam Masuk
                                </label>
                                <input
                                    type="text"
                                    id="hari_<?= $hari ?>_jam_masuk"
                                    name="hari_<?= $hari ?>_jam_masuk"
                                    placeholder="HH:MM"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 time-input"
                                    value="<?= $existing['jam_masuk'] ?? '' ?>"
                                    pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                    inputmode="numeric"
                                    maxlength="5"
                                    <?= $isActive ? '' : 'disabled' ?>>
                                <small class="text-gray-600">Format: HH:MM (00:00 - 23:59), contoh: 07:00, 14:30</small>
                            </div>

                            <!-- Jam Pulang -->
                            <div>
                                <label for="hari_<?= $hari ?>_jam_pulang" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-clock text-red-600 mr-1"></i>Jam Pulang
                                </label>
                                <input
                                    type="text"
                                    id="hari_<?= $hari ?>_jam_pulang"
                                    name="hari_<?= $hari ?>_jam_pulang"
                                    placeholder="HH:MM"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 time-input"
                                    value="<?= $existing['jam_pulang'] ?? '' ?>"
                                    pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                    inputmode="numeric"
                                    maxlength="5"
                                    <?= $isActive ? '' : 'disabled' ?>>
                                <small class="text-gray-600">Format: HH:MM (00:00 - 23:59), contoh: 16:00, 17:30</small>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold status-badge"
                                data-day="<?= $hari ?>"
                                style="background-color: #e5e7eb; color: #6b7280;">
                                <i class="fas fa-ban mr-1"></i>Tidak Aktif
                            </span>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button
                    type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Simpan Jadwal
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
                <i class="fas fa-info-circle mr-2"></i>Panduan
            </h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>✓ Centang checkbox untuk hari yang dijadwalkan</li>
                <li>✓ Isi waktu masuk dan pulang <strong>dalam format 24 jam (HH:MM)</strong></li>
                <li>✓ Contoh: 07:00 (pagi), 14:30 (siang), 16:00 (sore)</li>
                <li>✓ Input waktu akan otomatis diaktifkan saat checkbox dicentang</li>
                <li>✓ Hari tanpa jadwal tidak perlu diisi (tidak aktif)</li>
                <li>✓ Klik "Simpan Jadwal" untuk menyimpan perubahan</li>
            </ul>
        </div>
    </div>
</div>

<!-- JavaScript for Toggle Time Inputs -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.day-toggle');

        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const dayNum = this.dataset.day;
                const jamMasukInput = document.getElementById(`hari_${dayNum}_jam_masuk`);
                const jamPulangInput = document.getElementById(`hari_${dayNum}_jam_pulang`);
                const statusBadge = document.querySelector(`.status-badge[data-day="${dayNum}"]`);

                if (this.checked) {
                    jamMasukInput.disabled = false;
                    jamPulangInput.disabled = false;
                    statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Aktif';
                    statusBadge.style.backgroundColor = '#dcfce7';
                    statusBadge.style.color = '#166534';
                } else {
                    jamMasukInput.disabled = true;
                    jamPulangInput.disabled = true;
                    jamMasukInput.value = '';
                    jamPulangInput.value = '';
                    statusBadge.innerHTML = '<i class="fas fa-ban mr-1"></i>Tidak Aktif';
                    statusBadge.style.backgroundColor = '#e5e7eb';
                    statusBadge.style.color = '#6b7280';
                }
            });

            // Initialize badges on page load
            const dayNum = toggle.dataset.day;
            const statusBadge = document.querySelector(`.status-badge[data-day="${dayNum}"]`);
            if (toggle.checked) {
                statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Aktif';
                statusBadge.style.backgroundColor = '#dcfce7';
                statusBadge.style.color = '#166534';
            }
        });

        // Format time values on page load (strip seconds if exists)
        document.querySelectorAll('.time-input').forEach(input => {
            if (input.value && input.value.length > 5) {
                // Strip seconds: 08:00:00 -> 08:00
                input.value = input.value.substring(0, 5);
            }
        });

        // Form submit handler to validate and format times
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const timePattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;

                document.querySelectorAll('.time-input').forEach(input => {
                    if (!input.disabled && input.value) {
                        // Format: strip seconds if submitted with HH:MM:SS
                        if (input.value.length > 5) {
                            input.value = input.value.substring(0, 5);
                        }
                        // Validate format
                        if (!timePattern.test(input.value)) {
                            isValid = false;
                            input.classList.add('border-red-500');
                            alert(`Format waktu tidak valid: ${input.value}. Gunakan format HH:MM (contoh: 07:00)`);
                        } else {
                            input.classList.remove('border-red-500');
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>