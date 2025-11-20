<?php

namespace App\Models;

use CodeIgniter\Model;

class TrialModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Bisa 'object' juga
    protected $useSoftDeletes   = false; // Jika Anda ingin soft delete, ubah ke true dan tambahkan deleted_at di tabel
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'email',
        'password',
        'api_token',
        'role_access',
        'role_jabatan',
        'status'
    ];

    // Dates
    protected $useTimestamps = true; // Karena ada created_at dan updated_at
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Hanya jika useSoftDeletes = true

    // Callbacks
    protected $beforeInsert = ['hashPassword', 'generateApiToken'];
    protected $beforeUpdate = ['hashPasswordIfChanged']; // Perhatikan: hanya hash jika password diupdate

    /**
     * Hash password sebelum disimpan ke database.
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Hash password hanya jika password di-update (tidak kosong)
     */
    protected function hashPasswordIfChanged(array $data)
    {
        // Pastikan password ada dan tidak kosong
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        } else {
            // Jika password kosong, hapus dari data agar tidak menimpa password lama dengan nilai kosong atau hash dari string kosong
            unset($data['data']['password']);
        }
        return $data;
    }

    /**
     * Generate api_token unik sebelum user baru disimpan.
     */
    protected function generateApiToken(array $data)
    {
        if (!isset($data['data']['api_token']) || empty($data['data']['api_token'])) {
            // Generate token unik yang lebih kuat
            $token = bin2hex(random_bytes(32)); // 64 karakter hex
            // Pastikan token benar-benar unik di database
            while ($this->where('api_token', $token)->first()) {
                $token = bin2hex(random_bytes(32));
            }
            $data['data']['api_token'] = $token;
        }
        return $data;
    }

    /**
     * Metode untuk memverifikasi kredensial pengguna (opsional, bisa di login controller terpisah)
     */
    public function verifyCredentials($email, $password)
    {
        $user = $this->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
