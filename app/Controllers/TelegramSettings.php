<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TelegramSettings extends Controller
{
    public function index()
    {
        $configModel = new \App\Models\TelegramLinkConfigModel();
        $config = $configModel->orderBy('id', 'DESC')->first();

        return view('admin/telegram_settings', [
            'pin' => $config['pin'] ?? null,
            'title' => 'Telegram Link Settings'
        ]);
    }

    public function save()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'pin' => 'required|alpha_numeric|min_length[4]|max_length[20]'
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $pin = $this->request->getPost('pin');
        $configModel = new \App\Models\TelegramLinkConfigModel();
        // create a new record (simpler to keep history)
        $configModel->insert([
            'pin' => $pin,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/telegram-settings')->with('success', 'Global PIN diperbarui.');
    }

    public function registerWebhookPage()
    {
        // Try to read token for display masking
        $token = $this->getTelegramToken();
        $maskedToken = '';
        if (!empty($token)) {
            $maskedToken = substr($token, 0, 5) . '...' . substr($token, -5);
        }

        return view('admin/telegram_webhook', [
            'title' => 'Registrasi Webhook Telegram',
            'maskedToken' => $maskedToken,
            'hasToken' => !empty($token),
            'webhookUrl' => base_url('telegram/webhook')
        ]);
    }

    public function processRegisterWebhook()
    {
        $token = $this->getTelegramToken();
        if (empty($token)) {
            return redirect()->back()->with('error', 'Token Bot Telegram tidak ditemukan di .env (TELEGRAM_BOT_TOKEN).');
        }

        $webhookUrl = base_url('telegram/webhook');
        
        $url = "https://api.telegram.org/bot{$token}/setWebhook?url={$webhookUrl}";
        
        // Use file_get_contents or curl
        try {
            $context = stream_context_create([
                'http' => ['timeout' => 10],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            
            $json = file_get_contents($url, false, $context);
            $result = json_decode($json, true);

            if ($result && $result['ok']) {
                $description = $result['description'] ?? 'Webhook set successfully';
                return redirect()->back()->with('success', 'Webhook berhasil didaftarkan: ' . $description);
            } else {
                $error = $result['description'] ?? 'Unknown error';
                return redirect()->back()->with('error', 'Gagal mendaftarkan webhook: ' . $error);
            }
        } catch (\Exception $e) {
             return redirect()->back()->with('error', 'Exception: ' . $e->getMessage());
        }
    }

    private function getTelegramToken()
    {
        $envFile = ROOTPATH . '.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, 'TELEGRAM_BOT_TOKEN=') === 0) {
                    return trim(substr($line, strlen('TELEGRAM_BOT_TOKEN=')));
                }
            }
        }
        return getenv('TELEGRAM_BOT_TOKEN');
    }
}
