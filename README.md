# Sistem Presensi Sekolah - CodeIgniter 4

Aplikasi manajemen presensi siswa berbasis web dengan integrasi Telegram notifications, multi-role authentication, dan dashboard analytics.

## 📋 Fitur Utama

### 1. **Autentikasi Multi-Role**
- **Admin**: Full access ke semua modul
- **Guru**: Dashboard guru untuk kelas
- **Siswa**: Dashboard siswa untuk melihat absensi pribadi
- Password hashing dengan `password_hash()`
- Session-based authentication dengan validasi per request

### 2. **Sistem Absensi Lengkap**

#### Methods:
- **Scan Device**: Presensi via device (fingerprint/RFID/QR code)
- **Manual Mark**: Admin dapat menandai absensi manual dengan note
- **Auto-Alpha**: Job schedule yang otomatis mark alpha setiap hari jam 08:00

#### Statuses:
- `on_time` - Tepat Waktu (sebelum 07:15)
- `late` - Terlambat (setelah 07:15)
- `izin` - Izin
- `sakit` - Sakit
- `alpha` - Tidak hadir
- `unknown` - Status tidak diketahui

#### Features:
- Tracking masuk & pulang dengan timestamp
- Event logging untuk setiap perubahan status
- Notes/keterangan untuk setiap absensi
- Unique constraint pada (user_id, date)

### 3. **Integrasi Telegram Penuh**

#### Parent Linking:
- Orang tua link akun Telegram via command `/link NIS PIN`
- Global PIN untuk semua siswa (configurable by admin)
- Backward-compatible dengan per-student PINs
- Secure PIN validation dengan `hash_equals()` (timing-attack resistant)

#### Notifications:
- ✓ Realtime notifikasi saat siswa check-in/out
- ✓ Personalized message dengan NIS dan nama siswa
- ✓ Asynchronous queue processing (tidak block main request)
- ✓ Retry mechanism untuk failed notifications
- ✓ Status tracking (pending → sent → failed)

#### Configuration:
- **Bot Token**: isi `TELEGRAM_BOT_TOKEN` hanya di `.env`
- **Webhook**: POST `/telegram/webhook` melalui Cloudflare Tunnel di produksi
- **PIN Settings**: Admin dashboard untuk manage PIN

### 4. **Admin Dashboard**

#### Analytics:
- **Stats Cards**: Total Siswa, Absensi Hari Ini, Kehadiran Bulan Ini by Status
- **Chart.js Grafik**: Bar chart kehadiran siswa per hari dengan breakdown:
  - Tepat Waktu (Hijau)
  - Terlambat (Kuning)
  - Izin (Biru)
  - Sakit (Oranye)
  - Alpha (Merah)
- **Period**: Automatic untuk bulan berjalan dengan fallback data dummy

#### Manajemen Absensi:
- List view dengan filter advanced:
  - **Date Range**: Dari tanggal - Sampai tanggal
  - **Class**: Filter by kelas
  - **Search**: Cari by NIS atau nama siswa
- **Edit Attendance**: 
  - Edit waktu masuk/pulang dengan datetime picker
  - Ubah status masuk & pulang independent
  - Tambah keterangan/catatan
  - Track who updated (created_by field)
- **Statistics**: Auto-calculated per day (on_time, late, izin, sakit, alpha)

#### Master Data Management:
- User management (admin, guru, siswa)
- Teacher management (NIP, nama)
- Student management (NISN, nama, kelas, telegram_chat_id)
- Telegram PIN settings (set global PIN untuk semua siswa)

### 5. **API Endpoints**

#### Public API (untuk device/client)
- `POST /api/attendance/scan` - Record scan event
- `POST /api/attendance/mark` - Manual mark attendance

#### Telegram API
- `POST /telegram/webhook` - Incoming Telegram messages
- `GET /telegram/test-link` - Manual testing (development only)

## 🗄️ Struktur Database

### Core Tables

#### `users`
```sql
id (PK), username, password_hash, role (ENUM), is_active, created_at, updated_at
```

#### `students`
```sql
id (PK), user_id (FK), nis (VARCHAR, unique), full_name, class, 
telegram_chat_id (VARCHAR, nullable), created_at, updated_at
```

