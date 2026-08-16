<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsAppNotificationModel extends Model
{
    protected $table = 'whatsapp_notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'student_id',
        'phone_number',
        'message',
        'payload',
        'status',
        'attempts',
        'max_attempts',
        'scheduled_at',
        'sent_at',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;

    /**
     * Get pending notifications
     */
    public function getPending($limit = 10)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('whatsapp_notifications');

        return $builder->where('status', 'pending')
            ->where('attempts < max_attempts', null, false)
            ->orderBy('scheduled_at', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Mark as sent
     */
    public function markSent($id)
    {
        return $this->update($id, [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark as failed
     */
    public function markFailed($id)
    {
        return $this->update($id, [
            'status' => 'failed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Increment attempt counter
     */
    public function incrementAttempt($id)
    {
        $notification = $this->find($id);
        if ($notification) {
            $newAttempts = $notification['attempts'] + 1;
            $status = $newAttempts >= $notification['max_attempts'] ? 'failed' : 'pending';

            return $this->update($id, [
                'attempts' => $newAttempts,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        return false;
    }
}
