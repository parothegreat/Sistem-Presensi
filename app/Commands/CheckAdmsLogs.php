<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BiometricLogModel;

class CheckAdmsLogs extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'app:check-adms-logs';
    protected $description = 'Check biometric logs for ADMS test';

    public function run(array $params)
    {
        $model = new BiometricLogModel();
        // Check for specific timestamps from our curl test
        // 2025-12-21 07:05:00 and 16:05:00
        $logs = $model->whereIn('time', ['07:05:00', '16:05:00'])
                      ->orderBy('id', 'DESC')
                      ->findAll();
        
        CLI::write("Found " . count($logs) . " records matching test timestamps.");
        foreach ($logs as $log) {
            CLI::write("- ID: {$log['id']}, User: {$log['user_id']}, Type: {$log['user_type']}, Status: {$log['status']}");
        }
        
        // Also check if UNKNOWN_USER exists (should be 0)
        $unknown = $model->where('user_id', 'UNKNOWN_USER')->countAllResults();
        CLI::write("Unknown user records found: " . $unknown);
    }
}
