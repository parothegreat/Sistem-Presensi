<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendances';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'date', 'masuk_at', 'masuk_status', 'pulang_at', 'pulang_status', 'device_id', 'note', 'created_by', 'created_at', 'updated_at'];
    protected $useTimestamps = false;

    /**
     * Get expected check-in/check-out time untuk guru berdasarkan schedule
     * @param int $userId User ID
     * @param string $date Format: YYYY-MM-DD
     * @return array|null Format: ['jam_masuk' => 'HH:MM', 'jam_pulang' => 'HH:MM'] atau null jika tidak ada schedule
     */
    public function getExpectedTimeForTeacher(int $userId, string $date): ?array
    {
        // Get day of week (1=Senin, 7=Minggu)
        $dayOfWeek = (int) date('N', strtotime($date));

        $teacherScheduleModel = new TeacherScheduleModel();
        $schedule = $teacherScheduleModel->getScheduleByDay($userId, $dayOfWeek);

        if (!$schedule) {
            return null;
        }

        return [
            'jam_masuk' => $schedule['jam_masuk'],
            'jam_pulang' => $schedule['jam_pulang'],
        ];
    }

    /**
     * Calculate status check-in berdasarkan actual time vs expected time
     * @param string $actualTime Format: HH:MM atau HH:MM:SS
     * @param string $expectedTime Format: HH:MM
     * @param int $toleransi Toleransi dalam menit (default: 30)
     * @return string Status: 'on_time', 'late', atau 'alpha'
     */
    public static function calculateMasukStatus(string $actualTime, string $expectedTime, int $toleransi = 30): string
    {
        // Parse times
        $actual = strtotime("1970-01-01 " . $actualTime);
        $expected = strtotime("1970-01-01 " . $expectedTime);

        if (!$actual || !$expected) {
            return 'alpha';
        }

        $diff = ($actual - $expected) / 60; // dalam menit

        if ($diff <= $toleransi) {
            return 'on_time';
        } else {
            return 'late';
        }
    }

    /**
     * Calculate status check-out berdasarkan actual time vs expected time
     * @param string $actualTime Format: HH:MM atau HH:MM:SS
     * @param string $expectedTime Format: HH:MM
     * @return string Status: 'on_time' atau 'early'
     */
    public static function calculatePulangStatus(string $actualTime, string $expectedTime): string
    {
        // Parse times
        $actual = strtotime("1970-01-01 " . $actualTime);
        $expected = strtotime("1970-01-01 " . $expectedTime);

        if (!$actual || !$expected) {
            return 'on_time';
        }

        $diff = ($actual - $expected) / 60; // dalam menit (negatif = lebih awal)

        if ($diff >= 0) {
            return 'on_time'; // Check-out tepat waktu atau terlambat (boleh)
        } else {
            return 'early'; // Check-out lebih awal
        }
    }

    /**
     * Get today's attendance untuk user
     * @param int $userId User ID
     * @return array|null
     */
    public function getTodayAttendance(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('date', date('Y-m-d'))
            ->first();
    }

    /**
     * Get attendance untuk bulan tertentu
     * @param int $userId User ID
     * @param int $month Bulan (1-12)
     * @param int $year Tahun
     * @return array
     */
    public function getMonthlyAttendance(int $userId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? (int) date('m');
        $year = $year ?? (int) date('Y');

        return $this->where('user_id', $userId)
            ->where('MONTH(date)', $month)
            ->where('YEAR(date)', $year)
            ->orderBy('date', 'DESC')
            ->findAll();
    }

    /**
     * Get summary absensi untuk bulan tertentu
     * @param int $userId User ID
     * @param int $month Bulan (1-12)
     * @param int $year Tahun
     * @return array Format: ['hadir' => X, 'terlambat' => X, 'izin' => X, 'sakit' => X, 'alpha' => X]
     */
    public function getMonthlySummary(int $userId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? (int) date('m');
        $year = $year ?? (int) date('Y');

        $attendance = $this->where('user_id', $userId)
            ->where('MONTH(date)', $month)
            ->where('YEAR(date)', $year)
            ->findAll();

        $summary = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
        ];

        foreach ($attendance as $record) {
            if ($record['masuk_status'] === 'alpha') {
                // For now, mark as alpha. Permission checking will be added later
                $summary['alpha']++;
            } elseif ($record['masuk_status'] === 'late') {
                $summary['terlambat']++;
            } else {
                $summary['hadir']++;
            }
        }

        return $summary;
    }
    /**
     * Check if user sudah check-in hari ini
     * @param int $userId User ID
     * @return bool
     */
    public function hasCheckedInToday(int $userId): bool
    {
        $today = $this->where('user_id', $userId)
            ->where('date', date('Y-m-d'))
            ->where('masuk_at !=', null)
            ->first();

        return !empty($today);
    }

    /**
     * Check if user sudah check-out hari ini
     * @param int $userId User ID
     * @return bool
     */
    public function hasCheckedOutToday(int $userId): bool
    {
        $today = $this->where('user_id', $userId)
            ->where('date', date('Y-m-d'))
            ->where('pulang_at !=', null)
            ->first();

        return !empty($today);
    }

    /**
     * Get monthly attendance dengan role filtering
     * @param string $role Role: 'guru' atau 'karyawan'
     * @param int $month Bulan (1-12)
     * @param int $year Tahun
     * @return array
     */
    public function getAttendanceByRole(string $role, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? (int) date('m');
        $year = $year ?? (int) date('Y');

        return $this->select('attendances.*, teachers.full_name, users.role')
            ->join('users', 'users.id = attendances.user_id')
            ->join('teachers', 'teachers.user_id = users.id', 'left')
            ->where('users.role', $role)
            ->where('MONTH(attendances.date)', $month)
            ->where('YEAR(attendances.date)', $year)
            ->orderBy('attendances.date', 'DESC')
            ->orderBy('teachers.full_name', 'ASC')
            ->findAll();
    }
}
