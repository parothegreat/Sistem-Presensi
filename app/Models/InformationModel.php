<?php

namespace App\Models;

use CodeIgniter\Model;

class InformationModel extends Model
{
    protected $table = 'informations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'content',
        'target_classes', // JSON
        'type',
        'send_via_wa',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get recent informations
     */
    public function getRecent($limit = 5)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
