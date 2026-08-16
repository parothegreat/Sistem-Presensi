<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use App\Models\BiometricLogModel;
use App\Models\StudentModel;
use App\Models\TeacherModel;
use App\Models\UserModel;

class BiometricController extends Controller
{
    use ResponseTrait;

    /**
     * API endpoint untuk menerima push data dari finger machine
     * POST /api/attendance/receive-biometric
     * 
     * Headers:
     * - Authorization: Bearer YOUR_API_KEY
     * - X-Device-ID: LOCAL_COMPUTER_ID
     * - Content-Type: application/json
     */
    public function receiveBiometric()
    {
        // Hanya terima POST request
        if ($this->request->getMethod() !== 'POST') {
            return $this->fail('Method not allowed', 405);
        }

        // Validasi API Key
        $apiKey = $this->request->getHeaderLine('Authorization');
        $deviceId = $this->request->getHeaderLine('X-Device-ID');

        if (!$apiKey || !$deviceId) {
            log_message('warning', 'BiometricController: Missing API Key or Device ID');
            return $this->fail('Missing authentication', 401);
        }

        // Hapus "Bearer " prefix
        $apiKey = str_replace('Bearer ', '', $apiKey);

        // Validasi API Key (hardcoded untuk sekarang, bisa dipindah ke database)
        $validApiKey = getenv('BIOMETRIC_API_KEY') ?? 'YOUR_SECRET_API_KEY_HERE';
        if ($apiKey !== $validApiKey) {
            log_message('warning', "BiometricController: Invalid API Key from device {$deviceId}");
            return $this->fail('Invalid API Key', 401);
        }

        // Parse JSON body
        $json = $this->request->getJSON(true);

        if (!$json || !is_array($json)) {
            log_message('warning', 'BiometricController: Invalid JSON format');
            return $this->fail('Invalid JSON format', 400);
        }

        // Validasi struktur data
        if (!isset($json['records']) || !is_array($json['records'])) {
            log_message('warning', 'BiometricController: Missing records array');
            return $this->fail('Missing records array', 400);
        }

        $biometricLogModel = new BiometricLogModel();
        $records = $json['records'];
        $processed = 0;
        $failed = 0;
        $details = [];

        // Process setiap record
        foreach ($records as $record) {
            try {
                // Validasi required fields
                if (empty($record['user_id']) || empty($record['timestamp'])) {
                    $failed++;
                    $details[] = [
                        'user_id' => $record['user_id'] ?? 'NULL',
                        'timestamp' => $record['timestamp'] ?? 'NULL',
                        'result' => 'error',
                        'message' => 'Missing user_id or timestamp'
                    ];
                    continue;
                }

                // Validasi user_id ada di database (siswa atau guru)
                $studentModel = new StudentModel();
                $teacherModel = new TeacherModel();
                $student = $studentModel->where('nis', $record['user_id'])->first();
                $teacher = null;
                $userType = null;
                $userId = null;

                if ($student) {
                    $userType = 'siswa';
                    $userId = $student['user_id'];
                } else {
                    // Cek sebagai guru (NIP)
                    $teacher = $teacherModel->where('nip', $record['user_id'])->first();
                    if ($teacher) {
                        $userType = 'guru';
                        $userId = $teacher['user_id'];
                    }
                }

                if (!$userType || !$userId) {
                    $failed++;
                    $details[] = [
                        'user_id' => $record['user_id'],
                        'timestamp' => $record['timestamp'],
                        'result' => 'error',
                        'message' => 'User not found (siswa/guru)'
                    ];
                    continue;
                }

                // Cek duplicate (same user, same date, same time ±5 menit)
                $timestamp = $record['timestamp'];
                $date = date('Y-m-d', strtotime($timestamp));
                $timeFrom = date('H:i:s', strtotime($timestamp . ' -5 minutes'));
                $timeTo = date('H:i:s', strtotime($timestamp . ' +5 minutes'));

                $existing = $biometricLogModel
                    ->where('user_id', $record['user_id'])
                    ->where('date', $date)
                    ->where('time >=', $timeFrom)
                    ->where('time <=', $timeTo)
                    ->first();

                if ($existing) {
                    $failed++;
                    $details[] = [
                        'user_id' => $record['user_id'],
                        'timestamp' => $record['timestamp'],
                        'result' => 'error',
                        'message' => 'Duplicate entry (within 5 minutes)',
                        'log_id' => $existing['id']
                    ];
                    continue;
                }

                // Insert ke biometric_logs
                $logId = $biometricLogModel->insert([
                    'device_id' => $deviceId,
                    'user_id' => $record['user_id'],
                    'date' => $date,
                    'time' => date('H:i:s', strtotime($timestamp)),
                    'timestamp' => $timestamp,
                    'biometric_type' => $record['biometric_type'] ?? 'fingerprint',
                    'status' => $record['status'] ?? 'checkin',
                    'user_type' => $userType,
                    'processed' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $processed++;
                $details[] = [
                    'user_id' => $record['user_id'],
                    'timestamp' => $record['timestamp'],
                    'user_type' => $userType,
                    'result' => 'success',
                    'log_id' => $logId
                ];

                log_message('info', "BiometricController: Successfully logged biometric for {$userType} {$record['user_id']}");
            } catch (\Exception $e) {
                $failed++;
                $details[] = [
                    'user_id' => $record['user_id'] ?? 'NULL',
                    'timestamp' => $record['timestamp'] ?? 'NULL',
                    'result' => 'error',
                    'message' => $e->getMessage()
                ];
                log_message('error', "BiometricController: Error processing record - " . $e->getMessage());
            }
        }

        // Response
        $response = [
            'ok' => $failed === 0,
            'message' => $failed === 0 ? 'Data received and processed successfully' : 'Some records failed to process',
            'processed' => $processed,
            'failed' => $failed,
            'details' => $details
        ];

        $statusCode = ($failed === 0) ? 200 : 207;
        return $this->response->setStatusCode($statusCode)->setJSON($response);
    }
}
