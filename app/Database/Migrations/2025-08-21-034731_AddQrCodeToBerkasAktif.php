<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQrCodeToBerkasAktif extends Migration
{
    public function up()
    {
        $fields = [
            'qr_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'link_barcode', // Atau kolom lain yang relevan
            ],
        ];
        $this->forge->addColumn('berkas_aktif', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('berkas_aktif', 'qr_code');
    }
}
