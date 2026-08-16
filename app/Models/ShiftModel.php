<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftModel extends Model
{
    protected $table = 'shifts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'description', 'start_time', 'end_time', 'checkin_deadline', 'checkout_earliest', 'is_active', 'created_at', 'updated_at'];

    /**
     * Get all active shifts
     */
    public function getActiveShifts()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Determine attendance status based on shift and time
     * 
     * @param int $shiftId Shift ID
     * @param string $time Time in HH:MM:SS format
     * @param string $mode 'masuk' or 'pulang'
     * @return string Status: 'on_time', 'late', or 'early'
     */
    public static function getStatusForTime($shiftId, $time, $mode = 'masuk')
    {
        $shift = (new ShiftModel())->find($shiftId);
        if (!$shift) {
            return 'unknown';
        }

        if ($mode === 'masuk') {
            // Check-in status
            return $time <= $shift['checkin_deadline'] ? 'on_time' : 'late';
        } else {
            // Check-out status
            return $time >= $shift['checkout_earliest'] ? 'on_time' : 'early';
        }
    }
}
