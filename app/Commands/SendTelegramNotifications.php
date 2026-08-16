<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Helpers\TelegramHelper;

class SendTelegramNotifications extends BaseCommand
{
    protected $group = 'Telegram';
    protected $name = 'telegram:send-pending';
    protected $description = 'Send all pending Telegram notifications';
    protected $usage = 'telegram:send-pending [options]';
    protected $arguments = [];
    protected $options = [
        '--limit' => 'Limit number of notifications to send (default: all)',
    ];

    public function run(array $params = [])
    {
        try {
            $limit = $params['limit'] ?? null;
            
            CLI::write('Sending pending Telegram notifications...', 'yellow');
            
            $sent = TelegramHelper::sendPendingNotifications();
            
            CLI::write("Successfully sent {$sent} notification(s)", 'green');
            return 0;
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
