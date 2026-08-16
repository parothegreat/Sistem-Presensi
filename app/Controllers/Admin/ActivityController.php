<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityModel;
use App\Models\ActivityParticipantModel;
use App\Models\ActivityAttendanceModel;
use App\Models\StudentModel;

class ActivityController extends BaseController
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

    public function index()
    {
        $data = [
            'title' => 'Daftar Kegiatan',
            'activities' => $this->activityModel->orderBy('start_time', 'DESC')->findAll(),
        ];
        return view('admin/activities/index', $data);
    }

    public function create()
    {
        // Get classes for filter
        $classes = $this->studentModel->select('class')->distinct()->orderBy('class', 'ASC')->findAll();
        // Get religions for filter (manual list or from DB)
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];

        $data = [
            'title' => 'Tambah Kegiatan Baru',
            'classes' => array_column($classes, 'class'),
            'religions' => $religions,
            'students' => $this->studentModel->orderBy('class', 'ASC')->orderBy('full_name', 'ASC')->findAll(),
        ];
        return view('admin/activities/create', $data);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'start_time' => 'required|valid_date',
            'end_time' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Save Activity
        $activityData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'status' => 'scheduled',
        ];

        $this->activityModel->save($activityData);
        $activityId = $this->activityModel->getInsertID();

        // Process Participants
        $participants = [];

        // 1. By Filter (Religion & Class)
        $religionFilter = $this->request->getPost('filter_religion');
        $classFilter = $this->request->getPost('filter_class');

        // Save Target Audience Metadata
        $targetAudience = [
            'religion' => $religionFilter,
            'class' => $classFilter
        ];
        $this->activityModel->update($activityId, ['target_audience' => json_encode($targetAudience)]);

        if (!empty($religionFilter) && !empty($classFilter)) {
            // Both filters
            $students = $this->studentModel
                ->whereIn('religion', $religionFilter)
                ->whereIn('class', $classFilter)
                ->findAll();
            foreach ($students as $s) $participants[] = $s['id'];
        } elseif (!empty($religionFilter)) {
            // Religion only
            $students = $this->studentModel->whereIn('religion', $religionFilter)->findAll();
            foreach ($students as $s) $participants[] = $s['id'];
        } elseif (!empty($classFilter)) {
            // Class only
            $students = $this->studentModel->whereIn('class', $classFilter)->findAll();
            foreach ($students as $s) $participants[] = $s['id'];
        }

        // 2. Manual Checklist
        $manualStudents = $this->request->getPost('students'); // array of IDs
        if (!empty($manualStudents)) {
            $participants = array_merge($participants, $manualStudents);
        }

        // Unique IDs
        $participants = array_unique($participants);

        // Save Participants
        foreach ($participants as $studentId) {
            $this->participantModel->ignore(true)->insert([
                'activity_id' => $activityId,
                'student_id' => $studentId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->to('admin/activities')->with('success', 'Kegiatan berhasil dibuat dengan ' . count($participants) . ' peserta.');
    }

    public function edit($id)
    {
        $activity = $this->activityModel->find($id);
        if (!$activity) return redirect()->to('admin/activities')->with('error', 'Kegiatan tidak ditemukan');

        // Get classes for filter
        $classes = $this->studentModel->select('class')->distinct()->orderBy('class', 'ASC')->findAll();
        // Get religions for filter
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];

        // Get all students for manual checklist
        $allStudents = $this->studentModel->orderBy('class', 'ASC')->orderBy('full_name', 'ASC')->findAll();

        // Get currently assigned confirmed participants (IDs only)
        $currentParticipants = $this->participantModel->where('activity_id', $id)->findColumn('student_id') ?? [];

        $data = [
            'title' => 'Edit Kegiatan',
            'activity' => $activity,
            'classes' => array_column($classes, 'class'),
            'religions' => $religions,
            'students' => $allStudents,
            'currentParticipants' => $currentParticipants,
            // Pass target audience data if exists
            'targetAudience' => json_decode($activity['target_audience'] ?? '{}', true)
        ];
        return view('admin/activities/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'start_time' => 'required|valid_date',
            'end_time' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Update Basic Info
        $activityData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
        ];

        // Update Target Audience Metadata (Filters)
        $religionFilter = $this->request->getPost('filter_religion');
        $classFilter = $this->request->getPost('filter_class');
        $targetAudience = [
            'religion' => $religionFilter,
            'class' => $classFilter
        ];
        $activityData['target_audience'] = json_encode($targetAudience);

        $this->activityModel->update($id, $activityData);

        // --- SYNC PARTICIPANTS ---
        // 1. Calculate intended participants based on new Filters
        $participants = [];

        if (!empty($religionFilter) && !empty($classFilter)) {
            // Both filters
            $students = $this->studentModel
                ->whereIn('religion', $religionFilter)
                ->whereIn('class', $classFilter)
                ->findAll();
            foreach ($students as $s) $participants[] = $s['id'];
        } elseif (!empty($religionFilter)) {
            // Religion only
            $students = $this->studentModel->whereIn('religion', $religionFilter)->findAll();
            foreach ($students as $s) $participants[] = $s['id'];
        } elseif (!empty($classFilter)) {
            // Class only
            $students = $this->studentModel->whereIn('class', $classFilter)->findAll();
            foreach ($students as $s) $participants[] = $s['id'];
        }

        // 2. Merge with Manual Checklist
        $manualStudents = $this->request->getPost('students'); // array of IDs
        if (!empty($manualStudents)) {
            $participants = array_merge($participants, $manualStudents);
        }

        // Unique IDs
        $participants = array_unique($participants);

        // 3. Sync Logic: Delete all existing and re-insert.
        // NOTE: Deleting from activity_participants does NOT delete from activity_attendances, 
        // so history is safe. It just removes "enrollment".
        $this->participantModel->where('activity_id', $id)->delete();

        if (!empty($participants)) {
            foreach ($participants as $studentId) {
                $this->participantModel->ignore(true)->insert([
                    'activity_id' => $id,
                    'student_id' => $studentId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return redirect()->to('admin/activities')->with('success', 'Data kegiatan berhasil diperbarui. Peserta telah disinkronkan (' . count($participants) . ' siswa).');
    }

    public function show($id)
    {
        $activity = $this->activityModel->find($id);
        if (!$activity) return redirect()->to('admin/activities')->with('error', 'Kegiatan tidak ditemukan');

        // Fetch all participants first
        $allParticipants = $this->participantModel->getParticipants($id);

        // Extract unique classes for filter dropdown
        $classes = [];
        foreach ($allParticipants as $p) {
            if (!empty($p['class'])) {
                $classes[] = $p['class'];
            }
        }
        $classes = array_unique($classes);
        sort($classes);

        // Apply Filter if present
        $filterClass = $this->request->getGet('filter_class');
        $participants = $allParticipants;

        if (!empty($filterClass)) {
            $participants = array_filter($allParticipants, function ($p) use ($filterClass) {
                return $p['class'] === $filterClass;
            });
        }

        $data = [
            'title' => 'Detail Kegiatan: ' . $activity['name'],
            'activity' => $activity,
            'participants' => $participants,
            'attendance' => $this->attendanceModel->getAttendance($id),
            'classes' => $classes,
            'filterClass' => $filterClass
        ];

        return view('admin/activities/show', $data);
    }

    public function updateAttendanceStatus($activityId)
    {
        $studentId = $this->request->getPost('student_id');
        $status = $this->request->getPost('status');

        // Check if attendance record exists
        $existing = $this->attendanceModel
            ->where('activity_id', $activityId)
            ->where('student_id', $studentId)
            ->first();

        if ($existing) {
            $this->attendanceModel->update($existing['id'], ['status' => $status]);
        } else {
            // Create new record if manual override (e.g. marking absent as permission)
            $this->attendanceModel->insert([
                'activity_id' => $activityId,
                'student_id' => $studentId,
                'check_in_time' => null, // Manual status update doesn't imply check-in time unless present
                'status' => $status,
                'method' => 'manual_update',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->back()->with('success', 'Status kehadiran berhasil diperbarui.');
    }

    public function deleteAttendance($activityId)
    {
        $studentId = $this->request->getPost('student_id');

        $this->attendanceModel
            ->where('activity_id', $activityId)
            ->where('student_id', $studentId)
            ->delete();

        return redirect()->back()->with('success', 'Data kehadiran berhasil dihapus.');
    }

    public function deleteParticipant($activityId)
    {
        $studentId = $this->request->getPost('student_id');

        // Delete from participants
        $this->participantModel
            ->where('activity_id', $activityId)
            ->where('student_id', $studentId)
            ->delete();

        // Also delete from attendance if exists (clean up)
        $this->attendanceModel
            ->where('activity_id', $activityId)
            ->where('student_id', $studentId)
            ->delete();

        return redirect()->back()->with('success', 'Siswa berhasil dihapus dari kegiatan.');
    }

    public function print($id)
    {
        $activity = $this->activityModel->find($id);
        if (!$activity) return redirect()->to('admin/activities')->with('error', 'Kegiatan tidak ditemukan');

        // Reuse getting logic
        $allParticipants = $this->participantModel->getParticipants($id);
        $filterClass = $this->request->getGet('filter_class');

        $participants = $allParticipants;
        if (!empty($filterClass)) {
            $participants = array_filter($allParticipants, function ($p) use ($filterClass) {
                return $p['class'] === $filterClass;
            });
        }

        // Sort explicitly by class then name for printing
        usort($participants, function ($a, $b) {
            $c = strcmp($a['class'], $b['class']);
            if ($c !== 0) return $c;
            return strcmp($a['full_name'], $b['full_name']);
        });

        $data = [
            'title' => 'Cetak Absensi: ' . $activity['name'],
            'activity' => $activity,
            'participants' => $participants,
            'attendance' => $this->attendanceModel->getAttendance($id),
            'filterClass' => $filterClass
        ];

        return view('admin/activities/print', $data);
    }

    public function delete($id)
    {
        $this->activityModel->delete($id);
        return redirect()->to('admin/activities')->with('success', 'Kegiatan berhasil dihapus');
    }
}
