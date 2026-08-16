<?php

namespace App\Models;

use CodeIgniter\Model;

class TeacherModel extends Model
{
    protected $table = 'teachers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'nip', 'full_name', 'subject', 'photo', 'telegram_chat_id', 'phone_number', 'rfid_id', 'created_at', 'updated_at'];

    public function getTeachersWithUser()
    {
        return $this->select('teachers.*, users.username, users.role')
            ->join('users', 'users.id = teachers.user_id', 'left')
            ->findAll();
    }

    public function getTeacherWithUser($id)
    {
        return $this->select('teachers.*, users.username, users.role')
            ->join('users', 'users.id = teachers.user_id', 'left')
            ->where('teachers.id', $id)
            ->first();
    }
}
