<?php

namespace App\Models;

use CodeIgniter\Model;

class ArsipTematikModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Mencari arsip (HANYA item_aktif) berdasarkan ID tema.
     * @param int $themeId
     * @return array
     */
    public function searchByTheme(int $themeId): array
    {
        $builder = $this->db->table('item_aktif as ia')
            ->select("
                ia.judul_dokumen as judul, 
                u.name as pencipta_arsip, 
                k.kode as kode_klasifikasi, 
                jn.nama_naskah as uraian, 
                ia.no_dokumen as nomor_laporan, 
                ia.tk_perkembangan as tingkat_perkembangan,
                ia.tgl_dokumen as tanggal, 
                ia.tahun_cipta as kurun_waktu, 
                ia.jumlah, 
                ia.lokasi_simpan
            ")
            // Kunci: JOIN ke tabel link arsip_tema_link
            ->join('arsip_tema_link as atl', 'atl.id_item_aktif = ia.id')
            // JOIN ke tabel lain untuk mendapatkan detail
            ->join('users as u', 'u.id = ia.id_user', 'left')
            ->join('klasifikasi as k', 'k.id = ia.id_klasifikasi', 'left')
            ->join('jenis_naskah as jn', 'jn.id = ia.id_jenis_naskah', 'left')
            // Filter utama berdasarkan ID Tema
            ->where('atl.id_tema', $themeId)
            ->orderBy('ia.tgl_dokumen', 'DESC');

        return $builder->get()->getResultArray();
    }
}