#### `teachers`
```sql
id (PK), user_id (FK), nip (VARCHAR), full_name, created_at, updated_at
```

#### `attendances` (Main table)
```sql
id (PK), user_id (FK), date (DATE), 
masuk_at (DATETIME, nullable), masuk_status (ENUM), 
pulang_at (DATETIME, nullable), pulang_status (ENUM),
device_id (VARCHAR), note (TEXT), created_by (INT), 
created_at, updated_at
UNIQUE KEY (user_id, date)
INDEX (date)
```

#### `attendance_events` (Audit log)
```sql
id (PK), attendance_id (FK), user_id (FK), event_time, 
event_type (scan|manual_mark), device_id, payload (JSON), created_at
```

#### `telegram_notifications` (Queue)
```sql
id (PK), student_id (FK), chat_id, message (TEXT), 
payload (JSON), status (ENUM), attempts (INT), 
scheduled_at, created_at, updated_at
```

#### `telegram_links` (Backward compatibility)
```sql
id (PK), student_id (FK), token (VARCHAR, unique), 
expires_at, consumed_at, created_at
```

#### `telegram_link_config` (Global PIN)
```sql
id (PK), pin (VARCHAR), updated_at
```

## 🚀 Setup & Installation

### Prerequisites
- PHP 8.2–8.3
- MySQL 5.7+
- Composer
- Apache/Nginx dengan mod_rewrite
- Git (optional)

### Installation Steps

1. **Clone Project**
```bash
cd /var/www
git clone git@github.com:parothegreat/Sistem-Presensi.git presensi
cd presensi
composer install
```

2. **Environment Configuration**
```bash
cp env .env
```

Edit `.env`:
```ini
app.baseURL = 'http://localhost/presensi/'
database.default.hostname = localhost
database.default.database = presensi
database.default.username = root
database.default.password = ''
```

3. **Database Setup**
```bash
php spark migrate --all
```

4. **Run Application**
```bash
# Development server
php spark serve

# Or use WAMP/LAMP
# http://localhost/presensi/
```

5. **First Login**
- Akses `/login` pada base URL yang dikonfigurasi.
- Untuk database produksi, ikuti rotasi akun bawaan di `DEPLOY_UBUNTU.md` §8a.

6. **Telegram Webhook Setup** (Production)
```bash
# Get bot token dan register webhook
curl -X POST https://api.telegram.org/bot<TOKEN>/setWebhook \
  -d url=https://<your-domain>/telegram/webhook

# Test (local)
GET http://localhost/presensi/telegram/test-link?nis=S123456&pin=123456&chat_id=999999999
```

## 📖 Penggunaan

### Admin Panel
- **URL**: `/admin/dashboard`
- **Features**: 
  - Dashboard dengan grafik kehadiran bulanan
  - Manajemen user, guru, siswa (CRUD)
  - Manajemen absensi dengan advanced filtering
  - Telegram PIN settings

### API Examples

#### Scan Attendance
```bash
curl -X POST http://localhost/api/attendance/scan \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "device_id": "fingerprint_001",
    "timestamp": "2025-11-13 07:30:00"
  }'
```

Response:
```json
{
  "ok": true,
  "attendance": {
    "id": 1,
    "user_id": 1,
    "date": "2025-11-13",
    "masuk_at": "2025-11-13 07:30:00",
    "masuk_status": "on_time"
  }
}
```

#### Manual Mark
```bash
curl -X POST http://localhost/api/attendance/mark \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "date": "2025-11-13",
    "field": "masuk_status",
    "value": "izin",
    "note": "Izin sakit"
  }'
```

### Telegram Parent Features

**Link Chat ID** (Parent sends to bot):
```
/link S123456 123456
```

Response dari bot:
```
✅ Berhasil!

Chat Anda sudah ditautkan ke profil siswa:
NIS: S123456
Nama: Siswa Contoh
Kelas: XI-A

Terima kasih.
```

**Receive Notifications**:
- Saat masuk: `✓ Siswa A masuk pada 07:15 (Tepat Waktu)`
- Saat pulang: `⟲ Siswa A pulang pada 15:30`
- Manual mark: `⚑ Siswa A Izin pada 2025-11-13`

