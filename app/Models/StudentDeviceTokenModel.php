<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentDeviceTokenModel extends Model
{
    protected $table = 'student_device_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'student_id',
        'npsn',
        'device_token',
        'device_name',
        'app_version',
        'last_used_at',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    public function getActiveTokensByStudent($studentId, $npsn = null)
    {
        $query = $this->where('student_id', $studentId)
            ->where('is_active', true);

        if ($npsn) {
            $query->where('npsn', $npsn);
        }

        return $query->findAll();
    }

    public function registerToken($studentId, $deviceToken, $npsn = null, $deviceName = null, $appVersion = null)
    {
        if (!$npsn) {
            $npsn = getenv('SCHOOL_NPSN');
        }

        // Check if token already exists for this NPSN + device combination
        $existing = $this->where('npsn', $npsn)
            ->where('device_token', $deviceToken)
            ->first();

        if ($existing) {
            // Update last_used_at
            return $this->update($existing['id'], [
                'last_used_at' => date('Y-m-d H:i:s'),
                'is_active' => true
            ]);
        }

        // New token
        return $this->insert([
            'student_id' => $studentId,
            'npsn' => $npsn,
            'device_token' => $deviceToken,
            'device_name' => $deviceName,
            'app_version' => $appVersion,
            'last_used_at' => date('Y-m-d H:i:s'),
            'is_active' => true
        ]);
    }

    public function deactivateToken($deviceToken, $npsn = null)
    {
        if (!$npsn) {
            $npsn = getenv('SCHOOL_NPSN');
        }

        return $this->where('npsn', $npsn)
            ->where('device_token', $deviceToken)
            ->set(['is_active' => false])
            ->update();
    }

    public function deactivateOldTokens($studentId, $npsn = null, $daysBefore = 30)
    {
        if (!$npsn) {
            $npsn = getenv('SCHOOL_NPSN');
        }

        $date = date('Y-m-d H:i:s', strtotime("-{$daysBefore} days"));

        return $this->where('student_id', $studentId)
            ->where('npsn', $npsn)
            ->where('last_used_at <', $date)
            ->set(['is_active' => false])
            ->update();
    }
}
