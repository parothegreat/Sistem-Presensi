<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityParticipantModel extends Model
{
    protected $table = 'activity_participants';
    protected $primaryKey = 'id';
    protected $allowedFields = ['activity_id', 'student_id', 'created_at'];
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getParticipants($activityId)
    {
        return $this->select('activity_participants.*, students.full_name, students.nis, students.class')
            ->join('students', 'students.id = activity_participants.student_id')
            ->where('activity_id', $activityId)
            ->findAll();
    }
}