## ⏰ Scheduled Tasks

### Auto-Alpha Mark (08:00 daily)
```bash
# Manual trigger
php spark mark:alpha

# Cron schedule (production)
0 8 * * * cd /path/to/presensi && php spark mark:alpha >> /dev/null 2>&1
```

### Telegram Queue Processing
```bash
# Process max 50 pending notifications
php spark telegram:process --max=50

# Cron schedule (every 5 minutes)
*/5 * * * * cd /path/to/presensi && php spark telegram:process --max=50 >> /dev/null 2>&1
```

## 🛠️ CLI Tools

Berada di folder `tools/`:

### `set_global_pin.php`
Set/update global PIN untuk semua siswa
```bash
php tools/set_global_pin.php 123456
```

### `check_telegram_config.php`
Lihat current global PIN & timestamp
```bash
php tools/check_telegram_config.php
```

### `enqueue_notification.php`
Manual enqueue notification untuk testing
```bash
php tools/enqueue_notification.php S123456 "Pesan notifikasi test"
```

### `check_student.php`
Verify student data & telegram_chat_id
```bash
php tools/check_student.php S123456
```

### `test_attendance_data.php`
Show attendance stats & verify data integrity
```bash
php tools/test_attendance_data.php
```

### `test_chart_query.php`
Debug chart data query untuk bulan berjalan
```bash
php tools/test_chart_query.php
```

## 🗺️ Routes Map

### Public
- `GET /` - Landing page
- `GET /login` - Login form
- `POST /login` - Process authentication
- `GET /logout` - Logout & destroy session
- `POST /api/attendance/scan` - Scan API
- `POST /api/attendance/mark` - Manual mark API
- `POST /telegram/webhook` - Telegram webhook
- `GET /telegram/test-link` - Test Telegram linking

### Admin (Protected + Role:admin)
- `GET /admin/dashboard` - Dashboard with chart
- `GET /admin/users` - User list & CRUD
- `GET /admin/students` - Student list & CRUD
- `GET /admin/teachers` - Teacher list & CRUD
- `GET /admin/attendance` - Attendance list with filters
- `GET /admin/attendance/:id/edit` - Edit form
- `POST /admin/attendance/:id` - Update attendance
- `GET /admin/telegram-settings` - PIN config form
- `POST /admin/telegram-settings` - Update PIN

### Guru (Protected + Role:guru)
- `GET /guru/dashboard` - Dashboard guru

### Siswa (Protected + Role:siswa)
- `GET /siswa/dashboard` - Dashboard siswa

## 💾 Models

### `UserModel`
```php
table: users
Methods: findByUsername(), getByRole(), etc
```

### `StudentModel`
```php
table: students
Relations: user_id → users
Methods: getByNIS(), getByClass(), etc
```

### `AttendanceModel`
```php
table: attendances
Methods: getByDate(), getByStudentMonth(), etc
```

### `TelegramNotificationModel`
```php
table: telegram_notifications
Methods: getPending(), markSent(), etc
```

### `TelegramLinkConfigModel`
```php
table: telegram_link_config
Methods: getCurrentPIN(), etc
```

## 👨‍💼 Controllers

### `Admin.php` - Dashboard
- `dashboard()`: Fetch chart data & stats, render with Chart.js

### `AdminAttendance.php` - Attendance Management
- `index()`: List dengan date range, class, search filters
- `edit()`: Show edit form
- `update()`: Save changes

### `Attendance.php` - API
- `scan()`: Process device scan events
- `markManual()`: Handle manual marking via API

### `TelegramBot.php` - Telegram Integration
- `webhook()`: Parse /link commands, validate PIN, update chat_id
- `testLink()`: Manual testing endpoint
- `sendMessage()`: Utility untuk kirim message ke Telegram

### `TelegramSettings.php` - Admin PIN Config
- `index()`: Show current PIN form
- `save()`: Update global PIN

## 🎨 Frontend

### Layouts
- `layouts/admin.php` - Master layout dengan navbar, sidebar, responsive menu

