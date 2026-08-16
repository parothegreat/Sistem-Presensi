<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationTemplateModel;

class AdminNotificationTemplate extends BaseController
{
    protected $templateModel;

    public function __construct()
    {
        $this->templateModel = new NotificationTemplateModel();
    }

    /**
     * List all templates grouped by channel
     */
    public function index()
    {
        $templates = $this->templateModel->orderBy('channel', 'ASC')->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Template Notifikasi',
            'templates' => $templates
        ];

        return view('admin/notification_templates/index', $data);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to('/admin/notification-templates')->with('error', 'Template tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Template: ' . $template['name'],
            'template' => $template
        ];

        return view('admin/notification_templates/edit', $data);
    }

    /**
     * Process update
     */
    public function update($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to('/admin/notification-templates')->with('error', 'Template tidak ditemukan');
        }

        $rules = [
            'content' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'content' => $this->request->getPost('content'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($this->templateModel->update($id, $data)) {
            // Clear cache
            $this->templateModel->clearTemplateCache($template['code']);
            return redirect()->to('/admin/notification-templates')->with('success', 'Template berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui template');
        }
    }
}
