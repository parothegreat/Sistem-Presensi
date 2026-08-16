<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use App\Models\StudentModel;
use App\Models\AttendanceModel;

class Student extends Controller
{
    use ResponseTrait;

    /**
     * Get student data by NIS
     * GET /api/students/{nis}
     * 
     * Response:
     * {
     *   "ok": true,
     *   "student": {
     *     "id": 1,
     *     "nis": "001",
     *     "full_name": "Ahmad Rifqi",
     *     "class": "10-A",
     *     "user_id": 5,
     *     "shift_id": 1,
     *     "telegram_chat_id": "123456789",
     *     "created_at": "2025-01-01 10:00:00"
     *   }
     * }
     */
    public function getByNis($nis = null)
    {
        // Hanya terima GET request
        if ($this->request->getMethod() !== 'GET') {
            return $this->fail('Method not allowed', 405);
        }

        if (!$nis) {
            return $this->fail('NIS required', 400);
        }

        try {
            $studentModel = new StudentModel();
            $student = $studentModel->where('nis', $nis)->first();

            if (!$student) {
                log_message('warning', "Student API: Student NIS {$nis} not found");
                return $this->fail('Student not found', 404);
            }

            // Return student data
            $response = [
                'ok' => true,
                'student' => [
                    'id' => $student['id'],
                    'nis' => $student['nis'],
                    'full_name' => $student['full_name'],
                    'class' => $student['class'],
                    'user_id' => $student['user_id'],
                    'shift_id' => $student['shift_id'],
                    'telegram_chat_id' => $student['telegram_chat_id'],
                    'wali_kelas_id' => $student['wali_kelas_id'],
                    'created_at' => $student['created_at']
                ]
            ];

            log_message('info', "Student API: Retrieved student data for NIS {$nis}");
            return $this->response->setStatusCode(200)->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', "Student API: Error - " . $e->getMessage());
            return $this->fail('Server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get attendance history for a student by NIS (per month)
     * GET /api/students/{nis}/attendance-history?month=11&year=2025&limit=20&page=1
     * 
     * Query Parameters:
     * - month: Month number 1-12 (default: current month)
     * - year: Year (default: current year)
     * - limit: Number of records per page (default: 20, max: 100)
     * - page: Page number (default: 1)
     * - sort: Order by date (default: desc, options: asc, desc)
     * 
     * Response:
     * {
     *   "ok": true,
     *   "student": {
     *     "nis": "001",
     *     "full_name": "Ahmad Rifqi",
     *     "class": "12 A"
     *   },
     *   "period": {
     *     "month": 11,
     *     "year": 2025,
     *     "month_name": "November",
     *     "start_date": "2025-11-01",
     *     "end_date": "2025-11-30"
     *   },
     *   "pagination": {
     *     "page": 1,
     *     "limit": 20,
     *     "total": 22,
     *     "pages": 2,
     *     "has_next": true,
     *     "has_prev": false
     *   },
     *   "attendance": [
     *     {
     *       "id": 1,
     *       "date": "2025-11-17",
     *       "masuk_at": "2025-11-17 08:45:30",
     *       "masuk_status": "on_time",
     *       "pulang_at": "2025-11-17 15:30:00",
     *       "pulang_status": "on_time",
     *       "keterangan": null
     *     }
     *   ]
     * }
     */
    public function getAttendanceHistory($nis = null)
    {
        // Hanya terima GET request
        if ($this->request->getMethod() !== 'GET') {
            return $this->fail('Method not allowed', 405);
        }

        if (!$nis) {
            return $this->fail('NIS required', 400);
        }

        try {
            // Get query parameters
            $month = (int)($this->request->getGet('month') ?? date('m'));
            $year = (int)($this->request->getGet('year') ?? date('Y'));
            $limit = (int)($this->request->getGet('limit') ?? 20);
            $page = (int)($this->request->getGet('page') ?? 1);
            $sort = $this->request->getGet('sort') ?? 'desc';

            // Validate month
            if ($month < 1 || $month > 12) {
                $month = date('m');
            }

            // Validate year
            if ($year < 2020 || $year > date('Y') + 1) {
                $year = date('Y');
            }

            // Validate limit
            if ($limit < 1 || $limit > 100) {
                $limit = 20;
            }

            // Validate page
            if ($page < 1) {
                $page = 1;
            }

            // Validate sort
            if (!in_array($sort, ['asc', 'desc'])) {
                $sort = 'desc';
            }

            $studentModel = new StudentModel();
            $student = $studentModel->where('nis', $nis)->first();

            if (!$student) {
                log_message('warning', "Student API: Student NIS {$nis} not found");
                return $this->fail('Student not found', 404);
            }

            // Get attendance data for specific month
            $attendanceModel = new AttendanceModel();

            // Create date range for the month
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-d', strtotime('last day of ' . $startDate));

            // Count total records for this month
            $total = $attendanceModel
                ->where('user_id', $student['user_id'])
                ->where('date >=', $startDate)
                ->where('date <=', $endDate)
                ->countAllResults();

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Get paginated attendance records
            $attendance = $attendanceModel
                ->where('user_id', $student['user_id'])
                ->where('date >=', $startDate)
                ->where('date <=', $endDate)
                ->orderBy('date', strtoupper($sort))
                ->limit($limit)
                ->offset($offset)
                ->findAll();

            // Format attendance data
            $formattedAttendance = [];
            foreach ($attendance as $record) {
                $formattedAttendance[] = [
                    'id' => $record['id'],
                    'date' => $record['date'],
                    'masuk_at' => $record['masuk_at'],
                    'masuk_status' => $record['masuk_status'],
                    'pulang_at' => $record['pulang_at'],
                    'pulang_status' => $record['pulang_status'],
                    'keterangan' => $record['keterangan'] ?? null
                ];
            }

            // Calculate total pages
            $totalPages = ceil($total / $limit);

            // Month name
            $monthNames = [
                1 => 'January',
                2 => 'February',
                3 => 'March',
                4 => 'April',
                5 => 'May',
                6 => 'June',
                7 => 'July',
                8 => 'August',
                9 => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December'
            ];

            $response = [
                'ok' => true,
                'student' => [
                    'nis' => $student['nis'],
                    'full_name' => $student['full_name'],
                    'class' => $student['class']
                ],
                'period' => [
                    'month' => $month,
                    'year' => $year,
                    'month_name' => $monthNames[$month],
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ],
                'attendance' => $formattedAttendance
            ];

            log_message('info', "Student API: Retrieved attendance history for NIS {$nis} ({$year}-{$month})");
            return $this->response->setStatusCode(200)->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', "Student API: Error - " . $e->getMessage());
            return $this->fail('Server error: ' . $e->getMessage(), 500);
        }
    }
}
