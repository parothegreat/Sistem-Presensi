# Panduan Instalasi di cPanel

Berikut adalah langkah-langkah untuk menginstal aplikasi Presensi Sekolah di hosting cPanel.

## Persyaratan Server

- **PHP**: Versi 8.1 keatas
- **Ekstensi PHP**: `intl`, `mbstring`, `json`, `mysqlnd`, `xml`, `curl`
- **Database**: MySQL / MariaDB

## Langkah 1: Upload File

1.  Buka **File Manager** di cPanel.
2.  Masuk ke folder `public_html` (atau folder subdomain jika menggunakan subdomain).
3.  Upload semua file project (kecuali folder `writable`, `tests`, `git`, `node_modules`).
4.  **Penting**: Pastikan struktur folder benar.

    - CodeIgniter 4 merekomendasikan folder `public` sebagai document root.
    - Jika Anda mengupload langsung ke `public_html`, pindahkan isi folder `public` ke `public_html`, dan sisa folder project sejajar dengan `public_html` (di luar public).
    - **Alternatif mudah (Upload Utuh)**:
      1.  Upload semua file ke `public_html`.
      2.  Edit file `public/index.php`.
      3.  Ubah `$pathsPath = FCPATH . '../app/Config/Paths.php';` sesuai struktur Anda.
      4.  Atau, gunakan `.htaccess` di root untuk me-redirect traffic ke folder `public`.

    > **Rekomendasi Struktur Folder Aman:**
    >
    > - `/home/user/presensi_app/` (berisi app, system, writable, dll)
    > - `/home/user/public_html/` (berisi file-file dari folder public saja: index.php, assets, dll)
    > - Edit `/home/user/public_html/index.php` dan sesuaikan variable `$pathsPath` mengarah ke `/home/user/presensi_app/app/Config/Paths.php`.

## Langkah 2: Setup Database

1.  Buka **MySQL Database Wizard**.
2.  Buat Database baru (misal: `sekolah_db`).
3.  Buat User Database baru (misal: `sekolah_user`) dan password.
4.  Add User to Database dan centang **ALL PRIVILEGES**.
5.  Buka **phpMyAdmin**.
6.  Pilih database yang baru dibuat.
7.  Import file SQL database aplikasi (biasanya `database.sql` atau hasil export dari localhost).

## Langkah 3: Konfigurasi Environment (.env)

1.  Di File Manager, cari file `env` di root folder aplikasi.
2.  Rename menjadi `.env` (tambah titik di depan).
3.  Edit file `.env` dan sesuaikan konfigurasi berikut:

```ini
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
# production = Live (Error hidden), development = Localhost (Error showed)
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
# Wajib https jika production. Pastikan diakhiri slash (/)
app.baseURL = 'https://domain-anda.com/'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = sekolah_db
database.default.username = sekolah_user
database.default.password = password_database_anda
database.default.DBDriver = MySQLi

#--------------------------------------------------------------------
# SCHOOL INFO (Muncul di Laporan & Notifikasi)
#--------------------------------------------------------------------
SCHOOL_NAME='SMK Mitra Industri'
SCHOOL_NPSN=69754539

#--------------------------------------------------------------------
# TELEGRAM NOTIFICATIONS
#--------------------------------------------------------------------
# Token dari @BotFather
TELEGRAM_BOT_TOKEN = 1234567890:ABCdefGHIjklMNOpqRSTuvwXYZ

#--------------------------------------------------------------------
# WHATSAPP NOTIFICATIONS (OneSender / Watsap.id)
#--------------------------------------------------------------------
ONESENDER_API_ENDPOINT=https://wa4103.api-wa.my.id/api/v1/messages
ONESENDER_API_KEY=api_key_anda_disini
ONESENDER_PHONE_NUMBER=628123456789

#--------------------------------------------------------------------
# ATTENDANCE SETTINGS
#--------------------------------------------------------------------
# Hari Libur Rutin (Untuk skip Auto Alpha)
# 1=Senin, 5=Jumat, 7=Minggu
# Contoh sekolah Islam (Libur Jumat): 5
# Contoh sekolah umum (Libur Minggu): 7
# Libur Jumat & Minggu: 5,7
HOLIDAY_DAYS=5

#--------------------------------------------------------------------
# ANDROID PUSH SETTINGS (Firebase)
#--------------------------------------------------------------------
ANDROID_PUSH_ENABLED=true
```

4.  Simpan file.

## Langkah 4: Setup Permission

