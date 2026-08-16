<?php

namespace App\Models;

use CodeIgniter\Model;

class TelegramLinkModel extends Model
{
    protected $table = 'telegram_links';
    protected $primaryKey = 'id';
    protected $allowedFields = ['student_id', 'token', 'expires_at', 'consumed_at', 'created_at'];
    public $useTimestamps = false;
}
