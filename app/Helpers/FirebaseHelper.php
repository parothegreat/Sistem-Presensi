<?php

namespace App\Helpers;

use Config\Firebase as FirebaseConfig;

class FirebaseHelper
{
    /**
     * Get Firebase authorization header
     * Supports both API Key and Service Account methods
     */
    public static function getAuthHeader()
    {
        $config = new FirebaseConfig();

        if ($config->mode === 'service_account') {
            // OAuth2 token dari Service Account
            $token = self::getServiceAccountToken();
            return 'Authorization: Bearer ' . $token;
        } else {
            // Simple API Key
            return 'Authorization: key=' . $config->api_key;
        }
    }

    /**
     * Get OAuth2 token dari Service Account Private Key
     * Token valid untuk 1 jam, cache untuk performance
     */
    public static function getServiceAccountToken()
    {
        $config = new FirebaseConfig();

        // Check cache (session/file)
        $cacheKey = 'firebase_oauth2_token';
        $cache = cache();

        // Try get dari cache
        if ($cached = $cache->get($cacheKey)) {
            return $cached['access_token'];
        }

        // Generate JWT dari private key
        $jwt = self::generateServiceAccountJWT();

        // Exchange JWT untuk access token
        $token = self::exchangeJWTForAccessToken($jwt);

        // Cache token untuk 55 menit (expire di 60 menit)
        $cache->save($cacheKey, $token, 55 * 60);

        return $token['access_token'];
    }

    /**
     * Generate JWT (JSON Web Token) dari Service Account Private Key
     */
    private static function generateServiceAccountJWT()
    {
        $config = new FirebaseConfig();

        // JWT Header
        $header = [
            'typ' => 'JWT',
            'alg' => 'RS256'
        ];

        // JWT Claims
        $now = time();
        $claims = [
            'iss' => $config->service_account_email,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => $config->token_endpoint,
            'exp' => $now + 3600, // Valid untuk 1 jam
            'iat' => $now
        ];

        // Encode header & claims
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $claimsEncoded = self::base64UrlEncode(json_encode($claims));
        $signatureInput = $headerEncoded . '.' . $claimsEncoded;

        // Sign dengan private key
        $privateKeyResource = openssl_pkey_get_private($config->server_key);

        if (!$privateKeyResource) {
            throw new \Exception('Invalid Firebase private key');
        }

        openssl_sign(
            $signatureInput,
            $signature,
            $privateKeyResource,
            OPENSSL_ALGO_SHA256
        );

        $signatureEncoded = self::base64UrlEncode($signature);

        return $signatureInput . '.' . $signatureEncoded;
    }

    /**
     * Exchange JWT untuk access token via Google OAuth2
     */
    private static function exchangeJWTForAccessToken($jwt)
    {
        $config = new FirebaseConfig();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $config->token_endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $config->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $config->connect_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $config->verify_ssl);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('OAuth2 request failed: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new \Exception('OAuth2 token exchange failed (HTTP ' . $httpCode . ')');
        }

        return json_decode($response, true);
    }

    /**
     * Base64 URL Encode (untuk JWT)
     */
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get Firebase endpoint untuk kirim notification
     */
    public static function getEndpoint()
    {
        $config = new FirebaseConfig();

        if ($config->mode === 'service_account') {
            // FCM v1 API format
            return str_replace('{project_id}', $config->project_id, $config->fcm_v1_endpoint);
        } else {
            // Legacy FCM API format
            return $config->api_endpoint;
        }
    }

    /**
     * Format notification payload sesuai dengan method yang digunakan
     */
    public static function formatPayload($deviceToken, $notification, $mode = 'api_key')
    {
        $config = new FirebaseConfig();

        if ($config->mode === 'service_account') {
            // FCM v1 API format
            return self::formatPayloadV1($deviceToken, $notification);
        } else {
            // Legacy FCM API format
            return self::formatPayloadLegacy($deviceToken, $notification);
        }
    }

    /**
     * Format payload untuk Legacy FCM API (dengan API Key)
     */
    private static function formatPayloadLegacy($deviceToken, $notification)
    {
        // Parse data if it's a JSON string
        $data = $notification['data'] ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        // Ensure data is array
        if (!is_array($data)) {
            $data = [];
        }

        return [
            'to' => $deviceToken,
            'notification' => [
                'title' => $notification['title'],
                'body' => $notification['message'],
                'sound' => 'default',
                'priority' => 'high'
            ],
            'data' => $data,
            'priority' => 'high',
            'ttl' => (new FirebaseConfig())->ttl
        ];
    }

    /**
     * Format payload untuk FCM v1 API (dengan Service Account)
     */
    private static function formatPayloadV1($deviceToken, $notification)
    {
        // Parse data if it's a JSON string
        $data = $notification['data'] ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        // Ensure data is array
        if (!is_array($data)) {
            $data = [];
        }

        return [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $notification['title'],
                    'body' => $notification['message']
                ],
                'data' => (object)$data,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'attendance_notifications'
                    ]
                ]
            ]
        ];
    }
}
