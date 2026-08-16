<?php

namespace App\Models;

use CodeIgniter\Model;

class BiometricLogModel extends Model
{
    protected $table = 'biometric_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'device_id',
        'user_id',
        'date',
        'time',
        'timestamp',
        'biometric_type',
        'status',
        'user_type',
        'processed',
        'attendance_id',
        'process_error',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get unprocessed logs
     * Order by DESC to process earliest timestamps first (masuk before pulang)
     */
    public function getUnprocessed($limit = 100)
    {
        return $this->where('processed', 0)
            ->orderBy('timestamp', 'ASC')
            ->orderBy('id', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mark as processed
     */
    public function markProcessed($logId, $attendanceId = null, $error = null)
    {
        return $this->update($logId, [
            'processed' => 1,
            'attendance_id' => $attendanceId,
            'process_error' => $error,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
