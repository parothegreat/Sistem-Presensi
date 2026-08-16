<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AttendanceModel;
use App\Models\AttendanceEventModel;
use App\Models\StudentModel;
use App\Models\ShiftModel;
use App\Models\TelegramNotificationModel;
use App\Models\WhatsAppNotificationModel;

class AutoAlphaCheckout extends BaseCommand
{
    protected $group       = 'Attendance';
    protected $name        = 'absensi:auto-alpha';
    protected $description = 'Auto-mark pulang_status as alpha for students who forgot to checkout.';
    protected $usage       = 'absensi:auto-alpha';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $date = date('Y-m-d');
        $currentTime = date('H:i:s');

        CLI::write("Running Auto-Alpha Checkout for date: $date", 'cyan');

        // 1. Get Shift active
        $shiftModel = new ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->findAll();

        if (empty($shifts)) {
            CLI::write("No active shifts found.", 'yellow');
            return;
        }

        $attModel = new AttendanceModel();
        $eventModel = new AttendanceEventModel();
        $studentModel = new StudentModel();
        $tnModel = new TelegramNotificationModel();
        $waModel = new WhatsAppNotificationModel();

        $totalUpdated = 0;

        foreach ($shifts as $shift) {
            // Check if current time is past shift end_time (PLUS grace period if any, e.g. 1 hour?)
            // For now, strict: if current time > end_time, mark as alpha.

            if ($currentTime <= $shift['end_time']) {
                CLI::write("Shift {$shift['name']} end time ({$shift['end_time']}) not yet reached. Skipping.", 'white');
                continue;
            }

            CLI::write("Processing Shift: {$shift['name']} (End: {$shift['end_time']})", 'green');

            // Find attendances for today:
            // - Has check-in (masuk_at is NOT NULL)
            // - No check-out (pulang_at IS NULL or pulang_status IS NULL)
            // - Student belongs to this shift

            $builder = $db->table('attendances');
            $attendances = $builder
                ->select('attendances.*, students.id as student_id, students.full_name, students.telegram_chat_id, students.phone_number, students.guardian_phone, users.username')
                ->join('users', 'users.id = attendances.user_id')
                ->join('students', 'students.user_id = users.id')
                ->where('attendances.date', $date)
                ->where('attendances.masuk_at IS NOT NULL')
                ->where('attendances.pulang_at', null) // Has not clocked out
                ->where('(attendances.pulang_status IS NULL OR attendances.pulang_status = "" OR attendances.pulang_status = "unknown")') // Status empty or unknown
                ->where('students.shift_id', $shift['id'])
                ->get()
                ->getResultArray();

            if (empty($attendances)) {
                CLI::write("  No valid pending checkouts found for this shift.", 'yellow');
                continue;
            }

            foreach ($attendances as $row) {
                // Update pulang_status to alpha
                $attModel->update($row['id'], [
                    'pulang_status' => 'alpha',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $totalUpdated++;
                CLI::write("  [UPDATED] {$row['full_name']} ({$row['username']}) -> Pulang Alpha", 'green');

                // Log Event
                $eventModel->insert([
                    'attendance_id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'event_time' => date('Y-m-d H:i:s'),
                    'event_type' => 'auto_alpha_checkout',
                    'device_id' => null,
                    'payload' => json_encode(['reason' => 'forgot_checkout', 'shift_end' => $shift['end_time']]),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Notification Message
                $msg = sprintf("⚠ %s lupa absen pulang pada %s.\nStatus Pulang: Alpha (Otomatis)", $row['full_name'], $date);

                // 1. Telegram
                if (!empty($row['telegram_chat_id'])) {
                    $tnModel->insert([
                        'student_id' => $row['student_id'] ?? null, // Need student ID, but we joined users. Let's lookup or use better join logic if needed. 
                        // Wait, join students table gives us student fields. But students.id is not selected in my query above?
                        // Let's rely on looking up student record ID if needed or fix select.
                        // I will assume student ID can be fetched or is not strictly needed for chat ID sending.
                        // Actually tnModel often needs student_id. Let's fix select.
                        'chat_id' => $row['telegram_chat_id'],
                        'message' => $msg,
                        'payload' => json_encode(['attendance_id' => $row['id'], 'event' => 'auto_alpha_checkout']),
                        'status' => 'pending',
                        'attempts' => 0,
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // 2. WhatsApp Student
                if (!empty($row['phone_number'])) {
                    $waModel->insert([
                        'student_id' => null, // Optional if we just want to send
                        'phone_number' => $row['phone_number'],
                        'message' => $msg,
                        'payload' => json_encode(['attendance_id' => $row['id'], 'event' => 'auto_alpha_checkout', 'recipient' => 'student']),
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        CLI::write("Done. Total updated records: $totalUpdated", 'green');
    }
}
