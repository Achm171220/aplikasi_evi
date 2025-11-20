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
    protected $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1dWlkIjoiY2QxYjU4ZmMtMDdlYi00NmIyLWIyM2MtMzUxZmZmZTNmNTllIiwibmFtYV9hcGxpa2FzaSI6IkVWSSAoRXZhbHVhc2kgSW50ZXJuYWwgS2VhcnNpcGFuKSIsInVzZXJuYW1lIjoiLSIsImlhdCI6MTc1ODc4NzY4MSwiaXNzIjoiIyMkLjRwMVIzZjNyM241aS4kIyMifQ.0D727o2kTeLKPDW5xjMT1qvhz8LKSHVx9NFkixI7PSw';

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

        // ======== STEP 1: Autentikasi via API Eksternal dengan Retry Mechanism ========
        $apiData = $this->authenticateWithExternalAPI($username, $password);

        if ($apiData === false) {
            return redirect()->back()->withInput()->with('error', 'Login gagal. Tidak dapat terhubung ke server otentikasi.');
        }

        if ($apiData === null) {
            return redirect()->back()->withInput()->with('error', 'Login gagal. Periksa kembali kredensial Anda.');
        }

        // ======== STEP 2: Provisioning ke Database Lokal ========
        $provisionResult = $this->provisionUserToLocal($apiData, $username);

        if (!$provisionResult['success']) {
            return redirect()->back()->with('error', $provisionResult['message']);
        }

        $updatedUser = $provisionResult['user'];
        $localUserId = $updatedUser['id'];

        // ======== STEP 3: Hak Akses / Hak Fitur ========
        $accessConfig = $this->checkUserAccessConfiguration($localUserId, $updatedUser['role_access']);

        // ======== STEP 4: Buat Session ========
        $sessionData = [
            'id'            => $updatedUser['id'],
            'name'          => $updatedUser['name'],
            'nip'           => $updatedUser['nip'],
            'email'         => $updatedUser['email'],
            'role_access'   => $updatedUser['role_access'] ?? 'user',
            'role_jabatan'  => $updatedUser['role_jabatan'] ?? null,
            'jabatan_api'   => $updatedUser['nama_jabatan_api'],
            'api_token'     => $updatedUser['api_token'],
            'isLoggedIn'    => true,
            'is_configured' => $accessConfig['is_configured'],
            'auth_data'     => $accessConfig['auth_data'],
        ];

        $session->set($sessionData);

        // Redirect based on configuration status
        if (!$accessConfig['is_configured'] && $updatedUser['role_access'] !== 'superadmin') {
            return redirect()->to(base_url('dashboard/unconfigured'))
                ->with('warning', 'Akun aktif, namun belum dikonfigurasi unit kerja.');
        }

        return redirect()->to(base_url('dashboard'))->with('success', 'Selamat datang, ' . $updatedUser['name']);
    }

    /**
     * Authenticate with external API with retry mechanism
     * 
     * @param string $username
     * @param string $password
     * @return array|false|null Returns array on success, false on connection error, null on auth failure
     */
    private function authenticateWithExternalAPI($username, $password)
    {
        $maxRetries = 3;
        $retryDelay = 1; // seconds
        $timeout = 15; // increased timeout

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                log_message('info', "API authentication attempt {$attempt} for user: {$username}");

                // Create new client instance for each attempt
                $client = \Config\Services::curlrequest([
                    'timeout' => $timeout,
                    'connect_timeout' => 10,
                    'http_errors' => false, // Don't throw exceptions on HTTP errors
                    'verify' => true, // Verify SSL certificate
                ]);

                $response = $client->post($this->apiUrl, [
                    'json' => [
                        'username' => $username,
                        'password' => $password
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'User-Agent' => 'CodeIgniter4-App/1.0'
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                $responseBody = json_decode($response->getBody(), true);

                // Log response for debugging
                log_message('info', "API response status: {$statusCode}");

                // Check for successful response
                if ($statusCode === 200) {
                    if (!isset($responseBody['status']) || strtolower($responseBody['status']) !== 'success') {
                        log_message('warning', 'API authentication failed for user: ' . $username);
                        return null; // Authentication failed
                    }

                    if (empty($responseBody['data']['user_info'])) {
                        log_message('error', 'API response missing user_info');
                        return null;
                    }

                    // Extract data
                    $apiData = $responseBody['data']['user_info'];
                    $apiData['api_token'] = $responseBody['data']['api_token'] ?? null;

                    log_message('info', "API authentication successful for user: {$username}");
                    return $apiData;
                }

                // Handle specific HTTP errors
                if ($statusCode === 401 || $statusCode === 403) {
                    log_message('warning', "API authentication denied (HTTP {$statusCode}) for user: {$username}");
                    return null; // Don't retry on authentication errors
                }

                // For server errors (5xx), retry
                if ($statusCode >= 500) {
                    log_message('error', "API server error (HTTP {$statusCode}), attempt {$attempt}/{$maxRetries}");
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        continue;
                    }
                }

                // Other errors
                log_message('error', "Unexpected API response (HTTP {$statusCode})");
                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                    continue;
                }
            } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {
                log_message('error', "HTTP Exception on attempt {$attempt}: " . $e->getMessage());
                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                    continue;
                }
            } catch (\Throwable $e) {
                log_message('error', "API Connection Error on attempt {$attempt}: " . $e->getMessage());
                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                    continue;
                }
            }
        }

        // All retries failed
        log_message('error', "All API authentication attempts failed for user: {$username}");
        return false;
    }

    /**
     * Provision user to local database
     * 
     * @param array $apiData
     * @param string $username
     * @return array
     */
    private function provisionUserToLocal($apiData, $username)
    {
        try {
            $nip = isset($apiData['nipbaru']) ? str_replace(' ', '', $apiData['nipbaru']) : '';
            $name = $apiData['name'] ?? $username;
            $email = $apiData['email'] ?? '';
            $jabatan = trim(($apiData['jabatan'] ?? '') . ' - ' . ($apiData['namaunit'] ?? ''));
            $apiToken = $apiData['api_token'] ?? null;

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
                // Insert new user
                $userPayload['role_access'] = 'user';

                if (!$this->userModel->insert($userPayload)) {
                    log_message('error', 'Failed to insert user: ' . json_encode($userPayload));
                    return [
                        'success' => false,
                        'message' => 'Gagal membuat akun lokal. Hubungi administrator.'
                    ];
                }

                $localUserId = $this->userModel->getInsertID();
                log_message('info', "New user created with ID: {$localUserId}");
            } else {
                // Update existing user
                $localUserId = $localUser['id'];

                if (!$this->userModel->update($localUserId, $userPayload)) {
                    log_message('warning', 'Failed to update user ID: ' . $localUserId);
                }

                log_message('info', "User updated with ID: {$localUserId}");
            }

            // Fetch updated user data
            $updatedUser = $this->userModel->find($localUserId);

            if (!$updatedUser) {
                return [
                    'success' => false,
                    'message' => 'User lokal tidak ditemukan setelah sinkronisasi.'
                ];
            }

            if ($updatedUser['status'] !== 'aktif') {
                return [
                    'success' => false,
                    'message' => 'Akun Anda non-aktif. Hubungi administrator.'
                ];
            }

            return [
                'success' => true,
                'user' => $updatedUser
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error provisioning user: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data pengguna.'
            ];
        }
    }

    /**
     * Check user access configuration
     * 
     * @param int $userId
     * @param string $role
     * @return array
     */
    private function checkUserAccessConfiguration($userId, $role)
    {
        try {
            $hakFitur = $this->hakFiturModel->getHakFiturByUserId($userId);

            $authData = [
                'id_es1' => $hakFitur['id_es1'] ?? null,
                'id_es2' => $hakFitur['id_es2'] ?? null,
                'id_es3' => $hakFitur['id_es3'] ?? null,
            ];

            $isConfigured = false;

            if (in_array($role, ['superadmin', 'guest', 'manager'])) {
                $isConfigured = true;
            } elseif ($role === 'admin' && !empty($authData['id_es2'])) {
                $isConfigured = true;
            } elseif ($role === 'user' && !empty($authData['id_es3'])) {
                $isConfigured = true;
            }

            return [
                'is_configured' => $isConfigured,
                'auth_data' => $authData
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error checking access configuration: ' . $e->getMessage());
            return [
                'is_configured' => false,
                'auth_data' => [
                    'id_es1' => null,
                    'id_es2' => null,
                    'id_es3' => null,
                ]
            ];
        }
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
