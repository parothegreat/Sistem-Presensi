<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BiometricLogModel;

class CheckAdmsLogsRobust extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'app:check-adms-robust';
    protected $description = 'Check biometric logs for robust ADMS test';

    public function run(array $params)
    {
        $model = new BiometricLogModel();
        // Check for specific timestamps from our robust curl test
        // 2025-12-21 08:00:00, 16:30:00, 21:00:00
        $logs = $model->whereIn('time', ['08:00:00', '16:30:00', '21:00:00'])
                      ->orderBy('id', 'DESC')
                      ->findAll();
        
        CLI::write("Found " . count($logs) . " records matching test timestamps.");
        foreach ($logs as $log) {
            $type = $log['user_type'] ?? 'NULL';
            CLI::write("- ID: {$log['id']}, User: {$log['user_id']}, Type: {$type}, Status: {$log['status']}");
        }
    }
}
