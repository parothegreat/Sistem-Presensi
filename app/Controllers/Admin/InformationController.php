<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InformationModel;
use App\Models\WalikelasModel;
use App\Models\StudentModel;
use App\Models\WhatsAppNotificationModel;
use App\Models\SettingsModel;

class InformationController extends BaseController
{
    protected $teacherModel;

    public function __construct()
    {
        $this->informationModel = new InformationModel();
        $this->walikelasModel = new WalikelasModel();
        $this->studentModel = new StudentModel();
        $this->waNotificationModel = new WhatsAppNotificationModel();
        $this->settingsModel = new SettingsModel();
        $this->teacherModel = new \App\Models\TeacherModel();
    }

    public function index($type = 'student')
    {
        $validTypes = ['student', 'teacher'];
        if (!in_array($type, $validTypes)) {
            return redirect()->to('/admin/dashboard')->with('error', 'Tipe informasi tidak valid');
        }

        $data = [
            'title' => 'Kelola Informasi ' . ($type == 'teacher' ? 'Guru' : 'Siswa'),
            'type' => $type,
            'informations' => $this->informationModel->where('type', $type)->orderBy('created_at', 'DESC')->findAll(),
        ];

        return view('admin/information/index', $data);
    }

    public function create($type = 'student')
    {
        $validTypes = ['student', 'teacher'];
        if (!in_array($type, $validTypes)) {
            return redirect()->to('/admin/dashboard')->with('error', 'Tipe informasi tidak valid');
        }

        $data = [
            'title' => 'Buat Informasi ' . ($type == 'teacher' ? 'Guru' : 'Siswa'),
            'type' => $type,
        ];

        if ($type == 'teacher') {
            $data['recipients'] = $this->teacherModel->orderBy('full_name', 'ASC')->findAll();
        } else {
            $data['recipients'] = $this->walikelasModel->getAllActive();
        }

        return view('admin/information/create', $data);
    }

    public function store($type = 'student')
    {
        $validTypes = ['student', 'teacher'];
        if (!in_array($type, $validTypes)) {
            return redirect()->to('/admin/dashboard')->with('error', 'Tipe informasi tidak valid');
        }

        $rules = [
            'title' => 'required',
            'content' => 'required',
            'recipients' => 'required', 
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $content = $this->request->getPost('content');
        $selectedRecipients = $this->request->getPost('recipients'); 
        $sendViaWa = $this->request->getPost('send_via_wa');

        if (!is_array($selectedRecipients)) {
            $selectedRecipients = [$selectedRecipients];
        }

        $data = [
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'target_classes' => json_encode($selectedRecipients), // Keeping column name 'target_classes' for now, acting as generic 'targets'
            'send_via_wa' => $sendViaWa ? 1 : 0,
            'created_by' => session()->get('user_id'),
        ];

        $infoId = $this->informationModel->insert($data);

        if ($infoId && $sendViaWa) {
            $this->queueWhatsAppNotifications($infoId, $title, $content, $selectedRecipients, $type);
        }

        return redirect()->to('/admin/information/' . $type)->with('success', 'Informasi berhasil dibuat ' . ($sendViaWa ? 'dan antrian WhatsApp dibuat.' : '.'));
    }

    private function queueWhatsAppNotifications($infoId, $title, $content, $recipientIds, $type)
    {
        if ($type == 'teacher') {
            // Teacher Logic: Send generic message to each teacher
            foreach ($recipientIds as $teacherId) {
                $teacher = $this->teacherModel->find($teacherId);
                if ($teacher && !empty($teacher['phone_number'])) {
                     $msg = "*INFO GURU: $title*\n\n" . $this->replacePlaceholders($content, $teacher['full_name'], 'Guru') . "\n\n_Informasi Sekolah_";
                     
                     $this->waNotificationModel->insert([
                        'phone_number' => $teacher['phone_number'],
                        'message' => $msg,
                        'status' => 'pending',
                        'scheduled_at' => date('Y-m-d H:i:s'),
                        'payload' => json_encode([
                            'recipient_type' => 'individual',
                            'information_id' => $infoId,
                            'teacher_id' => $teacher['id']
                        ])
                    ]);
                }
            }
        } else {
            // Student Logic (Existing)
            $targetSetting = $this->settingsModel->getSetting('wa_notification_target') ?? 'guardian';
            
            // 1. Send to Groups if applicable
            if ($targetSetting === 'group' || $targetSetting === 'both') {
                foreach ($recipientIds as $classId) {
                    $walikelas = $this->walikelasModel->find($classId);
                    if ($walikelas && !empty($walikelas['wa_group_id'])) {
                        $groupMessage = "*INFO: $title*\n\n" . $this->replacePlaceholders($content, 'Siswa/i', $walikelas['class_name']) . "\n\n_Informasi Sekolah_";
                        
                        $this->waNotificationModel->insert([
                            'phone_number' => $walikelas['wa_group_id'],
                            'message' => $groupMessage,
                            'status' => 'pending',
                            'scheduled_at' => date('Y-m-d H:i:s'),
                            'payload' => json_encode([
                                'recipient_type' => 'group',
                                'information_id' => $infoId,
                                'class_id' => $classId
                            ])
                        ]);
                    }
                }
            }

            // 2. Send to Guardians if applicable
            if ($targetSetting === 'guardian' || $targetSetting === 'both') {
                foreach ($recipientIds as $classId) {
                    $students = $this->walikelasModel->getStudentsByClass($classId);
                    foreach ($students as $student) {
                         $studentDetail = $this->studentModel->find($student['id']);
                         $phone = !empty($studentDetail['guardian_phone']) ? $studentDetail['guardian_phone'] : $studentDetail['phone_number'];

                         if ($phone) {
                             $msg = "*INFO: $title*\n\n" . $this->replacePlaceholders($content, $student['full_name'], $student['class']) . "\n\n_Informasi Sekolah_";
                             
                             $this->waNotificationModel->insert([
                                'phone_number' => $phone,
                                'message' => $msg,
                                'status' => 'pending',
                                'scheduled_at' => date('Y-m-d H:i:s'),
                                'payload' => json_encode([
                                    'recipient_type' => 'individual',
                                    'information_id' => $infoId,
                                    'student_id' => $student['id']
                                ])
                            ]);
                         }
                    }
                }
            }
        }
    }

    private function replacePlaceholders($content, $name, $class)
    {
        $content = str_replace('{nama}', $name, $content);
        $content = str_replace('{kelas}', $class, $content);
        return $content;
    }
}
