<?php

namespace App\Models;

use CodeIgniter\Model;

class BeritaAcaraModel extends Model
{
    protected $table      = 'berita_acara';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Field-field yang boleh diisi/diupdate di tabel berita_acara
    protected $allowedFields = [
        'id_user',
        'no_ba',
        'tgl_ba',
        'nama_pemindah',
        'jabatan_pemindah',
        'nama_penerima',
        'jabatan_penerima',
        'catatan',
        'file_ba_scan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Tambahkan aturan validasi jika diperlukan
    protected $validationRules    = [
        'no_ba'         => 'required|is_unique[berita_acara.no_ba]',
        'tgl_ba'        => 'required|valid_date',
        'nama_pemindah' => 'required',
        'nama_penerima' => 'required',
        // id_user akan diambil dari session (setelah auth diterapkan)
    ];
    protected $validationMessages = [
        'no_ba' => [
            'required'   => 'Nomor Berita Acara wajib diisi.',
            'is_unique'  => 'Nomor Berita Acara sudah ada, mohon gunakan yang lain.',
        ],
        'tgl_ba' => [
            'required'   => 'Tanggal Berita Acara wajib diisi.',
            'valid_date' => 'Format tanggal BA tidak valid.',
        ],
        'nama_pemindah' => [
            'required'   => 'Nama pihak pemindah wajib diisi.',
        ],
        'nama_penerima' => [
            'required'   => 'Nama pihak penerima wajib diisi.',
        ],
    ];
    protected $skipValidation     = false;
}
