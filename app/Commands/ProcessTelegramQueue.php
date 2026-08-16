<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\TelegramNotificationModel;

class ProcessTelegramQueue extends BaseCommand
{
    protected $group = 'notifications';
    protected $name = 'telegram:process';
    protected $description = 'Process pending Telegram notification queue and send messages via Bot API';

    public function run(array $params = [])
    {
        $token = getenv('TELEGRAM_BOT_TOKEN') ?: null;
        if (empty($token)) {
            CLI::error('TELEGRAM_BOT_TOKEN not set in environment');
            return;
        }

        $model = new TelegramNotificationModel();
        // Fetch pending notifications (limit to 50 per run)
        $pending = $model->where('status', 'pending')->orderBy('scheduled_at', 'ASC')->findAll(50);
        if (empty($pending)) {
            CLI::write('No pending telegram notifications.');
            return;
        }

        foreach ($pending as $n) {
            $chatId = $n['chat_id'];
            $text = $n['message'];

            $url = "https://api.telegram.org/bot" . urlencode($token) . "/sendMessage";
            $post = http_build_query([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ]);

            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $post,
                    'timeout' => 10,
                ]
            ];

            $context = stream_context_create($opts);
            $result = @file_get_contents($url, false, $context);
            $update = ['attempts' => $n['attempts'] + 1, 'updated_at' => date('Y-m-d H:i:s')];

            if ($result === false) {
                $update['status'] = 'failed';
                $update['last_error'] = 'HTTP request failed';
                $model->update($n['id'], $update);
                CLI::write("Failed to send to {$chatId}: HTTP error");
                continue;
            }

            $json = json_decode($result, true);
            if (isset($json['ok']) && $json['ok'] === true) {
                $update['status'] = 'sent';
                $update['last_error'] = null;
                $model->update($n['id'], $update);
                CLI::write("Sent to {$chatId}");
            } else {
                $update['status'] = 'failed';
                $update['last_error'] = $result;
                $model->update($n['id'], $update);
                CLI::write("Failed to send to {$chatId}: {$result}");
            }
        }
    }
}
