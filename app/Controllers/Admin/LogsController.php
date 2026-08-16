<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BiometricLogModel;
use App\Models\AndroidNotificationModel;
use App\Models\TelegramNotificationModel;
use App\Models\WhatsAppNotificationModel;

class LogsController extends BaseController
{
    protected $biometricLogModel;
    protected $androidNotifModel;
    protected $telegramNotifModel;
    protected $whatsappNotifModel;

    public function __construct()
    {
        $this->biometricLogModel = new BiometricLogModel();
        $this->androidNotifModel = new AndroidNotificationModel();
        $this->telegramNotifModel = new TelegramNotificationModel();
        $this->whatsappNotifModel = new WhatsAppNotificationModel();
    }

    /**
     * Dashboard logs utama
     */
    public function index()
    {
        // Get statistics
        $biometricStats = $this->getBiometricStats();
        $notificationStats = $this->getNotificationStats();

        // Get recent activities
        // Get recent activities
        // Get recent activities
        $recentBiometricLogs = $this->biometricLogModel
            ->select('biometric_logs.*, 
                      COALESCE(students.full_name, teachers.full_name) as user_name,
                      CASE 
                        WHEN students.id IS NOT NULL THEN "Student" 
                        WHEN teachers.id IS NOT NULL THEN "Teacher" 
                        ELSE "Unknown" 
                      END as user_role', false)
            ->join('students', 'students.nis = biometric_logs.user_id', 'left')
            ->join('teachers', 'teachers.nip = biometric_logs.user_id', 'left')
            ->orderBy('biometric_logs.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $recentAndroidNotifs = $this->androidNotifModel->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $recentTelegramNotifs = $this->telegramNotifModel->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $recentWhatsappNotifs = $this->whatsappNotifModel->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $data = [
            'title' => 'Dashboard Logs',
            'biometricStats' => $biometricStats,
            'notificationStats' => $notificationStats,
            'recentBiometricLogs' => $recentBiometricLogs,
            'recentAndroidNotifs' => $recentAndroidNotifs,
            'recentTelegramNotifs' => $recentTelegramNotifs,
            'recentWhatsappNotifs' => $recentWhatsappNotifs,
        ];

        return view('admin/logs/dashboard', $data);
    }

    /**
     * Halaman detail biometric logs
     */
    public function biometricLogs()
    {


        // Get filters
        $filters = [
            'date_from' => $this->request->getVar('date_from'),
            'date_to' => $this->request->getVar('date_to'),
            'status' => $this->request->getVar('status'),
            'search' => $this->request->getVar('search'),
        ];

        // Build query
        // Fix: Join with students and teachers explicitly based on user_id (NIS/NIP)
        // instead of users table which uses integer ID.
        $query = $this->biometricLogModel
            ->select('biometric_logs.*, 
                      COALESCE(students.full_name, teachers.full_name) as user_name,
                      COALESCE(students.class, teachers.subject) as user_role_detail,
                      CASE 
                        WHEN students.id IS NOT NULL THEN "Student" 
                        WHEN teachers.id IS NOT NULL THEN "Teacher" 
                        ELSE "Unknown" 
                      END as user_role', false)
            ->join('students', 'students.nis = biometric_logs.user_id', 'left')
            ->join('teachers', 'teachers.nip = biometric_logs.user_id', 'left');

        if (!empty($filters['date_from'])) {
            $query = $query->where('DATE(biometric_logs.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query = $query->where('DATE(biometric_logs.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query = $query->where('biometric_logs.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $query = $query->groupStart()
                ->like('biometric_logs.user_id', $filters['search'])
                ->orLike('biometric_logs.device_id', $filters['search'])
                ->orLike('students.full_name', $filters['search'])
                ->orLike('teachers.full_name', $filters['search'])
                ->groupEnd();
        }

        $logs = $query->orderBy('biometric_logs.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Biometric Logs',
            'logs' => $logs,
            'filters' => $filters,
        ];

        return view('admin/logs/biometric', $data);
    }

    /**
     * Halaman detail android notifications
     */
    public function androidNotifications()
    {


        // Get filters
        $filters = [
            'date_from' => $this->request->getVar('date_from'),
            'date_to' => $this->request->getVar('date_to'),
            'status' => $this->request->getVar('notification_status'),
        ];

        // Build query
        $query = $this->androidNotifModel;

        if (!empty($filters['date_from'])) {
            $query = $query->where('DATE(created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query = $query->where('DATE(created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query = $query->where('notification_status', $filters['status']);
        }

        $logs = $query->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Android Notifications Log',
            'logs' => $logs,

            'filters' => $filters,
        ];

        return view('admin/logs/android_notifications', $data);
    }

    /**
     * Halaman detail telegram notifications
     */
    public function telegramNotifications()
    {


        // Get filters
        $filters = [
            'date_from' => $this->request->getVar('date_from'),
            'date_to' => $this->request->getVar('date_to'),
            'status' => $this->request->getVar('status'),
        ];

        // Build query
        $query = $this->telegramNotifModel;

        if (!empty($filters['date_from'])) {
            $query = $query->where('DATE(created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query = $query->where('DATE(created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query = $query->where('status', $filters['status']);
        }

        $logs = $query->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Telegram Notifications Log',
            'logs' => $logs,

            'filters' => $filters,
        ];

        return view('admin/logs/telegram_notifications', $data);
    }

    /**
     * Halaman detail whatsapp notifications
     */
    public function whatsappNotifications()
    {


        // Get filters
        $filters = [
            'date_from' => $this->request->getVar('date_from'),
            'date_to' => $this->request->getVar('date_to'),
            'status' => $this->request->getVar('status'),
        ];

        // Build query
        $query = $this->whatsappNotifModel;

        if (!empty($filters['date_from'])) {
            $query = $query->where('DATE(created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query = $query->where('DATE(created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query = $query->where('status', $filters['status']);
        }

        $logs = $query->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'WhatsApp Notifications Log',
            'logs' => $logs,

            'filters' => $filters,
        ];

        return view('admin/logs/whatsapp_notifications', $data);
    }

    /**
     * Get biometric statistics
     */
    private function getBiometricStats()
    {
        $today = date('Y-m-d');

        return [
            'today' => $this->biometricLogModel->where('DATE(created_at)', $today)->countAllResults(),
            'total_push' => $this->biometricLogModel->countAllResults(),
            'success' => $this->biometricLogModel->where('status', 'success')->countAllResults(),
            'failed' => $this->biometricLogModel->where('status', 'failed')->countAllResults(),
            'pending' => $this->biometricLogModel->where('status', 'pending')->countAllResults(),
        ];
    }

    /**
     * Get notification statistics
     */
    private function getNotificationStats()
    {
        return [
            'android' => [
                'total' => $this->androidNotifModel->countAllResults(),
                'sent' => $this->androidNotifModel->where('notification_status', 'sent')->countAllResults(),
                'failed' => $this->androidNotifModel->where('notification_status', 'failed')->countAllResults(),
                'pending' => $this->androidNotifModel->where('notification_status', 'pending')->countAllResults(),
            ],
            'telegram' => [
                'total' => $this->telegramNotifModel->countAllResults(),
                'sent' => $this->telegramNotifModel->where('status', 'sent')->countAllResults(),
                'failed' => $this->telegramNotifModel->where('status', 'failed')->countAllResults(),
                'pending' => $this->telegramNotifModel->where('status', 'pending')->countAllResults(),
            ],
            'whatsapp' => [
                'total' => $this->whatsappNotifModel->countAllResults(),
                'sent' => $this->whatsappNotifModel->where('status', 'sent')->countAllResults(),
                'failed' => $this->whatsappNotifModel->where('status', 'failed')->countAllResults(),
                'pending' => $this->whatsappNotifModel->where('status', 'pending')->countAllResults(),
            ],
        ];
    }
    /**
     * Biometric Logs API for DataTables Server-side
     */
    public function biometricLogsJson()
    {
        try {
            $request = $this->request;

            $start = (int)($request->getVar('start') ?? 0);
            $length = (int)($request->getVar('length') ?? 10);

            $search = $request->getVar('search');
            $searchValue = $search['value'] ?? null;

            $order = $request->getVar('order');
            $orderColumnIndex = $order[0]['column'] ?? 0;
            $orderDir = $order[0]['dir'] ?? 'desc';

            // Custom filters
            $dateFrom = $request->getVar('date_from');
            $dateTo = $request->getVar('date_to');
            $status = $request->getVar('status');
            $customSearch = $request->getVar('custom_search');

            // Columns mapping
            $columns = [
                0 => 'biometric_logs.created_at',
                1 => 'biometric_logs.device_id',
                2 => 'user_name',
                3 => 'biometric_logs.biometric_type',
                4 => 'biometric_logs.status',
                5 => 'biometric_logs.processed',
                6 => 'biometric_logs.process_error'
            ];

            // Start Builder with Clean Instance
            $builder = $this->biometricLogModel->builder();

            // Base Select & Join
            $builder->select('biometric_logs.*, 
                      COALESCE(students.full_name, teachers.full_name) as user_name,
                      students.id as student_id, teachers.id as teacher_id,
                      COALESCE(students.class, teachers.subject) as user_role_detail,
                      CASE 
                        WHEN students.id IS NOT NULL THEN "Student" 
                        WHEN teachers.id IS NOT NULL THEN "Teacher" 
                        ELSE "Unknown" 
                      END as user_role', false)
                ->join('students', 'students.nis = biometric_logs.user_id', 'left')
                ->join('teachers', 'teachers.nip = biometric_logs.user_id', 'left');

            // Apply Custom Filters
            if ($dateFrom) {
                $builder->where('DATE(biometric_logs.created_at) >=', $dateFrom);
            }
            if ($dateTo) {
                $builder->where('DATE(biometric_logs.created_at) <=', $dateTo);
            }
            if ($status) {
                $builder->where('biometric_logs.status', $status);
            }
            if ($customSearch) {
                $builder->groupStart()
                    ->like('biometric_logs.user_id', $customSearch)
                    ->orLike('biometric_logs.device_id', $customSearch)
                    ->orLike('students.full_name', $customSearch)
                    ->orLike('teachers.full_name', $customSearch)
                    ->groupEnd();
            }

            // Apply Global Search
            if ($searchValue) {
                $builder->groupStart()
                    ->like('biometric_logs.user_id', $searchValue)
                    ->orLike('students.full_name', $searchValue)
                    ->orLike('teachers.full_name', $searchValue)
                    ->orLike('biometric_logs.device_id', $searchValue)
                    ->groupEnd();
            }

            // Count Filtered
            $countBuilder = clone $builder;
            $totalRecordsFiltered = $countBuilder->countAllResults();

            // Sorting
            $orderColumn = $columns[$orderColumnIndex] ?? 'biometric_logs.created_at';
            $builder->orderBy($orderColumn, $orderDir);

            // Pagination
            if ($length != -1) {
                $builder->limit($length, $start);
            }

            // Get Data (getResultArray to ensure array output)
            $data = $builder->get()->getResultArray();

            // Total Records (unfiltered)
            $totalRecords = $this->biometricLogModel->countAll();

            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecordsFiltered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Android Logs API for DataTables Server-side
     */
    public function androidLogsJson()
    {
        try {
            $request = $this->request;

            $start = (int)($request->getVar('start') ?? 0);
            $length = (int)($request->getVar('length') ?? 10);
            $search = $request->getVar('search');
            $searchValue = $search['value'] ?? null;
            $order = $request->getVar('order');
            $orderColumnIndex = $order[0]['column'] ?? 0;
            $orderDir = $order[0]['dir'] ?? 'desc';

            // Custom filters
            $dateFrom = $request->getVar('date_from');
            $dateTo = $request->getVar('date_to');
            $status = $request->getVar('status');

            $columns = [
                0 => 'created_at',
                1 => 'title',
                2 => 'message',
                3 => 'attempts',
                4 => 'notification_status',
                5 => 'sent_at',
                6 => 'updated_at'
            ];

            $builder = $this->androidNotifModel->builder();

            if ($dateFrom) $builder->where('DATE(created_at) >=', $dateFrom);
            if ($dateTo) $builder->where('DATE(created_at) <=', $dateTo);
            if ($status) $builder->where('notification_status', $status);

            if ($searchValue) {
                $builder->groupStart()
                    ->like('title', $searchValue)
                    ->orLike('message', $searchValue)
                    ->groupEnd();
            }

            $countBuilder = clone $builder;
            $totalRecordsFiltered = $countBuilder->countAllResults();

            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $builder->orderBy($orderColumn, $orderDir);

            if ($length != -1) $builder->limit($length, $start);

            $data = $builder->get()->getResultArray();
            $totalRecords = $this->androidNotifModel->countAll();

            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecordsFiltered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Telegram Logs API for DataTables Server-side
     */
    public function telegramLogsJson()
    {
        try {
            $request = $this->request;

            $start = (int)($request->getVar('start') ?? 0);
            $length = (int)($request->getVar('length') ?? 10);
            $search = $request->getVar('search');
            $searchValue = $search['value'] ?? null;
            $order = $request->getVar('order');
            $orderColumnIndex = $order[0]['column'] ?? 0;
            $orderDir = $order[0]['dir'] ?? 'desc';

            $dateFrom = $request->getVar('date_from');
            $dateTo = $request->getVar('date_to');
            $status = $request->getVar('status');

            $columns = [
                0 => 'created_at',
                1 => 'chat_id',
                2 => 'message',
                3 => 'attempts',
                4 => 'status',
                5 => 'scheduled_at',
                6 => 'updated_at'
            ];

            $builder = $this->telegramNotifModel->builder();

            if ($dateFrom) $builder->where('DATE(created_at) >=', $dateFrom);
            if ($dateTo) $builder->where('DATE(created_at) <=', $dateTo);
            if ($status) $builder->where('status', $status);

            if ($searchValue) {
                $builder->groupStart()
                    ->like('chat_id', $searchValue)
                    ->orLike('message', $searchValue)
                    ->groupEnd();
            }

            $countBuilder = clone $builder;
            $totalRecordsFiltered = $countBuilder->countAllResults();

            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $builder->orderBy($orderColumn, $orderDir);

            if ($length != -1) $builder->limit($length, $start);

            $data = $builder->get()->getResultArray();
            $totalRecords = $this->telegramNotifModel->countAll();

            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecordsFiltered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * WhatsApp Logs API for DataTables Server-side
     */
    public function whatsappLogsJson()
    {
        try {
            $request = $this->request;

            $start = (int)($request->getVar('start') ?? 0);
            $length = (int)($request->getVar('length') ?? 10);
            $search = $request->getVar('search');
            $searchValue = $search['value'] ?? null;
            $order = $request->getVar('order');
            $orderColumnIndex = $order[0]['column'] ?? 0;
            $orderDir = $order[0]['dir'] ?? 'desc';

            $dateFrom = $request->getVar('date_from');
            $dateTo = $request->getVar('date_to');
            $status = $request->getVar('status');

            $columns = [
                0 => 'created_at',
                1 => 'phone_number',
                2 => 'message',
                3 => 'attempts',
                4 => 'status',
                5 => 'sent_at',
                6 => 'updated_at'
            ];

            $builder = $this->whatsappNotifModel->builder();

            if ($dateFrom) $builder->where('DATE(created_at) >=', $dateFrom);
            if ($dateTo) $builder->where('DATE(created_at) <=', $dateTo);
            if ($status) $builder->where('status', $status);

            if ($searchValue) {
                $builder->groupStart()
                    ->like('phone_number', $searchValue)
                    ->orLike('message', $searchValue)
                    ->groupEnd();
            }

            $countBuilder = clone $builder;
            $totalRecordsFiltered = $countBuilder->countAllResults();

            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $builder->orderBy($orderColumn, $orderDir);

            if ($length != -1) $builder->limit($length, $start);

            $data = $builder->get()->getResultArray();
            $totalRecords = $this->whatsappNotifModel->countAll();

            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecordsFiltered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }
}
