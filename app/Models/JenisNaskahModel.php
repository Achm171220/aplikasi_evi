<?php

namespace App\Models;

class JenisNaskahModel extends BaseModel
{

    protected $table            = 'jenis_naskah';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['kode_naskah', 'nama_naskah'];

    // --- PERBAIKAN UTAMA DI SINI ---
    protected $validationRules = [
        'id'          => 'permit_empty|is_natural_no_zero', // Aturan untuk Primary Key
        'kode_naskah' => 'required|is_unique[jenis_naskah.kode_naskah,id,{id}]',
        'nama_naskah' => 'required',
    ];
    /**
     * Menyediakan data untuk DataTables
     */
    public function getDataTablesJenisNaskah($request)
    {
        $column_search = ['kode_naskah', 'nama_naskah'];
        $column_order  = [null, 'kode_naskah', 'nama_naskah', null];
        $order = ['kode_naskah' => 'ASC'];
        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
}
