<?php $this->extend('layouts/admin'); ?>

<?php $this->section('content'); ?>
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-slate-800 mb-2"><i class="fas fa-book mr-3"></i>Help & Tutorial</h1>
        <p class="text-lg text-slate-600">Panduan lengkap menggunakan sistem Presensi Sekolah</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-8 flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/help#telegram') ?>" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold transition hover:bg-indigo-700">
            <i class="fas fa-paper-plane mr-2"></i>Telegram Setup
        </a>
        <a href="<?= base_url('/admin/help#webhook') ?>" class="px-4 py-2 rounded-lg bg-rose-600 text-white font-semibold transition hover:bg-rose-700">
            <i class="fas fa-link mr-2"></i>Webhook
        </a>
        <a href="<?= base_url('/admin/help#qrcode') ?>" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-semibold transition hover:bg-slate-300">
            <i class="fas fa-qrcode mr-2"></i>QR Code
        </a>
        <a href="<?= base_url('/admin/help#scanner') ?>" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-semibold transition hover:bg-slate-300">
            <i class="fas fa-camera mr-2"></i>Scanner
        </a>
        <a href="<?= base_url('/admin/help#absensi') ?>" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-semibold transition hover:bg-slate-300">
            <i class="fas fa-check-circle mr-2"></i>Absensi
        </a>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Telegram Setup Section -->
        <div class="p-8" id="telegram">
            <h2 class="text-3xl font-bold text-indigo-600 mb-6"><i class="fas fa-paper-plane mr-2"></i>Panduan Setup Notifikasi Telegram</h2>

            <!-- Step 1 -->
            <div class="mb-8 border-l-4 border-indigo-600 pl-6">
                <div class="flex items-center mb-3">
                    <div class="bg-indigo-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">1</div>
                    <h3 class="text-2xl font-bold text-slate-800">Buka Bot Telegram</h3>
                </div>
                <p class="text-slate-700 mb-4">Cari dan buka bot Telegram kami di aplikasi Telegram Anda:</p>
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-4">
                    <p class="text-center text-lg font-mono font-bold text-indigo-600"><?= $bot_username ?></p>
                    <p class="text-center text-sm text-slate-600 mt-2">Atau klik link di bawah</p>
                    <div class="text-center mt-3">
                        <a href="https://t.me/notifpresensi_bot" target="_blank" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-external-link-alt mr-2"></i>Buka Bot di Telegram
                        </a>
                    </div>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                    <p class="text-sm text-slate-700"><i class="fas fa-lightbulb mr-2"></i><strong>Tips:</strong> Pastikan Anda sudah menginstall aplikasi Telegram di HP</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="mb-8 border-l-4 border-indigo-600 pl-6">
                <div class="flex items-center mb-3">
                    <div class="bg-indigo-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">2</div>
                    <h3 class="text-2xl font-bold text-slate-800">Kirim Pesan /start</h3>
                </div>
                <p class="text-slate-700 mb-4">Setelah membuka bot, kirimkan pesan <strong>/start</strong> untuk mendaftar:</p>
                <div class="bg-slate-50 border border-slate-300 rounded-lg p-4 mb-4">
                    <div class="font-mono text-slate-800 mb-3">
                        <div class="text-sm text-slate-500">Anda mengirim:</div>
                        <div class="bg-white border border-slate-200 rounded p-2 text-blue-600 font-semibold">/start</div>
                    </div>
                </div>
                <div class="bg-green-50 border-l-4 border-green-500 p-4">
                    <p class="text-sm text-slate-700"><i class="fas fa-check mr-2"></i><strong>Berhasil!</strong> Bot akan mengirimkan panduan cara link dengan siswa</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="mb-8 border-l-4 border-indigo-600 pl-6">
                <div class="flex items-center mb-3">
                    <div class="bg-indigo-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">3</div>
                    <h3 class="text-2xl font-bold text-slate-800">Hubungkan dengan NIS Siswa</h3>
                </div>
                <p class="text-slate-700 mb-4">Bot akan meminta NIS siswa dan PIN yang sudah ditetapkan. Format pesan:</p>
                <div class="bg-slate-50 border border-slate-300 rounded-lg p-4 mb-4 space-y-3">
                    <div class="font-mono text-slate-800">
                        <div class="text-sm text-slate-500 mb-2">Format:</div>
                        <div class="bg-white border border-slate-200 rounded p-3 text-blue-600 font-semibold text-center text-lg">/link NIS PIN</div>
                    </div>
                    <hr class="my-3">
                    <div class="font-mono text-slate-800">
                        <div class="text-sm text-slate-500 mb-2">Contoh (jika NIS = S123456 dan PIN = 1234):</div>
                        <div class="bg-white border border-slate-200 rounded p-3 text-blue-600 font-semibold text-center text-lg">/link S123456 1234</div>
                    </div>
                </div>
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-4">
                    <p class="text-sm text-slate-700"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Penting:</strong> Setiap siswa harus mengirim pesan dengan format <code class="bg-amber-100 px-2 py-1 rounded">/link NIS PIN</code> untuk link akun mereka</p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="mb-8 border-l-4 border-indigo-600 pl-6">
                <div class="flex items-center mb-3">
                    <div class="bg-indigo-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">4</div>
                    <h3 class="text-2xl font-bold text-slate-800">Terima Notifikasi Absensi</h3>
                </div>
                <p class="text-slate-700 mb-4">Setelah link berhasil, siswa akan menerima notifikasi otomatis ketika:</p>
                <div class="grid gap-4 md:grid-cols-2 mb-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="font-semibold text-green-800 mb-2"><i class="fas fa-sign-in-alt mr-2"></i>Absensi Masuk</h4>
                        <p class="text-sm text-slate-700">Notifikasi dikirim dengan:</p>
                        <ul class="text-sm text-slate-700 mt-2 space-y-1">
                            <li><i class="fas fa-check-circle mr-1"></i> Nama siswa</li>
                            <li><i class="fas fa-check-circle mr-1"></i> Jam masuk</li>
                            <li><i class="fas fa-check-circle mr-1"></i> Status (Tepat Waktu/Terlambat)</li>
                        </ul>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <h4 class="font-semibold text-orange-800 mb-2"><i class="fas fa-sign-out-alt mr-2"></i>Absensi Pulang</h4>
                        <p class="text-sm text-slate-700">Notifikasi dikirim dengan:</p>
                        <ul class="text-sm text-slate-700 mt-2 space-y-1">
                            <li><i class="fas fa-check-circle mr-1"></i> Nama siswa</li>
                            <li><i class="fas fa-check-circle mr-1"></i> Jam pulang</li>
                            <li><i class="fas fa-check-circle mr-1"></i> Status (Tepat Waktu/Lebih Awal)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Admin Setup -->
            <div class="mb-8 border-l-4 border-purple-600 pl-6 bg-purple-50 p-6 rounded-lg">
                <h3 class="text-2xl font-bold text-purple-800 mb-4"><i class="fas fa-cog mr-2"></i>Setup Admin (Telegram PIN)</h3>
                <p class="text-slate-700 mb-4">Sebagai admin, Anda perlu menetapkan PIN yang akan digunakan siswa untuk link akun:</p>
                <a href="<?= base_url('/admin/telegram-settings') ?>" class="inline-block bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700 transition">
                    <i class="fas fa-cog mr-2"></i>Kelola PIN Telegram
                </a>
            </div>

            <!-- Setup Bot Sendiri -->
            <div class="mb-8 border-l-4 border-cyan-600 pl-6 bg-cyan-50 p-6 rounded-lg">
                <h3 class="text-2xl font-bold text-cyan-800 mb-4"><i class="fas fa-robot mr-2"></i>Menggunakan Bot Telegram Sendiri</h3>
                <p class="text-slate-700 mb-4">Jika Anda ingin menggunakan bot Telegram milik Anda sendiri, ikuti langkah-langkah berikut:</p>

                <!-- Step 1 -->
                <div class="mb-6 bg-white rounded-lg p-4 border border-cyan-200">
                    <h4 class="font-semibold text-cyan-800 mb-3"><i class="fas fa-plus-circle mr-2"></i>1. Buat Bot Baru di BotFather</h4>
                    <ol class="text-sm text-slate-700 space-y-2 ml-4 list-decimal">
                        <li>Buka <a href="https://t.me/BotFather" target="_blank" class="text-cyan-600 hover:underline font-semibold">@BotFather</a> di Telegram</li>
                        <li>Kirim pesan <code class="bg-cyan-100 px-2 py-1 rounded">/newbot</code></li>
                        <li>Ikuti petunjuk untuk memberi nama dan username bot</li>
                        <li>BotFather akan memberikan <strong>Bot Token</strong> (simpan baik-baik!)</li>
                    </ol>
                </div>

                <!-- Step 2 -->
                <div class="mb-6 bg-white rounded-lg p-4 border border-cyan-200">
                    <h4 class="font-semibold text-cyan-800 mb-3"><i class="fas fa-file-code mr-2"></i>2. Update Bot Token di File .env</h4>
                    <p class="text-sm text-slate-700 mb-3">Buka file <code class="bg-cyan-100 px-2 py-1 rounded">.env</code> di root folder project:</p>
                    <div class="bg-slate-900 text-white rounded-lg p-4 font-mono text-sm mb-3">
                        <div class="text-cyan-300">TELEGRAM_BOT_TOKEN=YOUR_TELEGRAM_BOT_TOKEN</div>
                    </div>
                    <p class="text-sm text-slate-700">Ganti dengan Bot Token yang baru dari BotFather</p>
                </div>

                <!-- Step 3 -->
                <div class="mb-6 bg-white rounded-lg p-4 border border-cyan-200">
                    <h4 class="font-semibold text-cyan-800 mb-3"><i class="fas fa-plug mr-2"></i>3. Setup Webhook (Opsional)</h4>
                    <p class="text-sm text-slate-700 mb-3">Jika ingin Telegram push notification ke server (webhook mode):</p>
                    <ol class="text-sm text-slate-700 space-y-2 ml-4 list-decimal">
                        <li>Bot harus memiliki webhook URL public (HTTPS)</li>
                        <li>Set webhook di BotFather dengan URL: <code class="bg-cyan-100 px-1 rounded">https://domain-anda.com/telegram/webhook</code></li>
                        <li>Sistem ini akan automatic menerima update dari Telegram</li>
                    </ol>
                </div>

                <!-- Step 4 -->
                <div class="mb-6 bg-white rounded-lg p-4 border border-cyan-200">
                    <h4 class="font-semibold text-cyan-800 mb-3"><i class="fas fa-redo mr-2"></i>4. Restart Application</h4>
                    <p class="text-sm text-slate-700">Setelah update .env file:</p>
                    <ul class="text-sm text-slate-700 space-y-2 ml-4 list-disc mt-2">
                        <li>Restart web server atau PHP-FPM</li>
                        <li>Clear browser cache (Ctrl+F5)</li>
                        <li>Bot baru sudah siap digunakan!</li>
                    </ul>
                </div>

                <!-- Important Notes -->
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded">
                    <h4 class="font-semibold text-amber-800 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Catatan Penting:</h4>
                    <ul class="text-sm text-slate-700 space-y-1">
                        <li>• Bot Token adalah rahasia - jangan share ke orang lain</li>
                        <li>• Jika token bocor, buat bot baru dan ganti tokennya</li>
                        <li>• Simpan Bot Token di file .env, jangan di code</li>
                        <li>• Setiap kali ganti bot, siswa yang lama harus re-link dengan bot baru</li>
                        <li>• Data telegram_chat_id di database akan otomatis update saat siswa /start ke bot baru</li>
                    </ul>
                </div>
            </div>

            <!-- Troubleshooting -->
            <div class="mb-8 border-l-4 border-red-600 pl-6">
                <h3 class="text-2xl font-bold text-red-600 mb-4"><i class="fas fa-exclamation-circle mr-2"></i>Troubleshooting</h3>
                <div class="space-y-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-semibold text-red-800 mb-2"><i class="fas fa-bell-slash mr-2"></i>Notifikasi tidak diterima</h4>
                        <ul class="text-sm text-slate-700 space-y-1">
                            <li>• Pastikan siswa sudah melakukan /start terlebih dahulu</li>
                            <li>• Siswa mengirim pesan dengan format: <code class="bg-red-100 px-1 rounded">/link NIS PIN</code></li>
                            <li>• Periksa bahwa telegram_chat_id sudah tersimpan di database</li>
                            <li>• Tunggu beberapa detik, notifikasi di-queue dan dikirim per menit</li>
                        </ul>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-800 mb-2"><i class="fas fa-robot mr-2"></i>Bot tidak merespon</h4>
                        <ul class="text-sm text-slate-700 space-y-1">
                            <li>• Pastikan Telegram bot sudah aktif (check di BotFather)</li>
                            <li>• Coba ketik /start lagi</li>
                            <li>• Cek koneksi internet di HP Anda</li>
                        </ul>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h4 class="font-semibold text-amber-800 mb-2"><i class="fas fa-key mr-2"></i>Lupa PIN</h4>
                        <ul class="text-sm text-slate-700 space-y-1">
                            <li>• Admin bisa change PIN kapan saja di Telegram Settings</li>
                            <li>• Siswa bisa link ulang dengan NIS + PIN yang baru</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Webhook Section -->
        <div class="p-8 border-t" id="webhook">
            <h2 class="text-3xl font-bold text-rose-600 mb-6"><i class="fas fa-link mr-2"></i>Panduan Register Webhook Telegram</h2>

            <p class="text-slate-700 mb-4">Webhook adalah URL yang Telegram gunakan untuk mengirim pesan ke bot Anda. Ini <strong>WAJIB</strong> dilakukan agar bot bisa menerima pesan dari siswa!</p>

            <!-- Apa itu Webhook -->
            <div class="mb-6 bg-white rounded-lg p-4 border border-rose-200">
                <h4 class="font-semibold text-rose-800 mb-2"><i class="fas fa-question-circle mr-2"></i>Apa itu Webhook?</h4>
                <p class="text-sm text-slate-700 mb-3">Webhook adalah mekanisme Telegram untuk mengirim pesan <strong>langsung</strong> ke server Anda saat ada pesan masuk.</p>
                <div class="bg-slate-50 border border-slate-300 rounded p-3 text-sm text-slate-700">
                    <strong>Tanpa Webhook:</strong><br />
                    ❌ Bot tidak bisa terima pesan dari user<br />
                    ❌ Siswa kirim /link tapi bot tidak terima
                </div>
                <div class="bg-green-50 border border-green-300 rounded p-3 text-sm text-slate-700 mt-3">
                    <strong>Dengan Webhook (Sekarang):</strong><br />
                    ✅ Telegram kirim pesan langsung ke bot<br />
                    ✅ Bot bisa reply dengan cepat<br />
                    ✅ Siswa bisa link akun dengan /link command
                </div>
            </div>

            <!-- Kapan Register -->
            <div class="mb-6 bg-white rounded-lg p-4 border border-rose-200">
                <h4 class="font-semibold text-rose-800 mb-3"><i class="fas fa-calendar mr-2"></i>Kapan Harus Register?</h4>
                <div class="space-y-2 text-sm text-slate-700">
                    <div class="flex items-start gap-3">
                        <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap">WAJIB</span>
                        <span><strong>First Time Setup</strong> - Saat pertama kali setup bot</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap">WAJIB</span>
                        <span><strong>Ganti Domain</strong> - Saat upload ke domain production</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap">WAJIB</span>
                        <span><strong>Bot Baru</strong> - Saat buat/ganti bot Telegram baru</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap">TIDAK</span>
                        <span><strong>Deploy Code</strong> - Saat deploy/update code saja (tidak perlu re-register)</span>
                    </div>
                </div>
            </div>

            <!-- Step-by-step -->
            <div class="mb-6">
                <h4 class="font-semibold text-rose-800 mb-4"><i class="fas fa-list-ol mr-2"></i>Cara Register Webhook (Otomatis)</h4>

                <!-- Step 1 -->
                <div class="mb-4 bg-white rounded-lg p-4 border border-rose-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="bg-rose-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm">1</div>
                        <h5 class="font-semibold text-rose-800"><i class="fas fa-shield-alt mr-2"></i>Verify Domain Anda</h5>
                    </div>
                    <p class="text-sm text-slate-700 ml-11 mb-3">Pastikan domain Anda sudah setup dengan HTTPS (SSL Certificate):</p>
                    <div class="ml-11 bg-slate-50 border border-slate-300 rounded p-3">
                        <p class="text-sm text-slate-700 font-semibold"><i class="fas fa-check mr-2"></i>Domain harus:</p>
                        <ul class="text-sm text-slate-700 list-disc ml-5 mt-2 space-y-1">
                            <li>Bisa diakses dari internet (public URL)</li>
                            <li>Punya HTTPS/SSL Certificate (secure)</li>
                            <li>Tidak localhost atau IP internal (kecuali pakai tunnel/ngrok)</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="mb-4 bg-white rounded-lg p-4 border border-rose-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="bg-rose-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm">2</div>
                        <h5 class="font-semibold text-rose-800"><i class="fas fa-bars mr-2"></i>Buka Menu Registrasi</h5>
                    </div>
                    <p class="text-sm text-slate-700 ml-11 mb-3">Akses menu registrasi di panel admin:</p>
                    <div class="ml-11 bg-slate-50 border border-slate-300 rounded p-3">
                         <p class="text-sm text-slate-700">Klik <strong>Master</strong> &rarr; <strong>Registrasi Webhook</strong></p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="mb-4 bg-white rounded-lg p-4 border border-rose-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="bg-rose-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm">3</div>
                        <h5 class="font-semibold text-rose-800"><i class="fas fa-check-circle mr-2"></i>Klik Register</h5>
                    </div>
                    <p class="text-sm text-slate-700 ml-11 mb-3">Klik tombol <strong>"Register Webhook Sekarang"</strong>.</p>
                    <div class="ml-11 space-y-3">
                        <div class="bg-green-50 border border-green-300 rounded p-3">
                            <p class="text-sm font-semibold text-green-800 mb-2"><i class="fas fa-check-circle mr-2"></i>Jika Berhasil (Status SUCCESS):</p>
                            <ul class="text-sm text-slate-700 list-disc ml-5 space-y-1">
                                <li>Akan muncul notifikasi sukses</li>
                                <li>Sekarang bot Anda sudah terhubung ke aplikasi ini!</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alternative: Verify via API -->
            <div class="mb-6 bg-white rounded-lg p-4 border border-rose-200">
                <h4 class="font-semibold text-rose-800 mb-3"><i class="fas fa-search mr-2"></i>Verify Webhook Status (Optional)</h4>
                <p class="text-sm text-slate-700 mb-3">Setelah register, Anda bisa verify status webhook dengan buka URL ini:</p>
                <div class="bg-slate-900 text-white rounded-lg p-4 font-mono text-sm overflow-x-auto mb-3">
                    <span class="text-cyan-300">https://api.telegram.org/bot[TOKEN]/getWebhookInfo</span>
                </div>
                <p class="text-sm text-slate-700 mb-3">Ganti <code class="bg-slate-100 px-2 py-1 rounded">[TOKEN]</code> dengan Bot Token Anda.</p>
                <p class="text-sm text-slate-700">Akan muncul JSON response yang menunjukkan:</p>
                <ul class="text-sm text-slate-700 list-disc ml-5 mt-2 space-y-1">
                    <li><code class="bg-slate-100 px-1 rounded">"url": "https://domain-anda.com/telegram/webhook"</code> - Webhook URL</li>
                    <li><code class="bg-slate-100 px-1 rounded">"pending_update_count": 0</code> - Tidak ada pending</li>
                    <li><code class="bg-slate-100 px-1 rounded">"last_error_message": null</code> - Tidak ada error</li>
                </ul>
            </div>

            <!-- Common Issues -->
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded">
                <h4 class="font-semibold text-amber-800 mb-3"><i class="fas fa-lightbulb mr-2"></i>Troubleshooting Webhook:</h4>
                <div class="space-y-3 text-sm text-slate-700">
                    <div>
                        <p class="font-semibold text-amber-800"><i class="fas fa-question-circle mr-2"></i>"Unauthorized" Error?</p>
                        <p class="ml-4">→ Bot Token di .env salah atau sudah expired. Generate token baru di BotFather</p>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-800"><i class="fas fa-question-circle mr-2"></i>"Domain tidak bisa diakses" Error?</p>
                        <p class="ml-4">→ Cek apakah domain punya SSL Certificate (HTTPS) dan bisa diakses dari internet</p>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-800"><i class="fas fa-question-circle mr-2"></i>Bot masih tidak terima pesan setelah register?</p>
                        <p class="ml-4">→ Tunggu 1-2 menit, coba /start command lagi di Telegram bot</p>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-800"><i class="fas fa-question-circle mr-2"></i>Perlu register ulang?</p>
                        <p class="ml-4">→ Hanya jika ganti domain atau bot baru. Jika deploy code saja, tidak perlu re-register</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="p-8 border-t" id="qrcode">
            <h2 class="text-3xl font-bold text-blue-600 mb-6"><i class="fas fa-qrcode mr-2"></i>Panduan QR Code</h2>

            <!-- Info Box -->
            <div class="mb-8 bg-indigo-50 border-l-4 border-indigo-500 p-6 rounded">
                <h3 class="text-xl font-bold text-indigo-800 mb-3"><i class="fas fa-info-circle mr-2"></i>QR Code Siswa</h3>
                <p class="text-slate-700">Setiap siswa memiliki QR Code unik yang berisi NIS mereka. QR Code ini digunakan untuk absensi cepat di scanner.</p>
            </div>

            <!-- Features -->
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-slate-800 mb-4"><i class="fas fa-star mr-2"></i>Fitur QR Code</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="bg-slate-50 border border-slate-300 rounded-lg p-4">
                        <div class="font-semibold text-slate-800 mb-2"><i class="fas fa-print mr-2"></i>Print QR Code</div>
                        <p class="text-sm text-slate-700">Cetak QR Code untuk ditempel di ID card siswa</p>
                    </div>
                    <div class="bg-slate-50 border border-slate-300 rounded-lg p-4">
                        <div class="font-semibold text-slate-800 mb-2"><i class="fas fa-eye mr-2"></i>Lihat QR Code</div>
                        <p class="text-sm text-slate-700">Tampilkan QR Code di layar untuk scan manual</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-300 rounded-lg p-4 mb-4 space-y-3">
                <h4 class="font-semibold text-slate-800"><i class="fas fa-check mr-2"></i>Fitur QR Code:</h4>
                <ul class="text-sm text-slate-700 space-y-2 ml-4 list-disc">
                    <li>Generate QR Code otomatis dari NIS siswa</li>
                    <li>Cetak QR Card untuk semua siswa</li>
                    <li>Scan dengan kamera HP langsung</li>
                    <li>Integrasi dengan Telegram untuk notifikasi</li>
                </ul>
            </div>

            <!-- Steps -->
            <div class="bg-white border border-slate-300 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-slate-800 mb-3"><i class="fas fa-list-ol mr-2"></i>Cara Generate & Print QR Code:</h4>
                <ol class="text-sm text-slate-700 space-y-2 ml-4 list-decimal">
                    <li>Masuk ke <a href="<?= base_url('/admin/qrcode/print-cards') ?>" class="text-blue-600 hover:underline font-semibold">Admin → QR Code</a></li>
                    <li>Klik tombol "Generate QR Code untuk Semua Siswa"</li>
                    <li>QR Code akan di-generate otomatis untuk siswa yang belum punya</li>
                    <li>Klik "Cetak Kartu" untuk print QR Card</li>
                    <li>Potong kartu dan berikan ke siswa</li>
                </ol>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-slate-700"><i class="fas fa-lightbulb mr-2"></i><strong>Tips:</strong> Cetak QR Code saat awal semester agar semua siswa punya kartu absensi</p>
            </div>
        </div>

        <!-- Scanner Section -->
        <div class="p-8 border-t" id="scanner">
            <h2 class="text-3xl font-bold text-green-600 mb-6"><i class="fas fa-camera mr-2"></i>Panduan Scanner QR Code</h2>

            <p class="text-slate-700 mb-4">Scanner adalah halaman untuk melakukan absensi dengan scan QR Code atau input NIS manual:</p>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-green-800 mb-2"><i class="fas fa-star mr-2"></i>Fitur Scanner:</h4>
                <ul class="text-sm text-slate-700 space-y-2">
                    <li><i class="fas fa-check mr-1 text-green-600"></i><strong>Scan QR:</strong> Scan kartu absensi dengan kamera HP</li>
                    <li><i class="fas fa-check mr-1 text-green-600"></i><strong>Input Manual:</strong> Input NIS langsung jika tidak punya QR</li>
                    <li><i class="fas fa-check mr-1 text-green-600"></i><strong>Mode Masuk/Pulang:</strong> Toggle untuk absensi masuk atau pulang</li>
                    <li><i class="fas fa-check mr-1 text-green-600"></i><strong>Real-time List:</strong> Lihat daftar siswa yang sudah absen</li>
                    <li><i class="fas fa-check mr-1 text-green-600"></i><strong>Jam Digital:</strong> Menampilkan jam dan tanggal real-time</li>
                    <li><i class="fas fa-check mr-1 text-green-600"></i><strong>Notifikasi:</strong> Bunyi bip saat scan berhasil</li>
                </ul>
            </div>

            <div class="bg-white border border-slate-300 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-slate-800 mb-3"><i class="fas fa-list-ol mr-2"></i>Cara Menggunakan Scanner:</h4>
                <ol class="text-sm text-slate-700 space-y-2 ml-4 list-decimal">
                    <li>Buka halaman <a href="<?= base_url('/scanner') ?>" class="text-green-600 hover:underline font-semibold">Scanner</a> di browser (di kiosk/tablet)</li>
                    <li>Klik "Mulai Scan" untuk aktifkan kamera</li>
                    <li>Arahkan kamera ke QR Code siswa</li>
                    <li>Atau input NIS manual di kolom bawah</li>
                    <li>Hasil absensi akan ditampilkan (sukses/gagal)</li>
                    <li>Daftar siswa yang sudah absen muncul di sidebar</li>
                </ol>
            </div>

            <div class="grid gap-4 md:grid-cols-2 mb-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-semibold text-green-800 mb-2"><i class="fas fa-sign-in-alt mr-2"></i>Absensi Masuk</h4>
                    <p class="text-sm text-slate-700">Dilakukan pagi hari saat siswa tiba di sekolah</p>
                    <p class="text-xs text-slate-500 mt-2">Status: Tepat Waktu (≤07:30) / Terlambat</p>
                </div>
                <div class="bg-orange-50 border border-orange-300 rounded-lg p-4">
                    <h4 class="font-semibold text-orange-800 mb-2"><i class="fas fa-sign-out-alt mr-2"></i>Absensi Pulang</h4>
                    <p class="text-sm text-slate-700">Dilakukan sore hari saat siswa pulang</p>
                    <p class="text-xs text-slate-500 mt-2">Status: Tepat Waktu (≥15:00) / Lebih Awal</p>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-slate-700"><i class="fas fa-lightbulb mr-2"></i><strong>Tips:</strong> Letakkan kiosk scanner di pintu masuk/keluar agar siswa mudah akses</p>
            </div>
        </div>

        <!-- Absensi Section -->
        <div class="p-8 border-t" id="absensi">
            <h2 class="text-3xl font-bold text-purple-600 mb-6"><i class="fas fa-check-circle mr-2"></i>Panduan Manajemen Absensi</h2>

            <p class="text-slate-700 mb-4">Kelola dan lihat data absensi siswa dari admin panel:</p>

            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-purple-800 mb-2"><i class="fas fa-list-ul mr-2"></i>Menu Absensi untuk Admin:</h4>
                <ul class="text-sm text-slate-700 space-y-2">
                    <li><i class="fas fa-check mr-1 text-purple-600"></i><strong>Lihat Data Absensi:</strong> Daftar lengkap absensi siswa</li>
                    <li><i class="fas fa-check mr-1 text-purple-600"></i><strong>Filter Tanggal:</strong> Cari data absensi per hari/bulan</li>
                    <li><i class="fas fa-check mr-1 text-purple-600"></i><strong>Edit Absensi:</strong> Ubah status jika ada kesalahan</li>
                    <li><i class="fas fa-check mr-1 text-purple-600"></i><strong>Tandai Izin/Sakit/Alpha:</strong> Input absensi manual untuk siswa yang tidak hadir</li>
                    <li><i class="fas fa-check mr-1 text-purple-600"></i><strong>Export Data:</strong> Download laporan absensi</li>
                </ul>
            </div>

            <div class="bg-white border border-slate-300 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-slate-800 mb-3"><i class="fas fa-tags mr-2"></i>Status Absensi:</h4>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Tepat Waktu</span>
                        <p class="text-sm text-slate-700">Siswa masuk/pulang sesuai jadwal</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Terlambat</span>
                        <p class="text-sm text-slate-700">Siswa masuk terlambat (>07:30)</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">Izin</span>
                        <p class="text-sm text-slate-700">Siswa tidak hadir karena izin (ada surat)</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-semibold">Sakit</span>
                        <p class="text-sm text-slate-700">Siswa tidak hadir karena sakit</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">Alpha</span>
                        <p class="text-sm text-slate-700">Siswa tidak hadir tanpa keterangan</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-300 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-slate-800 mb-3"><i class="fas fa-list-ol mr-2"></i>Cara Akses Data Absensi:</h4>
                <ol class="text-sm text-slate-700 space-y-2 ml-4 list-decimal">
                    <li>Masuk ke <a href="<?= base_url('/admin/attendance') ?>" class="text-purple-600 hover:underline font-semibold">Admin → Absensi</a></li>
                    <li>Pilih tanggal atau range untuk filter data</li>
                    <li>Lihat daftar siswa dengan status absensi mereka</li>
                    <li>Klik edit untuk mengubah data jika diperlukan</li>
                    <li>Tambah catatan (keterangan izin/sakit)</li>
                </ol>
            </div>

            <div class="bg-slate-50 border border-slate-300 rounded-lg p-6 mb-8">
                <h3 class="text-2xl font-bold text-slate-800 mb-4"><i class="fas fa-chart-bar mr-2"></i>Laporan Absensi</h3>
                <p class="text-slate-700 mb-4">Akses laporan absensi detail untuk analisis dan monitoring:</p>
                <a href="<?= base_url('/admin/attendance') ?>" class="inline-block bg-slate-700 text-white px-6 py-2 rounded-lg font-semibold hover:bg-slate-800 transition">
                    <i class="fas fa-chart-bar mr-2"></i>Lihat Laporan
                </a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="p-8 border-t bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-4"><i class="fas fa-rocket mr-2"></i>Quick Links</h3>
                <div class="grid gap-4 md:grid-cols-3">
                    <a href="<?= base_url('/admin/telegram-settings') ?>" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition">
                        <div class="font-semibold mb-1"><i class="fas fa-cog mr-2"></i>Telegram Settings</div>
                        <p class="text-sm">Setup PIN untuk link student</p>
                    </a>
                    <a href="<?= base_url('/admin/students') ?>" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition">
                        <div class="font-semibold mb-1"><i class="fas fa-users mr-2"></i>Kelola Siswa</div>
                        <p class="text-sm">Edit data dan NIS siswa</p>
                    </a>
                    <a href="<?= base_url('/scanner') ?>" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition">
                        <div class="font-semibold mb-1"><i class="fas fa-camera mr-2"></i>Scanner QR</div>
                        <p class="text-sm">Buka halaman scanner</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
</script>

<?php $this->endSection(); ?>