<?php

namespace App\Controllers;

// Pastikan semua model yang dibutuhkan di-load
use App\Models\UserModel;
use App\Models\HakFiturModel;
use App\Models\LoginAttemptModel; // <-- Tambahkan ini

class Auth extends BaseController
{
    protected $loginAttemptModel; // <-- Deklarasikan ini
    protected $hakFiturModel; // <-- Deklarasikan ini
    protected $userModel; // Sudah ada

    public function __construct()
    {
        // ... (inisialisasi model lain)
        $this->userModel = new UserModel();
        $this->loginAttemptModel = new LoginAttemptModel(); // <-- Inisialisasi ini
        $this->hakFiturModel = new HakFiturModel(); // <-- Inisialisasi ini
        helper('form');
    }

    public function index()
    {
        // Jika user sudah login, redirect ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }

        $data = [
            'title' => 'Login',
            'validation' => \Config\Services::validation(),
            'session' => session()
        ];
        return view('auth/login', $data);
    }

    public function loginProcess()
    {
        // 1. Validasi input form (email dan password)
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required'
        ];
        $messages = [
            'email' => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.'],
            'password' => ['required' => 'Password wajib diisi.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->to('/login')->withInput()->with('validation', $this->validator);
        }

        $session = session();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $ipAddress = $this->request->getIPAddress();

        $user = $this->userModel->where('email', $email)->first();

        // --- BRUTE FORCE PROTECTION LOGIC ---
        $maxAttempts = 3; // Batas percobaan login gagal
        $attemptWindow = '-5 minutes'; // Dalam 5 menit terakhir

        // 2. Cek percobaan gagal sebelumnya untuk email ini
        $failedAttempts = $this->loginAttemptModel
            ->where('email_attempt', $email)
            ->where('success', 0)
            ->where('time_attempt >=', date('Y-m-d H:i:s', strtotime($attemptWindow)))
            ->countAllResults();

        // 3. Jika sudah melewati batas, tolak login dan kunci akun (jika aktif)
        if ($failedAttempts >= $maxAttempts) {
            // Jika user ditemukan dan statusnya masih aktif, kunci akunnya
            if ($user && $user['status'] === 'aktif') {
                $this->userModel->update($user['id'], ['status' => 'non-aktif']);
                session()->setFlashdata('error', 'Akun Anda terkunci karena terlalu banyak percobaan login gagal.');
            } else {
                // Email tidak ditemukan atau sudah non-aktif, beri pesan umum
                session()->setFlashdata('error', 'Terlalu banyak percobaan login gagal. Silakan coba lagi nanti.');
            }
            return redirect()->to('/login')->withInput();
        }

        // --- LOGIKA LOGIN UTAMA ---

        // Langkah 4: Jika user tidak ditemukan
        if (!$user) {
            $this->loginAttemptModel->insert([
                'ip_address'    => $ipAddress,
                'email_attempt' => $email,
                'time_attempt'  => date('Y-m-d H:i:s'),
                'success'       => 0,
                'user_id'       => null // User tidak ditemukan
            ]);
            session()->setFlashdata('error', 'Email tidak terdaftar di sistem.');
            return redirect()->to('/login')->withInput();
        }

        // Langkah 5: Jika user ditemukan, verifikasi password
        if (!password_verify($password, $user['password'])) {
            $this->loginAttemptModel->insert([
                'ip_address'    => $ipAddress,
                'email_attempt' => $email,
                'time_attempt'  => date('Y-m-d H:i:s'),
                'success'       => 0,
                'user_id'       => $user['id']
            ]);
            session()->setFlashdata('error', 'Password yang Anda masukkan salah.');
            return redirect()->to('/login')->withInput();
        }

        // Langkah 6: Jika password benar, cek status akun
        if ($user['status'] !== 'aktif') {
            // Cek jika akun terkunci karena brute force sebelumnya
            if ($failedAttempts >= $maxAttempts) { // Sudah dihandle di atas, tapi sebagai jaring pengaman
                session()->setFlashdata('error', 'Akun Anda terkunci karena terlalu banyak percobaan login gagal.');
            } else {
                session()->setFlashdata('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
            }
            return redirect()->to('/login')->withInput();
        }

        // Langkah 7: Login BERHASIL - Catat sukses dan bersihkan percobaan gagal
        $this->loginAttemptModel->insert([
            'ip_address'    => $ipAddress,
            'email_attempt' => $email,
            'time_attempt'  => date('Y-m-d H:i:s'),
            'success'       => 1,
            'user_id'       => $user['id']
        ]);
        // Hapus semua percobaan gagal sebelumnya untuk user ini setelah berhasil login
        $this->loginAttemptModel->where('user_id', $user['id'])->where('success', 0)->delete();


        // Langkah 8: Ambil data hak fitur terkait dan set session
        $hakFitur = $this->hakFiturModel->where('id_user', $user['id'])->first();

        // Pastikan role_jabatan selalu lowercase atau null jika kosong dari DB
        $role_access_jabatan = !empty($user['role_jabatan']) ? strtolower($user['role_jabatan']) : null;

        $sessionData = [
            'user_id'           => $user['id'],
            'user_name'         => $user['name'],
            'user_email'        => $user['email'],
            'role_access'         => $user['role_access'],
            'role_access_jabatan' => $role_access_jabatan, // Simpan role_jabatan ke session
            'auth_data'         => $hakFitur, // Simpan data hak fitur (bisa NULL)
            'isLoggedIn'        => TRUE
        ];
        $session->set($sessionData);

        // Redirect ke dashboard dengan pesan sukses
        session()->setFlashdata('success_login', 'Selamat datang kembali, ' . $user['name'] . '!');
        return redirect()->to('/dashboard'); // Ganti /dashboard jika defaultnya bukan itu
    }

    public function logout()
    {
        // --- PERBAIKAN DI SINI ---
        // Jika Anda menggunakan isAskiLoggedIn di Aplikasi ASKI, hapus yang ini.
        // Jika Anda menggunakan isLoggedIn di Aplikasi EVI, hapus yang itu.
        // Asumsi ini adalah logout untuk EVI.
        session()->remove('user_id');
        session()->remove('user_name');
        session()->remove('user_email');
        session()->remove('role_access');
        session()->remove('auth_data');
        session()->remove('role_access_jabatan');
        session()->remove('isLoggedIn'); // Clear penanda login utama

        // Jangan gunakan session()->destroy(); karena menghapus semua.
        // Cukup hapus key yang relevan.

        return redirect()->to('/login')->with('success', 'Anda telah berhasil logout.');
    }
}
