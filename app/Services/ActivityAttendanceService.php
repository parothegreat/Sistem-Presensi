<?php

namespace App\Services;

use App\Models\ActivityModel;
use App\Models\ActivityParticipantModel;
use App\Models\ActivityAttendanceModel;
use App\Models\StudentModel;

class ActivityAttendanceService
{
    protected $activityModel;
    protected $participantModel;
    protected $attendanceModel;
    protected $studentModel;

    public function __construct()
    {
        $this->activityModel = new ActivityModel();
        $this->participantModel = new ActivityParticipantModel();
        $this->attendanceModel = new ActivityAttendanceModel();
        $this->studentModel = new StudentModel();
    }

    /**
     * Process potential activity attendance scan.
     * 
     * @param int $userId User ID (from users table)
     * @param string $timestamp Timestamp of scan (Y-m-d H:i:s)
     * @param string $method Method of scan (rfid, qrcode, fingerprint)
     * 
     * @return array Result ['processed' => bool, 'should_stop_regular' => bool, 'message' => string]
     */
    public function processScan($userId, $timestamp, $method)
    {
        // 1. Resolve Student from User ID
        $student = $this->studentModel->where('user_id', $userId)->first();
        if (!$student) {
            return ['processed' => false, 'should_stop_regular' => false, 'message' => 'Not a student'];
        }

        // 2. Find Active Activities for this Student
        // Activity is active if:
        // - Student is a participant
        // - Current time is within (Start Time - 30 mins) AND (End Time + 60 mins) to allow flexible checkin/out
        // - OR Status is 'ongoing'

        $activeActivities = $this->findActiveActivitiesForStudent($student['id'], $timestamp);

        if (empty($activeActivities)) {
            return ['processed' => false, 'should_stop_regular' => false, 'message' => 'No active activity found'];
        }

        $processedCount = 0;
        $shouldStopRegular = false;
        $messages = [];

        foreach ($activeActivities as $activity) {
            // Process Attendance for this activity
            $result = $this->logActivityAttendance($activity, $student['id'], $timestamp, $method);
            if ($result['success']) {
                $processedCount++;
                $messages[] = $result['message'];
            }

            // SAFETY LOGIC INTERCEPT
            // Check if this scan should prevent regular school check-out
            // Rule: If scan is "Too Early" for school end (e.g. < 12:00) AND we hit an activity
            // Then it is clearly an activity scan, NOT a school returning home scan.

            $scanTime = date('H:i:s', strtotime($timestamp));
            // Threshold for "Early School Check-out". Usually school ends at 15:00. 
            // If scanning before 12:00, it's definitely not "Pulang Sekolah".
            $earlyCheckoutThreshold = '12:00:00';

            if ($scanTime < $earlyCheckoutThreshold) {
                // Determine if school attendance IS ALREADY checked in.
                $today = date('Y-m-d', strtotime($timestamp));
                $regularAttendanceModel = new \App\Models\AttendanceModel();
                $existingRegular = $regularAttendanceModel
                    ->where('user_id', $userId)
                    ->where('date', $today)
                    ->first();

                // If NOT checked in to school (or no record), allow regular process to continue so they get checked in.
                // If checked in (masuk_at is present), preventing check-out is the goal.
                if ($existingRegular && !empty($existingRegular['masuk_at'])) {
                    $shouldStopRegular = true;
                } else {
                    $shouldStopRegular = false;
                }
            }
        }

        return [
            'processed' => $processedCount > 0,
            'should_stop_regular' => $shouldStopRegular,
            'message' => implode('; ', $messages),
            'activities' => $activeActivities
        ];
    }

    private function findActiveActivitiesForStudent($studentId, $timestamp)
    {
        $activities = $this->activityModel->asArray()->findAll();
        $studentActivities = [];

        foreach ($activities as $activity) {
            // Time Window Check
            $startTime = strtotime($activity['start_time']);
            $endTime = strtotime($activity['end_time']);
            $scanTime = strtotime($timestamp);

            // Buffer: Allow scan 30 mins before start and 60 mins after end
            $bufferStart = $startTime - (30 * 60);
            $bufferEnd = $endTime + (60 * 60);

            if ($scanTime >= $bufferStart && $scanTime <= $bufferEnd) {
                // Valid Time Window. Now check participation.
                $isParticipant = $this->participantModel
                    ->where('activity_id', $activity['id'])
                    ->where('student_id', $studentId)
                    ->countAllResults() > 0;

                if ($isParticipant) {
                    $studentActivities[] = $activity;
                }
            }
        }

        return $studentActivities;
    }

    private function logActivityAttendance($activity, $studentId, $timestamp, $method)
    {
        $existing = $this->attendanceModel
            ->where('activity_id', $activity['id'])
            ->where('student_id', $studentId)
            ->first();

        // Status Logic
        // Late if check_in > start_time + 15 mins
        $startTime = strtotime($activity['start_time']);
        $scanTime = strtotime($timestamp);
        $lateThreshold = $startTime + (15 * 60);

        $status = ($scanTime <= $lateThreshold) ? 'present' : 'late';

        if (!$existing) {
            // New Check In
            $this->attendanceModel->insert([
                'activity_id' => $activity['id'],
                'student_id' => $studentId,
                'check_in_time' => $timestamp,
                'status' => $status,
                'method' => $method
            ]);
            return ['success' => true, 'message' => "Activity '{$activity['name']}' Check-In"];
        } else {
            // Already checked in, is this a check-out?
            // Debounce: must be at least 5 mins after check-in
            $checkInTime = strtotime($existing['check_in_time']);
            if (($scanTime - $checkInTime) > (5 * 60)) {
                if (empty($existing['check_out_time'])) {
                    $this->attendanceModel->update($existing['id'], [
                        'check_out_time' => $timestamp,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    return ['success' => true, 'message' => "Activity '{$activity['name']}' Check-Out"];
                } else {
                    return ['success' => true, 'message' => "Activity '{$activity['name']}' Already Completed"]; // already checked out
                }
            } else {
                return ['success' => false, 'message' => "Activity Scan too fast (debounce)"];
            }
        }
    }
}
