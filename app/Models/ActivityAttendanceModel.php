<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityAttendanceModel extends Model
{
    protected $table = 'activity_attendances';
    protected $primaryKey = 'id';
    protected $allowedFields = ['activity_id', 'student_id', 'check_in_time', 'check_out_time', 'status', 'method', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getAttendance($activityId)
    {
        return $this->select('activity_attendances.*, students.full_name, students.nis, students.class')
            ->join('students', 'students.id = activity_attendances.student_id')
            ->where('activity_id', $activityId)
            ->findAll();
    }
}
