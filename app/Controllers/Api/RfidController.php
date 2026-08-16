<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

class RfidController extends ResourceController
{
    protected $studentModel;
    protected $teacherModel;
    protected $biometricLogModel;
    protected $attendanceModel;

    public function __construct()
    {
        $this->studentModel = model('StudentModel');
        $this->teacherModel = model('TeacherModel');
        $this->biometricLogModel = model('BiometricLogModel');
        $this->attendanceModel = model('AttendanceModel');
    }

    /**
     * Tap RFID - Record attendance via RFID reader
     * POST /api/rfid/tap
     * 
     * Required:
     * - rfid_id: string (RFID tag ID)
     * - device_id: string (Device/Reader ID)
     * - tap_time: datetime (optional, defaults to now)
     * 
     * Supports both:
     * - Siswa (by NIS)
     * - Guru (by NIP)
     */
    public function tap()
    {
        // Validate request method
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->fail('Only POST method allowed', ResponseInterface::HTTP_METHOD_NOT_ALLOWED);
        }

        // Get JSON body
        $data = $this->request->getJSON(true);

        // Validate required fields
        if (empty($data['rfid_id'])) {
            return $this->fail('RFID ID is required', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (empty($data['device_id'])) {
            return $this->fail('Device ID is required', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $rfidId = trim($data['rfid_id']);
        $deviceId = trim($data['device_id']);
        $tapTime = isset($data['tap_time']) ? $data['tap_time'] : date('Y-m-d H:i:s');

        try {
            $tapDateTime = new \DateTime($tapTime);
            $tapDate = $tapDateTime->format('Y-m-d');
            $tapTimeOnly = $tapDateTime->format('H:i:s');

            // Find user (student or teacher) by RFID ID
            $student = $this->studentModel->where('rfid_id', $rfidId)->first();
            $teacher = null;
            $userType = null;
            $identifier = null;  // NIS or NIP
            $userName = null;

            if ($student) {
                $userType = 'siswa';
                $identifier = $student['nis'];
                $userName = $student['full_name'];
            } else {
                // Check if it's a teacher (guru)
                $teacher = $this->teacherModel->where('rfid_id', $rfidId)->first();
                if ($teacher) {
                    $userType = 'guru';
                    $identifier = $teacher['nip'];
                    $userName = $teacher['full_name'];
                }
            }

            if (!$student && !$teacher) {
                return $this->fail('RFID tag not registered', ResponseInterface::HTTP_NOT_FOUND);
            }

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Create biometric log entry
            $biometricLogData = [
                'device_id' => $deviceId,
                'user_id' => $identifier,  // NIS for siswa, NIP for guru
                'user_type' => $userType,  // 'siswa' or 'guru'
                'date' => $tapDate,
                'time' => $tapTimeOnly,
                'timestamp' => $tapDateTime->format('Y-m-d H:i:s'),
                'biometric_type' => 'rfid',
                'status' => 'unprocessed',
                'processed' => false,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $biometricLogId = $this->biometricLogModel->insert($biometricLogData);

            if (!$biometricLogId) {
                $db->transRollback();
                return $this->fail('Failed to save RFID log', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }

            $db->transComplete();

            // Prepare LCD display (2 lines, ~16 chars per line)
            $lcdLine1 = substr($userName, 0, 16);  // Nama (max 16 char)
            $lcdLine2 = $tapDateTime->format('H:i:s') . ' ✓';  // Waktu + tanda sukses

            return $this->respond([
                'status' => 'success',
                'message' => 'RFID tap recorded successfully',
                'data' => [
                    'biometric_log_id' => $biometricLogId,
                    'user_type' => $userType,
                    'name' => $userName,
                    'identifier' => $identifier,
                    'rfid_id' => $rfidId,
                    'tap_time' => $tapDateTime->format('Y-m-d H:i:s'),
                ],
                'lcd_display' => [
                    'line1' => $lcdLine1,
                    'line2' => $lcdLine2,
                    'duration_seconds' => 3  // Tampilkan selama 3 detik
                ]
            ], ResponseInterface::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->fail('Error: ' . $e->getMessage(), ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get RFID status
     * GET /api/rfid/status/{rfid_id}
     * 
     * Returns status for both siswa and guru
     */
    public function status($rfidId = null)
    {
        if (!$rfidId) {
            return $this->fail('RFID ID is required', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $rfidId = trim($rfidId);
        $student = $this->studentModel->where('rfid_id', $rfidId)->first();
        $teacher = null;
        $userType = null;
        $identifier = null;
        $userInfo = null;

        if ($student) {
            $userType = 'siswa';
            $identifier = $student['nis'];
            $userInfo = [
                'id' => $student['id'],
                'name' => $student['full_name'],
                'nis' => $student['nis'],
                'class' => $student['class'],
            ];
        } else {
            $teacher = $this->teacherModel->where('rfid_id', $rfidId)->first();
            if ($teacher) {
                $userType = 'guru';
                $identifier = $teacher['nip'];
                $userInfo = [
                    'id' => $teacher['id'],
                    'name' => $teacher['full_name'],
                    'nip' => $teacher['nip'],
                    'subject' => $teacher['subject'],
                ];
            }
        }

        if (!$student && !$teacher) {
            return $this->fail('RFID tag not found', ResponseInterface::HTTP_NOT_FOUND);
        }

        // Get last attendance today
        $today = date('Y-m-d');
        $lastLog = $this->biometricLogModel
            ->where('user_id', $identifier)
            ->where('user_type', $userType)
            ->where('date', $today)
            ->where('biometric_type', 'rfid')
            ->orderBy('timestamp', 'DESC')
            ->first();

        // Prepare LCD display
        $lcdLine1 = substr($userInfo['name'], 0, 16);  // Nama
        $statusText = $lastLog ? substr($lastLog['status'], 0, 8) : 'Belum';  // Status
        $lcdLine2 = ($lastLog ? substr($lastLog['timestamp'], 11, 8) : '--:--:--') . ' ' . $statusText;

        return $this->respond([
            'status' => 'success',
            'data' => [
                'rfid_id' => $rfidId,
                'user_type' => $userType,
                'user' => $userInfo,
                'last_tap_today' => $lastLog ? [
                    'time' => $lastLog['timestamp'],
                    'type' => $lastLog['status'],
                    'processed' => (bool)$lastLog['processed'],
                ] : null,
            ],
            'lcd_display' => [
                'line1' => $lcdLine1,
                'line2' => $lcdLine2,
                'duration_seconds' => 3
            ]
        ], ResponseInterface::HTTP_OK);
    }

    /**
     * Verify RFID - Just check if RFID is registered
     * GET /api/rfid/verify/{rfid_id}
     * 
     * Works for both siswa and guru
     */
    public function verify($rfidId = null)
    {
        if (!$rfidId) {
            return $this->fail('RFID ID is required', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $rfidId = trim($rfidId);
        $student = $this->studentModel->where('rfid_id', $rfidId)->first();
        $teacher = null;

        if (!$student) {
            $teacher = $this->teacherModel->where('rfid_id', $rfidId)->first();
        }

        if (!$student && !$teacher) {
            return $this->respond([
                'valid' => false,
                'message' => 'RFID not registered',
                'lcd_display' => [
                    'line1' => 'RFID TIDAK',
                    'line2' => 'TERDAFTAR ✗',
                    'duration_seconds' => 2
                ]
            ], ResponseInterface::HTTP_OK);
        }

        if ($student) {
            $lcdLine1 = substr($student['full_name'], 0, 16);
            $lcdLine2 = 'NIS:' . substr($student['nis'], 0, 10);

            return $this->respond([
                'valid' => true,
                'message' => 'RFID registered',
                'user_type' => 'siswa',
                'user' => [
                    'id' => $student['id'],
                    'name' => $student['full_name'],
                    'nis' => $student['nis'],
                ],
                'lcd_display' => [
                    'line1' => $lcdLine1,
                    'line2' => $lcdLine2,
                    'duration_seconds' => 2
                ]
            ], ResponseInterface::HTTP_OK);
        } else {
            $lcdLine1 = substr($teacher['full_name'], 0, 16);
            $lcdLine2 = 'NIP:' . substr($teacher['nip'], 0, 10);

            return $this->respond([
                'valid' => true,
                'message' => 'RFID registered',
                'user_type' => 'guru',
                'user' => [
                    'id' => $teacher['id'],
                    'name' => $teacher['full_name'],
                    'nip' => $teacher['nip'],
                ],
                'lcd_display' => [
                    'line1' => $lcdLine1,
                    'line2' => $lcdLine2,
                    'duration_seconds' => 2
                ]
            ], ResponseInterface::HTTP_OK);
        }
    }
}
