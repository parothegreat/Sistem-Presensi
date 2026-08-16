<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationTemplateModel extends Model
{
    protected $table = 'notification_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['code', 'name', 'channel', 'content', 'description', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    // Callbacks
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get template content by code, optionally from cache
     * @param string $code
     * @param bool $useCache
     * @return array|null
     */
    public function getTemplate($code, $useCache = true)
    {
        $cacheKey = "notif_template_{$code}";
        
        if ($useCache) {
            $cached = cache($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $template = $this->where('code', $code)
            ->where('is_active', 1)
            ->first();

        if ($template && $useCache) {
            // Cache for 1 hour (3600s)
            cache()->save($cacheKey, $template, 3600);
        }

        return $template;
    }

    /**
     * Clear specific template cache
     */
    public function clearTemplateCache($code)
    {
        cache()->delete("notif_template_{$code}");
    }
}
