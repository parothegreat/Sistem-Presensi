# Deploy Presensi ke Ubuntu + Cloudflare Tunnel

Dokumen ini ditulis untuk dijalankan di server oleh manusia **atau** agen AI (Codex/Claude Code).
Semua perintah diasumsikan dijalankan sebagai user non-root yang punya sudo.

> **Untuk agen AI:** baca dokumen ini sampai habis sebelum menjalankan perintah apa pun.
> Bagian [§9 Jangan Lakukan](#9-jangan-lakukan) berisi hal-hal yang akan merusak sistem atau
> membocorkan data. Jangan improvisasi di luar dokumen ini tanpa konfirmasi pemilik.

---

## 0. Ringkasan aplikasi

| Aspek | Nilai |
|---|---|
| Framework | CodeIgniter 4.7.4 |
| PHP | **8.3** untuk CodeIgniter 4.7.4 |
| Database | MySQL/MariaDB, 24 tabel, nama DB `presensi` |
| Dependency | `phpoffice/phpspreadsheet` saja (di luar framework) |
| Document root | `public/` — **bukan** root project |
| Background job | 5 perintah `php spark` via cron |
| Role | admin, guru, siswa, petugas |

Alur inti: perangkat (fingerprint ADMS / RFID / QR) mengirim scan → tabel `attendances` →
notifikasi ke orang tua lewat Telegram / WhatsApp / Firebase.

Guru absen sendiri lewat web. Siswa **tidak** bisa absen lewat web — hanya melihat riwayat
dan mengajukan izin. Guru yang berstatus wali kelas dapat menyetujui izin dan mengabsen manual.

---

## 1. Prasyarat server

Target: **Ubuntu 24.04 LTS** dengan PHP 8.3 bawaan.

```bash
sudo apt update
sudo apt install -y nginx mariadb-server \
  php-fpm php-cli php-mysql php-intl php-mbstring php-xml php-curl php-gd php-zip \
  git unzip

php -v                     # pastikan PHP 8.3
php -m | grep -E '^(intl|mysqli|mbstring|curl|gd|zip|xml)$'   # harus muncul semua
```

Catat socket PHP-FPM `php8.3-fpm` — dipakai di §5.

Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 2. Memindahkan kode ke server

Ada dua hal terpisah yang harus dipindah, dan **caranya berbeda**:

| Apa | Cara | Kenapa |
|---|---|---|
| Kode aplikasi (folder `presensi/`) | GitHub repo **private** | butuh jalur update & riwayat |
| Dump database (`presensi.sql`) | `scp` langsung, **jangan git** | berisi data pribadi ratusan siswa |
| `public/uploads/` (~4 MB) | `rsync` langsung | tidak ada di git maupun di dump SQL |

### 2a. Sebelum push — pastikan snapshot aman

`.env` berisi nilai aktif dan sudah di-gitignore. File `env` yang ikut Git hanya template
bersanitasi dengan placeholder `CHANGE_ME`; jangan pernah menyalin kredensial aktif ke sana.
Repo private baru harus dimulai dari snapshot bersih agar secret dari riwayat repo lama tidak ikut.

Sebelum lanjut, `git status` harus bersih dan `public/uploads/` tidak boleh berisi file data
yang dilacak Git. Upload produksi dipindahkan terpisah pada §2c.

### 2b. Akses repo dari server — pakai deploy key (read-only)

Jangan taruh password GitHub atau Personal Access Token di server.

```bash
ssh-keygen -t ed25519 -C "presensi-server" -f ~/.ssh/id_presensi -N ""
cat ~/.ssh/id_presensi.pub
```

Tempel isi `.pub` itu ke **GitHub → repo → Settings → Deploy keys → Add deploy key**.
Biarkan "Allow write access" **tidak dicentang** — server hanya perlu membaca.

```bash
cat >> ~/.ssh/config <<'EOF'
Host github-presensi
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_presensi
  IdentitiesOnly yes
EOF

sudo mkdir -p /var/www
sudo chown "$USER":"$USER" /var/www
git clone github-presensi:parothegreat/Sistem-Presensi.git /var/www/presensi
cd /var/www/presensi
```

### 2c. Dependency dan file yang tidak ikut git

`vendor/` di-gitignore, jadi harus dibangun di server:

```bash
composer install --no-dev --optimize-autoloader
```

`public/uploads/` (foto siswa, foto guru, logo sekolah, bukti izin, background kartu)
**tidak ada di dump SQL dan tidak ada di git**. Salin manual dari mesin lama:

```bash
# dijalankan DARI mesin lama
rsync -avz "public/uploads/" user@server:/var/www/presensi/public/uploads/
```

---

## 3. Database

### 3a. Memindahkan `presensi.sql`

`presensi.sql` adalah hasil export phpMyAdmin dan **berada di luar folder repo**
(satu tingkat di atasnya). Itu memang tempat yang benar — **jangan pernah memasukkannya ke git**,
sekalipun repo-nya private. Isinya 191 akun berikut hash password, 90 data siswa, 97 data guru,
nomor telepon wali, dan NIS/NISN. Sekali masuk riwayat git, ia tidak bisa benar-benar dicabut.

Kirim langsung lewat SSH:

```bash
# dijalankan DARI mesin lama
scp "presensi.sql" user@server:/tmp/presensi.sql
```

Setelah import selesai (§3c), **hapus dari server**:

```bash
shred -u /tmp/presensi.sql      # atau: rm -f /tmp/presensi.sql
```

Jangan tinggalkan dump di `/tmp` atau di mana pun di dalam `public/`.

### 3b. Buat database dan user

```bash
sudo mysql_secure_installation      # set password root, hapus anonymous user & test db
```

Buat user khusus — **jangan pakai root untuk aplikasi**:

```bash
sudo mariadb <<'SQL'
CREATE DATABASE presensi CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'presensi'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT_DISINI';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON presensi.* TO 'presensi'@'localhost';
FLUSH PRIVILEGES;
SQL
```

### 3c. Import lalu migrate

Dump phpMyAdmin ini **tidak** memuat `CREATE DATABASE` maupun `USE`, jadi nama database
ditentukan saat import — pastikan menunjuk ke `presensi`:

```bash
mysql -u presensi -p presensi < /tmp/presensi.sql
php spark migrate
php spark migrate:status        # semua harus terdaftar
```

Urutannya penting: **import dulu, baru migrate**. Dump sudah memuat tabel `migrations` dengan
46 baris, jadi CI4 tahu mana yang sudah diterapkan dan hanya menjalankan yang baru.

Verifikasi:

```bash
mysql -u presensi -p presensi -e "SELECT COUNT(*) FROM users; SHOW INDEX FROM users WHERE Column_name='username';"
```

`Non_unique` pada index `username` harus `0`. Kalau masih `1`, migration
`2026-08-14-000001_AddUniqueUsernameToUsers` belum jalan.

---

## 4. Konfigurasi aplikasi

### 4a. File `.env`

```bash
cp env .env      # env adalah template aman; nilai aktif hanya boleh ada di .env
nano .env
```

Isi minimum yang **wajib** diubah dari nilai bawaan:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://presensi.domain-anda.com/'
app.forceGlobalSecureRequests = true

database.default.hostname = localhost
database.default.database = presensi
database.default.username = presensi
database.default.password = PASSWORD_YANG_TADI_DIBUAT
database.default.DBDriver = MySQLi
database.default.port = 3306

encryption.key = hex2bin:GANTI_DENGAN_KUNCI_BARU

SCHOOL_NAME='SMK Mitra Industri'
SCHOOL_NPSN=69754539
HOLIDAY_DAYS=5

TELEGRAM_BOT_TOKEN=TOKEN_BARU_DARI_BOTFATHER
ONESENDER_API_ENDPOINT=https://wa4103.api-wa.my.id/api/v1/messages
ONESENDER_API_KEY=API_KEY_BARU
ONESENDER_PHONE_NUMBER=628xxxxxxxxxx
BIOMETRIC_API_KEY=RAHASIA_BARU_MINIMAL_32_KARAKTER

ANDROID_PUSH_ENABLED=true
FIREBASE_MODE=service_account
FIREBASE_PROJECT_ID=absensi-75955
FIREBASE_SERVICE_ACCOUNT_EMAIL=firebase-adminsdk-fbsvc@absensi-75955.iam.gserviceaccount.com
FIREBASE_SERVER_KEY="-----BEGIN PRIVATE KEY-----\nISI_KEY_BARU\n-----END PRIVATE KEY-----"
FIREBASE_SENDER_ID=765951121615
```

Kunci enkripsi baru:

```bash
php spark key:generate
```

Amankan filenya:

```bash
chmod 640 .env
sudo chown "$USER":www-data .env
```

### 4b. HTTPS di belakang tunnel

`app.forceGlobalSecureRequests = true` sudah diaktifkan melalui `.env` produksi. Di
`app/Config/App.php`, hanya reverse proxy loopback yang dipercaya:

```php
public array $proxyIPs = ['127.0.0.1' => 'X-Forwarded-For'];
```

Cloudflared/Nginx masuk dari loopback dan meneruskan `X-Forwarded-Proto: https`. Header serupa
dari klien lain tidak dipercaya, sehingga HTTPS tetap terdeteksi tanpa membuka spoofing proxy.

### 4c. Permission file

```bash
sudo chown -R "$USER":www-data /var/www/presensi
sudo find /var/www/presensi -type d -exec chmod 750 {} \;
sudo find /var/www/presensi -type f -exec chmod 640 {} \;

# writable/ dan uploads/ harus bisa ditulis web server
sudo chmod -R 770 /var/www/presensi/writable
sudo chmod -R 770 /var/www/presensi/public/uploads
```

Bersihkan sampah development yang mungkin ikut terbawa:

```bash
rm -rf writable/debugbar/* writable/logs/* writable/session/*
rm -f writable/debug_adminwalikelas.txt writable/test_import.xlsx
find . -name ".DS_Store" -delete
```

---

## 5. Nginx + PHP-FPM

`/etc/nginx/sites-available/presensi`:

```nginx
server {
    listen 127.0.0.1:8080;
    server_name presensi.domain-anda.com;

    # PENTING: root menunjuk ke public/, bukan ke root project
    root /var/www/presensi/public;
    index index.php;

    client_max_body_size 20M;   # cukup untuk import Excel

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;   # sesuaikan versi
        fastcgi_read_timeout 90;
    }

    # jangan pernah sajikan file tersembunyi
    location ~ /\. { deny all; }

    access_log /var/log/nginx/presensi.access.log;
    error_log  /var/log/nginx/presensi.error.log;
}
```

`listen 127.0.0.1:8080` disengaja — hanya cloudflared yang boleh menjangkaunya.
Tidak ada port yang terbuka ke internet maupun LAN.

```bash
sudo ln -s /etc/nginx/sites-available/presensi /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

Verifikasi lokal sebelum menyambung tunnel:

```bash
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8080/login    # harus 200
```

---

## 6. Cloudflare Tunnel

```bash
curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb -o cloudflared.deb
sudo dpkg -i cloudflared.deb
cloudflared tunnel login
cloudflared tunnel create presensi
cloudflared tunnel route dns presensi presensi.domain-anda.com
```

`/etc/cloudflared/config.yml`:

```yaml
tunnel: ID_TUNNEL_ANDA
credentials-file: /etc/cloudflared/ID_TUNNEL_ANDA.json

ingress:
  # === 1. Endpoint perangkat — cukup LAN, tidak ada urusan dengan internet ===
  # /api/attendance/scan dan /mark TIDAK punya autentikasi sama sekali.
  - hostname: presensi.domain-anda.com
    path: ^/iclock/
    service: http_status:404
  - hostname: presensi.domain-anda.com
    path: ^/api/attendance/(scan|mark|today|receive-biometric)
    service: http_status:404
  - hostname: presensi.domain-anda.com
    path: ^/api/rfid
    service: http_status:404
  - hostname: presensi.domain-anda.com
    path: ^/api/qrcode/
    service: http_status:404

  # === 2. Endpoint yang membocorkan data pribadi (semuanya tanpa auth) ===
  # /api/students/{nis} mengembalikan full_name + telegram_chat_id orang tua.
  # NIS berpola dan gampang dienumerasi — ini panen data siap pakai.
  - hostname: presensi.domain-anda.com
    path: ^/api/students/
    service: http_status:404
  - hostname: presensi.domain-anda.com
    path: ^/api/device-token/student/
    service: http_status:404
  # /lobby = layar TV untuk lobi sekolah: nama + NIS + kelas, tanpa login.
  - hostname: presensi.domain-anda.com
    path: ^/(lobby|api/lobby)
    service: http_status:404

  # === 3. Sisa development & pemicu internal ===
  - hostname: presensi.domain-anda.com
    path: ^/(test-api|api/test|telegram/test-link|telegram/send-pending|tools/)
    service: http_status:404

  # === 4. Sisanya publik ===
  # JANGAN blokir ^/api/ borongan. Dua ini wajib tetap bisa diakses:
  #   /telegram/webhook                             -> dipanggil server Telegram
  #   /api/device-token/register|unregister|refresh -> app Android siswa
  - hostname: presensi.domain-anda.com
    service: http://127.0.0.1:8080

  - service: http_status:404
```

Urutan aturan menentukan hasil — cloudflared memakai yang **pertama cocok**. Jangan pindahkan
blok publik (§4) ke atas.

```bash
sudo cloudflared service install
sudo systemctl enable --now cloudflared
sudo systemctl status cloudflared
```

### Aturan Cloudflare tambahan (dashboard)

1. **Cache Rule — bypass `/uploads/*`.**
   Foto siswa dan bukti izin punya ekstensi statis, jadi kandidat kuat di-cache di edge.
   Itu data pribadi anak di bawah umur. Set *Bypass cache* untuk path `/uploads/*`.

2. **Timeout 100 detik (plan Free).** Export Excel dan `/admin/settings/backup` — yang men-dump
   seluruh database baris per baris lewat PHP — bisa melewati batas ini kalau data sudah besar.
   Untuk backup, pakai `mysqldump` lewat cron, jangan lewat browser.

3. **Bot Fight Mode**: kalau dinyalakan, pastikan `/telegram/webhook` dan
   `/api/device-token/*` dikecualikan, karena keduanya trafik non-browser yang sah.

### Perangkat keras tetap di LAN

Mesin fingerprint ADMS dan RFID reader **jangan** diarahkan ke domain tunnel:

- Cloudflare mewajibkan TLS; firmware ZKTeco lama sering hanya bisa HTTP polos atau TLS versi lama.
- WAF/Bot Fight Mode gampang memblokir POST tanpa sidik jari browser.

Arahkan mesin ke **IP LAN server** langsung, mis. `http://192.168.x.x/iclock/cdata`.
Untuk itu tambahkan `server` block kedua di nginx yang `listen 192.168.x.x:80` dan hanya
mengizinkan `location ^~ /iclock/` serta `/api/`, dibatasi dengan `allow 192.168.x.0/24; deny all;`.

---

## 7. Cron

Tanpa cron, aplikasi tetap terbuka tapi **notifikasi tidak pernah terkirim dan alpha tidak
pernah ditandai** — antrian menumpuk diam-diam tanpa error yang terlihat pengguna.

```bash
sudo crontab -u www-data -e
```

```cron
* * * * *   cd /var/www/presensi && /usr/bin/php spark telegram:send-pending  >> writable/logs/cron.log 2>&1
* * * * *   cd /var/www/presensi && /usr/bin/php spark whatsapp:send-pending  >> writable/logs/cron.log 2>&1
* * * * *   cd /var/www/presensi && /usr/bin/php spark android:send-pending   >> writable/logs/cron.log 2>&1
*/5 * * * * cd /var/www/presensi && /usr/bin/php spark attendance:process-biometric >> writable/logs/cron.log 2>&1
0 23 * * *  cd /var/www/presensi && /usr/bin/php spark mark:alpha             >> writable/logs/cron.log 2>&1
```

Backup harian (jalankan sebagai root atau user yang punya akses `.my.cnf`, jangan taruh
password di baris cron):

```cron
0 1 * * *   mysqldump --defaults-file=/root/.my.cnf presensi | gzip > /var/backups/presensi-$(date +\%F).sql.gz
```

Rotasi log supaya `writable/logs` tidak menggelembung:

```bash
sudo tee /etc/logrotate.d/presensi >/dev/null <<'EOF'
/var/www/presensi/writable/logs/*.log {
    weekly
    rotate 8
    compress
    missingok
    notifempty
    copytruncate
}
EOF
```

---

## 8. Setelah live — wajib dikerjakan

### 8a. Ganti semua password bawaan — LAKUKAN PERTAMA, SEBELUM URL DIBAGIKAN

Seeder di repo ini menetapkan **empat kredensial default**, dan nilainya tertulis terbuka di
dalam kode. Siapa pun yang punya akses repo — sekarang atau nanti — tahu keempatnya tanpa
perlu menebak:

| Ditetapkan di | Username | Password |
|---|---|---|
| `app/Database/Seeds/UserSeeder.php:14` | `admin` | `admin123` |
| `app/Database/Seeds/UserSeeder.php:23` | `guru` | `guru123` |
| `app/Database/Seeds/UserSeeder.php:41` | `siswa` | `siswa123` |
| `app/Database/Seeds/PetugasUserSeeder.php:19` | (akun petugas) | `password123` |

Akun `admin` di dump produksi memakai password `admin123` ini — sudah diverifikasi. Selama
belum diganti, siapa pun yang menemukan domainmu bisa masuk sebagai admin dan membuka seluruh
data siswa dan guru.

**Langkah:**

1. Daftar akun istimewa yang ada:

   ```bash
   mysql -u presensi -p presensi -e \
     "SELECT id, username, role, is_active, created_at FROM users WHERE role IN ('admin','petugas') ORDER BY id;"
   ```

2. Login ke `/login` sebagai admin → **Profil → ubah password**. Pakai password kuat yang
   tidak dipakai di tempat lain.

3. Coba login lagi dengan password lama. **Harus gagal.** Kalau masih bisa, perubahan tidak tersimpan.

4. Ulangi untuk akun `guru`, `siswa`, dan setiap akun petugas yang masih aktif.

5. **Hapus akun sisa uji coba.** Pada dump saat ini ada akun petugas bernama `testing`
   (id=8, dibuat 23 Januari 2026) yang tampaknya sisa percobaan. Kalau memang tidak dipakai,
   hapus lewat Admin → Users, jangan dibiarkan hidup.

> Seeder tetap berisi password-password itu, dan itu tidak masalah **selama seeder tidak pernah
> dijalankan di produksi**. Lihat larangan `db:seed` di §9.

### 8b. Daftarkan ulang webhook Telegram

Webhook menyimpan URL lama. Buka **Admin → Master → Registrasi Webhook**
(`/admin/telegram-webhook`) dan daftarkan `https://presensi.domain-anda.com/telegram/webhook`.

Koneksi outbound ke API Telegram sudah memverifikasi sertifikat TLS. Jika registrasi webhook
gagal karena sertifikat, perbaiki paket `ca-certificates` atau waktu server; jangan mematikan
verifikasi TLS di kode.

### 8c. Arahkan ulang perangkat

Mesin fingerprint dan RFID reader masih menyimpan alamat server lama **di firmware-nya**.
Masuk ke menu jaringan tiap mesin dan ganti ke IP LAN server baru. Ini tidak bisa dilakukan
dari sisi server.

### 8d. Verifikasi menyeluruh

```bash
H=https://presensi.domain-anda.com

# harus 200 — jalur yang memang publik
curl -s -o /dev/null -w "login              %{http_code}\n" $H/login

# harus 404 — ditutup di ingress cloudflared
curl -s -o /dev/null -w "scan               %{http_code}\n" -X POST $H/api/attendance/scan
curl -s -o /dev/null -w "iclock             %{http_code}\n" $H/iclock/cdata
curl -s -o /dev/null -w "data siswa+chat_id %{http_code}\n" $H/api/students/12345
curl -s -o /dev/null -w "lobby              %{http_code}\n" $H/lobby
curl -s -o /dev/null -w "route debug        %{http_code}\n" $H/test-api

# harus BUKAN 200 — kalau 200, document root salah (lihat §5)
curl -s -o /dev/null -w "env bocor?         %{http_code}\n" $H/.env
curl -s -o /dev/null -w "app/ bocor?        %{http_code}\n" $H/app/Config/Database.php

# harus TETAP hidup — kalau ikut 404, ingress kelewat ketat
curl -s -o /dev/null -w "webhook telegram   %{http_code}\n" -X POST $H/telegram/webhook
curl -s -o /dev/null -w "device-token       %{http_code}\n" -X POST $H/api/device-token/register
```

Dua tes terakhir penting: kalau keduanya ikut `404`, berarti kamu memblokir `^/api/` borongan
dan **notifikasi Telegram ke orang tua serta app Android siswa akan mati diam-diam**.
Keduanya boleh menjawab 400/401/500 — yang penting bukan 404.

---

## 9. Jangan Lakukan

Daftar ini bukan preferensi gaya. Masing-masing pernah jadi penyebab kebocoran atau kerusakan nyata.

| Jangan | Kenapa |
|---|---|
| Menaruh document root di root project | `.env`, `app/`, `writable/` jadi bisa diunduh lewat browser. Root harus `public/`. |
| Memasukkan `presensi.sql` ke git | Berisi hash password 191 akun dan data pribadi ratusan anak. Riwayat git tidak bisa benar-benar dibersihkan. |
| Membiarkan `CI_ENVIRONMENT = development` | Stack trace lengkap berikut query dan path server tampil ke pengunjung saat error. |
| Memakai user DB `root` untuk aplikasi | SQL injection sekecil apa pun langsung berubah jadi akses penuh ke seluruh server DB. |
| Membuka port MySQL (3306) ke internet | Tidak ada alasan. Aplikasi dan DB satu mesin; biarkan `bind-address = 127.0.0.1`. |
| Meneruskan `/iclock/` atau `/api/attendance/scan` lewat tunnel | `scan` dan `mark` **tanpa autentikasi apa pun** — siapa pun di internet bisa menulis presensi. |
| Mengarahkan mesin fingerprint ke domain Cloudflare | Firmware lama gagal TLS, dan WAF memblokir POST non-browser. Gunakan IP LAN. |
| `chmod -R 777` | 770 dengan grup `www-data` sudah cukup. 777 berarti user mana pun di server bisa menulis kode yang dieksekusi. |
| Memakai PHP selain 8.3 | Baseline dependency produksi dikunci dan diuji untuk PHP 8.3. |
| Commit `.env` ke git atau mengisi credential aktif di `env` | `.env` harus tetap lokal; `env` yang masuk Git hanya template berisi placeholder. |
| Membiarkan password bawaan seeder | Ada **empat**: `admin123`, `guru123`, `siswa123`, `password123`. Semuanya tertulis terbuka di `app/Database/Seeds/`. Lihat §8a. |
| Menjalankan `php spark db:seed` di produksi | Menyuntikkan ulang keempat akun default berikut passwordnya ke database yang sudah berisi data asli. |
| Menjalankan `php spark migrate:refresh` di produksi | Menghapus seluruh tabel lalu membangun ulang. **Semua data presensi hilang.** Untuk produksi hanya `php spark migrate`. |
| Menyunting file langsung di server | Perubahan akan tertimpa saat `git pull` berikutnya. Ubah di lokal, commit, push, tarik. |
| Membiarkan route debug terbuka | `/test-api`, `/api/test/push`, `/telegram/test-link`, `/tools/check_biometric_logs` — semuanya sisa development. |

---

## 10. Keamanan

### 10a. Kredensial yang harus dirotasi sebelum live

Checkout lama pernah memuat kredensial produksi di file terlacak. Snapshot repo baru sudah
menjadikan `env` template bersanitasi dan memindahkan private key Firebase ke `.env`, tetapi
semua kredensial lama tetap harus dianggap bocor dan diterbitkan ulang:

| Kredensial | Cara rotasi |
|---|---|
| Token bot Telegram | @BotFather → `/revoke` → `/token` |
| OneSender API key | Dashboard OneSender, terbitkan ulang |
| `BIOMETRIC_API_KEY` | Karang sendiri, min. 32 karakter acak. Perangkat yang memakainya harus ikut diperbarui |
| `encryption.key` | `php spark key:generate` |
| Firebase service account | Firebase Console → Service Accounts → Generate new private key |

`app/Config/Firebase.php` membaca private key dari `FIREBASE_SERVER_KEY`. Simpan key baru hanya
di `.env` dengan pemisah baris literal `\n`; jangan masukkan nilainya ke file PHP atau template `env`.

Menghapus file di commit baru **tidak** menghapusnya dari riwayat. Kalau repo pernah publik
walau sebentar, anggap semua kredensial lama sudah tersalin pihak lain.

### 10b. Endpoint tanpa autentikasi

`app/Controllers/Attendance.php` menyediakan `POST /api/attendance/scan` dan
`POST /api/attendance/mark` **tanpa pemeriksaan identitas sama sekali** — cukup tahu URL-nya
untuk menulis atau mengubah data presensi siapa pun.

Menutupnya di ingress cloudflared (§6) sudah menyelesaikan risiko dari internet. Yang belum
tertutup adalah dari dalam LAN. Perbaikan yang benar adalah menambahkan pemeriksaan API key
di controller tersebut, mengikuti pola yang **sudah ada** di
`app/Controllers/Api/BiometricController.php` (header `Authorization: Bearer` +
`X-Device-ID`, dicocokkan dengan `BIOMETRIC_API_KEY`). Kerjakan ini di lokal, bukan di server.

### 10c. Cakupan CSRF

Di `app/Config/Filters.php`, CSRF hanya aktif untuk route web:

```php
'csrf' => ['before' => ['/', 'login', 'admin/*', 'guru/*', 'siswa/*', 'petugas/*', 'auth/*']]
```

Semua `api/*`, `/iclock/*`, dan `telegram/webhook` sengaja di luar cakupan — memang harus
begitu supaya perangkat bisa POST. Konsekuensinya autentikasi di jalur-jalur itu harus kuat,
karena tidak ada lapis kedua. Lihat §10b.

### 10d. Hardening dasar server

```bash
sudo ufw allow OpenSSH
sudo ufw enable
```

Tidak perlu membuka port 80/443 sama sekali — cloudflared membuat koneksi **keluar**, bukan
menerima koneksi masuk. Ini keuntungan besar tunnel: server tidak punya permukaan serang
langsung dari internet.

Kalau mesin fingerprint LAN butuh akses, buka hanya ke subnet lokal:

```bash
sudo ufw allow from 192.168.1.0/24 to any port 80
```

SSH: matikan login root dan autentikasi password di `/etc/ssh/sshd_config`
(`PermitRootLogin no`, `PasswordAuthentication no`), pakai kunci saja.

### 10e. Data pribadi

Database berisi nama, NIS/NISN, nomor telepon wali, foto, dan dokumen izin ratusan siswa —
sebagian besar anak di bawah umur. Konsekuensi praktis:

- Backup (`/var/backups/*.sql.gz`) sama sensitifnya dengan database aslinya. `chmod 600`, dan
  jangan taruh di dalam `public/`.
- `presensi.sql` yang dikirim ke server harus dihapus setelah diimport (§3a).
- `public/uploads/permissions/` berisi foto bukti izin yang bisa memuat informasi kesehatan.
  Pastikan Cache Rule bypass Cloudflare aktif (§6).
- Jangan menyalin database produksi ke mesin development tanpa menyamarkan datanya.

---

## 11. Troubleshooting

| Gejala | Penyebab yang paling sering |
|---|---|
| Halaman putih / 500 | Cek `writable/logs/log-*.log`. Biasanya `.env` salah atau `writable/` tidak bisa ditulis. |
| "Unable to connect to the database" + "No such file or directory" | `hostname` diisi `localhost` sementara MySQL diakses lewat TCP. Ganti ke `127.0.0.1`. |
| Semua link/CSS mengarah ke domain salah | `app.baseURL` di `.env` belum diganti. Harus diakhiri garis miring. |
| CSS/JS tidak muncul, halaman polos | Document root tidak menunjuk ke `public/`. |
| Login berhasil lalu langsung terlempar keluar | `writable/session` tidak bisa ditulis www-data. |
| Notifikasi tidak terkirim padahal absensi tercatat | Cron belum terpasang. Cek `writable/logs/cron.log`. |
| Telegram diam saja | Webhook masih menunjuk URL lama — daftarkan ulang (§8b). |
| Absensi fingerprint tidak masuk | Mesin masih menunjuk server lama (§8c), atau cron `attendance:process-biometric` mati. |
| 502 Bad Gateway | Socket PHP-FPM di config nginx tidak cocok dengan versi PHP terpasang. |
| Error 1062 "Duplicate entry ... for key 'username'" | Perilaku yang benar — sejak migration `2026-08-14-000001`, username wajib unik. |

---

## 12. Untuk agen AI yang mengoperasikan server ini

- **Jangan** menjalankan `migrate:refresh`, `migrate:rollback`, atau `db:seed` di produksi.
  Ketiganya menghancurkan data nyata. Hanya `php spark migrate` yang aman.
- **Jangan** menyunting file di `/var/www/presensi` langsung. Ubah di repo, commit, push, `git pull`.
- **Jangan** menampilkan isi `.env`, `app/Config/Firebase.php`, atau isi tabel `users` ke
  dalam output, log, tiket, maupun layanan pihak ketiga.
- Sebelum tindakan yang sulit dibatalkan (menghapus data, mengubah schema, merotasi kunci),
  minta konfirmasi pemilik lebih dulu.
- Setelah setiap perubahan, jalankan blok verifikasi di §8d dan laporkan hasilnya apa adanya —
  termasuk kalau ada yang gagal.
- Sumber kebenaran untuk schema adalah `app/Database/Migrations/`, bukan dump SQL.
- Peta terbaik aplikasi ini adalah `app/Config/Routes.php`.
