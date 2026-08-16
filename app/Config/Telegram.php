<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Telegram extends BaseConfig
{
    // Bot token should come from .env: TELEGRAM_BOT_TOKEN
    public $botToken;

    public function __construct()
    {
        $this->botToken = getenv('TELEGRAM_BOT_TOKEN') ?: '';
    }
}
