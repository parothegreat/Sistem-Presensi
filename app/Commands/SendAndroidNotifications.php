<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AndroidNotificationModel;
use App\Models\StudentDeviceTokenModel;
use App\Helpers\FirebaseHelper;
use Config\Firebase as FirebaseConfig;

class SendAndroidNotifications extends BaseCommand
{
    protected $group = 'Android';
    protected $name = 'android:send-pending';
    protected $description = 'Send pending Android push notifications to external API';
    protected $usage = 'android:send-pending [options]';
    protected $arguments = [];
    protected $options = [
        '--limit' => 'Limit number of notifications to send (default: 500)',
        '--dry-run' => 'Show what would be sent without actually sending',
        '--verbose' => 'Show detailed output',
    ];

    public function run(array $params = [])
    {
        try {
            $limit = (int)($params['limit'] ?? 500);
            $dryRun = $params['dry-run'] ?? false;
            $verbose = $params['verbose'] ?? false;

            CLI::write('═══════════════════════════════════════════════════════', 'cyan');
            CLI::write('Android Push Notification Sender', 'yellow');
            CLI::write('═══════════════════════════════════════════════════════', 'cyan');

            // Check if enabled
            if (getenv('ANDROID_PUSH_ENABLED') !== 'true') {
                CLI::error('Android push notifications are disabled (ANDROID_PUSH_ENABLED != true)');
                return 1;
            }

            // Load Firebase config
            $firebaseConfig = new FirebaseConfig();

            // Validate Firebase configuration
            if ($firebaseConfig->mode === 'service_account') {
                if (!$firebaseConfig->server_key || !$firebaseConfig->project_id) {
                    CLI::error('Missing Firebase Service Account configuration: server_key or project_id');
                    return 1;
                }
                CLI::write('Using Firebase Service Account authentication', 'cyan');
            } else {
                if (!$firebaseConfig->api_key) {
                    CLI::error('Missing Firebase API Key configuration');
                    return 1;
                }
                CLI::write('Using Firebase API Key authentication', 'cyan');
            }

            // Get pending notifications
            $androidModel = new AndroidNotificationModel();
            $tokenModel = new StudentDeviceTokenModel();

            $pending = $androidModel
                ->where('notification_status', 'pending')
                ->orWhere('notification_status', 'retry')
                ->where('attempts <', 5)
                ->orderBy('attempts ASC')
                ->orderBy('created_at ASC')
                ->limit($limit)
                ->findAll();

            if (empty($pending)) {
                CLI::write('No pending notifications to send.', 'green');
                return 0;
            }

            $pendingCount = count($pending);
            CLI::write("Found {$pendingCount} pending notifications", 'yellow');
            if ($dryRun) {
                CLI::write('DRY RUN MODE - Not actually sending', 'magenta');
            }
            CLI::newLine();

            $sent = 0;
            $failed = 0;
            $retry = 0;
            $errors = [];

            foreach ($pending as $notification) {
                try {
                    // Get valid device token
                    $deviceToken = $tokenModel
                        ->where('student_id', $notification['student_id'])
                        ->where('is_active', true)
                        ->first();

                    if (!$deviceToken) {
                        // No valid token - mark as failed
                        if (!$dryRun) {
                            $androidModel->update($notification['id'], [
                                'notification_status' => 'failed',
                                'last_error' => 'No valid device token found',
                                'last_error_code' => 'NO_TOKEN',
                                'failed_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                        $failed++;
                        if ($verbose) {
                            CLI::write("✗ Notification {$notification['id']}: No valid device token", 'red');
                        }
                        continue;
                    }

                    if ($verbose) {
                        CLI::write("Sending notification {$notification['id']} to student {$notification['student_id']}...", 'white');
                    }

                    // Send to Firebase
                    $response = $this->sendToFirebase($deviceToken['device_token'], $notification, $dryRun);

                    if ($response['success']) {
                        // Mark as sent
                        if (!$dryRun) {
                            $androidModel->update($notification['id'], [
                                'notification_status' => 'sent',
                                'sent_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                        $sent++;
                        if ($verbose) {
                            CLI::write("✓ Notification {$notification['id']} sent", 'green');
                        }
                    } else {
                        // Handle failure
                        $attempts = ($notification['attempts'] ?? 0) + 1;
                        $shouldRetry = $response['retry'] ?? true;

                        if ($shouldRetry && $attempts < 5) {
                            // Retry later
                            if (!$dryRun) {
                                $androidModel->update($notification['id'], [
                                    'notification_status' => 'retry',
                                    'attempts' => $attempts,
                                    'last_error' => $response['error_message'],
                                    'last_error_code' => $response['error_code']
                                ]);
                            }
                            $retry++;
                            if ($verbose) {
                                CLI::write("↻ Notification {$notification['id']} queued for retry (Attempt {$attempts}/5): {$response['error_code']}", 'yellow');
                            }
                        } else {
                            // Give up
                            if (!$dryRun) {
                                $androidModel->update($notification['id'], [
                                    'notification_status' => 'failed',
                                    'attempts' => $attempts,
                                    'last_error' => $response['error_message'],
                                    'last_error_code' => $response['error_code'],
                                    'failed_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                            $failed++;
                            if ($verbose) {
                                CLI::write("✗ Notification {$notification['id']} failed: {$response['error_code']}", 'red');
                            }
                            $errors[] = [
                                'id' => $notification['id'],
                                'student_id' => $notification['student_id'],
                                'error' => $response['error_code'],
                                'message' => $response['error_message']
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    $failed++;
                    if ($verbose) {
                        CLI::write("✗ Exception for notification {$notification['id']}: " . $e->getMessage(), 'red');
                    }
                    log_message('error', "SendAndroidNotifications: Exception - " . $e->getMessage());
                }
            }

            // Summary
            CLI::newLine();
            CLI::write('═══════════════════════════════════════════════════════', 'cyan');
            CLI::write('Summary:', 'yellow');
            CLI::write("  ✓ Sent:   {$sent}", 'green');
            CLI::write("  ↻ Retry:  {$retry}", 'yellow');
            CLI::write("  ✗ Failed: {$failed}", 'red');

            if (!empty($errors)) {
                CLI::newLine();
                CLI::write('Failed Notifications:', 'red');
                foreach ($errors as $error) {
                    CLI::write("  - ID {$error['id']} (Student {$error['student_id']}): {$error['error']} - {$error['message']}", 'red');
                }
            }

            CLI::write('═══════════════════════════════════════════════════════', 'cyan');

            log_message('info', "SendAndroidNotifications: Sent {$sent}, Retry {$retry}, Failed {$failed}");

            return 0;
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
            log_message('error', 'SendAndroidNotifications: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', 'Backtrace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Send notification to Firebase Cloud Messaging
     * Supports both API Key and Service Account authentication methods
     */
    private function sendToFirebase($deviceToken, $notification, $dryRun = false)
    {
        if ($dryRun) {
            return [
                'success' => true,
                'message_id' => 'DRY_RUN_' . uniqid()
            ];
        }

        try {
            $firebaseConfig = new FirebaseConfig();

            // Parse payload if it exists
            $notificationPayload = $notification;
            if (!empty($notification['payload'])) {
                $parsed = json_decode($notification['payload'], true);
                if ($parsed) {
                    $notificationPayload = array_merge($notification, $parsed);
                }
            }

            // Get endpoint dan auth header
            $endpoint = FirebaseHelper::getEndpoint();
            $authHeader = FirebaseHelper::getAuthHeader();

            // Format payload sesuai dengan method
            $payload = FirebaseHelper::formatPayload($deviceToken, $notificationPayload);

            // Headers
            $headers = [
                'Content-Type: application/json',
                $authHeader,
                'User-Agent: PresensiApp/1.0',
                'X-Request-ID: ' . uniqid()
            ];

            // Send via curl
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $firebaseConfig->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $firebaseConfig->connect_timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $firebaseConfig->verify_ssl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                log_message('error', "SendAndroidNotifications: Curl error - {$curlError}");
                return [
                    'success' => false,
                    'error_code' => 'CURL_ERROR',
                    'error_message' => $curlError,
                    'retry' => true
                ];
            }

            $body = json_decode($response, true);

            // Handle different HTTP codes
            if ($httpCode === 200) {
                log_message('info', "SendAndroidNotifications: Success - HTTP {$httpCode}");
                $messageId = $body['name'] ?? $body['message_id'] ?? 'SUCCESS';
                return [
                    'success' => true,
                    'message_id' => $messageId
                ];
            } elseif ($httpCode === 400) {
                // Bad request - don't retry
                $errorMsg = $body['error'] ?? 'Unknown';
                if (is_array($errorMsg)) {
                    $errorMsg = json_encode($errorMsg);
                }
                log_message('warning', "SendAndroidNotifications: Bad request - {$errorMsg}");
                return [
                    'success' => false,
                    'error_code' => 'BAD_REQUEST',
                    'error_message' => $body['error']['message'] ?? ($body['error'] ?? 'Invalid request'),
                    'retry' => false
                ];
            } elseif ($httpCode === 401 || $httpCode === 403) {
                // Auth error - don't retry
                log_message('error', "SendAndroidNotifications: Auth error - HTTP {$httpCode}");
                return [
                    'success' => false,
                    'error_code' => 'AUTH_ERROR',
                    'error_message' => 'Authentication failed',
                    'retry' => false
                ];
            } elseif ($httpCode === 404) {
                // Device not found - don't retry (mark token invalid)
                log_message('warning', "SendAndroidNotifications: Device not found - HTTP 404");
                return [
                    'success' => false,
                    'error_code' => 'DEVICE_NOT_FOUND',
                    'error_message' => 'Device token invalid or expired',
                    'retry' => false
                ];
            } elseif ($httpCode >= 500) {
                // Server error - retry
                log_message('error', "SendAndroidNotifications: Server error - HTTP {$httpCode}");
                return [
                    'success' => false,
                    'error_code' => 'SERVER_ERROR',
                    'error_message' => 'External API server error',
                    'retry' => true
                ];
            } else {
                // Unknown error
                log_message('warning', "SendAndroidNotifications: Unknown response - HTTP {$httpCode}");
                return [
                    'success' => false,
                    'error_code' => "HTTP_{$httpCode}",
                    'error_message' => 'Unexpected response from external API',
                    'retry' => true
                ];
            }
        } catch (\Exception $e) {
            log_message('error', "SendAndroidNotifications: Exception - " . $e->getMessage());
            return [
                'success' => false,
                'error_code' => 'EXCEPTION',
                'error_message' => $e->getMessage(),
                'retry' => true
            ];
        }
    }
}
