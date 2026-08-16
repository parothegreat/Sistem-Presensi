<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolHolidayModel extends Model
{
    protected $table = 'school_holidays';
    protected $primaryKey = 'id';
    protected $allowedFields = ['date_from', 'date_to', 'name', 'type', 'description', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Check if a specific date is a holiday
     */
    public function isHoliday($date)
    {
        return $this->where('date_from <=', $date)
            ->where('date_to >=', $date)
            ->first();
    }

    /**
     * Get all holidays within a date range
     */
    public function getHolidaysInRange($dateFrom, $dateTo)
    {
        return $this->where('date_from <=', $dateTo)
            ->where('date_to >=', $dateFrom)
            ->orderBy('date_from', 'ASC')
            ->findAll();
    }

    /**
     * Check if date range overlaps with existing holidays
     * Exclude current holiday when checking (for edit mode)
     */
    public function checkOverlap($dateFrom, $dateTo, $excludeId = null)
    {
        $query = $this->where('date_from <=', $dateTo)
            ->where('date_to >=', $dateFrom);

        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }

        return $query->findAll();
    }

    /**
     * Get formatted overlap warning message
     */
    public function getOverlapMessage($overlappingHolidays)
    {
        if (empty($overlappingHolidays)) {
            return null;
        }

        $message = "Tanggal yang Anda masukkan bertabrakan dengan hari libur yang sudah ada:\n\n";
        foreach ($overlappingHolidays as $holiday) {
            $message .= "• " . $holiday['name'] . "\n";
            $message .= "  " . date('d M Y', strtotime($holiday['date_from'])) .
                " - " . date('d M Y', strtotime($holiday['date_to'])) . "\n\n";
        }

        return $message;
    }
}
