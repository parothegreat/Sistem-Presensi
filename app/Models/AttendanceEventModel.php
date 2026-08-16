<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceEventModel extends Model
{
    protected $table = 'attendance_events';
    protected $primaryKey = 'id';
    protected $allowedFields = ['attendance_id', 'user_id', 'event_time', 'event_type', 'device_id', 'payload', 'created_at'];
    protected $useTimestamps = false;
}
