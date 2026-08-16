<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Cetak Kartu QR Code</h1>
            <p class="text-sm text-slate-500 mt-1">Cetak kartu QR code untuk absensi siswa</p>
        </div>
        <div class="flex gap-2">
            <form action="<?= base_url('/admin/qrcode/generate-all') ?>" method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Generate Semua QR Code</button>
            </form>
            <button onclick="printCards()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Cetak Kartu</button>
            <button onclick="printBackSide()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Cetak Belakang</button>
            <a href="<?= base_url('/admin/dashboard') ?>" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg">Kembali</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 p-3 bg-green-50 border border-green-100 text-green-700 rounded"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>


    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700 font-bold mb-1">Tips Mencetak:</p>
                <ul class="list-disc list-inside text-sm text-blue-700">
                    <li>Pastikan opsi <strong>Background Graphics</strong> dicentang pada dialog print browser.</li>
                    <li>Jika background kartu tidak muncul saat print, <strong>Tutup</strong> dialog print lalu klik tombol <strong>Cetak Kartu</strong> kembali.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-lg shadow p-6 mb-6">
        <div class="mb-4">
            <label class="block text-sm text-slate-600 mb-2">Filter Kelas</label>
            <select id="filterClass" class="border rounded px-3 py-2 w-full sm:w-64">
                <option value="">Semua Kelas</option>
                <?php
                $classes = array_unique(array_map(function ($s) {
                    return $s['class'];
                }, $students));
                sort($classes);
                foreach ($classes as $class):
                ?>
                    <option value="<?= $class ?>"><?= $class ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <p class="text-sm text-slate-600">Total: <strong><?= count($students) ?></strong> siswa</p>
    </div>

    <!-- Display QR codes -->
    <div id="displayContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php foreach ($students as $student): ?>
            <div class="bg-white border rounded p-4 text-center student-card group hover:shadow-lg transition-shadow" data-class="<?= $student['class'] ?>" data-nis="<?= $student['nis'] ?>" data-name="<?= $student['full_name'] ?>" data-photo="<?= $student['photo'] ?? '' ?>">
                <div class="mb-3 relative">
                    <?php if (!empty($student['qr_code_data'])): ?>
                        <img src="<?= $student['qr_code_data'] ?>" alt="QR Code" class="w-full h-32 object-contain" />
                    <?php else: ?>
                        <div class="w-full h-32 bg-slate-100 flex items-center justify-center text-xs text-slate-500">
                            No QR Code
                        </div>
                    <?php endif; ?>
                    <!-- Print Button (hidden until hover) -->
                    <button onclick="printSingleCard(this)" class="absolute inset-0 w-full h-32 bg-blue-600 bg-opacity-80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded hover:bg-opacity-90">
                        <span class="text-white font-semibold text-sm">CETAK QR CODE</span>
                    </button>
                </div>
                <div class="text-xs text-slate-700">
                    <div class="font-semibold"><?= esc($student['full_name']) ?></div>
                    <div class="text-slate-500"><?= esc($student['nis']) ?></div>
                    <div class="text-slate-500 text-xs"><?= esc($student['class']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Hidden print iframe -->
    <iframe id="printFrame" style="display: none;"></iframe>
</div>

<script>
    function filterByClass() {
        const selectedClass = document.getElementById('filterClass').value;
        const cards = document.querySelectorAll('.student-card');
        cards.forEach(card => {
            if (selectedClass === '' || card.dataset.class === selectedClass) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Pass PHP config to JS
    const templateConfig = <?= json_encode($templateConfig ?? null) ?>;
    const settings = <?= json_encode($settings ?? []) ?>;

    function generateCardStyle(config) {
        if (!config || !config.background_image) {
            // Default Style (Gradient)
            return {
                card: `width: 190px; height: 280px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 12px; text-align: center; position: relative; overflow: hidden; page-break-inside: avoid;`,
                qr: `margin: 0 auto; width: 130px; height: 130px; background: white; padding: 4px; border-radius: 4px; display:flex; justify-content:center; align-items:center;`,
                name: `color: white; font-weight: bold; font-size: 11px; margin-top: 8px;`,
                nis: `color: white; font-size: 9px; opacity: 0.9;`,
                class: `color: white; font-size: 8px; opacity: 0.8;`,
                useDefaultStructure: true
            };
        }

        // Custom Style (mm)
        const w = config.card_width || '85.6';
        const h = config.card_height || '53.98';

        const getTxtStyle = (c, bold = false) => {
            const align = c.align || 'center';
            let transform = 'translateX(-50%)'; // default center
            if (align === 'left') transform = 'none';
            if (align === 'right') transform = 'translateX(-100%)';

            const display = (c.visible === false) ? 'display: none;' : '';
            return `position: absolute; left: ${c.x}mm; top: ${c.y}mm; font-size: ${c.size}pt; color: ${c.color}; transform: ${transform}; width: 100%; text-align: ${align}; white-space: nowrap; ${bold ? 'font-weight: bold;' : ''}; ${display}`;
        };

        const getImgStyle = (c) => {
            const display = (c.visible === false) ? 'display: none;' : '';
            return `position: absolute; left: ${c.x}mm; top: ${c.y}mm; width: ${c.width || c.size}mm; height: ${c.height || c.size}mm; ${display}`;
        };

        return {
            card: `width: ${w}mm; height: ${h}mm; background-image: url('${config.background_image}'); background-size: cover; background-position: center; border-radius: 4px; position: relative; overflow: hidden; page-break-inside: avoid;`,
            qr: getImgStyle(config.qr) + 'background: white; padding: 1px;',
            name: getTxtStyle(config.name, true),
            nis: getTxtStyle(config.nis),
            class: getTxtStyle(config.class),
            photo: getImgStyle(config.photo) + 'object-fit: cover;',
            header: getTxtStyle(config.header, true),
            school_name: getTxtStyle(config.school_name, true),
            school_info: getTxtStyle(config.school_info),
            logo: getImgStyle(config.logo || {
                visible: false
            }) + 'object-fit: contain;',
            useDefaultStructure: false
        };
    }

    function printCards() {
        const selectedClass = document.getElementById('filterClass').value;
        const cards = document.querySelectorAll('.student-card');
        const style = generateCardStyle(templateConfig);

        let cardsHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
        cardsHtml += 'body { margin: 0; padding: 10mm; font-family: "Segoe UI", Arial, sans-serif; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }';
        cardsHtml += '@page { size: A4; margin: 0; }';
        cardsHtml += '.cards-grid { display: grid; grid-template-columns: repeat(auto-fill, ' + (templateConfig?.card_width || '85.6') + 'mm); gap: 5mm; }';
        cardsHtml += `.qr-card { ${style.card} box-shadow: none; border: 1px dotted #ccc; -webkit-print-color-adjust: exact; print-color-adjust: exact; }`;

        if (style.useDefaultStructure) {
            cardsHtml += '.cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10mm; }';
            cardsHtml += `.qr-card { ${style.card} box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: none; }`;
            cardsHtml += '.qr-card::before { content: ""; position: absolute; top: -50%; right: -50%; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; }';
            cardsHtml += '.qr-card::after { content: ""; position: absolute; bottom: -50%; left: -50%; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%; }';
            cardsHtml += '.qr-content { position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; }';
            cardsHtml += '.qr-header { background: rgba(255,255,255,0.2); padding: 6px; border-radius: 4px; margin-bottom: 6px; }';
            cardsHtml += '.qr-header-text { color: white; font-size: 7px; font-weight: bold; letter-spacing: 0.5px; margin: 0; }';
            cardsHtml += '.qr-image-wrapper { flex: 1; display: flex; align-items: center; justify-content: center; background: white; border-radius: 6px; margin-bottom: 8px; padding: 4px; }';
            cardsHtml += '.qr-image { width: 130px; height: 130px; }';
            cardsHtml += '.qr-text { color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.2); }';
            cardsHtml += '.qr-name { font-weight: 700; font-size: 11px; margin-bottom: 2px; line-height: 1.2; }';
            cardsHtml += '.qr-nis { font-size: 9px; font-weight: 600; margin-bottom: 1px; letter-spacing: 0.3px; opacity: 0.95; }';
            cardsHtml += '.qr-class { font-size: 8px; opacity: 0.85; }';
            cardsHtml += '.qr-footer { border-top: 1px solid rgba(255,255,255,0.3); padding-top: 4px; margin-top: 4px; font-size: 6px; opacity: 0.8; }';
        } else {
            // Custom styles inline
        }

        cardsHtml += 'h2 { margin: 0 0 3px 0; font-size: 20px; color: #333; }';
        cardsHtml += 'p { margin: 0 0 15px 0; font-size: 12px; color: #666; }';
        cardsHtml += '.header { text-align: center; margin-bottom: 20px; }';
        cardsHtml += '.school-name { font-weight: bold; letter-spacing: 0.5px; }';
        cardsHtml += '</style></head><body>';
        cardsHtml += '<div class="header"><div class="school-name">KARTU ABSENSI SISWA</div><h2>QR Code Attendance Card</h2><p>Tahun Ajaran 2025</p></div>';
        cardsHtml += '<div class="cards-grid">';

        cards.forEach((card) => {
            if (selectedClass === '' || card.dataset.class === selectedClass) {
                const qrImg = card.querySelector('img')?.src || '';
                const name = card.dataset.name || card.querySelector('.font-semibold')?.textContent || '';
                const nis = card.dataset.nis || card.querySelector('.text-slate-500')?.textContent || '';
                const cls = card.dataset.class || card.querySelectorAll('.text-slate-500')[1]?.textContent || '';
                const photoUrl = card.dataset.photo ? '<?= base_url() ?>/' + card.dataset.photo : '';

                cardsHtml += '<div class="qr-card">';

                if (style.useDefaultStructure) {
                    cardsHtml += '<div class="qr-content">';
                    cardsHtml += '<div class="qr-header"><p class="qr-header-text">ATTENDANCE</p></div>';
                    cardsHtml += '<div class="qr-image-wrapper">';
                    if (qrImg) cardsHtml += '<img src="' + qrImg + '" class="qr-image" alt="QR" />';
                    cardsHtml += '</div>';
                    cardsHtml += '<div class="qr-text">';
                    cardsHtml += '<div class="qr-name">' + name + '</div>';
                    cardsHtml += '<div class="qr-nis">NIS: ' + nis + '</div>';
                    cardsHtml += '<div class="qr-class">' + cls + '</div>';
                    cardsHtml += '<div class="qr-footer">Scan untuk absensi</div>';
                    cardsHtml += '</div>';
                    cardsHtml += '</div>';
                } else {
                    // Custom Template Structure
                    const c = templateConfig;

                    // Logo
                    if (c.logo && c.logo.visible !== false && c.logo.path) {
                        cardsHtml += `<img src="${c.logo.path}" style="${style.logo}">`;
                    }

                    // Header
                    if (settings.card_header_text) {
                        cardsHtml += `<div style="${style.header}">${settings.card_header_text}</div>`;
                    }
                    // School Name
                    if (settings.school_name) {
                        cardsHtml += `<div style="${style.school_name}">${settings.school_name}</div>`;
                    }
                    // School Info
                    if (settings.school_address || settings.school_phone || settings.school_email) {
                        const info = [settings.school_address, settings.school_phone, settings.school_email].filter(Boolean).join(' | ');
                        cardsHtml += `<div style="${style.school_info}">${info}</div>`;
                    }

                    // Photo
                    if (photoUrl) {
                        cardsHtml += `<img src="${photoUrl}" style="${style.photo}" onerror="this.style.display='none'">`;
                    }

                    // QR Code
                    if (qrImg) {
                        cardsHtml += `<div style="${style.qr}"><img src="${qrImg}" style="width:100%;height:100%;"></div>`;
                    }

                    // Student Data with Labels
                    const nameLabel = c.name.label || '';
                    cardsHtml += `<div style="${style.name}">${nameLabel + name}</div>`;

                    const nisLabel = c.nis.label || '';
                    cardsHtml += `<div style="${style.nis}">${nisLabel + nis}</div>`;

                    const classLabel = c.class.label || '';
                    cardsHtml += `<div style="${style.class}">${classLabel + cls}</div>`;
                }


                cardsHtml += '</div>';
            }
        });

        // Preload background image if exists
        const bgUrl = '${templateConfig?.background_image || '
        '}';
        cardsHtml += '<script>';
        cardsHtml += 'function doPrint() { window.print(); }';
        cardsHtml += 'if ("' + bgUrl + '") {';
        cardsHtml += '  var img = new Image();';
        cardsHtml += '  img.onload = doPrint;';
        cardsHtml += '  img.onerror = doPrint;';
        cardsHtml += '  img.src = "' + bgUrl + '";';
        // Add safety timeout
        cardsHtml += '  setTimeout(function() { if (!img.complete) doPrint(); }, 2000);';
        cardsHtml += '} else {';
        cardsHtml += '  window.onload = doPrint;';
        cardsHtml += '}';
        cardsHtml += '<\/script>';
        cardsHtml += '</body></html>';

        // Write to iframe
        const frame = document.getElementById('printFrame');
        frame.srcdoc = cardsHtml;
    }

    // Filter when selection changes
    document.getElementById('filterClass').addEventListener('change', filterByClass);

    function printSingleCard(button) {
        const card = button.closest('.student-card');
        const qrImg = card.querySelector('img')?.src || '';
        const name = card.dataset.name || '';
        const nis = card.dataset.nis || '';
        const cls = card.dataset.class || '';
        const photoUrl = card.dataset.photo ? '<?= base_url() ?>/' + card.dataset.photo : '';

        const style = generateCardStyle(templateConfig);

        let cardsHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
        cardsHtml += 'body { margin: 0; padding: 15px; font-family: "Segoe UI", Arial, sans-serif; background: white; }';
        cardsHtml += '@page { size: A4; margin: 0; }';
        cardsHtml += '.cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }';
        cardsHtml += `.qr-card { ${style.card} box-shadow: 0 4px 6px rgba(0,0,0,0.1); }`;

        // Add shared styles
        cardsHtml += 'h2 { margin: 0 0 3px 0; font-size: 20px; color: #333; }';
        cardsHtml += 'p { margin: 0 0 15px 0; font-size: 12px; color: #666; }';
        cardsHtml += '.header { text-align: center; margin-bottom: 20px; }';
        cardsHtml += '.school-name { font-weight: bold; letter-spacing: 0.5px; }';

        if (style.useDefaultStructure) {
            cardsHtml += '.qr-card::before { content: ""; position: absolute; top: -50%; right: -50%; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; }';
            cardsHtml += '.qr-card::after { content: ""; position: absolute; bottom: -50%; left: -50%; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%; }';
            cardsHtml += '.qr-content { position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; }';
            cardsHtml += '.qr-header { background: rgba(255,255,255,0.2); padding: 6px; border-radius: 4px; margin-bottom: 6px; }';
            cardsHtml += '.qr-header-text { color: white; font-size: 7px; font-weight: bold; letter-spacing: 0.5px; margin: 0; }';
            cardsHtml += '.qr-image-wrapper { flex: 1; display: flex; align-items: center; justify-content: center; background: white; border-radius: 6px; margin-bottom: 8px; padding: 4px; }';
            cardsHtml += '.qr-image { width: 130px; height: 130px; }';
            cardsHtml += '.qr-text { color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.2); }';
            cardsHtml += '.qr-name { font-weight: 700; font-size: 11px; margin-bottom: 2px; line-height: 1.2; }';
            cardsHtml += '.qr-nis { font-size: 9px; font-weight: 600; margin-bottom: 1px; letter-spacing: 0.3px; opacity: 0.95; }';
            cardsHtml += '.qr-class { font-size: 8px; opacity: 0.85; }';
            cardsHtml += '.qr-footer { border-top: 1px solid rgba(255,255,255,0.3); padding-top: 4px; margin-top: 4px; font-size: 6px; opacity: 0.8; }';
        }

        cardsHtml += '</style></head><body>';
        cardsHtml += '<div class="cards-grid">';

        cardsHtml += '<div class="qr-card">';
        if (style.useDefaultStructure) {
            cardsHtml += '<div class="qr-content">';
            cardsHtml += '<div class="qr-header"><p class="qr-header-text">ATTENDANCE</p></div>';
            cardsHtml += '<div class="qr-image-wrapper">';
            if (qrImg) cardsHtml += '<img src="' + qrImg + '" class="qr-image" alt="QR" />';
            cardsHtml += '</div>';
            cardsHtml += '<div class="qr-text">';
            cardsHtml += '<div class="qr-name">' + name + '</div>';
            cardsHtml += '<div class="qr-nis">NIS: ' + nis + '</div>';
            cardsHtml += '<div class="qr-class">' + cls + '</div>';
            cardsHtml += '<div class="qr-footer">Scan untuk absensi</div>';
            cardsHtml += '</div>';
            cardsHtml += '</div>';
        } else {
            // Custom Template Structure
            const c = templateConfig || {};

            // Logo
            if (c.logo && c.logo.visible !== false && c.logo.path) {
                cardsHtml += `<img src="${c.logo.path}" style="${style.logo}">`;
            }

            // Header
            if (settings.card_header_text) {
                cardsHtml += `<div style="${style.header}">${settings.card_header_text}</div>`;
            }
            // School Name
            if (settings.school_name) {
                cardsHtml += `<div style="${style.school_name}">${settings.school_name}</div>`;
            }
            // School Info
            if (settings.school_address || settings.school_phone || settings.school_email) {
                const info = [settings.school_address, settings.school_phone, settings.school_email].filter(Boolean).join(' | ');
                cardsHtml += `<div style="${style.school_info}">${info}</div>`;
            }

            // Photo
            if (photoUrl) {
                cardsHtml += `<img src="${photoUrl}" style="${style.photo}" onerror="this.style.display='none'">`;
            }

            // QR Code
            if (qrImg) {
                cardsHtml += `<div style="${style.qr}"><img src="${qrImg}" style="width:100%;height:100%;"></div>`;
            }

            // Student Data with Labels
            const nameLabel = c.name.label || '';
            cardsHtml += `<div style="${style.name}">${nameLabel + name}</div>`;

            const nisLabel = c.nis.label || '';
            cardsHtml += `<div style="${style.nis}">${nisLabel + nis}</div>`;

            const classLabel = c.class.label || '';
            cardsHtml += `<div style="${style.class}">${classLabel + cls}</div>`;
        }
        cardsHtml += '</div>';

        cardsHtml += '</div>';
        // Preload background image if exists
        const bgUrl = '${templateConfig?.background_image || '
        '}';
        cardsHtml += '<script>';
        cardsHtml += 'function doPrint() { window.print(); }';
        cardsHtml += 'if ("' + bgUrl + '") {';
        cardsHtml += '  var img = new Image();';
        cardsHtml += '  img.onload = doPrint;';
        cardsHtml += '  img.onerror = doPrint;';
        cardsHtml += '  img.src = "' + bgUrl + '";';
        // Add safety timeout
        cardsHtml += '  setTimeout(function() { if (!img.complete) doPrint(); }, 2000);';
        cardsHtml += '} else {';
        cardsHtml += '  window.onload = doPrint;';
        cardsHtml += '}';
        cardsHtml += '<\/script>';
        cardsHtml += '</body></html>';

        // Write to iframe
        const frame = document.getElementById('printFrame');
        frame.srcdoc = cardsHtml;
    }

    function printBackSide() {
        const cards = document.querySelectorAll('.student-card');
        const style = generateCardStyle(templateConfig);
        const backText = <?= json_encode($settings['card_back_text'] ?? "KETENTUAN:\n\n1. KARTU INI HARUS DIBAWA SAAT SEKOLAH\n2. JIKA MENEMUKAN KARTU INI HARAP DIKEMBALIKAN KE SEKOLAH\n3. DILARANG MENYALAHGUNAKAN KARTU INI") ?>;

        let cardsHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
        cardsHtml += 'body { margin: 0; padding: 10mm; font-family: "Segoe UI", Arial, sans-serif; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }';
        cardsHtml += '@page { size: A4; margin: 0; }';
        // Add direction: rtl to mirror the grid for double-sided printing registration
        cardsHtml += '.cards-grid { direction: rtl; display: grid; grid-template-columns: repeat(auto-fill, ' + (templateConfig?.card_width || '85.6') + 'mm); gap: 5mm; }';
        // Ensure card content is LTR even inside RTL grid
        const bgColor = <?= json_encode($settings['card_back_bg_color'] ?? '#ffffff') ?>;
        const txtColor = <?= json_encode($settings['card_back_text_color'] ?? '#333333') ?>;

        cardsHtml += `.qr-card { direction: ltr; ${style.card} box-shadow: none; border: 1px dotted #ccc; -webkit-print-color-adjust: exact; print-color-adjust: exact; display: flex; align-items: center; justify-content: center; text-align: center; background-image: none !important; background-color: ${bgColor} !important; }`;

        cardsHtml += `.back-content { color: ${txtColor}; padding: 20px; font-size: 10px; line-height: 1.6; font-weight: 600; white-space: pre-wrap; }`;

        // Use different style for default structure to match front shape
        if (style.useDefaultStructure) {
            cardsHtml += '.cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10mm; }';
            cardsHtml += `.qr-card { ${style.card} box-shadow: none; border: none; }`;
            cardsHtml += '.qr-card::before { content: ""; position: absolute; top: -50%; right: -50%; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; }';
            cardsHtml += '.qr-card::after { content: ""; position: absolute; bottom: -50%; left: -50%; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%; }';
        }

        cardsHtml += 'h2 { margin: 0 0 3px 0; font-size: 20px; color: #333; }';
        cardsHtml += 'p { margin: 0 0 15px 0; font-size: 12px; color: #666; }';
        cardsHtml += '.header { text-align: center; margin-bottom: 20px; visibility: hidden; }'; // Hidden for back side but keeps space
        cardsHtml += '.school-name { font-weight: bold; letter-spacing: 0.5px; }';

        cardsHtml += '</style></head><body>';
        cardsHtml += '<div class="header"><div class="school-name">KARTU ABSENSI SISWA</div><h2>QR Code Attendance Card</h2><p>Tahun Ajaran 2025</p></div>';

        cardsHtml += '<div class="cards-grid">';

        cards.forEach((card) => {
            cardsHtml += '<div class="qr-card">';
            cardsHtml += '<div class="back-content">';
            // Add Logo if exists in config
            // if (templateConfig?.logo?.path && templateConfig?.logo?.visible !== false) {
            //      cardsHtml += `<img src="${templateConfig.logo.path}" style="width: 40px; margin-bottom: 10px; opacity: 0.9;">`;   
            // }
            cardsHtml += backText;
            cardsHtml += '</div>';
            cardsHtml += '</div>';
        });

        cardsHtml += '</div>';

        // Preload background image if exists
        const bgUrl = '${templateConfig?.background_image || '
        '}';
        cardsHtml += '<script>';
        cardsHtml += 'function doPrint() { window.print(); }';
        cardsHtml += 'if ("' + bgUrl + '") {';
        cardsHtml += '  var img = new Image();';
        cardsHtml += '  img.onload = doPrint;';
        cardsHtml += '  img.onerror = doPrint;';
        cardsHtml += '  img.src = "' + bgUrl + '";';
        cardsHtml += '  setTimeout(function() { if (!img.complete) doPrint(); }, 2000);';
        cardsHtml += '} else {';
        cardsHtml += '  window.onload = doPrint;';
        cardsHtml += '}';
        cardsHtml += '<\/script>';
        cardsHtml += '</body></html>';

        // Write to iframe
        const frame = document.getElementById('printFrame');
        frame.srcdoc = cardsHtml;
    }
</script>

<?= $this->endSection() ?>