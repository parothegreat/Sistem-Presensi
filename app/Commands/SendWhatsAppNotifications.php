<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\WhatsAppNotificationModel;
use App\Helpers\WhatsAppHelper;

class SendWhatsAppNotifications extends BaseCommand
{
    protected $group = 'Notifications';
    protected $name = 'whatsapp:send-pending';
    protected $description = 'Send pending WhatsApp notifications via OneSender';
    protected $usage = 'whatsapp:send-pending [options]';
    protected $arguments = [];
    protected $options = [
        '--limit' => 'Limit notifications to send (default: 10)',
        '--dry-run' => 'Show what would be sent without actually sending'
    ];

    public function run(array $params = [])
    {
        $limit = (int)($params['limit'] ?? 10);
        $dryRun = isset($params['dry-run']);

        CLI::write('=== Sending WhatsApp Notifications ===', 'green');
        CLI::write("Limit: {$limit}");
        CLI::write("Dry Run: " . ($dryRun ? 'Yes' : 'No'));
        CLI::newLine();

        $waModel = new WhatsAppNotificationModel();

        // Get pending notifications
        $notifications = $waModel->getPending($limit);

        if (empty($notifications)) {
            CLI::write('No pending notifications found', 'yellow');
            return;
        }

        CLI::write("Found " . count($notifications) . " pending notification(s)", 'cyan');
        CLI::newLine();

        $sent = 0;
        $failed = 0;

        foreach ($notifications as $notif) {
            try {
                CLI::write("Sending to: {$notif['phone_number']}", 'white');
                CLI::write("Message: " . substr($notif['message'], 0, 50) . "...", 'white');

                if (!$dryRun) {
                    // Extract recipient_type from payload if exists
                    $payloadData = json_decode($notif['payload'] ?? '{}', true);
                    $recipientType = $payloadData['recipient_type'] ?? 'individual';

                    // Send message
                    $result = WhatsAppHelper::sendMessage(
                        $notif['phone_number'],
                        $notif['message'],
                        $notif['payload'],
                        $recipientType
                    );

                    if ($result) {
                        // Mark as sent
                        $waModel->markSent($notif['id']);
                        CLI::write("  ✓ Sent", 'green');
                        $sent++;
                    } else {
                        // Increment attempt
                        $waModel->incrementAttempt($notif['id']);
                        CLI::write("  ✗ Failed (attempt recorded)", 'red');
                        $failed++;
                    }
                } else {
                    CLI::write("  [DRY RUN] Would send this message", 'yellow');
                    $sent++;
                }
            } catch (\Exception $e) {
                $failed++;
                CLI::write("  ✗ Error: " . $e->getMessage(), 'red');
                $waModel->incrementAttempt($notif['id']);
            }

            CLI::newLine();
        }

        // Summary
        CLI::newLine();
        CLI::write('=== Summary ===', 'green');
        CLI::write("Sent: {$sent}", 'cyan');
        CLI::write("Failed: {$failed}", $failed > 0 ? 'red' : 'cyan');
        CLI::write("Total: " . ($sent + $failed), 'cyan');
    }
}
