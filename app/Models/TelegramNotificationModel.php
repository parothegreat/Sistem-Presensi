<?php

namespace App\Models;

use CodeIgniter\Model;

class TelegramNotificationModel extends Model
{
    protected $table = 'telegram_notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = ['student_id', 'chat_id', 'message', 'payload', 'status', 'attempts', 'last_error', 'scheduled_at', 'created_at', 'updated_at'];
}
