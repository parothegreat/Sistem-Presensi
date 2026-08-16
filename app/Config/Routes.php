<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Landing::index');
$routes->get('scanner', 'Scanner::index', ['filter' => 'role:admin,guru,petugas']);
$routes->get('test-api', 'Scanner::testApi'); // Debug endpoint
$routes->post('api/test/push', 'Test::testPush'); // Test POST endpoint

// Public login/logout routes at top-level
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::authenticate');
$routes->get('logout', 'Auth::logout');

// Top-level guru and siswa dashboards (protected by login + role)
$routes->group('', ['filter' => 'adminAuth'], function ($routes) {
    $routes->get('guru/dashboard', 'Guru::dashboard', ['filter' => 'role:guru']);
    $routes->get('guru/permission', 'Guru::permissions', ['filter' => 'role:guru']);
    $routes->get('guru/permission/approve/(:num)', 'Guru::approvePermission/$1', ['filter' => 'role:guru']);
    $routes->get('guru/permission/reject/(:num)', 'Guru::rejectPermission/$1', ['filter' => 'role:guru']);

    $routes->get('siswa/dashboard', 'Siswa::dashboard', ['filter' => 'role:siswa']);
    $routes->get('siswa/attendance', 'Siswa::attendance');

    // Permission Routes
    $routes->get('siswa/permission', 'Siswa::permission');
    $routes->post('siswa/permission/submit', 'Siswa::submitPermission');

    // Profile & Password
    $routes->get('siswa/profile', 'Siswa::profile', ['filter' => 'role:siswa']);
    $routes->get('siswa/profile/edit', 'Siswa::editProfile', ['filter' => 'role:siswa']);
    $routes->post('siswa/profile', 'Siswa::updateProfile', ['filter' => 'role:siswa']);
    $routes->get('siswa/password/edit', 'Siswa::editPassword', ['filter' => 'role:siswa']);
    $routes->post('siswa/password', 'Siswa::updatePassword', ['filter' => 'role:siswa']);

    // Guru attendance management (wali kelas)
    $routes->get('guru/attendance', 'GuruAttendance::index', ['filter' => 'role:guru']);
    $routes->post('guru/attendance/mark-student', 'GuruAttendance::markStudent', ['filter' => 'role:guru']);
    $routes->get('guru/attendance/student/(:num)/history', 'GuruAttendance::studentHistory/$1', ['filter' => 'role:guru']);
    $routes->get('guru/profile', 'Guru::profile', ['filter' => 'role:guru']);
    $routes->get('guru/profile/edit', 'Guru::editProfile', ['filter' => 'role:guru']);
    $routes->post('guru/profile', 'Guru::updateProfile', ['filter' => 'role:guru']);
    $routes->get('guru/password/edit', 'Guru::editPassword', ['filter' => 'role:guru']);
    $routes->post('guru/password', 'Guru::updatePassword', ['filter' => 'role:guru']);

    // Guru attendance check-in/check-out
    $routes->post('guru/check-in', 'Guru::checkIn', ['filter' => 'role:guru']);
    $routes->post('guru/check-out', 'Guru::checkOut', ['filter' => 'role:guru']);
    $routes->get('guru/today-status', 'Guru::todayStatus', ['filter' => 'role:guru']);
    $routes->get('guru/attendance-history', 'Guru::attendance', ['filter' => 'role:guru']);

    // Admin profile (accessible to admin role)
    $routes->get('admin/profile', 'Admin::profile', ['filter' => 'role:admin']);
    $routes->get('admin/profile/edit', 'Admin::editProfile', ['filter' => 'role:admin']);
    $routes->post('admin/profile', 'Admin::updateProfile', ['filter' => 'role:admin']);
});

// Petugas Dashboard (Officers)
$routes->group('petugas', ['filter' => 'adminAuth'], function ($routes) {
    $routes->get('dashboard', 'Petugas::dashboard', ['filter' => 'role:petugas']);
});

// Attendance API (device -> scan)
$routes->post('api/attendance/scan', 'Attendance::scan');
$routes->post('api/attendance/mark', 'Attendance::markManual');
$routes->get('api/attendance/today', 'Attendance::today');

