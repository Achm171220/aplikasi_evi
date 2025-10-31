<?php

namespace App\Models;

class UnitKerjaEs3Model extends BaseModel
{
    protected $table            = 'unit_kerja_es3';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id_es2', 'kode', 'nama_es3'];

    protected $validationRules = [
        'id_es2'   => 'required|integer',
        'kode'     => 'required|is_unique[unit_kerja_es3.kode,id,{id}]',
        'nama_es3' => 'required',
    ];

    protected $validationMessages = [
        'id_es2'   => ['required' => 'Induk Unit Eselon 2 harus dipilih.'],
        'kode'     => ['is_unique' => 'Kode Eselon 3 ini sudah digunakan.'],
        'nama_es3' => ['required' => 'Nama Eselon 3 tidak boleh kosong.']
    ];

    /**
     * Menyediakan data untuk DataTables Eselon 3 dengan join ganda
     */
    public function getDataTablesEs3($request)
    {
        $builder = $this->db->table($this->table) // Mulai dengan builder baru
            ->select('unit_kerja_es3.*, es2.nama_es2, es1.nama_es1')
            ->join('unit_kerja_es2 as es2', 'es2.id = unit_kerja_es3.id_es2', 'left')
            ->join('unit_kerja_es1 as es1', 'es1.id = es2.id_es1', 'left');

        $this->builder = $builder; // Set builder utama agar bisa dipakai di BaseModel

        $column_search = ['unit_kerja_es3.kode', 'unit_kerja_es3.nama_es3', 'es2.nama_es2', 'es1.nama_es1'];
        $column_order  = [null, 'unit_kerja_es3.kode', 'unit_kerja_es3.nama_es3', 'es2.nama_es2', 'es1.nama_es1', null];
        $order = ['unit_kerja_es3.kode' => 'ASC'];

        // Method getDataTables di BaseModel sekarang akan otomatis menerapkan filter
        return $this->getDataTables($request, $column_search, $column_order, $order);
    }

    /**
     * FUNGSI BARU: Mengambil detail lengkap termasuk ID Eselon 1 untuk edit form
     */
    public function getDetailEs3($id)
    {
        return $this->builder()
            ->select('unit_kerja_es3.*, es2.id_es1')
            ->join('unit_kerja_es2 as es2', 'es2.id = unit_kerja_es3.id_es2', 'left')
            ->where('unit_kerja_es3.id', $id)
            ->get()->getRowArray();
    }
}
