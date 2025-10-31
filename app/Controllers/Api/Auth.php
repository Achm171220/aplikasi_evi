<?php

namespace App\Controllers\Api;

// Gunakan ResourceController untuk mendapatkan akses ke helper API
use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\HakFiturModel; // Tambahkan ini

class Auth extends ResourceController
{
    /**
     * Mengotentikasi pengguna dan mengembalikan data beserta token.
     */
    public function login()
    {
        // 1. Validasi input
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required'
        ];
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // 2. Cek kredensial
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $this->request->getVar('email'))->first();

        // 3. Pengecekan yang lebih detail
        if (!$user) {
            return $this->failUnauthorized('Email tidak ditemukan.');
        }
        if (!password_verify($this->request->getVar('password'), $user['password'])) {
            return $this->failUnauthorized('Password salah.');
        }
        if ($user['status'] !== 'aktif') {
            return $this->failForbidden('Akun Anda tidak aktif.');
        }

        // --- PERBAIKAN UTAMA DI SINI ---
        try {
            // 4. Buat/perbarui token
            $token = bin2hex(random_bytes(32));

            // Gunakan instance model yang sama untuk update
            if ($userModel->update($user['id'], ['api_token' => $token]) === false) {
                // Jika update gagal karena alasan lain (bukan karena ID tidak ditemukan)
                return $this->failServerError('Gagal memperbarui token pengguna.', 500, $userModel->errors());
            }
        } catch (\Exception $e) {
            // Menangkap error jika update gagal karena ID tidak ditemukan
            // (seharusnya tidak terjadi karena kita sudah cek $user di atas)
            return $this->failServerError('Terjadi kesalahan kritis saat update token: ' . $e->getMessage());
        }
        // --- AKHIR PERBAIKAN ---

        // 5. Ambil data hak fitur terkait
        $hakFiturModel = new \App\Models\HakFiturModel();
        $authData = $hakFiturModel->where('id_user', $user['id'])->first();

        // 6. Siapkan data respons
        $responseData = [
            'status' => 'success',
            'user' => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role_access'],
            ],
            'authorization' => [
                'token' => $token,
                'type'  => 'bearer'
            ],
            'hak_fitur' => $authData
        ];

        return $this->respond($responseData);
    }
}
