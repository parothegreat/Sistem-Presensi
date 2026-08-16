<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'key';
    protected $allowedFields = ['key', 'value', 'group', 'type', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    /**
     * Get setting value by key
     */
    public function get($key, $default = null)
    {
        $setting = $this->find($key);
        return $setting ? $setting['value'] : $default;
    }

    /**
     * Get setting value by key (Alias for get)
     */
    public function getSetting($key, $default = null)
    {
        return $this->get($key, $default);
    }

    /**
     * Set setting value
     */
    public function saveSetting($key, $value, $group = 'general', $type = 'string')
    {
        $data = [
            'key' => $key,
            'value' => $value,
            'group' => $group,
            'type' => $type,
        ];

        // Check if exists
        if ($this->find($key)) {
            return $this->update($key, ['value' => $value]);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Get all settings as key-value pair
     */
    public function getAll()
    {
        $settings = $this->findAll();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $setting['value'];
        }
        return $result;
    }
}
