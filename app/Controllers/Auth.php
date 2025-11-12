<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\HakFiturModel;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;

class Auth extends BaseController
{
    protected $userModel;
    protected $hakFiturModel;
    protected $unitKerjaEs2Model;
    protected $unitKerjaEs3Model;
    protected $apiUrl = 'https://api-stara.bpkp.go.id/api/auth/login';

    public function __construct()
    {
        helper(['form', 'url', 'session']);
        $this->userModel = new UserModel();
        $this->hakFiturModel = new HakFiturModel();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/login_form', [
            'title' => 'Login',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function attemptLogin()
    {
        $session = session();

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Username dan Password harus diisi.');
        }

        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));

        // ======== STEP 1: Autentikasi via API Eksternal ========
        $client = service('curlrequest', ['timeout' => 8]);
        $apiData = null;
        $apiToken = null;

        try {
            $response = $client->post($this->apiUrl, [
                'json' => ['username' => $username, 'password' => $password],
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            if ($statusCode !== 200) {
                log_message('error', 'API returned status: ' . $statusCode);
                return redirect()->back()->with('error', 'Server otentikasi sedang bermasalah.');
            }

            if (!isset($responseBody['status']) || strtolower($responseBody['status']) !== 'success') {
                log_message('error', 'API status failed: ' . json_encode($responseBody));
                return redirect()->back()->withInput()->with('error', 'Login gagal. Periksa kembali kredensial Anda.');
            }

            if (empty($responseBody['data']['user_info'])) {
                log_message('error', 'API response missing user_info: ' . json_encode($responseBody));
                return redirect()->back()->with('error', 'Data pengguna tidak ditemukan pada API.');
            }

            $apiData = $responseBody['data']['user_info'];
            $apiToken = $responseBody['data']['api_token'] ?? null;
        } catch (\Throwable $e) {
            log_message('error', 'API Connection Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Tidak dapat terhubung ke API otentikasi eksternal.');
        }

        // ======== STEP 2: Provisioning ke Database Lokal ========
        $nip = isset($apiData['nipbaru']) ? str_replace(' ', '', $apiData['nipbaru']) : '';
        $name = $apiData['name'] ?? $username;
        $email = $apiData['email'] ?? '';
        $jabatan = trim(($apiData['jabatan'] ?? '') . ' - ' . ($apiData['namaunit'] ?? ''));

        // pastikan method model ini benar-benar ada
        $localUser = $this->userModel->getUserByMappedName($name);

        $userPayload = [
            'nip'              => $nip,
            'name'             => $name,
            'email'            => $email,
            'api_token'        => $apiToken,
            'nama_jabatan_api' => $jabatan,
            'status'           => 'aktif',
        ];

        if (!$localUser) {
            // Insert user baru
            $userPayload['role_access'] = 'user';
            if (!$this->userModel->insert($userPayload)) {
                log_message('error', 'Failed to insert user: ' . json_encode($userPayload));
                return redirect()->back()->with('error', 'Gagal membuat akun lokal. Hubungi administrator.');
            }
            $localUserId = $this->userModel->getInsertID();
        } else {
            // Update data user yang sudah ada
            $localUserId = $localUser['id'];
            if (!$this->userModel->update($localUserId, $userPayload)) {
                log_message('error', 'Failed to update user: ID ' . $localUserId);
            }
        }

        $updatedUser = $this->userModel->find($localUserId);
        if (!$updatedUser) {
            return redirect()->back()->with('error', 'User lokal tidak ditemukan setelah sinkronisasi.');
        }

        if ($updatedUser['status'] !== 'aktif') {
            return redirect()->back()->with('error', 'Akun Anda non-aktif. Hubungi administrator.');
        }

        // ======== STEP 3: Hak Akses / Hak Fitur ========
        $hakFitur = $this->hakFiturModel->getHakFiturByUserId($localUserId);
        $isConfigured = false;
        $authData = [
            'id_es1' => $hakFitur['id_es1'] ?? null,
            'id_es2' => $hakFitur['id_es2'] ?? null,
            'id_es3' => $hakFitur['id_es3'] ?? null,
        ];

        $role = $updatedUser['role_access'] ?? 'user';

        if ($role === 'superadmin' || $role === 'guest' || $role === 'manager') {
            $isConfigured = true;
        } elseif ($role === 'admin' && !empty($authData['id_es2'])) {
            $isConfigured = true;
        } elseif ($role === 'user' && !empty($authData['id_es3'])) {
            $isConfigured = true;
        }

        // ======== STEP 4: Buat Session ========
        $sessionData = [
            'id'            => $updatedUser['id'],
            'name'          => $updatedUser['name'],
            'nip'           => $updatedUser['nip'],
            'email'         => $updatedUser['email'],
            'role_access'   => $role,
            'role_jabatan'  => $updatedUser['role_jabatan'] ?? null,
            'jabatan_api'   => $updatedUser['nama_jabatan_api'],
            'api_token'     => $updatedUser['api_token'],
            'isLoggedIn'    => true,
            'is_configured' => $isConfigured,
            'auth_data'     => $authData,
        ];

        $session->set($sessionData);

        if (!$isConfigured && $role !== 'superadmin') {
            return redirect()->to(base_url('dashboard/unconfigured'))
                ->with('warning', 'Akun aktif, namun belum dikonfigurasi unit kerja.');
        }

        return redirect()->to(base_url('dashboard'))->with('success', 'Selamat datang, ' . $updatedUser['name']);
    }

    public function unconfiguredDashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        if ($session->get('is_configured') === true) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('dashboard/unconfigured', [
            'title' => 'Akses Terbatas',
            'username' => $session->get('name'),
        ]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('info', 'Anda telah logout.');
    }
}
