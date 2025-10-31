<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;
use CodeIgniter\HTTP\CURLRequest;

class Profil extends BaseController
{
    protected $userModel;
    protected $unitKerjaEs1Model;
    protected $unitKerjaEs2Model;
    protected $unitKerjaEs3Model;

    // Gunakan environment variable atau simpan di config jika ini token statis
    private $apiToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1dWlkIjoiY2QxYjU4ZmMtMDdlYi00NmIyLWIyM2MtMzUxZmZmZTNmNTllIiwibmFtYV9hcGxpa2FzaSI6IkVWSSAoRXZhbHVhc2kgSW50ZXJuYWwgS2VhcnNpcGFuKSIsInVzZXJuYW1lIjoiLSIsImlhdCI6MTc1ODc4NzY4MSwiaXNzIjoiIyMkLjRwMVIzZjNyM241aS4kIyMifQ.0D727o2kTeLKPDW5xjMT1qvhz8LKSHVx9NFkixI7PSw';
    private $apiUrlPegawai = 'https://api-stara.bpkp.go.id/api/pegawai';

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->unitKerjaEs1Model = new UnitKerjaEs1Model();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
        helper('form');
    }

    /**
     * Metode untuk mengambil detail pegawai dari API Stara berdasarkan NIP
     */
    private function getPegawaiDetailFromApi(string $nip): array
    {
        $client = \Config\Services::curlrequest([
            'timeout' => 15,
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept'        => 'application/json',
            ],
        ]);

        // PENTING: Gunakan NIP yang sudah pasti bersih dari spasi
        $nipBersih = str_replace(' ', '', $nip);

        try {
            $response = $client->get($this->apiUrlPegawai, [
                'query' => [
                    'status' => 'Aktif',
                    'search' => $nipBersih, // Cari berdasarkan NIP bersih
                    'limit'  => 1,
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                if (isset($body['data']['result'][0])) {
                    return $body['data']['result'][0];
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'API Profil Connection Error: ' . $e->getMessage());
        }

        return [];
    }

    public function index()
    {
        $session = session();

        // Pastikan user sudah login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        $authData = $session->get('auth_data');
        $namaUnitKerjaLokal = 'Belum Diatur';
        $levelUnitKerja = 'Tidak Terikat';

        // Data dari Sesi (sudah disinkronisasi saat login)
        $nipSession = $session->get('nip');
        $emailSession = $session->get('email');
        $namaSession = $session->get('name');

        // 1. Panggil API untuk data profil lengkap menggunakan NIP bersih dari sesi
        $pegawaiData = $this->getPegawaiDetailFromApi($nipSession);

        // 2. Fallback ke data sesi jika API gagal
        if (empty($pegawaiData)) {
            session()->setFlashdata('warning', 'Gagal memuat detail profil dari API eksternal. Menampilkan data lokal.');
            $pegawaiData = [
                'nama'          => $namaSession,
                's_email_dinas' => $emailSession,
                'nipbaru'       => $nipSession,
                's_jabatan'     => $session->get('jabatan_api'), // Gunakan data sinkronisasi API dari sesi
                'namaunit'      => 'Data API Gagal Dimuat',
            ];
        }

        // 3. Tentukan nama dan level unit kerja lokal (Berdasarkan Hak Fitur)
        if (!empty($authData)) {
            if (!empty($authData['id_es3'])) {
                $unit = $this->unitKerjaEs3Model->find($authData['id_es3']);
                $namaUnitKerjaLokal = $unit['nama_es3'] ?? 'ID ES3 Tidak Ditemukan';
                $levelUnitKerja = 'Eselon 3';
            } elseif (!empty($authData['id_es2'])) {
                $unit = $this->unitKerjaEs2Model->find($authData['id_es2']);
                $namaUnitKerjaLokal = $unit['nama_es2'] ?? 'ID ES2 Tidak Ditemukan';
                $levelUnitKerja = 'Eselon 2';
            } elseif (!empty($authData['id_es1'])) {
                $unit = $this->unitKerjaEs1Model->find($authData['id_es1']);
                $namaUnitKerjaLokal = $unit['nama_es1'] ?? 'ID ES1 Tidak Ditemukan';
                $levelUnitKerja = 'Eselon 1';
            }
        }

        // Jika belum dikonfigurasi
        if ($session->get('is_configured') === FALSE) {
            $namaUnitKerjaLokal = 'Akses Terbatas / Belum dikonfigurasi';
            $levelUnitKerja = 'Belum Ditetapkan';
        }


        $data = [
            'title'          => 'Profil Pengguna',
            'session'        => $session,
            'validation'     => \Config\Services::validation(),

            // Data API/Pegawai
            'pegawai_nama'      => $pegawaiData['nama'] ?? $namaSession,
            'pegawai_email'     => $pegawaiData['s_email_dinas'] ?? $emailSession,
            'pegawai_nip'       => $pegawaiData['nipbaru'] ?? $nipSession,
            'pegawai_jabatan'   => $pegawaiData['s_jabatan'] ?? $pegawaiData['jabatan'] ?? $session->get('jabatan_api'),
            'pegawai_unit_api'  => $pegawaiData['namaunit'] ?? 'Tidak Tersedia',

            // Data Hak Akses Lokal
            'role_access_lokal'     => $session->get('role_access'),
            'role_jabatan_fungsional' => $session->get('role_jabatan'),
            'nama_unit_kerja_lokal' => $namaUnitKerjaLokal,
            'level_unit_kerja'      => $levelUnitKerja,
        ];

        return view('users/profil', $data);
    }
}
