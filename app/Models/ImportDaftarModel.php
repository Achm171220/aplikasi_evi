<?php

namespace App\Models;

// Pastikan sudah extend BaseModel
class ImportDaftarModel extends BaseModel
{
    protected $table            = 'import_daftar';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['id_es2', 'tahun', 'semester', 'id_user', 'file'];
    protected $useTimestamps    = true;

    /**
     * Menyediakan data untuk DataTables di halaman Riwayat Import.
     */
    public function getDataTablesList($request)
    {
        // --- PERBAIKAN DI SINI ---
        // Gunakan builder baru yang bersih untuk menghindari sisa query
        $builder = $this->db->table($this->table)
            ->select('import_daftar.*, users.name as user_name, es2.nama_es2')
            // Pastikan alias tabelnya benar
            ->join('users', 'users.id = import_daftar.id_user', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = import_daftar.id_es2', 'left');

        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');

        // Filter untuk admin agar hanya melihat riwayat import dari unitnya
        if ($userRole === 'admin' && !empty($authData['id_es2'])) {
            $builder->where('import_daftar.id_es2', $authData['id_es2']);
        }

        // Set builder utama untuk digunakan oleh method di BaseModel
        $this->builder = $builder;

        $column_search = ['tahun', 'users.name', 'es2.nama_es2', 'file'];
        $column_order  = [null, 'tahun', 'semester', 'es2.nama_es2', 'users.name', 'created_at', null];
        $order = ['import_daftar.id' => 'DESC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
}
