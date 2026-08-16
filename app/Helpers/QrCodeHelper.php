<?php

namespace App\Helpers;

class QrCodeHelper
{
    /**
     * Generate QR code SVG from text using a free online API or library
     * 
     * @param string $text Data to encode (e.g., student NIS)
     * @param int $size Size of QR code (e.g., 200)
     * @return string SVG or data URL of QR code
     */
    public static function generate($text, $size = 200)
    {
        // Using QR code from QR Server API (free, no library needed)
        // Format: https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=YOUR_DATA

        $encodedText = urlencode($text);
        $url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedText}&format=svg";

        try {
            $client = \Config\Services::curlRequest();
            $response = $client->get($url);

            if ($response->getStatusCode() === 200) {
                return $response->getBody();
            }
        } catch (\Exception $e) {
            // Fallback: return base64 encoded placeholder or generate locally
            return self::generateLocalQrCode($text, $size);
        }
    }

    /**
     * Generate QR code locally using simple method
     * Fallback if API is not available
     * 
     * @param string $text Data to encode
     * @param int $size Size of QR code
     * @return string SVG string
     */
    public static function generateLocalQrCode($text, $size = 200)
    {
        // For now, return a simple SVG with text
        // In production, consider installing a PHP QR code library like:
        // composer require chillerlan/php-qrcode

        $encodedText = htmlspecialchars($text);
        return <<<SVG
<svg width="{$size}" height="{$size}" xmlns="http://www.w3.org/2000/svg">
    <rect width="{$size}" height="{$size}" fill="white"/>
    <text x="50%" y="50%" text-anchor="middle" dy=".3em" font-family="Arial" font-size="16">
        QR: {$encodedText}
    </text>
</svg>
SVG;
    }

    /**
     * Get QR code image tag HTML
     * 
     * @param string $qrCodeData QR code SVG or URL
     * @param int $size Size of image
     * @return string HTML img tag
     */
    public static function getImageTag($qrCodeData, $size = 200)
    {
        if (strpos($qrCodeData, 'http') === 0) {
            // It's a URL
            return "<img src=\"{$qrCodeData}\" width=\"{$size}\" height=\"{$size}\" alt=\"QR Code\" />";
        } else {
            // It's SVG or data
            return $qrCodeData;
        }
    }

    /**
     * Get QR code as data URL (base64)
     * 
     * @param string $text Data to encode
     * @param int $size Size of QR code
     * @return string Data URL
     */
    public static function getDataUrl($text, $size = 200)
    {
        $encodedText = urlencode($text);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedText}";
    }
}
