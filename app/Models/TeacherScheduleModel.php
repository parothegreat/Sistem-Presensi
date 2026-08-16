<?php

namespace App\Models;

use CodeIgniter\Model;

class TeacherScheduleModel extends Model
{
    protected $table = 'teacher_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'role', 'hari', 'jam_masuk', 'jam_pulang', 'status'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'role' => 'required|in_list[guru]',
        'hari' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[7]',
        'jam_masuk' => 'permit_empty|valid_date[H:i]',
        'jam_pulang' => 'permit_empty|valid_date[H:i]',
        'status' => 'required|in_list[aktif,nonaktif]',
    ];

    protected $validationMessages = [
        'hari' => [
            'greater_than_equal_to' => 'Hari harus antara 1-7 (Senin-Minggu)',
            'less_than_equal_to' => 'Hari harus antara 1-7 (Senin-Minggu)',
        ],
    ];

    /**
     * Get schedule untuk user pada hari tertentu
     * @param int $userId User ID
     * @param int $dayOfWeek Day of week (1-7)
     * @return array|null
     */
    public function getScheduleByDay(int $userId, int $dayOfWeek): ?array
    {
        return $this->where('user_id', $userId)
            ->where('hari', $dayOfWeek)
            ->where('status', 'aktif')
            ->first();
    }

    /**
     * Get semua schedule yang aktif untuk user
     * @param int $userId User ID
     * @return array
     */
    public function getActiveSchedules(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('status', 'aktif')
            ->orderBy('hari', 'ASC')
            ->findAll();
    }

    /**
     * Check apakah user punya schedule pada hari tertentu
     * @param int $userId User ID
     * @param int $dayOfWeek Day of week (1-7)
     * @return bool
     */
    public function hasScheduleOnDay(int $userId, int $dayOfWeek): bool
    {
        $schedule = $this->getScheduleByDay($userId, $dayOfWeek);
        return !empty($schedule) && $schedule['jam_masuk'] !== null && $schedule['jam_pulang'] !== null;
    }

    /**
     * Get full week schedule untuk user
     * @param int $userId User ID
     * @return array Array dengan index 1-7 untuk setiap hari
     */
    public function getFullWeekSchedule(int $userId): array
    {
        $schedules = $this->getActiveSchedules($userId);
        $fullWeek = [];

        for ($i = 1; $i <= 7; $i++) {
            $fullWeek[$i] = null;
        }

        foreach ($schedules as $schedule) {
            $fullWeek[$schedule['hari']] = $schedule;
        }

        return $fullWeek;
    }

    /**
     * Get schedule dengan informasi user
     * @param int $userId User ID
     * @return array
     */
    public function getScheduleWithUser(int $userId): array
    {
        return $this->select('teacher_schedules.*, teachers.full_name, users.role as user_role')
            ->join('users', 'users.id = teacher_schedules.user_id')
            ->join('teachers', 'teachers.user_id = users.id', 'left')
            ->where('teacher_schedules.user_id', $userId)
            ->where('teacher_schedules.status', 'aktif')
            ->orderBy('teacher_schedules.hari', 'ASC')
            ->findAll();
    }

    /**
     * Get all schedules with user info untuk admin list
     * @return array
     */
    public function getAllSchedulesWithUsers(): array
    {
        return $this->select('teacher_schedules.*, teachers.full_name, users.role as user_role')
            ->join('users', 'users.id = teacher_schedules.user_id')
            ->join('teachers', 'teachers.user_id = users.id', 'left')
            ->orderBy('teachers.full_name', 'ASC')
            ->orderBy('teacher_schedules.hari', 'ASC')
            ->findAll();
    }

    /**
     * Get day name dalam Bahasa Indonesia
     * @param int $dayOfWeek (1-7)
     * @return string
     */
    public static function getDayName(int $dayOfWeek): string
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        return $days[$dayOfWeek] ?? 'Unknown';
    }

    /**
     * Format schedule untuk display (e.g., "07:00-14:30")
     * @param array $schedule Schedule data
     * @return string
     */
    public static function formatSchedule(array $schedule): string
    {
        if (empty($schedule['jam_masuk']) || empty($schedule['jam_pulang'])) {
            return '-';
        }
        return $schedule['jam_masuk'] . ' - ' . $schedule['jam_pulang'];
    }
}
