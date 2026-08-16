<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\BiometricLogModel;
use App\Models\StudentModel;
use App\Models\TeacherModel;

class AdmsController extends Controller
{
    /**
     * Handle ADMS Push Data (cdata)
     * URL: /iclock/cdata
     * Methods: GET, POST
     */
    public function cdata()
    {
        // Get Serial Number
        $sn = $this->request->getGet('SN');
        
        // Get Table Name
        $table = $this->request->getGet('table');

        // If it's a handshake/registry check (GET request usually)
        if ($this->request->getMethod() === 'GET' && $this->request->getGet('options') === 'all') {
             return 'GET OPTION FROM: ' . $sn . "\n" .
                'Stamp=9999' . "\n" .
                'OpStamp=9999' . "\n" .
                'ErrorDelay=60' . "\n" .
                'Delay=30' . "\n" .
                'TransTimes=00:00;14:05' . "\n" .
                'TransInterval=1' . "\n" .
                'TransFlag=1111000000' . "\n" .
                'TimeZone=7' . "\n" .
                'Realtime=1' . "\n" .
                'Encrypt=0';
        }

        if ($table === 'ATTLOG') {
            // Get Body Content (Raw Post Data)
            $content = $this->request->getBody();
            
            if (empty($content)) {
                return 'OK'; 
            }

            $rows = explode("\n", $content);
            $biometricLogModel = new BiometricLogModel();
            $studentModel = new StudentModel();
            $teacherModel = new TeacherModel();
            
            $count = 0;

            foreach ($rows as $row) {
                if (empty(trim($row))) continue;

                // Format: USERID \t CHECKTIME \t STATUS \t VERIFYTYPE
                $cols = explode("\t", trim($row));
                
                if (count($cols) < 2) continue;

                $userIdFromDevice = $cols[0];
                $timestamp = $cols[1];
                $status = $cols[2] ?? '0'; // 0=CheckIn, 1=CheckOut
                $verifyType = $cols[3] ?? '1';

                // Map status
                // 0: Check-In, 1: Check-Out, 4: Overtime-In, 5: Overtime-Out
                $statusMap = [
                    '0' => 'checkin',
                    '1' => 'checkout',
                    '4' => 'checkin',
                    '5' => 'checkout'
                ];
                $mappedStatus = $statusMap[$status] ?? 'checkin';

                // Determine User Type (Siswa/Guru)
                $userType = null;
                $systemUserId = null;

                // Cek Siswa (NIS)
                $student = $studentModel->where('nis', $userIdFromDevice)->first();
                if ($student) {
                    $userType = 'siswa';
                    $systemUserId = $student['user_id']; // This is the users.id
                } else {
                    // Cek Guru (NIP)
                    $teacher = $teacherModel->where('nip', $userIdFromDevice)->first();
                    if ($teacher) {
                        $userType = 'guru';
                        $systemUserId = $teacher['user_id'];
                    }
                }

                // Skip if user not found (or log as unknown)
                // For now we log only if identified, or we can log everything.
                // It is safer to log everything and process later, but let's stick to identifying first.
                // The current BiometricLogModel expects 'user_id' which is user_id string from device? 
                // Wait, BiometricLogModel schema says `user_id` VARCHAR(50).
                // Let's store the device user id in `user_id` column as per schema comment/usage in BiometricController.
                
                // If user not found, we still log it with user_type = null
                if (!$userType) {
                     // Optional: Log warning, but proceed
                     log_message('warning', "ADMS: Unknown user ID {$userIdFromDevice} from device {$sn}");
                }

                $date = date('Y-m-d', strtotime($timestamp));
                $time = date('H:i:s', strtotime($timestamp));

                // Check Duplicate
                 $existing = $biometricLogModel
                    ->where('user_id', $userIdFromDevice)
                    ->where('timestamp', $timestamp)
                    ->first();

                if (!$existing) {
                    $biometricLogModel->insert([
                        'device_id' => $sn ?? 'unknown',
                        'user_id' => $userIdFromDevice, // NIP or NIS
                        'date' => $date,
                        'time' => $time,
                        'timestamp' => $timestamp,
                        'biometric_type' => 'adms',
                        'status' => $mappedStatus,
                        'user_type' => $userType, // Can be null now
                        'processed' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $count++;
                }
            }

            return 'OK';
        }

        // Handle other tables if necessary
        return 'OK';
    }

    /**
     * Handle Device Handshake / Heartbeat
     * URL: /iclock/getrequest
     */
    public function getrequest()
    {
         return 'OK';
    }
    
    /**
     * Handle Device Command Reply
     * URL: /iclock/devicecmd
     */
    public function devicecmd()
    {
        return 'OK';
    }
}
