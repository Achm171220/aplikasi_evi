<?php

namespace App\Controllers;

// Panggil semua model yang dibutuhkan di satu tempat
use App\Models\ItemAktifModel;
use App\Models\BerkasAktifModel;
use App\Models\ItemInaktifModel;
use App\Models\BerkasInaktifModel;
use App\Models\UserModel;
use App\Models\KlasifikasiModel;
use App\Models\JenisNaskahModel;
use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;
use App\Models\NilaiPengawasanModel; // Pastikan ini di-use

class Dashboard extends BaseController
{
    protected $itemAktifModel;
    protected $berkasAktifModel;
    protected $itemInaktifModel;
    protected $berkasInaktifModel;
    protected $unitKerjaEs3Model;
    protected $unitKerjaEs2Model;
    protected $userModel;

    public function __construct()
    {
        $this->itemAktifModel = new ItemAktifModel();
        $this->berkasAktifModel = new BerkasAktifModel();
        $this->itemInaktifModel = new ItemInaktifModel();
        $this->berkasInaktifModel = new BerkasInaktifModel();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $session = session();
        $userRole = $session->get('role_access');
        $userJabatan = $session->get('role_jabatan'); // Digunakan untuk guest/pimpinan
        $data = ['title' => 'Dashboard'];

        // 1. Cek Konfigurasi
        if ($session->get('is_configured') === FALSE) {
            // Jika user login tapi belum dikonfigurasi, alihkan ke halaman khusus
            return redirect()->to(base_url('dashboard/unconfigured'));
        }

        // --- ROLE GUEST / PIMPINAN ---
        if ($userRole === 'guest' && $userJabatan === 'pimpinan') {
            $data['pageTitle'] = 'Dashboard Pemantauan Kearsipan (Pimpinan)';
            $data = array_merge($data, $this->getPimpinanDashboardData());
            return view('dashboard/pimpinan', $data);
        }

        // --- ROLE SUPERADMIN ---
        elseif ($userRole === 'superadmin') {
            $data['pageTitle'] = 'Dashboard Super Administrator';
            // Asumsi getSuperadminDashboardData() ada
            $data = array_merge($data, $this->getSuperadminDashboardData());
            return view('dashboard/superadmin', $data);
        }

        // --- ROLE ADMIN ---
        elseif ($userRole === 'admin') {
            $data['pageTitle'] = 'Dashboard Administrator';
            // Asumsi getAdminDashboardData() ada
            $data = array_merge($data, $this->getAdminDashboardData());
            return view('dashboard/admin', $data);
        }

        // --- ROLE USER (default) ---
        else {
            $data['pageTitle'] = 'Dashboard Pengguna';
            $data = array_merge($data, $this->getUserDashboardData(session()->get('id')));
            return view('dashboard/user', $data);
        }
    }