// QR Code API
$routes->post('api/qrcode/scan', 'QrCode::scan');

// Biometric API (from finger machine)
$routes->post('api/attendance/receive-biometric', 'Api\BiometricController::receiveBiometric');

// Student API
$routes->get('api/students/(:segment)', 'Api\Student::getByNis/$1');
$routes->get('api/students/(:segment)/attendance-history', 'Api\Student::getAttendanceHistory/$1');

// RFID API (IoT reader integration)
$routes->post('api/rfid/tap', 'Api\RfidController::tap');
$routes->get('api/rfid/status/(:segment)', 'Api\RfidController::status/$1');
$routes->get('api/rfid/verify/(:segment)', 'Api\RfidController::verify/$1');

// RFID Scanner (USB reader - public interface)
$routes->get('rfid-scanner', 'RfidScanner::index', ['filter' => 'role:admin,guru,petugas']);
$routes->post('api/rfid-scan', 'RfidScanner::scan');

// ADMS Integration (Fingerprint Machines)
$routes->group('iclock', function ($routes) {
    $routes->post('cdata', 'Api\AdmsController::cdata');
    $routes->get('cdata', 'Api\AdmsController::cdata');
    $routes->post('getrequest', 'Api\AdmsController::getrequest'); // Some devices use POST
    $routes->get('getrequest', 'Api\AdmsController::getrequest');
    $routes->post('devicecmd', 'Api\AdmsController::devicecmd');
});

// Device Token API (Android app registration)
$routes->post('api/device-token/register', 'Api\DeviceTokenController::register');
$routes->post('api/device-token/unregister', 'Api\DeviceTokenController::unregister');
$routes->post('api/device-token/refresh', 'Api\DeviceTokenController::refresh');
$routes->get('api/device-token/student/(:segment)', 'Api\DeviceTokenController::getByStudent/$1');

// Biometric Logs Viewer
$routes->get('tools/check_biometric_logs', function () {
    return view('biometric_logs_viewer');
});

// Telegram notifications
$routes->get('telegram/send-pending', 'TelegramNotifications::sendPending');

// Telegram webhook (set webhook to https://your-host/telegram/webhook)
$routes->post('telegram/webhook', 'TelegramBot::webhook');

// Telegram webhook test (local only - remove in production!)
$routes->get('telegram/test-link', 'TelegramBot::testLink');

// TV Lobby / Kiosk Mode (Public but hidden)
$routes->get('lobby', 'Lobby::index');
$routes->get('api/lobby/updates', 'Lobby::getUpdates');

