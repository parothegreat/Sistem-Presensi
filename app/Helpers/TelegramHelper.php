<?php

namespace App\Helpers;

use App\Models\TelegramNotificationModel;

class TelegramHelper
{
    /**
     * Send Telegram notification for attendance scan
     * 
     * @param int $studentId Student ID
     * @param string $chatId Telegram chat ID
     * @param string $studentName Student full name
     * @param string $mode 'masuk' or 'pulang'
     * @param string $status 'on_time', 'late', 'early'
     * @param string $time Time in HH:MM format
     * @return bool
     */
    public static function sendAttendanceNotification($studentId, $chatId, $studentName, $mode, $status, $time)
    {
        if (empty($chatId)) {
            log_message('warning', "No Telegram chat_id for student: {$studentName}");
            return false;
        }

        try {
            // Build message based on mode
            if ($mode === 'masuk') {
                $emoji = '✓';
                $statusLabel = $status === 'on_time' ? '✅ Tepat Waktu' : '⚠️ Terlambat';
                $message = sprintf(
                    "%s Absensi Masuk\n\n" .
                        "Nama: %s\n" .
                        "Waktu: %s\n" .
                        "Status: %s\n" .
                        "Tanggal: %s",
                    $emoji,
                    $studentName,
                    $time,
                    $statusLabel,
                    date('d/m/Y')
                );
            } else if ($mode === 'pulang') {
                // pulang
                $emoji = '⟲';
                $statusLabel = $status === 'on_time' ? '✅ Tepat Waktu' : '⏱️ Lebih Awal';
                $message = sprintf(
                    "%s Absensi Pulang\n\n" .
                        "Nama: %s\n" .
                        "Waktu: %s\n" .
                        "Status: %s\n" .
                        "Tanggal: %s",
                    $emoji,
                    $studentName,
                    $time,
                    $statusLabel,
                    date('d/m/Y')
                );
            } else if ($mode === 'manual_update') {
                // Manual update by guru
                $statusLabels = [
                    'on_time' => '✅ Tepat Waktu',
                    'late' => '⚠️ Terlambat',
                    'izin' => '📋 Izin',
                    'sakit' => '🏥 Sakit',
                    'alpha' => '❌ Alfa',
                    'unknown' => '❓ Tidak Diketahui',
                ];
                $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                $message = sprintf(
                    "📝 Perubahan Status Absensi (Guru)\n\n" .
                        "Nama: %s\n" .
                        "Status Baru: %s\n" .
                        "Waktu Update: %s\n" .
                        "Tanggal: %s",
                    $studentName,
                    $statusLabel,
                    $time,
                    date('d/m/Y')
                );
            } else {
                $message = sprintf(
                    "📌 Update Absensi\n\n" .
                        "Nama: %s\n" .
                        "Mode: %s\n" .
                        "Status: %s\n" .
                        "Waktu: %s\n" .
                        "Tanggal: %s",
                    $studentName,
                    $mode,
                    $status,
                    $time,
                    date('d/m/Y')
                );
            }

            // Save to queue for sending
            $telegramModel = new TelegramNotificationModel();
            $telegramModel->insert([
                'student_id' => $studentId,
                'chat_id' => $chatId,
                'message' => $message,
                'payload' => json_encode([
                    'mode' => $mode,
                    'status' => $status,
                    'time' => $time,
                    'student_name' => $studentName
                ]),
                'status' => 'pending',
                'attempts' => 0,
                'scheduled_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            log_message('info', "Telegram notification queued for {$studentName} (ID: {$studentId})");
            return true;
        } catch (\Exception $e) {
            log_message('error', "Failed to queue Telegram notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send generic Telegram message
     * 
     * @param string $chatId Telegram chat ID
     * @param string $message Message content
     * @return bool
     */
    public static function sendMessage($chatId, $message)
    {
        if (empty($chatId)) return false;

        try {
            // Save to queue for sending
            $telegramModel = new TelegramNotificationModel();
            $telegramModel->insert([
                'student_id' => 0, // System message or unknown student
                'chat_id' => $chatId,
                'message' => $message,
                'payload' => json_encode(['mode' => 'custom']),
                'status' => 'pending',
                'attempts' => 0,
                'scheduled_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return true;
        } catch (\Exception $e) {
            log_message('error', "Failed to queue Telegram message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send queued Telegram notifications
     * This should be called by a cron job or queue worker
     * 
     * @return int Number of messages sent
     */
    public static function sendPendingNotifications()
    {
        try {
            $telegramModel = new TelegramNotificationModel();

            // Read token directly from .env file - more reliable
            $envFile = ROOTPATH . '.env';
            $botToken = '';

            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos($line, 'TELEGRAM_BOT_TOKEN=') === 0) {
                        $botToken = trim(substr($line, strlen('TELEGRAM_BOT_TOKEN=')));
                        break;
                    }
                }
            }

            // Fallback to config if not found in file
            if (empty($botToken)) {
                $config = config('Telegram');
                $botToken = $config->botToken;
            }

            if (empty($botToken)) {
                log_message('error', 'TELEGRAM_BOT_TOKEN is not configured');
                return 0;
            }

            // Get pending notifications
            $pending = $telegramModel->where('status', 'pending')
                ->where('attempts <', 3) // Max 3 attempts
                ->findAll();

            $sent = 0;

            foreach ($pending as $notification) {
                try {
                    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

                    $client = service('curlrequest');
                    $response = $client->post($url, [
                        'form_params' => [
                            'chat_id' => $notification['chat_id'],
                            'text' => $notification['message'],
                            'parse_mode' => 'HTML'
                        ],
                    ]);

                    $result = json_decode($response->getBody(), true);

                    if ($result['ok'] ?? false) {
                        $telegramModel->update($notification['id'], [
                            'status' => 'sent',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        $sent++;
                        log_message('info', "Telegram notification sent to chat: {$notification['chat_id']}");
                    } else {
                        $attempts = ($notification['attempts'] ?? 0) + 1;
                        $telegramModel->update($notification['id'], [
                            'attempts' => $attempts,
                            'last_error' => $result['description'] ?? 'Unknown error',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        log_message('warning', "Failed to send Telegram notification: " . ($result['description'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    $attempts = ($notification['attempts'] ?? 0) + 1;
                    $telegramModel->update($notification['id'], [
                        'attempts' => $attempts,
                        'last_error' => $e->getMessage(),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    log_message('error', "Error sending Telegram notification: " . $e->getMessage());
                }
            }

            return $sent;
        } catch (\Exception $e) {
            log_message('error', "Error in sendPendingNotifications: " . $e->getMessage());
            return 0;
        }
    }
}