Pastikan folder `writable` beserta sub-foldernya memiliki permission `0777` atau dapat ditulisi oleh web server (writable).

- Klik kanan folder `writable` -> Change Permissions -> Centang Write untuk User, Group, World (777) jika perlu, atau sesuaikan dengan user permission hosting.

## Menjalankan Cron Job

Aplikasi ini memiliki fitur pengiriman notifikasi antrian (queue) yang perlu dijalankan secara berkala.

### Daftar Cron Job yang Tersedia

Berikut adalah daftar perintah yang HARUS disetting di Cron Job cPanel agar sistem berjalan otomatis:

#### 1. Kirim Notifikasi Telegram (Wajib)

Mengirim pesan antrian Telegram (Siswa Check-in/out, Notifikasi Alpha, dll).

- **Jadwal**: Setiap Menit `* * * * *`
- **Command**:
  ```bash
  /usr/local/bin/php /home/username/folder_aplikasi/spark telegram:send-pending
  ```

#### 2. Kirim Notifikasi WhatsApp (Wajib jika aktif)

Mengirim pesan antrian WhatsApp via OneSender.

- **Jadwal**: Setiap Menit `* * * * *`
- **Command**:
  ```bash
  /usr/local/bin/php /home/username/folder_aplikasi/spark whatsapp:send-pending
  ```

#### 3. Kirim Notifikasi Android (Wajib jika aktif)

Mengirim push notification ke aplikasi Android siswa via Firebase.

- **Jadwal**: Setiap Menit `* * * * *`
- **Command**:
  ```bash
  /usr/local/bin/php /home/username/folder_aplikasi/spark android:send-pending
  ```

#### 4. Auto Mark Alpha (Wajib)

Menandai siswa/guru yang tidak hadir sebagai "Alpha" secara otomatis pada malam hari.

- **Jadwal**: Setiap Hari pukul 23:00 `0 23 * * *`
- **Command**:
  ```bash
  /usr/local/bin/php /home/username/folder_aplikasi/spark mark:alpha
  ```

#### 5. Proses Data Biometric/Fingerprint (Opsional)

Hanya jika menggunakan mesin fingerprint ADMS yang push data ke server. Memproses raw logs menjadi data absensi.

- **Jadwal**: Setiap 5 Menit `*/5 * * * *`
- **Command**:
  ```bash
  /usr/local/bin/php /home/username/folder_aplikasi/spark attendance:process-biometric
  ```

   > **Catatan:**
   >
   > - Ganti `/home/username/folder_aplikasi` dengan path sebenarnya di hosting Anda.
   > - Path PHP (`/usr/local/bin/php`) bisa berbeda tergantung hosting.

### Khusus Hostinger (hPanel)

Di Hostinger, path dan strukturnya sedikit berbeda.

1.  Masuk ke **hPanel** -> **Advanced** -> **Cron Jobs**.
2.  Pilih tipe **Custom**.
3.  **Command yang digunakan**:
    ```bash
    /usr/bin/php /home/u123456789/domains/domain.com/public_html/spark whatsapp:send-pending
    ```
    *(Ganti `u123456789` dan `domain.com` sesuai milik Anda)*.
4.  **Cara Mengetahui Path**:
    - Lihat di **Dashboard** hPanel bagian **File Manager**, atau
    - Buat file `path.php` berisi `<?php echo getcwd(); ?>`, akses di browser, lalu copy path-nya.

## Setup Task Scheduler (Windows Localhost)

Jika Anda menggunakan Windows (XAMPP/Laragon) dan tidak memiliki akses Cron Job, Anda bisa menggunakan **Task Scheduler** bawaan Windows untuk menjalankan perintah otomatis.

### Cara Membuat Task

1.  Tekan `Win + R`, ketik `taskschd.msc`, lalu Enter untuk membuka **Task Scheduler**.
2.  Di panel kanan (Actions), klik **Create Basic Task**.
3.  **Name**: Beri nama task, misal "Absensi - Kirim Telegram". Klik Next.
4.  **Trigger**:
    - Untuk Notifikasi (Telegram/WA/Android): Pilih **Daily**.
    - Untuk Auto Alpha: Pilih **Daily**.
    - Klik Next.
5.  **Daily**:
    - Start: Biarkan default.
    - Recur every: 1 days.
    - Klik Next.
6.  **Action**: Pilih **Start a program**. Klik Next.
7.  **Program/script**: Browse ke file `php.exe` instalasi PHP Anda.
    - Contoh XAMPP: `C:\xampp\php\php.exe`
    - Contoh Laragon: `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe`
