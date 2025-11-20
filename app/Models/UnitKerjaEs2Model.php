<?php

namespace App\Models;

class UnitKerjaEs2Model extends BaseModel
{
    protected $table            = 'unit_kerja_es2';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id_es1', 'kode', 'nama_es2'];

    protected $validationRules = [
        'id_es1'   => 'required|integer',
        'kode'     => 'required|is_unique[unit_kerja_es2.kode,id,{id}]',
        'nama_es2' => 'required',
    ];

    protected $validationMessages = [
        'id_es1'   => ['required' => 'Induk Unit Eselon 1 harus dipilih.'],
        'kode'     => ['is_unique' => 'Kode Eselon 2 ini sudah digunakan.'],
        'nama_es2' => ['required' => 'Nama Eselon 2 tidak boleh kosong.']
    ];

    /**
     * Menyediakan data untuk DataTables Eselon 2 dengan join ke Eselon 1
     */
    public function getDataTablesEs2($request)
    {
        // Siapkan builder dengan JOIN
        $this->builder()
            ->select('unit_kerja_es2.*, es1.nama_es1 as nama_induk')
            ->join('unit_kerja_es1 as es1', 'es1.id = unit_kerja_es2.id_es1', 'left');

        $column_search = ['unit_kerja_es2.kode', 'unit_kerja_es2.nama_es2', 'es1.nama_es1'];
        $column_order  = [null, 'unit_kerja_es2.kode', 'unit_kerja_es2.nama_es2', 'nama_induk', null];
        $order = ['unit_kerja_es2.kode' => 'ASC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
}
