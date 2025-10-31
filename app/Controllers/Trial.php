<?php

// app/Controllers/ItemAktifController.php

namespace App\Controllers;

use App\Models\ItemAktifModel;
use CodeIgniter\Controller;

class Trial extends Controller
{
    protected $itemAktifModel;

    // URL API
    protected $simaApiUrl = 'https://api-stara.bpkp.go.id/api/sima/pkau/laporan';
    protected $sadewaApiUrl = 'https://api-stara.bpkp.go.id/api/surat-masuk';

    // Token Anda
    private $apiToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1dWlkIjoiY2QxYjU4ZmMtMDdlYi00NmIyLWIyM2MtMzUxZmZmZTNmNTllIiwibmFtYV9hcGxpa2FzaSI6IkVWSSAoRXZhbHVhc2kgSW50ZXJuYWwgS2VhcnNpcGFuKSIsInVzZXJuYW1lIjoiLSIsImlhdCI6MTc1ODc4NzY4MSwiaXNzIjoiIyMkLjRwMVIzZjNyM241aS4kIyMifQ.0D727o2kTeLKPDW5xjMT1qvhz8LKSHVx9NFkixI7PSw';

    public function __construct()
    {
        $this->itemAktifModel = new ItemAktifModel();
    }

    /**
     * Menampilkan form input data dan memastikan variabel $validation tersedia.
     */
    public function create()
    {
        $data['title'] = 'Input Data Item Aktif';
        // Pastikan $validation selalu didefinisikan untuk menghindari error di view
        $data['validation'] = \Config\Services::validation();
        return view('trial/form', $data);
    }

    /**
     * Endpoint API untuk mencari data SIMA (PKAU Laporan).
     * Filter berdasarkan keyword dan tahun (thang).
     */
    public function searchSimaApi()
    {
        $keyword = $this->request->getVar('term');
        $tahun_filter = $this->request->getVar('tahun_filter');

        if (empty($keyword) || strlen($keyword) < 3 || empty($tahun_filter)) {
            return $this->response->setJSON(['results' => []]);
        }

        $client = \Config\Services::curlrequest();

        try {
            $response = $client->request('GET', $this->simaApiUrl, [
                'headers' => ['Authorization' => 'Bearer ' . $this->apiToken, 'Accept' => 'application/json'],
                'query' => [
                    'keyword' => $keyword,
                    'thang' => $tahun_filter, // Konfirmasi parameter SIMA adalah 'thang'
                ],
            ]);

            // ... (sisa logika decoding JSON dan filtering manual) ...
            // (Logika ini tetap dipertahankan untuk memastikan hasil akurat, meskipun API sudah difilter tahun)

            $apiData = json_decode($response->getBody(), true);

            if (!isset($apiData['data']) || !is_array($apiData['data'])) {
                return $this->response->setJSON(['results' => []]);
            }

            $results = [];
            $lowerKeyword = strtolower($keyword);

            foreach ($apiData['data'] as $item) {
                // ... (filtering manual tetap sama) ...
                if (isset($item['thang']) && $item['thang'] != $tahun_filter) {
                    continue;
                }

                $nomorLaporan = strtolower($item['nomor_laporan'] ?? '');
                $keterangan = strtolower($item['keterangan_penugasan'] ?? '');

                if (
                    strpos($nomorLaporan, $lowerKeyword) !== false ||
                    strpos($keterangan, $lowerKeyword) !== false
                ) {

                    $results[] = [
                        'id'   => $item['nomor_laporan'],
                        'text' => $item['nomor_laporan'] . ' - ' . $item['keterangan_penugasan'],
                        'data_full' => $item
                    ];
                }
            }
            return $this->response->setJSON(['results' => $results]);
        } catch (\Exception $e) {
            log_message('error', 'API SIMA Error: ' . $e->getMessage());
            return $this->response->setJSON(['results' => []]);
        }
    }

    /**
     * Endpoint API untuk mencari data SADEWA (Surat Masuk).
     * Filter berdasarkan keyword dan tahun (tahun).
     */
    public function searchSadewaApi()
    {
        $keyword = $this->request->getVar('term');
        $tahun_filter = $this->request->getVar('tahun_filter');

        if (empty($keyword) || strlen($keyword) < 3 || empty($tahun_filter)) {
            return $this->response->setJSON(['results' => []]);
        }

        $client = \Config\Services::curlrequest();

        try {
            $response = $client->request('GET', $this->sadewaApiUrl, [
                'headers' => ['Authorization' => 'Bearer ' . $this->apiToken, 'Accept' => 'application/json'],
                'query' => [
                    'tahun' => $tahun_filter,
                    'row' => 100, // Menambah jumlah baris data yang diambil per request
                    // Catatan: Tidak mengirim 'keyword' ke API karena tidak terlihat di docs
                ],
            ]);

            $apiData = json_decode($response->getBody(), true);

            if (!isset($apiData['data']) || !is_array($apiData['data'])) {
                return $this->response->setJSON(['results' => []]);
            }

            $results = [];
            $lowerKeyword = strtolower($keyword);

            // Filter Manual: Menggunakan field 'nomor_surat' dan 'perihal'
            foreach ($apiData['data'] as $item) {
                // Konfirmasi: nomor_surat dan perihal (sesuai JSON)
                $nomorSurat = strtolower($item['nomor_surat'] ?? '');
                $perihal = strtolower($item['perihal'] ?? '');

                // Filter data yang diterima berdasarkan keyword yang diketik user (filtering akurasi)
                if (
                    strpos($nomorSurat, $lowerKeyword) !== false ||
                    strpos($perihal, $lowerKeyword) !== false
                ) {

                    $results[] = [
                        'id'   => $item['nomor_surat'],
                        'text' => $item['nomor_surat'] . ' - ' . $item['perihal'],
                        'data_full' => $item
                    ];
                }
            }

            return $this->response->setJSON(['results' => $results]);
        } catch (\Exception $e) {
            log_message('error', 'API SADEWA Error: ' . $e->getMessage());
            return $this->response->setJSON(['results' => []]);
        }
    }


    /**
     * Menyimpan data yang diinput ke database lokal.
     */
    public function store()
    {
        // Aturan validasi
        $rules = [
            'judul_dokumen' => 'required|min_length[5]',
            'no_dokumen'    => 'required|min_length[3]',
            'tgl_dokumen'   => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $data = [
            'judul_dokumen' => $this->request->getPost('judul_dokumen'),
            'no_dokumen'    => $this->request->getPost('no_dokumen'),
            'tgl_dokumen'   => $this->request->getPost('tgl_dokumen'),
        ];

        if ($this->itemAktifModel->save($data)) {
            return redirect()->to('/itemaktif/create')->with('success', 'Data berhasil disimpan!');
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan data.');
        }
    }
}