// You can add other routes here as needed
$routes->group('admin', function ($routes) {
    // Public (login handled at top-level '/login')

    // Protected routes (all roles)
    $routes->group('', ['filter' => 'adminAuth'], function ($routes) {
        // Admin-only dashboard
        $routes->get('dashboard', 'Admin::dashboard', ['filter' => 'role:admin']);

        // (guru and siswa dashboards moved to top-level routes)

        // Admin user management (admin-only)
        $routes->get('users', 'User::index', ['filter' => 'role:admin']);
        $routes->get('users/create', 'User::create', ['filter' => 'role:admin']);
        $routes->post('users', 'User::store', ['filter' => 'role:admin']);
        $routes->get('users/(:num)/edit', 'User::edit/$1', ['filter' => 'role:admin']);
        $routes->post('users/(:num)', 'User::update/$1', ['filter' => 'role:admin']);
        $routes->get('users/(:num)/delete', 'User::delete/$1', ['filter' => 'role:admin']);

        // Teacher management (admin-only)
        $routes->get('teachers', 'Teacher::index', ['filter' => 'role:admin']);
        $routes->get('teachers/create', 'Teacher::create', ['filter' => 'role:admin']);
        $routes->post('teachers', 'Teacher::store', ['filter' => 'role:admin']);
        $routes->get('teachers/(:num)/edit', 'Teacher::edit/$1', ['filter' => 'role:admin']);
        $routes->post('teachers/(:num)', 'Teacher::update/$1', ['filter' => 'role:admin']);
        $routes->get('teachers/(:num)/delete', 'Teacher::delete/$1', ['filter' => 'role:admin']);
        $routes->get('teachers/import', 'Teacher::importPage', ['filter' => 'role:admin']);
        $routes->post('teachers/import', 'Teacher::import', ['filter' => 'role:admin']);

        // Student management (admin-only)
        $routes->get('students', 'Student::index', ['filter' => 'role:admin']);
        $routes->get('students/create', 'Student::create', ['filter' => 'role:admin']);
        $routes->post('students', 'Student::store', ['filter' => 'role:admin']);
        $routes->get('students/import', 'Student::importPage', ['filter' => 'role:admin']);
        $routes->post('students/import', 'Student::import', ['filter' => 'role:admin']);
        $routes->get('students/(:num)/edit', 'Student::edit/$1', ['filter' => 'role:admin']);
        $routes->post('students/(:num)', 'Student::update/$1', ['filter' => 'role:admin']);
        $routes->post('students/(:num)/generate-link', 'Student::generateLink/$1', ['filter' => 'role:admin']);
        $routes->get('students/(:num)/delete', 'Student::delete/$1', ['filter' => 'role:admin']);
        $routes->post('students/bulk-delete', 'Student::bulkDelete', ['filter' => 'role:admin']);

        // Telegram link settings (global PIN) - admin only
        $routes->get('telegram-settings', 'TelegramSettings::index', ['filter' => 'role:admin']);
        $routes->post('telegram-settings', 'TelegramSettings::save', ['filter' => 'role:admin']);
        $routes->get('telegram-webhook', 'TelegramSettings::registerWebhookPage', ['filter' => 'role:admin']);
        $routes->post('telegram-webhook', 'TelegramSettings::processRegisterWebhook', ['filter' => 'role:admin']);

        // Wali Kelas management
        $routes->get('walikelas', 'AdminWalikelas::index', ['filter' => 'role:admin']);
        $routes->get('walikelas/create', 'AdminWalikelas::create', ['filter' => 'role:admin']);
        $routes->post('walikelas', 'AdminWalikelas::store', ['filter' => 'role:admin']);
        $routes->get('walikelas/(:num)/edit', 'AdminWalikelas::edit/$1', ['filter' => 'role:admin']);
        $routes->post('walikelas/(:num)', 'AdminWalikelas::update/$1', ['filter' => 'role:admin']);
        $routes->get('walikelas/(:num)/delete', 'AdminWalikelas::delete/$1', ['filter' => 'role:admin']);

        // Attendance management
        $routes->get('attendance', 'AdminAttendance::index', ['filter' => 'role:admin']);
        $routes->get('attendance/get-stats', 'AdminAttendance::getStats', ['filter' => 'role:admin']);
        $routes->get('attendance/get-guru-stats', 'AdminAttendance::getGuruStats', ['filter' => 'role:admin']);
        $routes->post('attendance/mark-student', 'AdminAttendance::markStudent', ['filter' => 'role:admin']);
        $routes->get('attendance/insert-guru', 'AdminAttendance::insertGuru', ['filter' => 'role:admin']);
        $routes->post('attendance/mark-guru', 'AdminAttendance::markGuru', ['filter' => 'role:admin']);
        $routes->get('attendance/laporan', 'AdminAttendance::report', ['filter' => 'role:admin']);
        $routes->get('attendance/rekap', 'AdminAttendance::rekap', ['filter' => 'role:admin']);
        $routes->get('attendance/guru-riwayat', 'AdminAttendance::guruRiwayat', ['filter' => 'role:admin']);
        $routes->get('attendance/guru-rekap', 'AdminAttendance::guruRekap', ['filter' => 'role:admin']);
        $routes->get('attendance/export-excel', 'AdminAttendance::exportExcel', ['filter' => 'role:admin']);
        $routes->get('attendance/export-excel-rekap', 'AdminAttendance::exportExcelRekap', ['filter' => 'role:admin']);
        $routes->get('attendance/export-guru-riwayat', 'AdminAttendance::exportGuruRiwayat', ['filter' => 'role:admin']);
        $routes->get('attendance/export-guru-rekap', 'AdminAttendance::exportGuruRekap', ['filter' => 'role:admin']);
        $routes->get('attendance/(:num)/edit', 'AdminAttendance::edit/$1', ['filter' => 'role:admin']);
        $routes->post('attendance/(:num)', 'AdminAttendance::update/$1', ['filter' => 'role:admin']);

        // Guru Attendance Edit
        $routes->get('attendance/guru/(:num)/edit', 'AdminAttendance::editGuru/$1', ['filter' => 'role:admin']);
        $routes->post('attendance/guru/(:num)', 'AdminAttendance::updateGuru/$1', ['filter' => 'role:admin']);

        // QR Code management (admin-only)
        $routes->get('qrcode/print-cards', 'QrCode::printCards', ['filter' => 'role:admin']);
        $routes->get('qrcode/print-cards-guru', 'QrCode::printCardsTeacher', ['filter' => 'role:admin']);
        $routes->post('qrcode/generate-all', 'QrCode::generateAll', ['filter' => 'role:admin']);
        $routes->post('qrcode/generate/(:num)', 'QrCode::generateStudent/$1', ['filter' => 'role:admin']);
        $routes->get('qrcode/scanner', 'QrCode::scanner', ['filter' => 'role:admin']);

        // RFID Tester tool
        $routes->get('rfid-tester', function () {
            return view('tools/rfid_tester');
        }, ['filter' => 'role:admin']);

        // Card Template Settings (admin-only)
        $routes->get('card-template', 'Admin\CardTemplateController::index', ['filter' => 'role:admin']);
        $routes->post('card-template/save', 'Admin\CardTemplateController::save', ['filter' => 'role:admin']);

        // Help & Tutorial (admin-only)
        $routes->get('help', 'Help::index', ['filter' => 'role:admin']);
        $routes->get('help/(:alpha)', 'Help::tutorial/$1', ['filter' => 'role:admin']);

        // Shift management (admin-only)
        $routes->get('shifts', 'AdminShift::index', ['filter' => 'role:admin']);
        $routes->get('shifts/create', 'AdminShift::create', ['filter' => 'role:admin']);
        $routes->post('shifts', 'AdminShift::store', ['filter' => 'role:admin']);
        $routes->get('shifts/(:num)/edit', 'AdminShift::edit/$1', ['filter' => 'role:admin']);
        $routes->post('shifts/(:num)', 'AdminShift::update/$1', ['filter' => 'role:admin']);
        $routes->get('shifts/(:num)/delete', 'AdminShift::delete/$1', ['filter' => 'role:admin']);

        // Holiday management (admin-only)
        $routes->get('holidays', 'AdminHoliday::index', ['filter' => 'role:admin']);
        $routes->get('holidays/create', 'AdminHoliday::create', ['filter' => 'role:admin']);
        $routes->post('holidays', 'AdminHoliday::store', ['filter' => 'role:admin']);
        $routes->get('holidays/(:num)/edit', 'AdminHoliday::edit/$1', ['filter' => 'role:admin']);
        $routes->post('holidays/(:num)', 'AdminHoliday::update/$1', ['filter' => 'role:admin']);
        $routes->get('holidays/(:num)/delete', 'AdminHoliday::delete/$1', ['filter' => 'role:admin']);

        // Teacher Schedule management (admin-only)
        $routes->get('teacher-schedule', 'AdminTeacherSchedule::index', ['filter' => 'role:admin']);
        $routes->get('teacher-schedule/create', 'AdminTeacherSchedule::create', ['filter' => 'role:admin']);
        $routes->get('teacher-schedule/set-schedule/(:num)', 'AdminTeacherSchedule::setSchedule/$1', ['filter' => 'role:admin']);
        $routes->post('teacher-schedule/save-schedule/(:num)', 'AdminTeacherSchedule::saveSchedule/$1', ['filter' => 'role:admin']);
        $routes->get('teacher-schedule/edit/(:num)', 'AdminTeacherSchedule::edit/$1', ['filter' => 'role:admin']);
        $routes->post('teacher-schedule/delete/(:num)', 'AdminTeacherSchedule::delete/$1', ['filter' => 'role:admin']);

        // Logs monitoring (admin-only)
        $routes->get('logs', 'Admin\LogsController::index', ['filter' => 'role:admin']);
        $routes->get('logs/biometric', 'Admin\LogsController::biometricLogs', ['filter' => 'role:admin']);
        $routes->get('logs/biometric-json', 'Admin\LogsController::biometricLogsJson', ['filter' => 'role:admin']);
        $routes->get('logs/android-json', 'Admin\LogsController::androidLogsJson', ['filter' => 'role:admin']);
        $routes->get('logs/telegram-json', 'Admin\LogsController::telegramLogsJson', ['filter' => 'role:admin']);
        $routes->get('logs/whatsapp-json', 'Admin\LogsController::whatsappLogsJson', ['filter' => 'role:admin']);
        $routes->get('logs/android', 'Admin\LogsController::androidNotifications', ['filter' => 'role:admin']);
        $routes->get('logs/telegram', 'Admin\LogsController::telegramNotifications', ['filter' => 'role:admin']);
        $routes->get('logs/whatsapp', 'Admin\LogsController::whatsappNotifications', ['filter' => 'role:admin']);

        // Notification Templates (admin-only) - NEW
        $routes->get('notification-templates', 'AdminNotificationTemplate::index', ['filter' => 'role:admin']);
        $routes->get('notification-templates/(:num)/edit', 'AdminNotificationTemplate::edit/$1', ['filter' => 'role:admin']);
        $routes->post('notification-templates/(:num)', 'AdminNotificationTemplate::update/$1', ['filter' => 'role:admin']);

        // Information / Announcements (admin-only) - NEW
        $routes->get('information', 'Admin\InformationController::index', ['filter' => 'role:admin']); // Redirects or default
        $routes->get('information/(:segment)', 'Admin\InformationController::index/$1', ['filter' => 'role:admin']); // type: student/teacher
        $routes->get('information/(:segment)/create', 'Admin\InformationController::create/$1', ['filter' => 'role:admin']);
        $routes->post('information/(:segment)', 'Admin\InformationController::store/$1', ['filter' => 'role:admin']);
        $routes->get('information/(:num)/edit', 'Admin\InformationController::edit/$1', ['filter' => 'role:admin']); // ID Edit
        $routes->post('information/(:num)', 'Admin\InformationController::update/$1', ['filter' => 'role:admin']); // ID Update
        $routes->get('information/(:num)/delete', 'Admin\InformationController::delete/$1', ['filter' => 'role:admin']);

        // Settings (admin-only) - NEW
        $routes->get('settings', 'Admin\SettingsController::index', ['filter' => 'role:admin']);
        $routes->post('settings/save', 'Admin\SettingsController::save', ['filter' => 'role:admin']);
        $routes->get('settings/backup', 'Admin\SettingsController::backup', ['filter' => 'role:admin']);

        // Activity Management (admin-only) - NEW
        $routes->get('activities', 'Admin\ActivityController::index', ['filter' => 'role:admin']);
        $routes->get('activities/create', 'Admin\ActivityController::create', ['filter' => 'role:admin']);
        $routes->post('activities', 'Admin\ActivityController::store', ['filter' => 'role:admin']);
        $routes->get('activities/(:num)', 'Admin\ActivityController::show/$1', ['filter' => 'role:admin']);
        $routes->get('activities/(:num)/print', 'Admin\ActivityController::print/$1', ['filter' => 'role:admin']);
        $routes->post('activities/(:num)/attendance/update-status', 'Admin\ActivityController::updateAttendanceStatus/$1', ['filter' => 'role:admin']);
        $routes->post('activities/(:num)/attendance/delete', 'Admin\ActivityController::deleteAttendance/$1', ['filter' => 'role:admin']);
        $routes->post('activities/(:num)/participant/delete', 'Admin\ActivityController::deleteParticipant/$1', ['filter' => 'role:admin']);
        $routes->get('activities/(:num)/edit', 'Admin\ActivityController::edit/$1', ['filter' => 'role:admin']);
        $routes->post('activities/update/(:num)', 'Admin\ActivityController::update/$1', ['filter' => 'role:admin']);
        $routes->get('activities/(:num)/delete', 'Admin\ActivityController::delete/$1', ['filter' => 'role:admin']);

        // add more protected admin routes here (siswa, perangkat, etc.)
    });

    // logout available at top-level '/logout'
});
