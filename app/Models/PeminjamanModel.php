<?php

namespace App\Models;

class PeminjamanModel extends BaseModel // Pastikan extend BaseModel
{
    protected $table            = 'peminjaman';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'id_item_aktif',
        'id_berkas_aktif',
        'id_user_peminjam',
        'peminjam_nama',
        'peminjam_unit',
        'tgl_pinjam',
        'tgl_kembali_rencana',
        'tgl_kembali_aktual',
        'status',
        'keterangan',
        'created_by'
    ];
    protected $useTimestamps    = true;

    // Untuk DataTables halaman daftar peminjaman
    public function getDataTablesList($request)
    {
        $builder = $this->builder()
            ->select('peminjaman.*, item_aktif.judul_dokumen, berkas_aktif.nama_berkas, users.name as peminjam_user_name')
            ->join('item_aktif', 'item_aktif.id = peminjaman.id_item_aktif', 'left')
            ->join('berkas_aktif', 'berkas_aktif.id = peminjaman.id_berkas_aktif', 'left')
            ->join('users', 'users.id = peminjaman.id_user_peminjam', 'left');

        // Tambahkan filter hak akses jika perlu, mirip dengan item_aktif
        // Misalnya, hanya admin yang melihat peminjaman di unitnya
        // if (session()->get('user_role') === 'admin') { ... }

        $column_search = ['peminjam_nama', 'item_aktif.judul_dokumen', 'berkas_aktif.nama_berkas', 'status'];
        $column_order = [null, 'tgl_pinjam', 'peminjam_nama', null, 'status', null];
        $order = ['peminjaman.id' => 'DESC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
}
