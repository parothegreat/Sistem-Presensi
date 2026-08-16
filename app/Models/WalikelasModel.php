<?php

namespace App\Models;

use CodeIgniter\Model;

class WalikelasModel extends Model
{
    protected $table = 'walikelas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['teacher_id', 'class_name', 'academic_year', 'semester', 'is_active', 'wa_group_id'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'teacher_id' => 'required|integer',
        'class_name' => 'required|string|max_length[50]',
        'academic_year' => 'permit_empty|string|max_length[20]',
        'semester' => 'required|in_list[1,2]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get all students in a specific wali kelas
     */
    public function getStudentsByClass($waliKelasId)
    {
        return $this->db->table('students')
            ->where('wali_kelas_id', $waliKelasId)
            ->join('users', 'users.id = students.user_id', 'left')
            ->select('students.id, students.nis, students.full_name, students.class, users.username')
            ->orderBy('students.full_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get wali kelas by teacher ID
     */
    public function getByTeacherId($teacherId)
    {
        return $this->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active wali kelas with teacher info
     */
    public function getAllActive()
    {
        return $this->db->table('walikelas')
            ->where('walikelas.is_active', true)
            ->join('teachers', 'teachers.id = walikelas.teacher_id', 'left')
            ->select('walikelas.id, walikelas.class_name, walikelas.academic_year, walikelas.semester, walikelas.is_active, teachers.full_name as teacher_name, teachers.nip, teachers.photo')
            ->orderBy('walikelas.class_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get wali kelas detail with teacher and students count
     */
    public function getDetail($waliKelasId)
    {
        $waliKelas = $this->db->table('walikelas')
            ->where('walikelas.id', $waliKelasId)
            ->join('teachers', 'teachers.id = walikelas.teacher_id', 'left')
            ->select('walikelas.*, teachers.full_name as teacher_name, teachers.nip, teachers.subject')
            ->get()
            ->getRowArray();

        if ($waliKelas) {
            $waliKelas['students_count'] = $this->db->table('students')
                ->where('wali_kelas_id', $waliKelasId)
                ->countAllResults();
        }

        return $waliKelas;
    }
}
