<?php

namespace App\Models;

class UnitKerjaEs1Model extends BaseModel
{
    protected $table            = 'unit_kerja_es1';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['kode', 'nama_es1'];

    // Aturan validasi
    protected $validationRules = [
        'kode'     => 'required|is_unique[unit_kerja_es1.kode,id,{id}]',
        'nama_es1' => 'required',
    ];

    protected $validationMessages = [
        'kode' => ['is_unique' => 'Kode Eselon 1 ini sudah digunakan.'],
        'nama_es1' => ['required' => 'Nama Eselon 1 tidak boleh kosong.']
    ];

    /**
     * Menyediakan data untuk DataTables Eselon 1.
     */
    public function getDataTablesEs1($request)
    {
        $column_search = ['kode', 'nama_es1'];
        $column_order  = [null, 'kode', 'nama_es1', null];
        $order = ['kode' => 'ASC'];
        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
}
