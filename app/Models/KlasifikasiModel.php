<?php

namespace App\Models;

class KlasifikasiModel extends BaseModel // Extend BaseModel kita yang sudah ada
{
    protected $table            = 'klasifikasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['kode', 'nama_klasifikasi', 'umur_aktif', 'umur_inaktif', 'skkad', 'nasib_akhir'];

    // Aturan validasi untuk form klasifikasi
    protected $validationRules = [
        // --- TAMBAHKAN BARIS INI ---
        'id'               => 'permit_empty|is_natural_no_zero', // Penting untuk PK
        // --- AKHIR TAMBAHAN ---
        'kode'             => 'required|is_unique[klasifikasi.kode,id,{id}]',
        'nama_klasifikasi' => 'required',
        'umur_aktif'       => 'required|integer',
        'umur_inaktif'     => 'required|integer',
        'skkad'            => 'required|in_list[biasa,terbatas,rahasia]',
        'nasib_akhir'      => 'required|in_list[musnah,permanen,lainnya]',
    ];

    protected $validationMessages = [
        'kode' => ['is_unique' => 'Kode klasifikasi ini sudah ada.'],
        'nama_klasifikasi' => ['required' => 'Nama Klasifikasi tidak boleh kosong.'],
        'umur_aktif' => ['required' => 'Umur Aktif wajib diisi.', 'integer' => 'Umur Aktif harus angka.'],
        'umur_inaktif' => ['required' => 'Umur Inaktif wajib diisi.', 'integer' => 'Umur Inaktif harus angka.'],
        'skkad' => ['required' => 'SKKAD wajib dipilih.', 'in_list' => 'Nilai SKKAD tidak valid.'],
        'nasib_akhir' => ['required' => 'Nasib Akhir wajib dipilih.', 'in_list' => 'Nilai Nasib Akhir tidak valid.'],
    ];

    /**
     * Fungsi untuk menyediakan data ke DataTables di halaman Klasifikasi
     */
    public function getDataTablesKlasifikasi($request)
    {
        $column_search = ['kode', 'nama_klasifikasi'];
        $column_order  = [null, 'kode', 'nama_klasifikasi', 'umur_aktif', 'umur_inaktif', 'nasib_akhir', null];
        $order = ['kode' => 'ASC']; // Urutkan berdasarkan kode secara default

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
    public function parseUmurAktifToYears(?string $umurAktifString): int
    {
        // Jika string kosong atau NULL, anggap tidak ada batas tahunan (berlaku selamanya secara virtual untuk perpindahan)
        if (empty($umurAktifString)) {
            return PHP_INT_MAX;
        }

        $umurAktifString = strtolower(trim($umurAktifString));

        // Case: "Selama Berlaku" atau tidak ada batas tahunan jelas
        if (str_contains($umurAktifString, 'selama berlaku')) {
            return PHP_INT_MAX;
        }

        // Case: "X Tahun" atau "X Tahun Setelah Y"
        // Menggunakan regex untuk mengekstrak angka di awal string
        if (preg_match('/^(\d+)\s+tahun/i', $umurAktifString, $matches)) {
            return (int)$matches[1];
        }

        // Case: "Setelah X Selesai" tanpa angka tahun eksplisit di awal
        if (str_contains($umurAktifString, 'setelah')) {
            // Ini adalah kasus yang lebih kompleks. Untuk kesederhanaan,
            // jika tidak ada angka tahun yang bisa diekstrak, kita anggap
            // arsip ini tidak akan otomatis dipindahkan berdasarkan retensi tahun.
            return PHP_INT_MAX;
        }

        // Default: Jika format tidak dikenali, anggap tidak ada batas tahunan
        return PHP_INT_MAX;
    }
    public function getByCodeLevel(int $level)
    {
        // Kondisi WHERE berdasarkan jumlah segmen kode
        $whereCondition = '';
        if ($level === 1) {
            $whereCondition = "LENGTH(kode) = 5 AND LOCATE('.', kode) = 0"; // Contoh: XX.YY
            $whereCondition = "LENGTH(kode) = 2 AND LOCATE('.', kode) = 0"; // Contoh: XX
        } elseif ($level === 2) {
            $whereCondition = "LENGTH(kode) = 5 AND LOCATE('.', kode) = 3"; // Contoh: XX.YY
        } elseif ($level === 3) {
            $whereCondition = "LENGTH(kode) = 8 AND SUBSTRING_INDEX(kode, '.', 2) != kode"; // Contoh: XX.YY.ZZ
        } else {
            return [];
        }

        return $this->where($whereCondition)
            ->orderBy('kode', 'ASC')
            ->findAll();
    }

    /**
     * PERBAIKAN: Mengambil daftar klasifikasi untuk dropdown input (hanya level 3).
     * @return array
     */
    public function getForDropdownInput()
    {
        // Panggil method getByCodeLevel untuk mendapatkan hanya level 3
        return $this->getByCodeLevel(3);
    }
    /**
     * Override method validate() untuk menambahkan aturan 'is_unique' secara dinamis.
     * Logika ini harus ada di sini, sama seperti di UserModel.
     */
   
}
