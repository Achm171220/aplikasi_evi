<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiPengawasanModel extends Model
{
    protected $table            = 'nilai_pengawasan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['id_es2', 'id_user', 'skor', 'kategori', 'tahun', 'created_by', 'updated_at']; // Tambahkan updated_at

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';

    // Callbacks untuk menentukan kategori dan mengisi ID user saat insert/update
    protected $beforeInsert = ['setCategoryAndUser'];
    protected $beforeUpdate = ['setCategoryAndUser'];

    // --- Validasi Dasar ---
    protected $validationRules = [
        'id_es2' => 'required|is_natural_no_zero',
        'skor'   => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'tahun'  => 'required|integer|exact_length[4]|greater_than_equal_to[2000]',
    ];

    protected $validationMessages = [
        'skor' => [
            'greater_than_equal_to' => 'Skor harus antara 0 hingga 100.',
            'less_than_equal_to' => 'Skor harus antara 0 hingga 100.',
        ],
        'id_es2' => [
            'required' => 'Unit Eselon 2 wajib diisi.'
        ]
    ];


    /**
     * Helper untuk menentukan Kategori berdasarkan Skor.
     */
    public function determineCategory(float $skor): string
    {
        if ($skor >= 90) return 'AA (Sangat Memuaskan)';
        if ($skor >= 80) return 'A (Memuaskan)';
        if ($skor >= 70) return 'BB (Sangat Baik)';
        if ($skor >= 60) return 'B (Baik)';
        if ($skor >= 50) return 'CC (Sangat Cukup)';
        return 'C (Cukup)';
    }

    /**
     * Callback untuk menentukan kategori dan mengisi ID user saat menyimpan.
     */
    protected function setCategoryAndUser(array $data)
    {
        // 1. Tentukan Kategori
        if (isset($data['data']['skor'])) {
            $skor = (float)$data['data']['skor'];
            $data['data']['kategori'] = $this->determineCategory($skor);
        }

        // 2. Isi ID User Pencatat (id_user)
        if (!isset($data['data']['id_user']) && session()->get('id')) {
            $data['data']['id_user'] = session()->get('id');
        }

        return $data;
    }

    /**
     * Helper untuk memvalidasi kombinasi unik secara custom.
     */
    public function validateUniqueEs2Tahun($id_es2, $tahun, $currentId = null): bool
    {
        $query = $this->where('id_es2', $id_es2)
            ->where('tahun', $tahun);

        if ($currentId) {
            // Kecualikan ID yang sedang diupdate
            $query->where('id !=', $currentId);
        }

        return $query->countAllResults() === 0;
    }


    /**
     * Mendapatkan data untuk DataTables (JOIN dengan Unit Kerja).
     * ASUMSI: Method ini memanggil getBaseQueryBuilder dari BaseModel atau sejenisnya.
     */
    public function getDataTablesList($request)
    {
        $builder = $this->builder()
            ->select('np.*, es2.nama_es2, u.name as user_name')
            ->from($this->table . ' np')
            ->join('unit_kerja_es2 as es2', 'es2.id = np.id_es2')
            ->join('users as u', 'u.id = np.id_user');

        $session = \Config\Services::session();
        $userRole = $session->get('role_access');
        $id_es2_admin = $session->get('auth_data')['id_es2'] ?? null;

        // --- PENERAPAN FILTER HAK AKSES ---
        if ($userRole === 'admin' && !empty($id_es2_admin)) {
            // Admin hanya melihat data nilai_pengawasan yang terkait dengan ES2-nya
            $builder->where('np.id_es2', $id_es2_admin);
        } elseif ($userRole === 'user') {
            // User tidak seharusnya melihat modul ini, atau batasi akses
            $builder->where('1=0');
        }

        // --- Konfigurasi DataTables ---
        $this->builder = $builder;

        $column_search = ['es2.nama_es2', 'np.tahun', 'np.kategori'];
        $column_order  = [null, 'es2.nama_es2', 'np.tahun', 'np.skor', 'np.kategori', null];
        $order = ['np.tahun' => 'DESC', 'es2.nama_es2' => 'ASC'];

        // Asumsi Anda memiliki metode getDataTables di Model Dasar (BaseModel)
        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
}