    private function getPimpinanDashboardData()
    {
        $db = \Config\Database::connect();
        $currentYear = date('Y');
        $nilaiPengawasanModel = new NilaiPengawasanModel();

        // 1. Ambil Data Nilai Pengawasan Tahun Ini
        // Kunci: Ambil nilai terbaru (skor dan kategori) per ES2 untuk tahun berjalan
        $nilaiNP = $db->table('nilai_pengawasan')
            ->select('id_es2, skor, kategori, tahun')
            ->where('tahun', $currentYear)
            // Jika ada duplikasi data, ambil yang id-nya terbesar (terbaru diinput)
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();
        $nilaiMap = [];
        foreach ($nilaiNP as $n) {
            // Gunakan array mapping untuk memastikan hanya satu nilai per id_es2 (yang paling baru)
            if (!isset($nilaiMap[$n['id_es2']])) {
                $nilaiMap[$n['id_es2']] = $n;
            }
        }


        // 2. Query Gabungan (Aktif, Inaktif, Berkas, BA Pemindahan Count)
        $query = $db->table('unit_kerja_es2 as es2')
            ->select('
                es2.id, es2.nama_es2,
                es1.kode as kode_es1,
                COUNT(DISTINCT ia.id) as total_aktif,
                COUNT(DISTINCT ia.id_berkas) as total_berkas_aktif,
                COUNT(DISTINCT ii.id) as total_inaktif,
                (
                    SELECT COUNT(id) FROM item_aktif 
                    WHERE status_pindah="ba" AND id_es2=es2.id
                ) as total_ba_pemindahan
            ')
            // JOIN BARU: Ke unit_kerja_es1
            ->join('unit_kerja_es1 as es1', 'es1.id = es2.id_es1', 'left')
            ->join('item_aktif as ia', 'ia.id_es2 = es2.id', 'left')
            ->join('item_inaktif as ii', 'ii.id_es2 = es2.id', 'left')
            // Grouping harus menyertakan es1.kode
            ->groupBy('es2.id, es2.nama_es2, es1.kode')
            ->orderBy('es2.nama_es2', 'ASC')
            ->get()->getResultArray();


        $rekapData = [];
        foreach ($query as $row) {
            $total_item = (int)$row['total_aktif'] + (int)$row['total_inaktif'];

            // Perhitungan Persentase (Perlu Berhati-hati, ini hanya contoh)
            $persentase_aktif = ($row['total_aktif'] > 0)
                ? round(($row['total_berkas_aktif'] / $row['total_aktif']) * 100)
                : 0;

            // Dapatkan Nilai Pengawasan
            $np = $nilaiMap[$row['id']] ?? null;

            $rekapData[] = [
                'id_es2' => $row['id'],
                // KUNCI PERUBAHAN: Gabungkan Kode ES1 dan Nama ES2
                'nama_es2_lengkap' => ($row['kode_es1'] ?? 'N/A') . ' - ' . $row['nama_es2'],
                'total_item_aktif' => (int)$row['total_aktif'],
                'total_item_inaktif' => (int)$row['total_inaktif'],
                'persentase_aktif' => min(100, $persentase_aktif), // Tidak dipakai di view ini, tapi jaga-jaga
                'total_ba_pemindahan' => (int)$row['total_ba_pemindahan'],
                'nilai_pengawasan' => $np['skor'] ?? null,
                'kategori_np' => $np['kategori'] ?? 'N/A',
                'tahun_np' => $np['tahun'] ?? $currentYear,
            ];
        }
        $itemAktifModel = new ItemAktifModel();
        $itemInaktifModel = new ItemInaktifModel();
        $berkasAktifModel = new BerkasAktifModel();
        $berkasInaktifModel = new BerkasInaktifModel();

        // --- DATA GLOBAL (BARU) ---
        $global_total_item_aktif = $itemAktifModel->countAllResults();
        $global_total_item_inaktif = $itemInaktifModel->countAllResults();
        $global_total_berkas_aktif = $berkasAktifModel->countAllResults();
        $global_total_berkas_inaktif = $berkasInaktifModel->countAllResults();


        return [
            // DATA GLOBAL (BARU)
            'global_total_item_aktif' => $global_total_item_aktif,
            'global_total_item_inaktif' => $global_total_item_inaktif,
            'global_total_berkas_aktif' => $global_total_berkas_aktif,
            'global_total_berkas_inaktif' => $global_total_berkas_inaktif,

            // DATA REKAP UNIT (LAMA)
            'rekap_unit' => $rekapData,
            'tahun_data' => $currentYear,
        ];
    }
    private function getSuperadminDashboardData()
    {
        $db = \Config\Database::connect();

        // --- Perhitungan Baris 1: Arsip Aktif ---
        $itemAktifModel = new ItemAktifModel();
        $total_item_aktif = $itemAktifModel->countAllResults();
        $item_diberkaskan_aktif = $itemAktifModel->where('id_berkas IS NOT NULL')->countAllResults();
        $persentase_aktif = ($total_item_aktif > 0) ? ($item_diberkaskan_aktif / $total_item_aktif) * 100 : 0;

        // --- Perhitungan Baris 2: Arsip Inaktif (Ganti dengan model asli jika sudah ada) ---
        $itemInaktifModel = new ItemInaktifModel();
        $total_item_inaktif = $itemInaktifModel->countAllResults();
        $item_diberkaskan_inaktif = $itemInaktifModel->where('id_berkas IS NOT NULL')->countAllResults();
        $persentase_inaktif = ($total_item_inaktif > 0) ? ($item_diberkaskan_inaktif / $total_item_inaktif) * 100 : 0;

        // --- Tabel Rekapitulasi per Eselon 2 ---
        $rekap_per_es2 = $db->table('unit_kerja_es2 as es2')
            ->select('es2.nama_es2, COUNT(item.id) as jumlah_item_aktif') // TODO: Tambah join & count untuk item inaktif
            ->join('item_aktif as item', 'item.id_es2 = es2.id', 'left')
            ->groupBy('es2.id')
            ->orderBy('es2.nama_es2', 'ASC')
            ->get()->getResultArray();

        // Panggil data pemindahan untuk semua user (userId = null)
        $pemindahanData = $this->getPemindahanDashboardData(null);

        $data = [
            // BARIS 1
            'total_item_aktif'      => $total_item_aktif,
            'total_berkas_aktif'    => (new BerkasAktifModel())->countAllResults(),
            'persentase_aktif'      => round($persentase_aktif),
            // BARIS 2 (Asumsi)
            'total_item_inaktif'    => $total_item_inaktif,
            'total_berkas_inaktif'  => (new BerkasInaktifModel())->countAllResults(),
            'persentase_inaktif'    => round($persentase_inaktif),
            // BARIS 3
            'total_klasifikasi'     => (new KlasifikasiModel())->countAllResults(),
            'total_jenis_naskah'    => (new JenisNaskahModel())->countAllResults(),
            'total_users'           => (new UserModel())->countAllResults(),
            'total_es1'             => (new UnitKerjaEs1Model())->countAllResults(),
            'total_es2'             => (new UnitKerjaEs2Model())->countAllResults(),
            'total_es3'             => (new UnitKerjaEs3Model())->countAllResults(),
            // Tabel Bawah
            'rekap_per_es2'         => $rekap_per_es2,
        ];

        return array_merge($data, $pemindahanData);
    }
    private function getPemindahanDashboardData($userId = null, $es2Id = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('item_aktif');

        if ($userId) $builder->where('id_user', $userId);
        if ($es2Id) $builder->where('id_es2', $es2Id);

        return [
            'jumlah_usulan'      => (clone $builder)->where('status_pindah', 'usul_pindah')->countAllResults(),
            'jumlah_ditolak'     => (clone $builder)->where('status_pindah', 'ditolak')->countAllResults(), // Asumsi status 'ditolak'
            'jumlah_diproses'    => (clone $builder)->whereIn('status_pindah', ['verifikasi', 'ba'])->countAllResults(),
            'jumlah_dipindahkan' => 0, // Ini harusnya dihitung dari item_inaktif
        ];
    }

    /**
     * Mengambil data rekapitulasi total untuk Superadmin & Admin.
     */
    private function getAdminDashboardData()
    {
        $session = session();
        $authData = $session->get('auth_data');

        // Cek dasar hak akses Admin
        if (empty($authData) || empty($authData['id_es2'])) {
            return ['nama_es2_admin' => 'Hak Akses Belum Diatur', 'data_kosong' => true];
        }

        $id_es2_admin = $authData['id_es2'];
        $db = \Config\Database::connect();

        // Model Instantiation
        $unitKerjaEs2Model = new \App\Models\UnitKerjaEs2Model();
        $itemAktifModel = new \App\Models\ItemAktifModel();
        $itemInaktifModel = new \App\Models\ItemInaktifModel();
        $berkasAktifModel = new \App\Models\BerkasAktifModel();
        $berkasInaktifModel = new \App\Models\BerkasInaktifModel();
        $nilaiPengawasanModel = new \App\Models\NilaiPengawasanModel();
        $nama_es2_admin = $unitKerjaEs2Model->find($id_es2_admin)['nama_es2'] ?? 'Unit Tidak Ditemukan';

        // --- Perhitungan Data Aktif & Inaktif Lingkup Es2 ---

        // Aktif
        $total_item_aktif = $itemAktifModel->where('id_es2', $id_es2_admin)->countAllResults();
        $item_diberkaskan_aktif = $itemAktifModel->where('id_es2', $id_es2_admin)->where('id_berkas IS NOT NULL')->countAllResults();
        $persentase_aktif = ($total_item_aktif > 0) ? ($item_diberkaskan_aktif / $total_item_aktif) * 100 : 0;

        // Inaktif
        $total_item_inaktif = $itemInaktifModel->where('id_es2', $id_es2_admin)->countAllResults();
        $item_diberkaskan_inaktif = $itemInaktifModel->where('id_es2', $id_es2_admin)->where('id_berkas IS NOT NULL')->countAllResults();
        $persentase_inaktif = ($total_item_inaktif > 0) ? ($item_diberkaskan_inaktif / $total_item_inaktif) * 100 : 0;

        // Total Berkas
        $total_berkas_aktif = $berkasAktifModel->where('id_es2', $id_es2_admin)->countAllResults();
        $total_berkas_inaktif = $berkasInaktifModel->where('id_es2', $id_es2_admin)->countAllResults();

        // --- Rekapitulasi per Eselon 3 (Aktif & Inaktif) ---
        $rekap_per_es3 = $db->table('unit_kerja_es3 as es3')
            ->select('
                es3.nama_es3, 
                COUNT(DISTINCT ia.id) as jumlah_item_aktif,
                COUNT(DISTINCT ii.id) as jumlah_item_inaktif
            ')
            ->join('item_aktif as ia', 'ia.id_es3 = es3.id', 'left')
            ->join('item_inaktif as ii', 'ii.id_es3 = es3.id', 'left')
            ->where('es3.id_es2', $id_es2_admin) // Filter HANYA untuk Es.3 di bawah admin ini
            ->groupBy('es3.id, es3.nama_es3')
            ->orderBy('es3.nama_es3', 'ASC')
            ->get()->getResultArray();

        // --- Ambil Nilai Pengawasan (Terbaru untuk ES2 ini) ---
        $nilai_pengawasan_unit = $nilaiPengawasanModel->builder()
            ->select('np.*, u.name as user_name')
            ->join('users as u', 'u.id = np.id_user')
            ->from('nilai_pengawasan np')
            ->where('np.id_es2', $id_es2_admin)
            ->orderBy('np.tahun', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        // Panggil data pemindahan untuk lingkup Eselon 2 ini
        $pemindahanData = $this->getPemindahanDashboardData(null, $id_es2_admin); // Asumsi getPemindahanDashboardData support filter ES2

        $data = [
            'nama_es2_admin'        => $nama_es2_admin,
            // WIDGET ARSIP AKTIF
            'total_item_aktif'      => $total_item_aktif,
            'total_berkas_aktif'    => $total_berkas_aktif,
            'persentase_aktif'      => round($persentase_aktif),
            // WIDGET ARSIP INAKTIF
            'total_item_inaktif'    => $total_item_inaktif,
            'total_berkas_inaktif'  => $total_berkas_inaktif,
            'persentase_inaktif'    => round($persentase_inaktif),
            // REKAPITULASI
            'rekap_per_es3'         => $rekap_per_es3,
            // NILAI PENGAWASAN
            'nilai_pengawasan'      => $nilai_pengawasan_unit,
        ];

        return array_merge($data, $pemindahanData);
    }
    public function unconfiguredDashboard()
    {
        $session = session();

        // 1. Cek apakah user benar-benar login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        // 2. Jika ternyata user sudah dikonfigurasi, alihkan ke dashboard normal
        if ($session->get('is_configured') === TRUE) {
            return redirect()->to(base_url('dashboard'));
        }

        $data['title'] = 'Akses Terbatas: Unit Kerja Belum Diatur';
        $data['username'] = $session->get('name');
        $data['user_email'] = $session->get('email');
        $data['role_access'] = $session->get('role_access');

        return view('dashboard/unconfigured', $data);
    }
    /**
     * Mengambil data rekapitulasi spesifik untuk User.
     */
    private function getUserDashboardData($userId)
    {
        $itemAktifModel = new ItemAktifModel();
        $berkasAktifModel = new BerkasAktifModel();
        $itemInaktifModel = new ItemInaktifModel();
        $berkasInaktifModel = new BerkasInaktifModel();
        $unitKerjaEs3Model = new UnitKerjaEs3Model();
        $unitKerjaEs2Model = new UnitKerjaEs2Model();

        $db = \Config\Database::connect();
        $session = session();
        $authData = $session->get('auth_data') ?? [];

        $isConfigured = $session->get('is_configured');
        $id_es3_filter = null;

        $namaUnitKerja = 'Belum Terkonfigurasi'; // Nama unit kerja yang akan ditampilkan di alert
        $kodeEs2 = '-';
        $namaEs2 = '-';

        if (!$isConfigured || empty($authData['id_es3'] ?? null)) {
            // Jika belum terkonfigurasi, return flag
            return [
                'nama_unit_kerja' => $namaUnitKerja,
                'data_kosong' => true,
                'total_item_aktif_user' => 0, // Tetap kirim nilai default
                // ... (kirim semua variabel lain sebagai 0 atau '-')
            ];
        }

        $id_es3_filter = $authData['id_es3'];

        // 1. Dapatkan Detail Unit Kerja (ES3 & ES2)
        $unitEs3 = $unitKerjaEs3Model->find($id_es3_filter);

        if ($unitEs3) {
            $namaUnitKerja = $unitEs3['nama_es3'];

            // Dapatkan data ES2 induk untuk ditampilkan di alert (jika perlu)
            $unitEs2 = $unitKerjaEs2Model->find($unitEs3['id_es2']);
            $kodeEs2 = $unitEs2['kode'] ?? '-';
            $namaEs2 = $unitEs2['nama_es2'] ?? '-';
        }
        // --- Perhitungan Rekapitulasi Arsip (FILTER BERDASARKAN ID_ES3) ---

        // AKTIF
        $totalItemAktifUser = $itemAktifModel->where('id_es3', $id_es3_filter)->countAllResults();
        $itemDiberkaskanAktifUser = $itemAktifModel->where('id_es3', $id_es3_filter)->where('id_berkas IS NOT NULL')->countAllResults();
        $persentaseAktif = ($totalItemAktifUser > 0) ? ($itemDiberkaskanAktifUser / $totalItemAktifUser) * 100 : 0;
        $totalBerkasAktifUser = $berkasAktifModel->where('id_es3', $id_es3_filter)->countAllResults();

        // INAKTIF
        $totalItemInaktifUser = $itemInaktifModel->where('id_es3', $id_es3_filter)->countAllResults();
        $itemDiberkaskanInaktifUser = $itemInaktifModel->where('id_es3', $id_es3_filter)->where('id_berkas IS NOT NULL')->countAllResults();
        $persentaseInaktif = ($totalItemInaktifUser > 0) ? ($itemDiberkaskanInaktifUser / $totalItemInaktifUser) * 100 : 0;
        $totalBerkasInaktifUser = $berkasInaktifModel->where('id_es3', $id_es3_filter)->countAllResults();

        // --- Ambil Data Input Bulanan (FILTER BERDASARKAN ID_ES3) ---
        // Perlu sedikit penyesuaian di getMonthlyInputData untuk menerima $id_es3_filter
        $monthlyData = $this->getMonthlyInputData($userId, $id_es3_filter);

        // --- Panggil Data Pemindahan (Filter dengan ES3) ---
        $pemindahanData = $this->getPemindahanDashboardData(null, null, $id_es3_filter);

        // Kumpulkan data
        $data = [
            // Rekapitulasi Aktif
            'total_item_aktif_user'         => $totalItemAktifUser,
            'item_diberkaskan_aktif_user'   => $itemDiberkaskanAktifUser,
            'persentase_aktif'              => round($persentaseAktif),
            'total_berkas_aktif_user'       => $totalBerkasAktifUser,

            // Rekapitulasi Inaktif
            'total_item_inaktif_user'       => $totalItemInaktifUser,
            'item_diberkaskan_inaktif_user' => $itemDiberkaskanInaktifUser,
            'persentase_inaktif'            => round($persentaseInaktif),
            'total_berkas_inaktif_user'     => $totalBerkasInaktifUser,

            // Unit Kerja & Profil (Informasi ES2 yang akan ditampilkan di Alert)
            'nama_unit_kerja'               => $namaUnitKerja, // Nama ES3
            'kode_es2_unit'                 => $kodeEs2,
            'nama_es2_unit'                 => $namaEs2,

            'user_name_full'                => $session->get('name'),
            'user_email_full'               => $session->get('email'),
            'user_role_access'              => $session->get('role_access'),
            'user_jabatan_api'              => $session->get('jabatan_api'),

            // Data Chart
            'chart_labels'      => $monthlyData['months'],
            'chart_data'        => $monthlyData['counts'],
            'current_year'      => $monthlyData['current_year'],
        ];

        return array_merge($data, $pemindahanData);
    }
    private function getMonthlyInputData(?int $userId, ?int $idEs3Filter)
    {
        $currentYear = date('Y');

        // 1. Tangani kasus jika ES3 ID NULL (seharusnya dicek di getUserDashboardData)
        if (is_null($idEs3Filter) || $idEs3Filter === 0) {
            $monthNames = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthNames[] = date("M", mktime(0, 0, 0, $i, 1));
            }
            return [
                'months' => $monthNames,
                'counts' => array_fill(0, 12, 0),
                'current_year' => $currentYear
            ];
        }

        $db = \Config\Database::connect();

        // Query: Menghitung jumlah item berdasarkan bulan di tahun berjalan, difilter berdasarkan UNIT KERJA
        $rawQuery = $db->table('item_aktif')
            ->select("MONTH(tgl_dokumen) as month, COUNT(id) as count")
            ->where('id_es3', $idEs3Filter)
            ->where('YEAR(tgl_dokumen)', $currentYear)
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        $monthlyData = array_fill(1, 12, 0);
        $monthNames = [];

        foreach ($rawQuery as $row) {
            $monthlyData[(int)$row['month']] = (int)$row['count'];
        }

        for ($i = 1; $i <= 12; $i++) {
            $monthNames[] = date("M", mktime(0, 0, 0, $i, 1));
        }

        return [
            'months' => $monthNames,
            'counts' => array_values($monthlyData),
            'current_year' => $currentYear
        ];
    }
}
