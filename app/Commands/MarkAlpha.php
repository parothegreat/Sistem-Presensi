<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AttendanceModel;
use App\Models\AttendanceEventModel;
use App\Models\StudentModel;
use App\Models\TelegramNotificationModel;
use App\Models\WhatsAppNotificationModel;
use App\Models\SchoolHolidayModel;

class MarkAlpha extends BaseCommand
{
    protected $group = 'Attendance';
    protected $name = 'mark:alpha';
    protected $description = 'Auto-mark students without check-in as alpha';
    protected $usage = 'mark:alpha [options]';
    protected $arguments = [];
    protected $options = [
        '--date' => 'Target date (default: today, format: YYYY-MM-DD)',
        '--no-skip-weekend' => 'Force mark alpha on weekend/holiday',
    ];

    public function run(array $params = [])
    {
        $date = $params['date'] ?? date('Y-m-d');
        $skipWeekend = !array_key_exists('no-skip-weekend', $params);

        CLI::write("Starting mark:alpha for date: $date", 'cyan');
        if (!$skipWeekend) {
            CLI::write("Force mode: Will mark alpha even on holidays", 'yellow');
        }

        // Check Level 1: Recurring holidays from .env
        if ($skipWeekend && $this->isRecurringHoliday($date)) {
            CLI::write("Skipping $date - recurring holiday configured in HOLIDAY_DAYS", 'yellow');
            return;
        }

        // Check Level 2: Special holidays from database
        if ($skipWeekend && $this->isSpecialHoliday($date)) {
            CLI::write("Skipping $date - special holiday in school_holidays table", 'yellow');
            return;
        }

        $attModel = new AttendanceModel();
        $eventModel = new AttendanceEventModel();
        $studentModel = new StudentModel();
        $tnModel = new TelegramNotificationModel();
        $waModel = new WhatsAppNotificationModel();
        $shiftModel = new \App\Models\ShiftModel();

        // Auto-detect applicable shift(s) based on current time
        $currentTime = date('H:i:s');
        $applicableShifts = $this->getApplicableShifts($shiftModel, $currentTime);

        if (empty($applicableShifts)) {
            CLI::write("No applicable shift at current time ($currentTime). Skipping mark:alpha.", 'yellow');
            return;
        }

        CLI::write("Found " . count($applicableShifts) . " applicable shift(s) for time: $currentTime", 'green');

        $totalMarked = 0;

        // Process each applicable shift
        foreach ($applicableShifts as $applicableShift) {
            CLI::write("Processing shift: {$applicableShift['name']} (deadline: {$applicableShift['checkin_deadline']})", 'cyan');

            // Find all students with this shift who are active
            $db = \Config\Database::connect();
            $builder = $db->table('users');
            $students = $builder
                ->select('users.id, users.username')
                ->join('students', 'students.user_id = users.id', 'left')
                ->where('users.role', 'siswa')
                ->where('users.is_active', 1)
                ->where('students.shift_id', $applicableShift['id'])
                ->get()
                ->getResultArray();

            if (empty($students)) {
                CLI::write("  No students found for this shift. Skipping.", 'yellow');
                continue;
            }

            CLI::write("  Found " . count($students) . " students", 'cyan');

            $marked = 0;
            foreach ($students as $student) {
                $studentId = $student['id'];

                // Check if attendance exists for today
                $att = $attModel->where('user_id', $studentId)->where('date', $date)->first();

                if (!$att) {
                    // Create new attendance record marked as alpha
                    $attModel->insert([
                        'user_id' => $studentId,
                        'date' => $date,
                        'masuk_status' => 'alpha',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $attId = $attModel->getInsertID();
                    CLI::write("  ✓ {$student['username']}: marked alpha (new record)", 'green');
                    $marked++;

                    // Log event
                    $eventModel->insert([
                        'attendance_id' => $attId,
                        'user_id' => $studentId,
                        'event_time' => date('Y-m-d H:i:s'),
                        'event_type' => 'auto_alpha',
                        'device_id' => null,
                        'payload' => json_encode(['reason' => 'no_checkin_by_cutoff', 'cutoff' => $applicableShift['checkin_deadline'], 'shift_id' => $applicableShift['id']]),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    // Queue telegram notification
                    try {
                        $stud = $studentModel->where('user_id', $studentId)->first();
                        if ($stud && !empty($stud['telegram_chat_id'])) {
                            $msg = sprintf("⚠ %s tidak hadir pada %s\nStatus: Alpha", $stud['full_name'] ?? $student['username'], $date);
                            $tnModel->insert([
                                'student_id' => $stud['id'],
                                'chat_id' => $stud['telegram_chat_id'],
                                'message' => $msg,
                                'payload' => json_encode(['attendance_id' => $attId, 'event' => 'auto_alpha']),
                                'status' => 'pending',
                                'attempts' => 0,
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }

                    // Queue WhatsApp notification to student
                    try {
                        $stud = $studentModel->where('user_id', $studentId)->first();
                        if ($stud && !empty($stud['phone_number'])) {
                            $message = "⚠️ Tidak Hadir\nNama: {$stud['full_name']}\nTanggal: {$date}\nStatus: Alpha";
                            $waModel->insert([
                                'student_id' => $stud['id'],
                                'phone_number' => $stud['phone_number'],
                                'message' => $message,
                                'payload' => json_encode(['attendance_id' => $attId, 'event' => 'auto_alpha', 'recipient' => 'student']),
                                'status' => 'pending',
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }

                    // Queue WhatsApp notification to guardian
                    try {
                        $stud = $studentModel->where('user_id', $studentId)->first();
                        if ($stud && !empty($stud['guardian_phone'])) {
                            $message = "⚠️ Siswa Tidak Hadir\nNama: {$stud['full_name']}\nTanggal: {$date}\nStatus: Alpha";
                            $waModel->insert([
                                'student_id' => $stud['id'],
                                'phone_number' => $stud['guardian_phone'],
                                'message' => $message,
                                'payload' => json_encode(['attendance_id' => $attId, 'event' => 'auto_alpha', 'recipient' => 'guardian']),
                                'status' => 'pending',
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }
                } else {
                    // Attendance exists - check if needs to mark as alpha
                    $masukStatus = $att['masuk_status'] ?? 'unknown';
                    if ($masukStatus === 'unknown' && empty($att['masuk_at'])) {
                        $attModel->update($att['id'], [
                            'masuk_status' => 'alpha',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        CLI::write("  ✓ {$student['username']}: marked alpha (updated)", 'green');
                        $marked++;

                        // Log event
                        $eventModel->insert([
                            'attendance_id' => $att['id'],
                            'user_id' => $studentId,
                            'event_time' => date('Y-m-d H:i:s'),
                            'event_type' => 'auto_alpha',
                            'device_id' => null,
                            'payload' => json_encode(['reason' => 'no_checkin_by_cutoff', 'cutoff' => $applicableShift['checkin_deadline'], 'shift_id' => $applicableShift['id']]),
                            'created_at' => date('Y-m-d H:i:s')
                        ]);

                        // Queue telegram notification
                        try {
                            $stud = $studentModel->where('user_id', $studentId)->first();
                            if ($stud && !empty($stud['telegram_chat_id'])) {
                                $msg = sprintf("⚠ %s tidak hadir pada %s\nStatus: Alpha", $stud['full_name'] ?? $student['username'], $date);
                                $tnModel->insert([
                                    'student_id' => $stud['id'],
                                    'chat_id' => $stud['telegram_chat_id'],
                                    'message' => $msg,
                                    'payload' => json_encode(['attendance_id' => $att['id'], 'event' => 'auto_alpha']),
                                    'status' => 'pending',
                                    'attempts' => 0,
                                    'scheduled_at' => date('Y-m-d H:i:s'),
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        } catch (\Exception $e) {
                            // ignore
                        }

                        // Queue WhatsApp notification to student
                        try {
                            $stud = $studentModel->where('user_id', $studentId)->first();
                            if ($stud && !empty($stud['phone_number'])) {
                                $message = "⚠️ Tidak Hadir\nNama: {$stud['full_name']}\nTanggal: {$date}\nStatus: Alpha";
                                $waModel->insert([
                                    'student_id' => $stud['id'],
                                    'phone_number' => $stud['phone_number'],
                                    'message' => $message,
                                    'payload' => json_encode(['attendance_id' => $att['id'], 'event' => 'auto_alpha', 'recipient' => 'student']),
                                    'status' => 'pending',
                                    'scheduled_at' => date('Y-m-d H:i:s'),
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        } catch (\Exception $e) {
                            // ignore
                        }

                        // Queue WhatsApp notification to guardian
                        try {
                            $stud = $studentModel->where('user_id', $studentId)->first();
                            if ($stud && !empty($stud['guardian_phone'])) {
                                $message = "⚠️ Siswa Tidak Hadir\nNama: {$stud['full_name']}\nTanggal: {$date}\nStatus: Alpha";
                                $waModel->insert([
                                    'student_id' => $stud['id'],
                                    'phone_number' => $stud['guardian_phone'],
                                    'message' => $message,
                                    'payload' => json_encode(['attendance_id' => $att['id'], 'event' => 'auto_alpha', 'recipient' => 'guardian']),
                                    'status' => 'pending',
                                    'scheduled_at' => date('Y-m-d H:i:s'),
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                }
            }

            $totalMarked += $marked;
        }

        CLI::write("Completed: Marked $totalMarked total student(s) as alpha.", 'cyan');

        // Now handle Guru
        $this->markGuruAlpha($date, $skipWeekend);

        return 0;
    }

    /**
     * Mark guru as alpha if they have schedule but didn't check-in by jam_pulang (end of schedule)
     * Only trigger when current time >= jam_pulang
     */
    private function markGuruAlpha($date, $skipWeekend)
    {
        CLI::write("\nProcessing Guru attendance...", 'cyan');

        // Check holidays
        if ($skipWeekend && $this->isRecurringHoliday($date)) {
            CLI::write("  Skipping - recurring holiday", 'yellow');
            return;
        }
        if ($skipWeekend && $this->isSpecialHoliday($date)) {
            CLI::write("  Skipping - special holiday", 'yellow');
            return;
        }

        $db = \Config\Database::connect();
        $dayOfWeek = (int) date('N', strtotime($date)); // 1=Monday, 7=Sunday
        $currentTime = date('H:i:s');

        // Get all guru with schedule for this day of week
        $gurus = $db->table('users')
            ->select('users.id, users.username, teacher_schedules.jam_masuk, teacher_schedules.jam_pulang')
            ->join('teacher_schedules', 'teacher_schedules.user_id = users.id', 'left')
            ->where('users.role', 'guru')
            ->where('users.is_active', 1)
            ->where('teacher_schedules.hari', $dayOfWeek)
            ->where('teacher_schedules.status', 1)
            ->get()
            ->getResultArray();

        if (empty($gurus)) {
            CLI::write("  No guru with schedule for day " . $dayOfWeek, 'yellow');
            return;
        }

        CLI::write("  Found " . count($gurus) . " guru with schedule", 'cyan');

        $marked = 0;
        $skipped = 0;
        $attModel = new AttendanceModel();
        $eventModel = new AttendanceEventModel();
        $teacherModel = new \App\Models\TeacherModel();
        $tnModel = new TelegramNotificationModel();
        $waModel = new WhatsAppNotificationModel();

        foreach ($gurus as $guru) {
            $guruId = $guru['id'];
            $jamMasuk = $guru['jam_masuk'];
            $jamPulang = $guru['jam_pulang'];

            // Only process mark alpha if current time >= jam_pulang (end of schedule)
            if ($currentTime < $jamPulang) {
                $skipped++;
                continue;
            }

            // Check if checked-in
            $att = $attModel->where('user_id', $guruId)->where('date', $date)->first();

            if (!$att) {
                // No record = didn't check in = mark alpha
                $attModel->insert([
                    'user_id' => $guruId,
                    'date' => $date,
                    'masuk_status' => 'alpha',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $attId = $attModel->getInsertID();

                $eventModel->insert([
                    'attendance_id' => $attId,
                    'user_id' => $guruId,
                    'event_time' => date('Y-m-d H:i:s'),
                    'event_type' => 'auto_alpha',
                    'device_id' => null,
                    'payload' => json_encode(['reason' => 'guru_no_checkin', 'jam_masuk' => $jamMasuk, 'jam_pulang' => $jamPulang]),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                CLI::write("  ✓ {$guru['username']}: marked alpha (no check-in) [jam pulang: {$jamPulang}]", 'green');
                $marked++;

                // Queue Telegram notification
                try {
                    $teacherData = $teacherModel->where('user_id', $guruId)->first();
                    if ($teacherData && !empty($teacherData['telegram_chat_id'])) {
                        $msg = sprintf("⚠ %s tidak hadir pada %s\nJam: %s - %s\nStatus: Alpha", $teacherData['full_name'], $date, $jamMasuk, $jamPulang);
                        $tnModel->insert([
                            'student_id' => null,
                            'chat_id' => $teacherData['telegram_chat_id'],
                            'message' => $msg,
                            'payload' => json_encode(['attendance_id' => $attId, 'event' => 'auto_alpha', 'user_type' => 'guru']),
                            'status' => 'pending',
                            'attempts' => 0,
                            'scheduled_at' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                } catch (\Exception $e) {
                    // ignore
                }

                // Queue WhatsApp notification
                try {
                    $teacherData = $teacherModel->where('user_id', $guruId)->first();
                    if ($teacherData && !empty($teacherData['phone_number'])) {
                        $message = "⚠️ Tidak Hadir\nNama: {$teacherData['full_name']}\nTanggal: {$date}\nJam: {$jamMasuk} - {$jamPulang}\nStatus: Alpha";
                        $waModel->insert([
                            'student_id' => null,
                            'phone_number' => $teacherData['phone_number'],
                            'message' => $message,
                            'payload' => json_encode(['attendance_id' => $attId, 'event' => 'auto_alpha', 'user_type' => 'guru']),
                            'status' => 'pending',
                            'scheduled_at' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                } catch (\Exception $e) {
                    // ignore
                }
            } elseif (empty($att['masuk_at']) && $att['masuk_status'] === 'alpha') {
                // Already marked as alpha, skip
                CLI::write("  ⊘ {$guru['username']}: already marked alpha", 'yellow');
                continue;
            } elseif (empty($att['masuk_at'])) {
                // Has record but no masuk_at and not alpha = mark alpha
                $attModel->update($att['id'], [
                    'masuk_status' => 'alpha',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $eventModel->insert([
                    'attendance_id' => $att['id'],
                    'user_id' => $guruId,
                    'event_time' => date('Y-m-d H:i:s'),
                    'event_type' => 'auto_alpha',
                    'device_id' => null,
                    'payload' => json_encode(['reason' => 'guru_no_checkin', 'jam_masuk' => $jamMasuk, 'jam_pulang' => $jamPulang]),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                CLI::write("  ✓ {$guru['username']}: marked alpha (updated) [jam pulang: {$jamPulang}]", 'green');
                $marked++;

                // Queue Telegram notification
                try {
                    $teacherData = $teacherModel->where('user_id', $guruId)->first();
                    if ($teacherData && !empty($teacherData['telegram_chat_id'])) {
                        $msg = sprintf("⚠ %s tidak hadir pada %s\nJam: %s - %s\nStatus: Alpha", $teacherData['full_name'], $date, $jamMasuk, $jamPulang);
                        $tnModel->insert([
                            'student_id' => null,
                            'chat_id' => $teacherData['telegram_chat_id'],
                            'message' => $msg,
                            'payload' => json_encode(['attendance_id' => $att['id'], 'event' => 'auto_alpha', 'user_type' => 'guru']),
                            'status' => 'pending',
                            'attempts' => 0,
                            'scheduled_at' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                } catch (\Exception $e) {
                    // ignore
                }

                // Queue WhatsApp notification
                try {
                    $teacherData = $teacherModel->where('user_id', $guruId)->first();
                    if ($teacherData && !empty($teacherData['phone_number'])) {
                        $message = "⚠️ Tidak Hadir\nNama: {$teacherData['full_name']}\nTanggal: {$date}\nJam: {$jamMasuk} - {$jamPulang}\nStatus: Alpha";
                        $waModel->insert([
                            'student_id' => null,
                            'phone_number' => $teacherData['phone_number'],
                            'message' => $message,
                            'payload' => json_encode(['attendance_id' => $att['id'], 'event' => 'auto_alpha', 'user_type' => 'guru']),
                            'status' => 'pending',
                            'scheduled_at' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                } catch (\Exception $e) {
                    // ignore
                }
            }
        }

        CLI::write("  Marked $marked guru as alpha (Skipped: $skipped - not yet reached jam pulang)", 'cyan');
    }

    /**
     * Get applicable shifts based on current time
     * A shift is applicable if current time is after checkin_deadline + 60 minutes
     */
    private function getApplicableShifts($shiftModel, $currentTime)
    {
        $shifts = $shiftModel->findAll();
        $applicable = [];

        foreach ($shifts as $shift) {
            if (empty($shift['checkin_deadline'])) {
                continue;
            }

            // Calculate mark alpha time: 60 minutes after deadline
            $markAlphaTime = date('H:i:s', strtotime($shift['checkin_deadline'] . ' +60 minutes'));

            // If current time is at or after mark alpha time and before end shift
            if ($currentTime >= $markAlphaTime && $currentTime < $shift['end_time']) {
                $applicable[] = $shift;
            }
        }

        return $applicable;
    }

    /**
     * Check if date is a recurring holiday based on HOLIDAY_DAYS from .env
     * 1=Monday, 5=Friday, 7=Sunday
     */
    private function isRecurringHoliday($date)
    {
        $holidayDaysStr = getenv('HOLIDAY_DAYS');
        if (empty($holidayDaysStr)) {
            return false;
        }

        $holidayDays = array_map('intval', array_filter(explode(',', $holidayDaysStr)));
        $dayOfWeek = (int) date('N', strtotime($date));

        return in_array($dayOfWeek, $holidayDays);
    }

    /**
     * Check if date is a special holiday in school_holidays table
     */
    private function isSpecialHoliday($date)
    {
        $holidayModel = new SchoolHolidayModel();
        return $holidayModel->isHoliday($date) !== null;
    }
}
