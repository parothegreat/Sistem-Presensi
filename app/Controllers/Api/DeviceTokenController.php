<?php

namespace App\Controllers\Api;

use App\Models\StudentDeviceTokenModel;
use App\Models\StudentModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class DeviceTokenController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\StudentDeviceTokenModel';
    protected $format = 'json';

    /**
     * Register or update device token for a student
     * 
     * Accepts format dari Android:
     * {
     *   "user_id": "nisn_school_a_001",
     *   "nis": "001234",
     *   "npsn": "20401559",
     *   "token": "token_a_device_001",
     *   "device_name": "Samsung A50"
     * }
     * 
     * POST /api/device-token/register
     */
    public function register()
    {
        try {
            // Get JSON input
            $data = $this->request->getJSON();

            // Validate required fields
            if (!isset($data->nis) || empty($data->nis)) {
                return $this->fail('nis wajib diisi', 400);
            }

            if (!isset($data->token) || empty($data->token)) {
                return $this->fail('token wajib diisi', 400);
            }

            if (!isset($data->npsn) || empty($data->npsn)) {
                return $this->fail('npsn wajib diisi', 400);
            }

            $nis = $data->nis;
            $token = $data->token;
            $npsn = $data->npsn;
            $deviceName = $data->device_name ?? 'Unknown Device';

            // Find student by NIS
            $studentModel = new StudentModel();
            $student = $studentModel->where('nis', $nis)->first();

            if (!$student) {
                return $this->fail('Siswa dengan NIS ' . $nis . ' tidak ditemukan', 404);
            }

            // Register or update token
            $tokenModel = new StudentDeviceTokenModel();

            // Check if token already exists for this NPSN + device combination
            $existingToken = $tokenModel->where('npsn', $npsn)
                ->where('device_token', $token)
                ->first();

            if ($existingToken) {
                // Token already registered for this school - update
                $tokenModel->update($existingToken['id'], [
                    'student_id' => $student['id'],
                    'device_name' => $deviceName,
                    'last_used_at' => date('Y-m-d H:i:s'),
                    'is_active' => true
                ]);

                return $this->respond([
                    'success' => true,
                    'message' => 'Device token berhasil di-update',
                    'data' => [
                        'student_id' => $student['id'],
                        'nis' => $student['nis'],
                        'student_name' => $student['full_name'],
                        'device_name' => $deviceName,
                        'token' => $token,
                        'npsn' => $npsn,
                        'status' => 'updated',
                        'registered_at' => date('Y-m-d H:i:s')
                    ]
                ], 200);
            }

            // New token - insert
            $newId = $tokenModel->insert([
                'student_id' => $student['id'],
                'npsn' => $npsn,
                'device_token' => $token,
                'device_name' => $deviceName,
                'is_active' => true,
                'last_used_at' => date('Y-m-d H:i:s')
            ]);

            if ($newId) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Device token berhasil didaftarkan',
                    'data' => [
                        'student_id' => $student['id'],
                        'nis' => $student['nis'],
                        'student_name' => $student['full_name'],
                        'device_name' => $deviceName,
                        'token' => $token,
                        'npsn' => $npsn,
                        'status' => 'created',
                        'registered_at' => date('Y-m-d H:i:s')
                    ]
                ], 201);
            } else {
                return $this->fail('Gagal mendaftarkan device token', 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'DeviceTokenController::register - ' . $e->getMessage());
            return $this->fail('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Unregister device token (when app is uninstalled)
     * 
     * POST /api/device-token/unregister
     * {
     *   "device_token": "abc123def456..."
     * }
     */
    public function unregister()
    {
        try {
            $data = $this->request->getJSON();

            if (!$data->device_token) {
                return $this->fail('device_token wajib diisi', 400);
            }

            // Get NPSN from env
            $npsn = getenv('SCHOOL_NPSN');
            if (!$npsn) {
                return $this->fail('SCHOOL_NPSN tidak dikonfigurasi di .env', 500);
            }

            $tokenModel = new StudentDeviceTokenModel();

            if ($tokenModel->deactivateToken($data->device_token, $npsn)) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Device token berhasil dihapuskan'
                ], 200);
            } else {
                return $this->fail('Device token tidak ditemukan', 404);
            }
        } catch (\Exception $e) {
            log_message('error', 'DeviceTokenController::unregister - ' . $e->getMessage());
            return $this->fail('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all active tokens for a student
     * 
     * GET /api/device-token/student/{nis}
     */
    public function getByStudent($nis = null)
    {
        try {
            if (!$nis) {
                return $this->fail('NIS harus disediakan', 400);
            }

            // Get NPSN from env
            $npsn = getenv('SCHOOL_NPSN');
            if (!$npsn) {
                return $this->fail('SCHOOL_NPSN tidak dikonfigurasi di .env', 500);
            }

            $studentModel = new StudentModel();
            $student = $studentModel->where('nis', $nis)->first();

            if (!$student) {
                return $this->fail('Siswa tidak ditemukan', 404);
            }

            $tokenModel = new StudentDeviceTokenModel();
            $tokens = $tokenModel->getActiveTokensByStudent($student['id'], $npsn);

            return $this->respond([
                'success' => true,
                'student_id' => $student['id'],
                'nis' => $student['nis'],
                'student_name' => $student['full_name'],
                'npsn' => $npsn,
                'devices_count' => count($tokens),
                'devices' => $tokens
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'DeviceTokenController::getByStudent - ' . $e->getMessage());
            return $this->fail('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Refresh token (keep-alive)
     * 
     * POST /api/device-token/refresh
     * {
     *   "device_token": "abc123def456..."
     * }
     */
    public function refresh()
    {
        try {
            $data = $this->request->getJSON();

            if (!$data->device_token) {
                return $this->fail('device_token wajib diisi', 400);
            }

            // Get NPSN from env
            $npsn = getenv('SCHOOL_NPSN');
            if (!$npsn) {
                return $this->fail('SCHOOL_NPSN tidak dikonfigurasi di .env', 500);
            }

            $tokenModel = new StudentDeviceTokenModel();
            $existing = $tokenModel->where('npsn', $npsn)
                ->where('device_token', $data->device_token)
                ->first();

            if (!$existing) {
                return $this->fail('Device token tidak ditemukan', 404);
            }

            // Update last_used_at
            $tokenModel->update($existing['id'], [
                'last_used_at' => date('Y-m-d H:i:s'),
                'is_active' => true
            ]);

            return $this->respond([
                'success' => true,
                'message' => 'Device token berhasil di-refresh',
                'last_used_at' => date('Y-m-d H:i:s')
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'DeviceTokenController::refresh - ' . $e->getMessage());
            return $this->fail('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
