<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQrCodeToStudents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('students', [
            'qr_code_data' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'QR code SVG data or image string',
            ],
            'qr_code_generated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Timestamp when QR code was generated',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('students', ['qr_code_data', 'qr_code_generated_at']);
    }
}
