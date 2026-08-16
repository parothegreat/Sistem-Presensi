<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityModel extends Model
{
    protected $table = 'activities';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'start_time', 'end_time', 'status', 'target_audience', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    // Helper to get active activities
    public function getActiveActivities()
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('status', 'ongoing')
            ->orGroupStart()
            ->where('status', 'scheduled')
            ->where('start_time <=', $now)
            ->where('end_time >=', $now)
            ->groupEnd()
            ->findAll();
    }
}
