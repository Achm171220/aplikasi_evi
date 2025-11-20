<?php

namespace App\Models;

// Asumsi Anda memiliki BaseModel yang memperluas CodeIgniter\Model
class TemaModel extends BaseModel
{
    protected $table            = 'tema';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama_tema', 'deskripsi', 'created_at', 'updated_at'];
    protected $useTimestamps    = true;

    // --- Validasi Terpusat di Model ---
    protected $validationRules = [
        'id'        => 'permit_empty',
        'nama_tema' => 'required|max_length[255]|is_unique[tema.nama_tema,id,{id}]',
        'deskripsi' => 'permit_empty',
    ];

    protected $validationMessages = [
        'nama_tema' => [
            'required'  => 'Nama Tema wajib diisi.',
            'is_unique' => 'Nama Tema ini sudah ada. Silakan gunakan nama lain.',
        ],
    ];

    /**
     * Mendapatkan data untuk DataTables.
     */
    public function getDataTablesList($request)
    {
        $builder = $this->db->table($this->table)
            ->select('id, nama_tema, deskripsi');

        $this->builder = $builder;

        $column_search = ['nama_tema', 'deskripsi'];
        $column_order  = [null, 'nama_tema', 'deskripsi', null];
        $order = ['nama_tema' => 'ASC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
}
