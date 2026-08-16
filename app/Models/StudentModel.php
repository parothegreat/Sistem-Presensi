<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'nis', 'full_name', 'class', 'wali_kelas_id', 'telegram_chat_id', 'qr_code_data', 'qr_code_generated_at', 'phone_number', 'guardian_name', 'guardian_phone', 'shift_id', 'rfid_id', 'photo', 'religion', 'created_at', 'updated_at'];

    public function getStudentsWithUser()
    {
        return $this->select('students.*, users.username, users.role')
            ->join('users', 'users.id = students.user_id', 'left')
            ->findAll();
    }

    public function getStudentWithUser($id)
    {
        return $this->select('students.*, users.username, users.role')
            ->join('users', 'users.id = students.user_id', 'left')
            ->where('students.id', $id)
            ->first();
    }

    /**
     * Get students by wali kelas ID
     */
    public function getByWaliKelas($waliKelasId)
    {
        return $this->where('wali_kelas_id', $waliKelasId)
            ->join('users', 'users.id = students.user_id', 'left')
            ->select('students.*, users.username')
            ->orderBy('students.full_name', 'ASC')
            ->findAll();
    }

    /**
     * Get students by class name
     */
    public function getByClass($className)
    {
        return $this->where('class', $className)
            ->join('users', 'users.id = students.user_id', 'left')
            ->select('students.*, users.username')
            ->orderBy('students.full_name', 'ASC')
            ->findAll();
    }

    /**
     * Generate and save QR code for a student
     */
    public function generateQrCode($studentId)
    {
        $student = $this->find($studentId);
        if (!$student) {
            return false;
        }

        // Generate QR code data URL using the student's NIS
        $qrCodeUrl = \App\Helpers\QrCodeHelper::getDataUrl($student['nis']);

        // Save to database
        $this->update($studentId, [
            'qr_code_data' => $qrCodeUrl,
            'qr_code_generated_at' => date('Y-m-d H:i:s'),
        ]);

        return $qrCodeUrl;
    }

    /**
     * Generate QR codes for all students without one
     */
    public function generateMissingQrCodes()
    {
        $studentsWithoutQr = $this->where('qr_code_data IS NULL')
            ->findAll();

        $count = 0;
        foreach ($studentsWithoutQr as $student) {
            $this->generateQrCode($student['id']);
            $count++;
        }

        return $count;
    }
}
