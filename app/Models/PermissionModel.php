<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'student_id',
        'date',
        'end_date',
        'status',
        'reason',
        'evidence',
        'approval_status',
        'approved_by',
        'approved_at',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get permissions for a specific student
     */
    public function getByStudent($studentId, $limit = 20)
    {
        return $this->where('student_id', $studentId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get pending permissions for a list of student IDs
     */
    public function getPendingByStudents(array $studentIds)
    {
        if (empty($studentIds)) {
            return [];
        }

        return $this->select('permissions.*, students.full_name, students.nis, students.class')
            ->join('students', 'students.id = permissions.student_id')
            ->whereIn('permissions.student_id', $studentIds)
            ->where('permissions.approval_status', 'pending')
            ->orderBy('permissions.created_at', 'ASC')
            ->findAll();
    }
}
