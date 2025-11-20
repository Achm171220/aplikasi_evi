<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
// Asumsikan Anda punya model untuk setiap unit kerja
use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;

class UnitKerja extends BaseController
{
    /**
     * Mengembalikan daftar Unit Eselon 2 berdasarkan ID Eselon 1.
     */
    public function getEselon2ByEselon1($id_es1 = null)
    {
        if (!$this->request->isAJAX() || !$id_es1) {
            return $this->response->setStatusCode(403);
        }

        $es2Model = new UnitKerjaEs2Model();
        // Ganti 'id_es1' dengan nama foreign key yang benar di tabel Eselon 2 Anda
        $data = $es2Model->where('id_es1', $id_es1)->findAll();

        return $this->response->setJSON($data);
    }

    /**
     * Mengembalikan daftar Unit Eselon 3 berdasarkan ID Eselon 2.
     */
    public function getEselon3ByEselon2($id_es2 = null)
    {
        if (!$this->request->isAJAX() || !$id_es2) {
            return $this->response->setStatusCode(403);
        }

        $es3Model = new UnitKerjaEs3Model();
        // Ganti 'id_es2' dengan nama foreign key yang benar di tabel Eselon 3 Anda
        $data = $es3Model->where('id_es2', $id_es2)->findAll();

        return $this->response->setJSON($data);
    }
}
