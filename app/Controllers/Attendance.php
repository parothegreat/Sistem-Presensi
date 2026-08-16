<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AttendanceModel;
use App\Models\AttendanceEventModel;
use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\TelegramNotificationModel;

class Attendance extends Controller
{
    public function scan()
    {
        // Accept JSON or form-post payload
        $request = service('request');
        $data = [];
        try {
            $json = $request->getJSON(true);
            if ($json) $data = $json;
        } catch (\Exception $e) {
            // not JSON, fall through
        }
        if (empty($data)) {
            $data = $request->getPost();
        }

        // Required: user_id (or username) and optional device_id and timestamp
        $userId = $data['user_id'] ?? null;
        if (empty($userId) && ! empty($data['username'])) {
            $um = new UserModel();
            $u = $um->where('username', $data['username'])->first();
            $userId = $u['id'] ?? null;
        }

        if (empty($userId)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'error' => 'user_id or username required']);
        }

        $deviceId = $data['device_id'] ?? ($data['device'] ?? null);
        $timeRaw = $data['timestamp'] ?? $data['time'] ?? null;
        $when = $timeRaw ? date('Y-m-d H:i:s', strtotime($timeRaw)) : date('Y-m-d H:i:s');
        $date = date('Y-m-d', strtotime($when));

        $attModel = new AttendanceModel();
        $eventModel = new AttendanceEventModel();
        $studentModel = new StudentModel();
        $tnModel = new TelegramNotificationModel();

        // Insert event log
        $eventData = [
            'user_id' => $userId,
            'event_time' => $when,
            'event_type' => 'scan',
            'device_id' => $deviceId,
            'payload' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $eventModel->insert($eventData);

        // === ACTIVITY ATTENDANCE INTERCEPT ===
        // Check if this scan is for an activity
        $activityService = new \App\Services\ActivityAttendanceService();
        $activityResult = $activityService->processScan($userId, $when, 'qr_or_manual');

        if ($activityResult['processed']) {
            // Safety Logic: If logic says we should stop regular attendance (e.g. early scan), return now.
            if ($activityResult['should_stop_regular']) {
                return $this->response->setJSON([
                    'ok' => true,
                    'message' => 'Absensi Kegiatan Berhasil (' . $activityResult['message'] . ')',
                    'activity' => true,
                    'details' => $activityResult
                ]);
            }
            // Otherwise continues to Regular Attendance (Double Check-In case)
        }
        // =====================================

        // Find existing attendance for the date
        $attendance = $attModel->where('user_id', $userId)->where('date', $date)->first();

        // Basic shift: 07:00 start, 15:00 end, 15m grace
        $shiftStart = strtotime($date . ' 07:00:00');
        $shiftEnd = strtotime($date . ' 15:00:00');
        $grace = 15 * 60; // seconds

        if (! $attendance) {
            $attModel->insert([
                'user_id' => $userId,
                'date' => $date,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $attendance = $attModel->where('user_id', $userId)->where('date', $date)->first();
        }

        $update = [];
        // Decide masuk vs pulang: if no masuk_at -> masuk, else set pulang
        $currentStatus = $attendance['masuk_status'] ?? 'unknown';
        // If manually marked izin/sakit/alpha, do not accept scans for masuk unless override provided
        $override = ! empty($data['override']) && $data['override'] === '1';

        if (empty($attendance['masuk_at'])) {
            if (! in_array($currentStatus, ['izin', 'sakit', 'alpha']) || $override) {
                $update['masuk_at'] = $when;
                $update['device_id'] = $deviceId;
                $ts = strtotime($when);
                $update['masuk_status'] = ($ts <= ($shiftStart + $grace)) ? 'on_time' : 'late';
            } else {
                // Respect manual status: do not overwrite
            }
        } else {
            // treat as pulang (set pulang_at to this event) unless pulang_status is manual
            $pulangStatus = $attendance['pulang_status'] ?? 'unknown';
            if (! in_array($pulangStatus, ['izin', 'sakit', 'alpha']) || $override) {
                $update['pulang_at'] = $when;
                $ts = strtotime($when);
                $update['pulang_status'] = ($ts >= $shiftEnd) ? 'on_time' : 'early';
            }
        }

        if (! empty($update)) {
            $attModel->update($attendance['id'], $update);
        }

        $attendance = $attModel->find($attendance['id']);

        // Queue Telegram notification for parent (if chat_id available)
        try {
            $student = $studentModel->where('user_id', $userId)->first();
            if ($student && ! empty($student['telegram_chat_id'])) {
                $chat = $student['telegram_chat_id'];
                // Build message depending on event (masuk or pulang)
                $msg = '';
                if (! empty($update['masuk_at'])) {
                    $statusLabel = ($update['masuk_status'] === 'on_time') ? 'Tepat Waktu' : 'Terlambat';
                    $msg = sprintf("✓ %s masuk pada %s\nTanggal: %s\nStatus: %s", $student['full_name'] ?? 'Siswa', date('H:i', strtotime($update['masuk_at'])), $attendance['date'], $statusLabel);
                } elseif (! empty($update['pulang_at'])) {
                    $statusLabel = ($update['pulang_status'] === 'on_time') ? 'Pulang Tepat' : 'Pulang Lebih Awal';
                    $msg = sprintf("⟲ %s pulang pada %s\nTanggal: %s\nStatus: %s", $student['full_name'] ?? 'Siswa', date('H:i', strtotime($update['pulang_at'])), $attendance['date'], $statusLabel);
                }

                if (! empty($msg)) {
                    $tnModel->insert([
                        'student_id' => $student['id'],
                        'chat_id' => $chat,
                        'message' => $msg,
                        'payload' => json_encode(['attendance_id' => $attendance['id'], 'event' => 'scan', 'update' => $update]),
                        'status' => 'pending',
                        'attempts' => 0,
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // don't break the main flow on notification failures
        }

        return $this->response->setJSON(['ok' => true, 'attendance' => $attendance]);
    }

    // Optional manual mark endpoint
    public function markManual()
    {
        $request = service('request');
        $data = [];
        try {
            $json = $request->getJSON(true);
            if ($json) $data = $json;
        } catch (\Exception $e) {
            // not JSON, fall through
        }
        if (empty($data)) {
            $data = $request->getPost();
        }
        $userId = $data['user_id'] ?? null;
        $date = $data['date'] ?? date('Y-m-d');
        $field = $data['field'] ?? null; // masuk_status or pulang_status
        $value = $data['value'] ?? null; // izin/sakit/alpha

        if (empty($userId) || empty($field) || empty($value)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'error' => 'user_id, field and value required']);
        }

        $attModel = new AttendanceModel();
        $attendance = $attModel->where('user_id', $userId)->where('date', $date)->first();
        if (! $attendance) {
            $attModel->insert(['user_id' => $userId, 'date' => $date, 'created_at' => date('Y-m-d H:i:s')]);
            $attendance = $attModel->where('user_id', $userId)->where('date', $date)->first();
        }

        // record created_by if user is logged in
        $createdBy = session()->get('user_id') ?? null;
        $update = [$field => $value];
        if (! empty($data['note'])) $update['note'] = $data['note'];
        if ($createdBy) $update['created_by'] = $createdBy;

        $attModel->update($attendance['id'], $update);

        // log event
        $ev = new AttendanceEventModel();
        $ev->insert([
            'attendance_id' => $attendance['id'],
            'user_id' => $userId,
            'event_time' => date('Y-m-d H:i:s'),
            'event_type' => 'manual_mark',
            'device_id' => null,
            'payload' => json_encode(['field' => $field, 'value' => $value, 'note' => $data['note'] ?? null, 'by' => $createdBy]),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Queue Telegram notification for parent if applicable
        try {
            $studentModel = new StudentModel();
            $tnModel = new TelegramNotificationModel();
            $student = $studentModel->where('user_id', $userId)->first();
            if ($student && ! empty($student['telegram_chat_id'])) {
                $chat = $student['telegram_chat_id'];
                $label = $value === 'izin' ? 'Izin' : ($value === 'sakit' ? 'Sakit' : ($value === 'alpha' ? 'Alpha' : ucfirst($value)));
                $msg = sprintf("⚑ %s: %s pada %s\nKeterangan: %s", $student['full_name'] ?? 'Siswa', $label, $date, $data['note'] ?? '-');
                $tnModel->insert([
                    'student_id' => $student['id'],
                    'chat_id' => $chat,
                    'message' => $msg,
                    'payload' => json_encode(['attendance_id' => $attendance['id'], 'event' => 'manual_mark', 'field' => $field, 'value' => $value]),
                    'status' => 'pending',
                    'attempts' => 0,
                    'scheduled_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Exception $e) {
            // ignore
        }

        return $this->response->setJSON(['ok' => true, 'attendance' => $attModel->find($attendance['id'])]);
    }

    // Get today's attendance for display (both students and teachers)
    public function today()
    {
        $attModel = new AttendanceModel();
        $studentModel = new StudentModel();
        $teacherModel = new \App\Models\TeacherModel();

        $today = date('Y-m-d');
        $mode = service('request')->getGet('mode') ?? 'masuk'; // masuk or pulang

        $results = [];

        if ($mode === 'masuk') {
            // Get masuk attendance
            $attendanceRecords = $attModel->where('date', $today)
                ->where('masuk_at IS NOT NULL', null, false)
                ->orderBy('masuk_at', 'DESC')
                ->limit(20)
                ->findAll();

            foreach ($attendanceRecords as $record) {
                // Try student first
                $student = $studentModel->where('user_id', $record['user_id'])->first();
                if ($student) {
                    $results[] = [
                        'name' => $student['full_name'],
                        'nis' => $student['nis'],
                        'status' => $record['masuk_status'],
                        'time' => date('H:i', strtotime($record['masuk_at']))
                    ];
                    continue;
                }

                // Try teacher
                $teacher = $teacherModel->where('user_id', $record['user_id'])->first();
                if ($teacher) {
                    $results[] = [
                        'name' => $teacher['full_name'],
                        'nip' => $teacher['nip'],
                        'status' => $record['masuk_status'],
                        'time' => date('H:i', strtotime($record['masuk_at']))
                    ];
                }
            }
        } else {
            // Get pulang attendance
            $attendanceRecords = $attModel->where('date', $today)
                ->where('pulang_at IS NOT NULL', null, false)
                ->orderBy('pulang_at', 'DESC')
                ->limit(20)
                ->findAll();

            foreach ($attendanceRecords as $record) {
                // Try student first
                $student = $studentModel->where('user_id', $record['user_id'])->first();
                if ($student) {
                    $results[] = [
                        'name' => $student['full_name'],
                        'nis' => $student['nis'],
                        'status' => $record['pulang_status'],
                        'time' => date('H:i', strtotime($record['pulang_at']))
                    ];
                    continue;
                }

                // Try teacher
                $teacher = $teacherModel->where('user_id', $record['user_id'])->first();
                if ($teacher) {
                    $results[] = [
                        'name' => $teacher['full_name'],
                        'nip' => $teacher['nip'],
                        'status' => $record['pulang_status'],
                        'time' => date('H:i', strtotime($record['pulang_at']))
                    ];
                }
            }
        }

        return $this->response->setJSON($results);
    }
}
