<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface; // Penting untuk type-hinting

class UserModel extends BaseModel
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Daftar lengkap field yang diizinkan untuk diisi
    protected $allowedFields = [
        'nip',
        'name',
        'password', // PENTING: Harus ada di allowedFields meskipun isinya NULL atau placeholder
        'email',
        'api_token',
        'role_access',
        'role_jabatan',
        'nama_jabatan_api', // <--- TAMBAHKAN FIELD BARU INI
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // AKTIFKAN TIMESTAMPS AGAR created_at & updated_at TERISI OTOMATIS
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime'; // Pastikan sesuai dengan tipe kolom (datetime)

    // Jika Anda menggunakan soft delete
    protected $useSoftDeletes = false;
    protected $deletedField  = 'deleted_at';

    // Callbacks untuk hashing password otomatis
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    // Aturan validasi lengkap untuk form dan import
    protected $validationRules = [
        'id'           => 'permit_empty|is_natural_no_zero',
        // 'nip'          => 'permit_empty|max_length[50]|is_unique[users.nip,id,{id}]', // NIP harus permit_empty
        // 'name'         => 'required|min_length[3]',
        // 'email'        => 'required|valid_email|is_unique[users.email,id,{id}]',
        'role_access'  => 'required|in_list[superadmin,admin,user]',
        'status'       => 'required|in_list[aktif,non-aktif]',
        'password'     => 'permit_empty|min_length[6]', // JIKA password NOT NULL, harus ada default hash
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Maaf, email ini sudah terdaftar. Silakan gunakan email lain.'
        ],
        'role_jabatan' => [
            'in_list' => 'Nilai Role Jabatan tidak valid.'
        ]
    ];
    protected function generateApiToken(array $data)
    {
        // Pastikan api_token belum ada, agar tidak menimpa jika sudah diset dari luar
        if (!isset($data['data']['api_token'])) {
            $data['data']['api_token'] = bin2hex(random_bytes(32)); // Contoh sederhana, gunakan UUID atau token lebih kompleks untuk produksi
        }
        return $data;
    }

    /**
     * Fungsi callback untuk hashing password.
     * Hanya berjalan jika field 'password' ada dan tidak kosong.
     * Jika kosong, hapus dari data agar tidak menimpa password lama.
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['data']['password']);
        }

        return $data;
    }

    /**
     * Menyediakan data untuk DataTables di halaman Manajemen User.
     * Sudah mencakup filter hak akses dan join ke tabel lain.
     */
    public function getDataTablesUsers($request)
    {
        $builder = $this->db->table($this->table . ' as users')
            // --- TAMBAHKAN users.role_jabatan secara eksplisit di SELECT ---
            ->select('users.*, users.role_jabatan, hf.id_es3, es3.nama_es3, es2_user.kode as kode_es2, es2_admin.nama_es2 as nama_es2_admin')
            ->join('hak_fitur as hf', 'hf.id_user = users.id', 'left')
            ->join('unit_kerja_es3 as es3', 'es3.id = hf.id_es3', 'left')
            ->join('unit_kerja_es2 as es2_user', 'es2_user.id = es3.id_es2', 'left')
            ->join('unit_kerja_es2 as es2_admin', 'es2_admin.id = hf.id_es2', 'left');

        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');

        // Terapkan filter jika yang login adalah admin
        if ($userRole === 'admin' && !empty($authData['id_es2'])) {
            $db = \Config\Database::connect();
            // Subquery untuk mendapatkan semua user yang hak fiturnya di bawah Es2 admin ini
            $subQueryUsers = $db->table('hak_fitur')->select('id_user')->where('id_es2', $authData['id_es2']);

            $builder->whereIn('users.id', $subQueryUsers);
        }
        // Superadmin tidak perlu difilter

        $this->builder = $builder;

        // Definisikan kolom yang bisa dicari dan diurutkan
        $column_search = ['users.name', 'users.email', 'es3.nama_es3', 'es2_admin.nama_es2'];
        $column_order  = [null, 'users.name', 'users.email', 'users.role_access', null, 'users.status', null];
        $order = ['users.id' => 'DESC'];
        $builder->where('users.deleted_at', null); // <-- Tambahkan baris ini!

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
    protected function _runCustomValidation(array $data): array
    {
        // 1. Ambil aturan validasi dasar
        $rules = $this->validationRules;
        $messages = $this->validationMessages;

        // 2. Tambahkan aturan 'is_unique' pada email secara kondisional
        if (isset($data['data']['id']) && $data['data']['id'] !== null) {
            // Jika ID ada (operasi UPDATE), abaikan ID tersebut
            $rules['email'] .= "|is_unique[users.email,id,{$data['data']['id']}]";
        } else {
            // Jika ID tidak ada (operasi CREATE), cek keunikan secara global
            $rules['email'] .= "|is_unique[users.email]";
        }

        // 3. Set aturan validasi ke validator dan jalankan
        $validator = \Config\Services::validation();
        $validator->setRules($rules, $messages);

        // Penting: validasi harus dijalankan pada array $data['data']
        if (!$validator->run($data['data'])) {
            // Jika validasi gagal, simpan error di Model untuk diakses oleh controller
            $this->errors = $validator->getErrors();
            // Kembalikan FALSE untuk memberitahu Model agar membatalkan operasi
            return $data; // Callback harus mengembalikan $data (atau throw exception)
        }

        // Jika validasi sukses, kembalikan data
        return $data;
    }

    public function getVerifiers()
    {
        return $this->where('role_access', 'admin')
            ->groupStart()
            ->orWhere('role_jabatan', 'arsiparis')
            ->orWhere('role_jabatan', 'pemangku')
            ->orWhere('role_jabatan', 'verifikator')
            ->groupEnd()
            ->where('status', 'aktif') // Hanya user aktif
            ->findAll();
    }

    // Tambahkan method untuk mencari user berdasarkan email untuk login
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    // Ambil user untuk Verifikator 1
    public function getVerifikator1Users()
    {
        return $this->select('id, name')
            ->where('role_access', 'admin')
            ->where('role_jabatan', 'arsiparis') // Admin dengan role_jabatan arsiparis adalah V1
            ->where('status', 'aktif')
            ->findAll();
    }

    // Ambil user untuk Verifikator 2
    public function getVerifikator2Users()
    {
        return $this->select('id, name')
            ->where('role_access', 'admin')
            ->where('role_jabatan', 'pengampu')
            ->where('status', 'aktif')
            ->findAll();
    }

    // Ambil user untuk Verifikator 3
    public function getVerifikator3Users()
    {
        return $this->select('id, name')
            ->where('role_access', 'admin')
            ->where('role_jabatan', 'verifikator')
            ->where('status', 'aktif')
            ->findAll();
    }

    private function syncUserFromApi(array $apiUserData): array
    {
        $nip = $apiUserData['nip'] ?? null;
        $syncedUser = null;
        $localUser = null;

        if (!$nip && empty($apiUserData['s_email_dinas'])) {
            log_message('error', 'API User Sync Error: Data API tidak memiliki NIP dan Email Dinas yang valid: ' . json_encode($apiUserData));
            return [];
        }

        try {
            // 1. Cari user lokal (utamakan NIP, fallback ke email)
            if ($nip) {
                $localUser = $this->userModel->where('nip', $nip)->first();
            }
            if (!$localUser && !empty($apiUserData['s_email_dinas'])) {
                $localUser = $this->userModel->where('email', $apiUserData['s_email_dinas'])->first();
            }

            // 2. Data yang akan di-update atau di-insert
            $dataToSave = [
                'nip'                => $nip,
                'name'               => $apiUserData['nama'] ?? 'Unknown User',
                'email'              => $apiUserData['s_email_dinas'] ?? $nip . '@bpkp.go.id',
                'api_token'          => $apiUserData['token'] ?? null,
                // Jika user baru, role_access dan role_jabatan diisi default, jika user lama, pertahankan role lokal
                'role_access'        => $localUser['role_access'] ?? 'user',
                'role_jabatan'       => $localUser['role_jabatan'] ?? $this->getRoleJabatanFromApi($apiUserData['s_jabatan'] ?? ''),
                'nama_jabatan_api'   => $apiUserData['s_jabatan'] ?? null,
                'status'             => 'aktif',
                'password'           => password_hash(random_string('alnum', 32), PASSWORD_BCRYPT),
            ];

            if ($localUser) {
                // Cek apakah data berubah sebelum update
                $shouldUpdate = false;
                foreach ($dataToSave as $key => $value) {
                    // Jangan bandingkan password
                    if ($key !== 'password' && ($localUser[$key] ?? null) !== $value) {
                        $shouldUpdate = true;
                        break;
                    }
                }

                if ($shouldUpdate) {
                    // Update user lokal
                    if (!$this->userModel->update($localUser['id'], $dataToSave)) {
                        // Jika update gagal, log error DB spesifik
                        log_message('error', 'DB Update Failed for NIP ' . ($nip ?? 'NULL') . ': ' . $this->userModel->errors());
                        return [];
                    }
                }

                // Ambil data user (baik di-update atau tidak)
                $syncedUser = $this->userModel->find($localUser['id']);
            } else {
                // Buat user baru jika belum ada
                if ($this->userModel->insert($dataToSave)) {
                    $newId = $this->userModel->getInsertID();
                    $syncedUser = $this->userModel->find($newId);
                } else {
                    // Jika insert gagal, log error DB spesifik
                    log_message('error', 'DB Insert Failed for NIP ' . ($nip ?? 'NULL') . ': ' . json_encode($this->userModel->errors()));
                    return [];
                }
            }

            // Final check dan return
            if (is_array($syncedUser)) {
                return $syncedUser;
            }
        } catch (\Exception $e) {
            log_message('error', 'API User Sync Exception for NIP ' . ($nip ?? 'NULL') . ': ' . $e->getMessage());
        }

        // Jika ada kegagalan yang tidak tertangkap oleh logic Model error atau exception
        return [];
    }
    public function findByName(string $name)
    {
        // Pastikan nama method-nya findByName (bukan findByName atau FindByName)
        return $this->where('name', $name)->first();
    }
    public function getUserByMappedName(string $name)
    {
        // Mencari user berdasarkan field 'name' yang menjadi kunci mapping
        return $this->where('name', $name)
            ->where('status', 'aktif')
            ->first();
    }
    public function getUserByNip(string $nip)
    {
        // 1. Bersihkan NIP dari spasi
        $cleanNip = str_replace(' ', '', $nip);

        // 2. Lakukan pencarian di database
        return $this->where('nip', $cleanNip)->first();
    }
}
