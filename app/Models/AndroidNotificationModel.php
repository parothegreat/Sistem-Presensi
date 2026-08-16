<?php

namespace App\Models;

use CodeIgniter\Model;

class AndroidNotificationModel extends Model
{
    protected $table = 'android_notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'student_id',
        'nis',
        'npsn',
        'device_token',
        'title',
        'message',
        'type',
        'status_attendance',
        'payload',
        'notification_status',
        'attempts',
        'max_attempts',
        'scheduled_at',
        'sent_at',
        'failed_at',
        'last_error',
        'last_error_code'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    public function getPendingNotifications($limit = 500)
    {
        return $this->where('notification_status', 'pending')
            ->orWhere('notification_status', 'retry')
            ->where('attempts <', 5)
            ->orderBy('attempts', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    public function getFailedNotifications($limit = 100)
    {
        return $this->where('notification_status', 'failed')
            ->orderBy('failed_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getSentToday()
    {
        return $this->where('notification_status', 'sent')
            ->where('DATE(sent_at) =', date('Y-m-d'))
            ->countAllResults();
    }

    public function getQueueDepth()
    {
        return $this->where('notification_status', 'pending')
            ->orWhere('notification_status', 'retry')
            ->countAllResults();
    }
}
