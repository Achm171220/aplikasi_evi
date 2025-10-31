<?php

namespace App\Models;

class HakFiturModel extends BaseModel
{
    protected $table            = 'hak_fitur';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['id_user', 'id_es1', 'id_es2', 'id_es3'];
    protected $useTimestamps    = true;

    // Validasi paling penting: satu user hanya boleh punya satu hak fitur.
    protected $validationRules = [
        'id_user' => 'required'
        // Kita akan menambahkan is_unique di Controller
    ];
    protected $validationMessages = [
        'id_user' => ['is_unique' => 'User ini sudah memiliki hak fitur. Silakan edit data yang ada.']
    ];

    // Query untuk DataTables
    public function getDataTablesHakFitur($request)
    {
        $this->builder()
            ->select('hak_fitur.id, users.name as user_name, hak_fitur.id_es1, hak_fitur.id_es2, hak_fitur.id_es3, es1.nama_es1, es2.nama_es2, es3.nama_es3, es2.kode as kode_es2')
            ->join('users', 'users.id = hak_fitur.id_user')
            ->join('unit_kerja_es1 as es1', 'es1.id = hak_fitur.id_es1', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = hak_fitur.id_es2', 'left')
            ->join('unit_kerja_es3 as es3', 'es3.id = hak_fitur.id_es3', 'left');

        $column_search = ['users.name', 'es1.nama_es1', 'es2.nama_es2', 'es3.nama_es3'];
        $column_order  = [null, 'users.name', null, null]; // Disederhanakan

        return $this->getDataTables($request, $column_search, $column_order, ['hak_fitur.id' => 'DESC']);
    }

    // Mengambil detail lengkap untuk form edit
    public function getDetail($id)
    {
        $data = $this->find($id);
        if (!$data) return null;

        // Cari induknya untuk pre-fill chained dropdown
        if (!empty($data['id_es3'])) {
            $es3 = (new \App\Models\UnitKerjaEs3Model())->find($data['id_es3']);
            if ($es3) $data['id_es2'] = $es3['id_es2'];
        }
        if (!empty($data['id_es2'])) {
            $es2 = (new \App\Models\UnitKerjaEs2Model())->find($data['id_es2']);
            if ($es2) $data['id_es1'] = $es2['id_es1'];
        }

        return $data;
    }
    public function getHakFiturByUserId(int $userId)
    {
        // Menggunakan where dan first() untuk mencari satu baris berdasarkan id_user
        return $this->where('id_user', $userId)->first();
    }
}
