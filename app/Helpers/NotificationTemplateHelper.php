<?php

namespace App\Helpers;

class NotificationTemplateHelper
{
    /**
     * Replace placeholders in template with actual data
     * 
     * @param string $templateContent Template string with {variables}
     * @param array $data Associative array of data ['variable' => 'value']
     * @return string Processed content
     */
    public static function replaceVariables($templateContent, $data)
    {
        if (empty($templateContent)) {
            return '';
        }

        // Add common global variables if not present
        if (!isset($data['school_name'])) {
            // Get from Database Settings
            $settingsModel = new \App\Models\SettingsModel();
            // Use getSetting alias we created earlier (or fallback to empty string if fails)
            $schoolName = $settingsModel->getSetting('school_name');
            $data['school_name'] = $schoolName ?: (getenv('SCHOOL_NAME') ?: 'Sekolah');
        }
        if (!isset($data['date'])) {
            $data['date'] = date('d-m-Y');
        }

        // Process replacements
        foreach ($data as $key => $value) {
            // Handle null values
            $val = $value ?? '';
            // Case-insensitive replacement for {variable}
            // Using str_ireplace for simpler logic, or regex for strict {variable} pattern
            // Let's use strict regex for safety and precision
            $pattern = '/\{' . preg_quote($key, '/') . '\}/i';
            $templateContent = preg_replace($pattern, $val, $templateContent);
        }

        return trim($templateContent);
    }

    /**
     * Get status label from status code (common helper)
     */
    public static function getStatusLabel($status, $type = 'text')
    {
        $labels = [
            'on_time' => 'Tepat Waktu',
            'late' => 'Terlambat',
            'early' => 'Pulang Awal',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
            'unknown' => 'Belum Absen'
        ];
        
        $label = $labels[$status] ?? ucfirst($status);

        if ($type === 'emoji') {
            $emojis = [
                'on_time' => '✅',
                'late' => '⚠',
                'early' => '⏱️',
                'izin' => '📋',
                'sakit' => '🏥',
                'alpha' => '❌',
                'unknown' => '❓'
            ];
            return ($emojis[$status] ?? '') . ' ' . $label;
        }

        return $label;
    }
}