8.  **Add arguments (optional)**: Masukkan perintah spark.
    - Format: `spark <nama_perintah>`
    - Contoh: `spark telegram:send-pending`
9.  **Start in (optional)**: Masukkan **path folder project** aplikasi Anda.
    - Contoh: `C:\xampp\htdocs\absensi`
    - _Penting_: Jangan gunakan tanda petik.
10. Klik **Next**, lalu **Finish**.

### Mengatur Interval (Untuk Notifikasi)

Agar notifikasi berjalan setiap menit (bukan cuma sekali sehari):

1.  Klik kanan task yang baru dibuat di list, pilih **Properties**.
2.  Tab **General**: Centang **Run with highest privileges**.
3.  Tab **Triggers**, pilih trigger yang ada, klik **Edit**.
4.  Centang **Repeat task every**: Pilih atau ketik **1 minute**.
5.  Set **for a duration of**: Pilih **Indefinitely**.
6.  Klik OK lalu OK lagi.

### Daftar Argumen Command

Gunakan argumen berikut pada kolom **Add arguments**:

1.  **Notifikasi Telegram** (Set repeat 1 minute):
    `spark telegram:send-pending`
2.  **Notifikasi WhatsApp** (Set repeat 1 minute):
    `spark whatsapp:send-pending`
3.  **Notifikasi Android** (Set repeat 1 minute):
    `spark android:send-pending`
4.  **Auto Alpha** (Set trigger jam 23:00, tidak perlu repeat):
    `spark mark:alpha`
5.  **Proses Fingerprint** (Jika pakai ADMS, set repeat 5 minute):
    `spark attendance:process-biometric`

### Alternatif: Menggunakan Windows Service (NSSM)

Jika Task Scheduler bermasalah atau Anda ingin proses berjalan di background sebagai Service (agar auto-start saat boot tanpa login), Anda bisa menggunakan **NSSM (Non-Sucking Service Manager)**.

#### 1. Siapkan Script Worker

Karena Service harus berjalan terus-menerus (daemon), kita perlu membuat script `.bat` yang melakukan looping.

1.  Buat file baru bernama `worker.bat` di folder project Anda (sejajar dengan file `spark`).
2.  Isi file `worker.bat` dengan kode berikut:

    ```batch
    @echo off
    cd /d "%~dp0"

    :loop
    echo [%DATE% %TIME%] Menjalankan antrian...

    :: Jalankan command yang dibutuhkan
    php spark telegram:send-pending
    php spark whatsapp:send-pending
    php spark android:send-pending
    php spark mark:alpha
    php spark attendance:process-biometric

    :: Tunggu 60 detik sebelum loop berikutnya
    timeout /t 60 /nobreak >nul
    goto loop
    ```

#### 2. Install Service dengan NSSM

1.  Download **NSSM** dari [nssm.cc](https://nssm.cc/download).
2.  Extract `nssm.exe` (pilih folder `win64` jika Windows 64-bit) ke folder aman, misal `C:\nssm`.
3.  Buka **CMD** atau **PowerShell** sebagai **Administrator**.
4.  Jalankan perintah install:
    ```cmd
    C:\nssm\nssm.exe install AbsensiWorker
    ```
5.  Akan muncul window GUI NSSM:
    - **Path**: Browse ke file `worker.bat` yang Anda buat tadi.
    - **Startup directory**: Otomatis terisi folder project Anda.
    - **Service name**: Biarkan `AbsensiWorker`.
6.  Klik tab **I/O** (Opsional tapi disarankan):
    - **Output (stdout)**: Browse ke folder `writable/logs`, ketik nama file `service-out.log`.
    - **Error (stderr)**: Browse ke folder `writable/logs`, ketik nama file `service-err.log`.
7.  Klik **Install service**.

#### 3. Jalankan Service

1.  Buka **Services** (Win + R, ketik `services.msc`).
2.  Cari `AbsensiWorker`.
3.  Klik kanan -> **Start**.
4.  Pastikan Status menjadi **Running**.

Kini notifikasi akan berjalan otomatis di background setiap menit, bahkan jika server restart.

## Troubleshooting

- **Tidak bisa login?** Cek koneksi database di `.env`.
- **Halaman 404?** Pastikan file `.htaccess` di folder `public` sudah benar (bawaan CodeIgniter 4).
- **Telegram tidak jalan?** Pastikan Cronjob sudah diset dan Webhook sudah diregister (lihat menu Admin -> Master -> Registrasi Webhook).
