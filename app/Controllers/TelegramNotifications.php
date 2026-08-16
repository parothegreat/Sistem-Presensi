<?php

namespace App\Controllers;

use App\Helpers\TelegramHelper;

class TelegramNotifications extends BaseController
{
    /**
     * Send all pending Telegram notifications
     * Can be called manually or via cron job
     * 
     * Route: GET /telegram/send-pending
     */
    public function sendPending()
    {
        // Require admin role or localhost (optional, can be removed for cron)
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            // Allow CLI execution and localhost without login
            $clientIP = $this->request->getIPAddress();
            $allowedIPs = ['127.0.0.1', '::1', 'localhost'];
            if (!in_array($clientIP, $allowedIPs)) {
                // Log the attempt
                log_message('warning', "Unauthorized access to /telegram/send-pending from IP: {$clientIP}");
                return $this->response->setStatusCode(403)
                    ->setJSON(['success' => false, 'message' => 'Unauthorized']);
            }
        }

        try {
            log_message('info', 'sendPending() called from ' . $this->request->getIPAddress());
            $sent = TelegramHelper::sendPendingNotifications();
            log_message('info', "sendPendingNotifications() returned: {$sent}");

            return $this->response->setJSON([
                'success' => true,
                'message' => "Sent {$sent} Telegram notification(s)",
                'count' => $sent
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Exception in sendPending: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
        }
    }
}
