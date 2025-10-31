<?php

namespace App\Controllers;

use CodeIgniter\HTTP\CURLRequest;
use App\Models\UserModel;
use App\Models\HakFiturModel;
use App\Models\UnitKerjaEs2Model; // Diperlukan untuk sinkronisasi nama ES2
use App\Models\UnitKerjaEs3Model; // Diperlukan untuk sinkronisasi nama ES3

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

    // --- Tampilan dan Proses Login ---

    /**
     * Menampilkan form login.
     */
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }
        $data = [
            'title' => 'Login',
            'validation' => \Config\Services::validation(),
            'session' => session()
        ];
        return view('auth/login_form', $data);
    }

    /**
     * Memproses otentikasi login, Provisioning (Create/Update), dan mengecek Hak Fitur.
     */
    public function attemptLogin()
    {
        $session = session();

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Username dan Password harus diisi.');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // --- 1. Otentikasi API Eksternal ---
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

            if ($statusCode !== 200 || !isset($responseBody['status']) || $responseBody['status'] !== 'Success') {
                return redirect()->back()->withInput()->with('error', 'Kredensial tidak valid atau API sedang bermasalah.');
            }

            if (!isset($responseBody['data']['user_info'])) {
                return redirect()->back()->withInput()->with('error', 'Format respons API tidak sesuai: user_info tidak ditemukan.');
            }

            $apiData = $responseBody['data']['user_info'];
            $apiToken = $responseBody['data']['api_token'] ?? null;
        } catch (\Exception $e) {
            log_message('error', 'API Connection Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal terhubung ke layanan otentikasi eksternal BPKP.');
        }

        // --- 1.5. Sanitasi Data NIP ---
        $rawNip = $apiData['nipbaru'] ?? '';
        $sanitizedNip = str_replace(' ', '', $rawNip);

        // --- 2. LOGIKA PROVISIONING (CREATE atau UPDATE) ---

        $apiName = $apiData['name'];
        $localUser = $this->userModel->getUserByMappedName($apiName);

        $localUserId = null;
        $updatedUser = null;

        // Payload data yang disinkronisasi ke DB users
        $userPayload = [
            'nip'                => $sanitizedNip,
            'name'               => $apiName,
            'email'              => $apiData['email'],
            'api_token'          => $apiToken,
            'nama_jabatan_api'   => $apiData['jabatan'] . ' - ' . ($apiData['namaunit'] ?? 'Unit Tidak Diketahui'),
            'status'             => 'aktif',
        ];

        if (!$localUser) {
            // A. CREATE: User baru
            $userPayload['role_access'] = 'user'; // Default role_access

            if ($this->userModel->insert($userPayload)) {
                $localUserId = $this->userModel->insertID();
                $session->setFlashdata('notification', 'Akun lokal berhasil dibuat secara otomatis.');
            } else {
                return redirect()->back()->with('error', 'Gagal membuat akun lokal. Hubungi administrator.');
            }
        } else {
            // B. UPDATE: User sudah ada
            $localUserId = $localUser['id'];

            // Kita hanya update field yang datang dari API (NIP, email, token, jabatan API)
            if ($this->userModel->update($localUserId, $userPayload) === false) {
                log_message('error', 'Failed to update local user: ' . $localUserId);
            }
        }

        // Ambil data user lengkap yang sudah di CREATE/UPDATE
        $updatedUser = $this->userModel->find($localUserId);
        $userRole = $updatedUser['role_access'];

        // Cek status lokal
        if ($updatedUser['status'] !== 'aktif') {
            return redirect()->back()->with('error', 'Akun Lokal Anda saat ini berstatus non-aktif. Hubungi administrator.');
        }

        // --- 3. Cek Konfigurasi Unit Kerja (Hak Fitur) ---

        $hakFitur = $this->hakFiturModel->getHakFiturByUserId($localUserId);
        $isConfigured = false;
        $authData = [];

        if ($hakFitur) {
            $id_es1 = $hakFitur['id_es1'] ?? null;
            $id_es2 = $hakFitur['id_es2'] ?? null;
            $id_es3 = $hakFitur['id_es3'] ?? null;

            $authData = [
                'id_es1' => $id_es1,
                'id_es2' => $id_es2,
                'id_es3' => $id_es3,
            ];

            // Logika pengecekan konfigurasi (Kapan user dianggap 'terkonfigurasi'?)
            if ($userRole === 'superadmin') {
                $isConfigured = true;
            } elseif ($userRole === 'guest') {
                $isConfigured = true;
            } elseif ($userRole === 'admin' && !empty($id_es2)) {
                $isConfigured = true;
            } elseif ($userRole === 'user' && !empty($id_es3)) {
                $isConfigured = true;
            }
        }

        // --- 4. Buat Sesi Lokal LENGKAP ---

        $userData = [
            'id'                 => $updatedUser['id'], // KUNCI FILTERING
            'name'               => $updatedUser['name'],
            'nip'                => $updatedUser['nip'],
            'email'              => $updatedUser['email'],

            // Data Otorisasi LOKAL
            'role_access'        => $userRole,
            'role_jabatan'       => $updatedUser['role_jabatan'], // ENUM lokal

            // Data Sinkronisasi API
            'jabatan_api'        => $updatedUser['nama_jabatan_api'],
            'api_token'          => $updatedUser['api_token'],

            // Status dan Hak Fitur
            'isLoggedIn'         => TRUE,
            'is_configured'      => $isConfigured,
            'auth_data'          => $authData,
        ];

        $session->set($userData);

        if (!$isConfigured && $userRole !== 'superadmin') {
            return redirect()->to(base_url('dashboard/unconfigured'))->with('warning', 'Akun Anda aktif, namun akses data dibatasi karena unit kerja belum diatur oleh Administrator Sistem.');
        }

        return redirect()->to(base_url('dashboard'))->with('success', 'Selamat datang, ' . $updatedUser['name']);
    }

    // --- METODE LAINNYA ---

    public function unconfiguredDashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        if ($session->get('is_configured') === TRUE) {
            return redirect()->to(base_url('dashboard'));
        }

        $data['title'] = 'Akses Terbatas';
        $data['username'] = $session->get('name');
        return view('dashboard/unconfigured', $data);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('info', 'Anda telah berhasil logout.');
    }
}
