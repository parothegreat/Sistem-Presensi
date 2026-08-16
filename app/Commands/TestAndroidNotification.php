<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestAndroidNotification extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:android-flow';
    protected $description = 'Test Android notification flow';

    public function run(array $params = [])
    {
        $db = \Config\Database::connect();

        // 1. Get a student
        CLI::write("\n" . str_repeat("=", 50), 'white');
        CLI::write("STEP 1: Get Student", 'white');
        CLI::write(str_repeat("=", 50), 'white');

        $student = $db->table('students')->limit(1)->get()->getFirstRow('array');
        if (!$student) {
            CLI::error("No students in database!");
            return;
        }
        CLI::write("✓ Student ID: " . $student['id']);
        CLI::write("✓ NIS: " . $student['nis']);
        CLI::write("✓ Name: " . $student['full_name']);
        CLI::write("✓ Class: " . $student['class']);

        // 2. Insert test device token
        CLI::write("\n" . str_repeat("=", 50), 'white');
        CLI::write("STEP 2: Insert Test Device Token", 'white');
        CLI::write(str_repeat("=", 50), 'white');

        $testToken = 'test_device_token_' . time() . '_' . rand(1000, 9999);
        $npsn = getenv('SCHOOL_NPSN') ?: '69754539';
        $db->table('student_device_tokens')->insert([
            'student_id' => $student['id'],
            'npsn' => $npsn,
            'device_token' => $testToken,
            'device_name' => 'Test Device',
            'app_version' => '1.0',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        CLI::write("✓ Device token inserted: " . substr($testToken, 0, 30) . "...");

        // 3. Queue test Android notification (MASUK event)
        CLI::write("\n" . str_repeat("=", 50), 'white');
        CLI::write("STEP 3: Queue Android Notification", 'white');
        CLI::write(str_repeat("=", 50), 'white');

        $db->table('android_notifications')->insert([
            'student_id' => $student['id'],
            'nis' => $student['nis'],
            'device_token' => $testToken,
            'title' => 'Test Masuk - ' . date('Y-m-d H:i:s'),
            'message' => 'Test notification dari PHP Laravel - Silakan cek Android device',
            'notification_status' => 'pending',
            'attempts' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        CLI::write("✓ Notification queued for sending");

        // 4. Verify
        CLI::write("\n" . str_repeat("=", 50), 'white');
        CLI::write("STEP 4: Verify Queue", 'white');
        CLI::write(str_repeat("=", 50), 'white');

        $pending = $db->table('android_notifications')
            ->where('notification_status', 'pending')
            ->countAllResults();
        CLI::write("✓ Pending notifications in queue: " . $pending, 'green');

        $device = $db->table('student_device_tokens')
            ->where('device_token', $testToken)
            ->get()
            ->getFirstRow('array');
        CLI::write("✓ Device active: " . ($device['is_active'] ? 'YES ✓' : 'NO ✗'));

        // 5. Next steps
        CLI::write("\n" . str_repeat("=", 50), 'white');
        CLI::write("✅ TEST DATA READY!", 'green');
        CLI::write(str_repeat("=", 50), 'white');
        CLI::write("\nNext step - Run sender command:");
        CLI::write("   php spark android:send-pending --verbose", 'yellow');
        CLI::write("\nOr with dry-run first:");
        CLI::write("   php spark android:send-pending --dry-run --verbose\n", 'yellow');
    }
}
