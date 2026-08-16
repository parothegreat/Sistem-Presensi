<?php

namespace App\Models;

use CodeIgniter\Model;

class TelegramLinkConfigModel extends Model
{
    protected $table = 'telegram_link_config';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pin', 'updated_at'];
    public $useTimestamps = false;
}
