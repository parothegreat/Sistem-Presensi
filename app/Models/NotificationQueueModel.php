<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationQueueModel extends Model
{
    protected $table = 'notification_queue';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'student_id',
        'nis',
        'npsn',
        'title',
        'message',
        'data',
        'type',
        'source',
        'sent',
        'sent_at',
        'error',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;

    /**
     * Get pending notifications (not sent yet)
     */
    public function getPending($limit = 100)
    {
        return $this->where('sent', 0)
            ->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get pending notifications for specific user
     */
    public function getPendingByUserId($userId)
    {
        return $this->where('user_id', $userId)
            ->where('sent', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent($id, $fcmResponse = null)
    {
        return $this->update($id, [
            'sent' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed($id, $errorMessage)
    {
        return $this->update($id, [
            'error' => $errorMessage,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get recent notifications for user
     */
    public function getRecent($userId, $limit = 20)
    {
        return $this->where('user_id', $userId)
            ->where('sent', 1)
            ->orderBy('sent_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
