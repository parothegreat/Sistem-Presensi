<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TelegramBot extends Controller
{
    public function webhook()
    {
        // Read token from .env file directly - more reliable than getenv()
        $envFile = ROOTPATH . '.env';
        $token = '';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, 'TELEGRAM_BOT_TOKEN=') === 0) {
                    $token = trim(substr($line, strlen('TELEGRAM_BOT_TOKEN=')));
                    break;
                }
            }
        }

        // Fallback to getenv if not found in file
        if (empty($token)) {
            $token = getenv('TELEGRAM_BOT_TOKEN') ?: '';
        }

        // Log bot token status
        if (empty($token)) {
            @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - ERROR: TELEGRAM_BOT_TOKEN is empty\n", FILE_APPEND);
            return $this->response->setStatusCode(400)->setBody('No token');
        }

        @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - Token loaded: " . substr($token, 0, 20) . "...\n", FILE_APPEND);

        $input = file_get_contents('php://input');
        // debug: log raw incoming update
        @file_put_contents(WRITEPATH . 'logs/telegram_incoming.log', date('c') . "\n" . $input . "\n---\n", FILE_APPEND);
        if (empty($input)) {
            return $this->response->setStatusCode(400)->setBody('No input');
        }

        $update = json_decode($input, true);
        if (! $update) {
            return $this->response->setStatusCode(400)->setBody('Invalid JSON');
        }

        $message = $update['message'] ?? null;
        if (! $message) {
            return $this->response->setStatusCode(200)->setBody('No message');
        }

        $chat = $message['chat'] ?? null;
        $text = trim($message['text'] ?? '');
        if (! $chat || $text === '') {
            return $this->response->setStatusCode(200)->setBody('Ignored');
        }

        // handle /start command
        if (stripos($text, '/start') === 0) {
            @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - handling /start for chat_id={$chat['id']}\n", FILE_APPEND);
            $welcomeMsg = "👋 Halo! Selamat datang di Bot Notifikasi Presensi.\n\nGunakan perintah:\n/link [NIS/NIP] [PIN]\n\nContoh Siswa:\n/link S123456 112233\n\nContoh Guru:\n/link G123456 112233";
            $this->sendMessage($token, $chat['id'], $welcomeMsg);
            return $this->response->setStatusCode(200)->setBody('ok');
        }

        // handle /link command
        if (stripos($text, '/link') === 0) {
            @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - handling /link: $text\n", FILE_APPEND);
            $parts = preg_split('/\s+/', $text);

            // Backward-compatible: /link <token> (old behavior)
            if (count($parts) === 2) {
                $tokenStr = $parts[1];
                $linkModel = new \App\Models\TelegramLinkModel();
                $link = $linkModel->where('token', $tokenStr)->where('consumed_at', null)->first();
                if (! $link) {
                    @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - token not found: $tokenStr\n", FILE_APPEND);
                    $this->sendMessage($token, $chat['id'], 'Token tidak ditemukan atau sudah kadaluarsa.');
                    return $this->response->setStatusCode(200)->setBody('ok');
                }

                // check expiry
                if (! empty($link['expires_at']) && strtotime($link['expires_at']) < time()) {
                    $this->sendMessage($token, $chat['id'], 'Token sudah kadaluarsa. Minta admin buat token baru.');
                    return $this->response->setStatusCode(200)->setBody('ok');
                }

                // link student by link entry
                $studentModel = new \App\Models\StudentModel();
                $student = $studentModel->find($link['student_id']);
                if (! $student) {
                    $this->sendMessage($token, $chat['id'], 'Profil siswa tidak ditemukan.');
                    return $this->response->setStatusCode(200)->setBody('ok');
                }

                $studentModel->update($student['id'], ['telegram_chat_id' => $chat['id'], 'updated_at' => date('Y-m-d H:i:s')]);
                $linkModel->update($link['id'], ['consumed_at' => date('Y-m-d H:i:s')]);
                @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - linked by token to student {$student['id']}\n", FILE_APPEND);
                $successMsg = "✅ <b>Berhasil!</b>\n\nChat Anda sudah ditautkan ke profil siswa:\n<b>NIS:</b> " . esc($student['nis']) . "\n<b>Nama:</b> " . esc($student['full_name']) . "\n<b>Kelas:</b> " . esc($student['class']) . "\n\nTerima kasih.";
                $this->sendMessage($token, $chat['id'], $successMsg);
                return $this->response->setStatusCode(200)->setBody('ok');
            }

            // New: /link <nis/nip> <pin>
            if (count($parts) >= 3) {
                $identifier = $parts[1];
                $pin = $parts[2];

                // Check if it's NIS (siswa) or NIP (guru)
                $studentModel = new \App\Models\StudentModel();
                $teacherModel = new \App\Models\TeacherModel();

                $student = $studentModel->where('nis', $identifier)->first();
                $teacher = $teacherModel->where('nip', $identifier)->first();

                if (! $student && ! $teacher) {
                    @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - nis/nip not found: $identifier\n", FILE_APPEND);
                    $this->sendMessage($token, $chat['id'], 'NIS/NIP tidak ditemukan. Periksa kembali NIS siswa atau NIP guru.');
                    return $this->response->setStatusCode(200)->setBody('ok');
                }

                // Check global PIN config first
                $configModel = new \App\Models\TelegramLinkConfigModel();
                $config = $configModel->orderBy('id', 'DESC')->first();
                $isPinValid = false;
                if ($config && ! empty($config['pin']) && hash_equals((string)$config['pin'], (string)$pin)) {
                    $isPinValid = true;
                }

                // Process SISWA (NIS)
                if ($student) {
                    if (! $isPinValid) {
                        // fallback: check per-student one-time links (backwards-compatible)
                        $linkModel = new \App\Models\TelegramLinkModel();
                        $link = $linkModel->where('student_id', $student['id'])->where('token', $pin)->where('consumed_at', null)->first();
                        if (! $link) {
                            @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - pin not valid for student {$student['id']}: $pin\n", FILE_APPEND);
                            $this->sendMessage($token, $chat['id'], 'Kode PIN tidak valid atau sudah digunakan.');
                            return $this->response->setStatusCode(200)->setBody('ok');
                        }

                        if (! empty($link['expires_at']) && strtotime($link['expires_at']) < time()) {
                            @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - pin expired for link {$link['id']}\n", FILE_APPEND);
                            $this->sendMessage($token, $chat['id'], 'Kode PIN sudah kadaluarsa. Minta admin buat PIN baru.');
                            return $this->response->setStatusCode(200)->setBody('ok');
                        }

                        // ensure student has not been linked yet (prevent takeover)
                        if (! empty($student['telegram_chat_id'])) {
                            @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - student {$student['id']} already linked to {$student['telegram_chat_id']}\n", FILE_APPEND);
                            $this->sendMessage($token, $chat['id'], 'Profil siswa sudah memiliki chat yang terdaftar. Hubungi admin jika perlu mengganti.');
                            return $this->response->setStatusCode(200)->setBody('ok');
                        }

                        $studentModel->update($student['id'], ['telegram_chat_id' => $chat['id'], 'updated_at' => date('Y-m-d H:i:s')]);
                        $linkModel->update($link['id'], ['consumed_at' => date('Y-m-d H:i:s')]);
                        @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - linked student {$student['id']} to chat {$chat['id']} via per-student link\n", FILE_APPEND);
                        $successMsg = "✅ <b>Berhasil!</b>\n\nChat Anda sudah ditautkan ke profil siswa:\n<b>NIS:</b> " . esc($student['nis']) . "\n<b>Nama:</b> " . esc($student['full_name']) . "\n<b>Kelas:</b> " . esc($student['class']) . "\n\nTerima kasih.";
                        $this->sendMessage($token, $chat['id'], $successMsg);
                        return $this->response->setStatusCode(200)->setBody('ok');
                    }

                    // If we reach here, global PIN matched
                    if (! empty($student['telegram_chat_id'])) {
                        @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - student {$student['id']} already linked to {$student['telegram_chat_id']}\n", FILE_APPEND);
                        $this->sendMessage($token, $chat['id'], 'Profil siswa sudah memiliki chat yang terdaftar. Hubungi admin jika perlu mengganti.');
                        return $this->response->setStatusCode(200)->setBody('ok');
                    }

                    $studentModel->update($student['id'], ['telegram_chat_id' => $chat['id'], 'updated_at' => date('Y-m-d H:i:s')]);
                    @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - linked student {$student['id']} to chat {$chat['id']} via global PIN\n", FILE_APPEND);
                    $successMsg = "✅ <b>Berhasil!</b>\n\nChat Anda sudah ditautkan ke profil siswa:\n<b>NIS:</b> " . esc($student['nis']) . "\n<b>Nama:</b> " . esc($student['full_name']) . "\n<b>Kelas:</b> " . esc($student['class']) . "\n\nTerima kasih.";
                    $this->sendMessage($token, $chat['id'], $successMsg);
                    return $this->response->setStatusCode(200)->setBody('ok');
                }

                // Process GURU (NIP)
                if ($teacher) {
                    if (! $isPinValid) {
                        @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - pin not valid for teacher {$teacher['id']}: $pin\n", FILE_APPEND);
                        $this->sendMessage($token, $chat['id'], 'Kode PIN tidak valid.');
                        return $this->response->setStatusCode(200)->setBody('ok');
                    }

                    // ensure teacher has not been linked yet (prevent takeover)
                    if (! empty($teacher['telegram_chat_id'])) {
                        @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - teacher {$teacher['id']} already linked to {$teacher['telegram_chat_id']}\n", FILE_APPEND);
                        $this->sendMessage($token, $chat['id'], 'Profil guru sudah memiliki chat yang terdaftar. Hubungi admin jika perlu mengganti.');
                        return $this->response->setStatusCode(200)->setBody('ok');
                    }

                    $teacherModel->update($teacher['id'], ['telegram_chat_id' => $chat['id'], 'updated_at' => date('Y-m-d H:i:s')]);
                    @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - linked teacher {$teacher['id']} to chat {$chat['id']} via global PIN\n", FILE_APPEND);
                    $successMsg = "✅ <b>Berhasil!</b>\n\nChat Anda sudah ditautkan ke profil guru:\n<b>NIP:</b> " . esc($teacher['nip']) . "\n<b>Nama:</b> " . esc($teacher['full_name']) . "\n\nTerima kasih.";
                    $this->sendMessage($token, $chat['id'], $successMsg);
                    return $this->response->setStatusCode(200)->setBody('ok');
                }
            }

            // If command format not recognized
            $this->sendMessage($token, $chat['id'], 'Gunakan: /link <NIS> <PIN> (contoh: /link S12345 123456)');
            return $this->response->setStatusCode(200)->setBody('ok');
        }

        // default: ignore
        return $this->response->setStatusCode(200)->setBody('ok');
    }

    protected function sendMessage($token, $chatId, $text)
    {
        if (empty($token)) {
            @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - ERROR sendMessage: token is empty\n", FILE_APPEND);
            return false;
        }

        $url = "https://api.telegram.org/bot" . urlencode($token) . "/sendMessage";
        $post = http_build_query(['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML']);

        // Try cURL first (more reliable)
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $post,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - ERROR sendMessage curl failed for chat_id=$chatId, error=$error\n", FILE_APPEND);
                return false;
            }
        } else {
            // Fallback to file_get_contents with SSL context
            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $post,
                    'timeout' => 10
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $response = @file_get_contents($url, false, stream_context_create($opts));
            if ($response === false) {
                @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - ERROR sendMessage file_get_contents failed for chat_id=$chatId\n", FILE_APPEND);
                return false;
            }
        }

        // Check if response is valid JSON from Telegram
        $decoded = json_decode($response, true);
        if (! $decoded || ! $decoded['ok']) {
            @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - ERROR sendMessage API rejected for chat_id=$chatId: " . $response . "\n", FILE_APPEND);
            return false;
        }

        @file_put_contents(WRITEPATH . 'logs/telegram_debug.log', date('c') . " - sendMessage SUCCESS for chat_id=$chatId\n", FILE_APPEND);
        return true;
    }

    // Test endpoint - manually trigger /link flow (remove in production!)
    public function testLink()
    {
        $nis = $this->request->getGet('nis') ?? 'S123456';
        $pin = $this->request->getGet('pin') ?? '112233';
        $chat_id = $this->request->getGet('chat_id') ?? '999999999';

        $studentModel = new \App\Models\StudentModel();
        $student = $studentModel->where('nis', $nis)->first();
        if (! $student) {
            return "NIS not found: $nis";
        }

        // Check global PIN
        $configModel = new \App\Models\TelegramLinkConfigModel();
        $config = $configModel->orderBy('id', 'DESC')->first();
        if ($config && ! empty($config['pin']) && hash_equals((string)$config['pin'], (string)$pin)) {
            if (! empty($student['telegram_chat_id'])) {
                return "Student {$nis} already linked to {$student['telegram_chat_id']}";
            }
            $studentModel->update($student['id'], ['telegram_chat_id' => $chat_id, 'updated_at' => date('Y-m-d H:i:s')]);
            $successMsg = "✅ <b>Berhasil!</b>\n\nChat Anda sudah ditautkan ke profil siswa:\n<b>NIS:</b> " . esc($student['nis']) . "\n<b>Nama:</b> " . esc($student['full_name']) . "\n<b>Kelas:</b> " . esc($student['class']) . "\n\nTerima kasih.";
            return "[GLOBAL PIN] SUCCESS: {$successMsg}\n\nDatabase updated_at: " . date('Y-m-d H:i:s');
        }

        // fallback to per-student PINs
        $linkModel = new \App\Models\TelegramLinkModel();
        $link = $linkModel->where('student_id', $student['id'])->where('token', $pin)->where('consumed_at', null)->first();
        if (! $link) {
            return "PIN not valid for student $nis: $pin";
        }

        if (! empty($link['expires_at']) && strtotime($link['expires_at']) < time()) {
            return "PIN expired for link {$link['id']}";
        }

        if (! empty($student['telegram_chat_id'])) {
            return "Student {$nis} already linked to {$student['telegram_chat_id']}";
        }

        // Update
        $studentModel->update($student['id'], ['telegram_chat_id' => $chat_id, 'updated_at' => date('Y-m-d H:i:s')]);
        $linkModel->update($link['id'], ['consumed_at' => date('Y-m-d H:i:s')]);

        $successMsg = "✅ <b>Berhasil!</b>\n\nChat Anda sudah ditautkan ke profil siswa:\n<b>NIS:</b> " . esc($student['nis']) . "\n<b>Nama:</b> " . esc($student['full_name']) . "\n<b>Kelas:</b> " . esc($student['class']) . "\n\nTerima kasih.";
        return "[PER-STUDENT PIN] SUCCESS: {$successMsg}\n\nDatabase updated_at: " . date('Y-m-d H:i:s');
    }
}
