<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Template Notifikasi</h1>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Template</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview Isi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $currentChannel = '';
                    foreach ($templates as $template) : 
                        // Separator row for channel grouping
                        if ($currentChannel !== $template['channel']):
                            $currentChannel = $template['channel'];
                    ?>
                        <tr class="bg-gray-100">
                            <td colspan="5" class="px-6 py-2 font-bold text-gray-700 uppercase text-xs">
                                <?php 
                                    switch($currentChannel) {
                                        case 'whatsapp': echo '📱 WhatsApp'; break;
                                        case 'telegram': echo '✈️ Telegram'; break;
                                        case 'android': echo '🤖 Android App'; break;
                                        default: echo $currentChannel;
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                            <?= $template['channel'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= esc($template['name']) ?></div>
                            <div class="text-xs text-gray-500"><?= esc($template['code']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="truncate max-w-xs" title="<?= esc($template['content']) ?>">
                                <?= esc(substr($template['content'], 0, 50)) . (strlen($template['content']) > 50 ? '...' : '') ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($template['is_active']) : ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            <?php else : ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?= base_url('admin/notification-templates/' . $template['id'] . '/edit') ?>" class="text-indigo-600 hover:text-indigo-900 font-bold">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