### Admin Views
- `admin/dashboard.php` - Stats cards + Chart.js grafik
- `admin/attendance/index.php` - Filter form + table + stats
- `admin/attendance/edit.php` - DateTime picker + select dropdown
- `admin/telegram_settings.php` - PIN input form
- `admin/users/`, `admin/students/`, `admin/teachers/` - CRUD pages

### Styling
- Tailwind CSS (CDN)
- Custom responsive classes
- Color-coded badges for status

## 🔐 Security Features

✅ **Authentication & Authorization**
- Password hashing dengan `password_hash()` & `password_verify()`
- Session validation pada setiap request
- Role-based access control (filter)
- CSRF protection (CI4 default)

✅ **Data Protection**
- SQL injection prevention via prepared statements
- XSS protection dengan `esc()` function
- PIN hashing dengan `hash_equals()` (timing-attack resistant)
- Input validation & sanitization

✅ **API Security**
- Attendance scan dengan user_id validation
- Manual mark dengan permission check
- Telegram webhook signature verification (optional)

## ⚡ Performance Optimization

- Database indexing pada frequently filtered columns
- Asynchronous notification queue (tidak block requests)
- Lazy loading untuk dropdown filters
- Chart query optimized dengan GROUP BY
- Attendance unique constraint untuk prevent duplicates

## 🔧 Troubleshooting

### Dashboard Grafik Tidak Muncul
- Check browser console (F12) untuk JavaScript error
- Verify Chart.js CDN accessible
- Ensure data passed dari controller ke view
- Check `attendances` table punya data untuk bulan ini

**Solution**:
```bash
# Test query
php tools/test_chart_query.php

# Check data
php tools/test_attendance_data.php
```

### Telegram Notifikasi Tidak Terima
- Verify bot token di `.env` atau controller
- Check webhook registered di Telegram API
- Verify `telegram_chat_id` di students table
- Check notification queue di `telegram_notifications` table
- Process queue manually: `php spark telegram:process`

### Attendance Scan Tidak Tercatat
- Check POST request ke `/api/attendance/scan`
- Verify user_id exists di database
- Check `attendance_events` table untuk debug logs
- Verify device_id format

### Auto-Alpha Tidak Berjalan
- Verify cron job configured: `0 8 * * * php spark mark:alpha`
- Check logs: `writable/logs/`
- Manual trigger: `php spark mark:alpha`

## 📚 Development Notes

### Adding New Attendance Status
1. Update ENUM di migration:
```php
'masuk_status' => ['type' => 'ENUM', 'constraint' => [..., 'new_status']]
```
2. Update badge color di view:
```php
'new_status' => 'bg-purple-100 text-purple-800'
```
3. Update controller stats calculation

### Adding New Notification Type
1. Create message template di `TelegramBot::sendMessage()`
2. Enqueue ke `telegram_notifications`:
```php
$tnModel->insert([
  'student_id' => $student['id'],
  'chat_id' => $chat,
  'message' => $message,
  'status' => 'pending'
]);
```
3. Process via `php spark telegram:process`

### Testing Locally
```bash
# Test scan
curl -X POST http://localhost/api/attendance/scan \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"device_id":"test","timestamp":"2025-11-13 09:00:00"}'

# Test manual mark
curl -X POST http://localhost/api/attendance/mark \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"date":"2025-11-13","field":"masuk_status","value":"izin"}'

# Test Telegram link
curl "http://localhost/telegram/test-link?nis=S123456&pin=123456&chat_id=999999999"
```

## 📦 Technology Stack

| Component | Technology |
|-----------|------------|
| Framework | CodeIgniter 4.6.3 |
| PHP | 8.0+ |
| Database | MySQL 5.7+ |
| Frontend | Tailwind CSS + Chart.js |
| APIs | Telegram Bot API |
| Authentication | Session-based |
| Password | `password_hash()` |

## 📝 Version History

- **v1.0.0** (Nov 13, 2025) - Initial release
  - Core attendance system
  - Admin dashboard with chart
  - Telegram integration with global PIN
  - Date range attendance filtering

## 📧 Support & Contact

Untuk bug report atau feature request, hubungi tim development atau buat issue di repository.

---

**Developed**: November 2025  
**Project**: Sistem Presensi Sekolah  
**Status**: Production Ready  
**License**: Proprietary
