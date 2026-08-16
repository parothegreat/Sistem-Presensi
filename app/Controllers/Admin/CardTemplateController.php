<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CardTemplateController extends BaseController
{
    protected $configPath;

    public function __construct()
    {
        // Path to store the JSON config
        $this->configPath = WRITEPATH . 'uploads/card_template.json';
    }

    public function index()
    {
        // Load existing config or default
        $config = $this->getTemplateConfig();

        return view('admin/qrcode/card_template', [
            'title' => 'Pengaturan Template Kartu',
            'config' => $config
        ]);
    }

    public function save()
    {
        // Handle Image Upload
        $img = $this->request->getFile('background_image');
        $currentConfig = $this->getTemplateConfig();
        $bgPath = $currentConfig['background_image'] ?? null;

        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = 'card_bg_' . time() . '.' . $img->getExtension();
            $img->move(FCPATH . 'uploads/cards', $newName);
            $bgPath = base_url('uploads/cards/' . $newName);
        }

        // Handle Logo Upload
        $logo = $this->request->getFile('logo_image');
        $logoPath = $currentConfig['logo']['path'] ?? null;
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newLogoName = 'card_logo_' . time() . '.' . $logo->getExtension();
            $logo->move(FCPATH . 'uploads/cards', $newLogoName);
            $logoPath = 'uploads/cards/' . $newLogoName; // Store relative path for flexibility, or absolute URL
            $logoPath = base_url($logoPath);
        }

        // Collect POST data for positions
        $newConfig = [
            'background_image' => $bgPath,
            'card_width' => $this->request->getPost('card_width'),
            'card_height' => $this->request->getPost('card_height'),
            'qr' => [
                'x' => $this->request->getPost('qr_x'),
                'y' => $this->request->getPost('qr_y'),
                'size' => $this->request->getPost('qr_size'),
                'visible' => $this->request->getPost('qr_visible') ? true : false,
            ],
            'name' => [
                'x' => $this->request->getPost('name_x'),
                'y' => $this->request->getPost('name_y'),
                'size' => $this->request->getPost('name_size'),
                'color' => $this->request->getPost('name_color'),
                'align' => $this->request->getPost('name_align'),
                'visible' => $this->request->getPost('name_visible') ? true : false,
                'label' => $this->request->getPost('name_label'),
            ],
            'nis' => [
                'x' => $this->request->getPost('nis_x'),
                'y' => $this->request->getPost('nis_y'),
                'size' => $this->request->getPost('nis_size'),
                'color' => $this->request->getPost('nis_color'),
                'align' => $this->request->getPost('nis_align'),
                'visible' => $this->request->getPost('nis_visible') ? true : false,
                'label' => $this->request->getPost('nis_label'),
            ],
            'class' => [
                'x' => $this->request->getPost('class_x'),
                'y' => $this->request->getPost('class_y'),
                'size' => $this->request->getPost('class_size'),
                'color' => $this->request->getPost('class_color'),
                'align' => $this->request->getPost('class_align'),
                'visible' => $this->request->getPost('class_visible') ? true : false,
                'label' => $this->request->getPost('class_label'),
            ],
            'photo' => [
                'x' => $this->request->getPost('photo_x'),
                'y' => $this->request->getPost('photo_y'),
                'width' => $this->request->getPost('photo_width'),
                'height' => $this->request->getPost('photo_height'),
                'visible' => $this->request->getPost('photo_visible') ? true : false,
            ],
            'header' => [
                'x' => $this->request->getPost('header_x'),
                'y' => $this->request->getPost('header_y'),
                'size' => $this->request->getPost('header_size'),
                'color' => $this->request->getPost('header_color'),
                'align' => $this->request->getPost('header_align'),
                'visible' => $this->request->getPost('header_visible') ? true : false,
            ],
            'school_name' => [
                'x' => $this->request->getPost('school_name_x'),
                'y' => $this->request->getPost('school_name_y'),
                'size' => $this->request->getPost('school_name_size'),
                'color' => $this->request->getPost('school_name_color'),
                'align' => $this->request->getPost('school_name_align'),
                'visible' => $this->request->getPost('school_name_visible') ? true : false,
            ],
            'school_info' => [
                'x' => $this->request->getPost('school_info_x'),
                'y' => $this->request->getPost('school_info_y'),
                'size' => $this->request->getPost('school_info_size'),
                'color' => $this->request->getPost('school_info_color'),
                'align' => $this->request->getPost('school_info_align'),
                'visible' => $this->request->getPost('school_info_visible') ? true : false,
            ],
            'logo' => [
                'path' => $logoPath,
                'x' => $this->request->getPost('logo_x'),
                'y' => $this->request->getPost('logo_y'),
                'width' => $this->request->getPost('logo_width'),
                'height' => $this->request->getPost('logo_height'),
                'visible' => $this->request->getPost('logo_visible') ? true : false,
            ]
        ];

        // Save to JSON
        file_put_contents($this->configPath, json_encode($newConfig, JSON_PRETTY_PRINT));

        return redirect()->to('/admin/card-template')->with('success', 'Pengaturan template berhasil disimpan');
    }

    private function getTemplateConfig()
    {
        if (file_exists($this->configPath)) {
            return json_decode(file_get_contents($this->configPath), true);
        }

        // Default Config (Standard ID-1 Landscape: 85.6mm x 53.98mm)
        // If they want portrait, they can swap width/height in settings
        return [
            'background_image' => null,
            'card_width' => '85.6',
            'card_height' => '53.98',
            'qr' => ['x' => '5', 'y' => '30', 'size' => '20', 'visible' => true],
            'name' => ['x' => '42', 'y' => '25', 'size' => '12', 'color' => '#000000', 'align' => 'center', 'visible' => true, 'label' => ''],
            'nis' => ['x' => '42', 'y' => '30', 'size' => '10', 'color' => '#666666', 'align' => 'center', 'visible' => true, 'label' => 'NIS: '],
            'class' => ['x' => '42', 'y' => '35', 'size' => '8', 'color' => '#666666', 'align' => 'center', 'visible' => true, 'label' => ''],
            'photo' => ['x' => '65', 'y' => '15', 'width' => '15', 'height' => '20', 'visible' => true],
            'header' => ['x' => '42', 'y' => '5', 'size' => '10', 'color' => '#000000', 'align' => 'center', 'visible' => true],
            'school_name' => ['x' => '42', 'y' => '10', 'size' => '14', 'color' => '#000000', 'align' => 'center', 'visible' => true],
            'school_info' => ['x' => '42', 'y' => '50', 'size' => '6', 'color' => '#000000', 'align' => 'center', 'visible' => true],
            'logo' => ['path' => null, 'x' => '5', 'y' => '5', 'width' => '10', 'height' => '10', 'visible' => true],
        ];
    }
}
