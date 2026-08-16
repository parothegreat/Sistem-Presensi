<?php

namespace App\Helpers;

class WhatsAppHelper
{
    /**
     * Send WhatsApp message via OneSender
     * Non-blocking curl request
     */
    /**
     * Send WhatsApp message via OneSender
     * Non-blocking curl request
     */
    public static function sendMessage($phoneNumber, $message, $payload = null, $recipientType = 'individual')
    {
        try {
            $apiEndpoint = getenv('ONESENDER_API_ENDPOINT');
            $apiKey = getenv('ONESENDER_API_KEY');

            if (!$apiEndpoint || !$apiKey) {
                log_message('warning', 'WhatsAppHelper: OneSender credentials not configured');
                return false;
            }

            // Ensure phone number format (international, no +), ONLY if individual
            if ($recipientType === 'individual') {
                $phoneNumber = self::formatPhoneNumber($phoneNumber);
            }

            $data = [
                'recipient_type' => $recipientType,
                'to' => $phoneNumber,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ];

            if ($payload) {
                $data['custom_data'] = $payload;
            }

            // Send via curl (non-blocking)
            self::curlRequest($apiEndpoint, $data, $apiKey);

            log_message('info', "WhatsAppHelper: Queued message for $phoneNumber ($recipientType)");
            return true;
        } catch (\Exception $e) {
            log_message('error', "WhatsAppHelper: Error - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number to OneSender format
     * Input: 0812345678, 62812345678, +62812345678
     * Output: 62812345678
     */
    private static function formatPhoneNumber($phone)
    {
        // Remove + if exists
        $phone = ltrim($phone, '+');

        // If starts with 0, replace with 62
        if (strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Send curl request to OneSender API
     */
    private static function curlRequest($url, $data, $apiKey)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_message('error', "WhatsAppHelper: Curl error - {$curlError}");
        }

        if ($response) {
            log_message('info', "WhatsAppHelper: OneSender response - HTTP {$httpCode} - {$response}");
        } else {
            log_message('info', "WhatsAppHelper: OneSender response - HTTP {$httpCode}");
        }

        return $httpCode >= 200 && $httpCode < 300;
    }
}
