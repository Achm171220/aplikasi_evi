<?php

namespace App\Controllers;

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
use App\Models\NilaiPengawasanModel;

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
        $this->itemAktifModel    = new ItemAktifModel();
        $this->berkasAktifModel  = new BerkasAktifModel();
        $this->itemInaktifModel  = new ItemInaktifModel();
        $this->berkasInaktifModel = new BerkasInaktifModel();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->userModel         = new UserModel();
    }

    public function index()
    {
        $session     = session();
        $userRole    = $session->get('role_access');
        $userJabatan = $session->get('role_jabatan');
        $data        = ['title' => 'Dashboard'];

        // Cek konfigurasi
        if ($session->get('is_configured') === FALSE) {
            return redirect()->to(base_url('dashboard/unconfigured'));
        }

        // Guest / Pimpinan
        if ($userRole === 'guest' && $userJabatan === 'pimpinan') {
            $data['pageTitle'] = 'Dashboard Pemantauan Kearsipan (Pimpinan)';
            $data = array_merge($data, $this->getPimpinanDashboardData());
            return view('dashboard/pimpinan', $data);
        }

        // Superadmin
        elseif ($userRole === 'superadmin') {
            $data['pageTitle'] = 'Dashboard Super Administrator';
            $data = array_merge($data, $this->getSuperadminDashboardData());
            return view('dashboard/superadmin', $data);
        }

        // Admin
        elseif ($userRole === 'admin') {
            $data['pageTitle'] = 'Dashboard Administrator';
            $data = array_merge($data, $this->getAdminDashboardData());
            return view('dashboard/admin', $data);
        }

        // Manager (dipetakan ke Admin)
        elseif ($userRole === 'manager') {
            $data['pageTitle'] = 'Dashboard Administrator';
            $data = array_merge($data, $this->getSuperadminDashboardData());
            return view('dashboard/manager', $data);
        }

        // User
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

        // Ambil nilai pengawasan terbaru tiap ES2
        $nilaiNP = $db->table('nilai_pengawasan')
            ->select('id_es2, skor, kategori, tahun')
            ->where('tahun', $currentYear)
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        $nilaiMap = [];
        foreach ($nilaiNP as $n) {
            if (!isset($nilaiMap[$n['id_es2']])) {
                $nilaiMap[$n['id_es2']] = $n;
            }
        }

        // Rekap
        $query = $db->table('unit_kerja_es2 as es2')
            ->select('
                es2.id, es2.nama_es2, es1.kode as kode_es1,
                COUNT(DISTINCT ia.id) as total_aktif,
                COUNT(DISTINCT ia.id_berkas) as total_berkas_aktif,
                COUNT(DISTINCT ii.id) as total_inaktif,
                (
                    SELECT COUNT(id) FROM item_aktif 
                    WHERE status_pindah="ba" AND id_es2=es2.id
                ) as total_ba_pemindahan
            ')
            ->join('unit_kerja_es1 as es1', 'es1.id = es2.id_es1', 'left')
            ->join('item_aktif as ia', 'ia.id_es2 = es2.id', 'left')
            ->join('item_inaktif as ii', 'ii.id_es2 = es2.id', 'left')
            ->groupBy('es2.id, es2.nama_es2, es1.kode')
            ->orderBy('es2.nama_es2', 'ASC')
            ->get()->getResultArray();

        $rekapData = [];
        foreach ($query as $row) {
            $np = $nilaiMap[$row['id']] ?? null;

            $rekapData[] = [
                'id_es2'               => $row['id'],
                'nama_es2_lengkap'     => ($row['kode_es1'] ?? 'N/A') . ' - ' . $row['nama_es2'],
                'total_item_aktif'     => (int)$row['total_aktif'],
                'total_item_inaktif'   => (int)$row['total_inaktif'],
                'total_ba_pemindahan'  => (int)$row['total_ba_pemindahan'],
                'nilai_pengawasan'     => $np['skor'] ?? null,
                'kategori_np'          => $np['kategori'] ?? 'N/A',
                'tahun_np'             => $np['tahun'] ?? $currentYear,
            ];
        }

        // Data global
        return [
            'global_total_item_aktif'    => $this->itemAktifModel->countAllResults(),
            'global_total_item_inaktif'  => $this->itemInaktifModel->countAllResults(),
            'global_total_berkas_aktif'  => $this->berkasAktifModel->countAllResults(),
            'global_total_berkas_inaktif' => $this->berkasInaktifModel->countAllResults(),
            'rekap_unit'                 => $rekapData,
            'tahun_data'                 => $currentYear,
        ];
    }

    private function getSuperadminDashboardData()
    {
        $db = \Config\Database::connect();

        $itemAktifModel    = new ItemAktifModel();
        $itemInaktifModel  = new ItemInaktifModel();

        $total_item_aktif       = $itemAktifModel->countAllResults();
        $item_diberkaskan_aktif = $itemAktifModel->where('id_berkas IS NOT NULL')->countAllResults();
        $persentase_aktif       = ($total_item_aktif > 0) ? ($item_diberkaskan_aktif / $total_item_aktif) * 100 : 0;

        $total_item_inaktif       = $itemInaktifModel->countAllResults();
        $item_diberkaskan_inaktif = $itemInaktifModel->where('id_berkas IS NOT NULL')->countAllResults();
        $persentase_inaktif       = ($total_item_inaktif > 0) ? ($item_diberkaskan_inaktif / $total_item_inaktif) * 100 : 0;

        $rekap_per_es2 = $db->table('unit_kerja_es2 as es2')
            ->select('es2.nama_es2, COUNT(item.id) as jumlah_item_aktif')
            ->join('item_aktif as item', 'item.id_es2 = es2.id', 'left')
            ->groupBy('es2.id')
            ->orderBy('es2.nama_es2', 'ASC')
            ->get()->getResultArray();

        $pemindahanData = $this->getPemindahanDashboardData(null);

        return array_merge([
            'total_item_aktif'      => $total_item_aktif,
            'total_berkas_aktif'    => (new BerkasAktifModel())->countAllResults(),
            'persentase_aktif'      => round($persentase_aktif),
            'total_item_inaktif'    => $total_item_inaktif,
            'total_berkas_inaktif'  => (new BerkasInaktifModel())->countAllResults(),
            'persentase_inaktif'    => round($persentase_inaktif),
            'total_klasifikasi'     => (new KlasifikasiModel())->countAllResults(),
            'total_jenis_naskah'    => (new JenisNaskahModel())->countAllResults(),
            'total_users'           => (new UserModel())->countAllResults(),
            'total_es1'             => (new UnitKerjaEs1Model())->countAllResults(),
            'total_es2'             => (new UnitKerjaEs2Model())->countAllResults(),
            'total_es3'             => (new UnitKerjaEs3Model())->countAllResults(),
            'rekap_per_es2'         => $rekap_per_es2,
        ], $pemindahanData);
    }

    private function getPemindahanDashboardData($userId = null, $es2Id = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('item_aktif');

        if ($userId) $builder->where('id_user', $userId);
        if ($es2Id) $builder->where('id_es2', $es2Id);

        return [
            'jumlah_usulan'      => (clone $builder)->where('status_pindah', 'usul_pindah')->countAllResults(),
            'jumlah_ditolak'     => (clone $builder)->where('status_pindah', 'ditolak')->countAllResults(),
            'jumlah_diproses'    => (clone $builder)->whereIn('status_pindah', ['verifikasi', 'ba'])->countAllResults(),
            'jumlah_dipindahkan' => 0,
        ];
    }

    private function getAdminDashboardData()
    {
        $session = session();
        $authData = $session->get('auth_data');

        if (empty($authData) || empty($authData['id_es2'])) {
            return ['nama_es2_admin' => 'Hak Akses Belum Diatur', 'data_kosong' => true];
        }

        $id_es2_admin = $authData['id_es2'];
        $db = \Config\Database::connect();

        $unitKerjaEs2Model = new UnitKerjaEs2Model();
        $itemAktifModel    = new ItemAktifModel();
        $itemInaktifModel  = new ItemInaktifModel();
        $berkasAktifModel  = new BerkasAktifModel();
        $berkasInaktifModel = new BerkasInaktifModel();
        $nilaiPengawasanModel = new NilaiPengawasanModel();

        $nama_es2_admin = $unitKerjaEs2Model->find($id_es2_admin)['nama_es2'] ?? 'Unit Tidak Ditemukan';

        // Aktif
        $total_item_aktif = $itemAktifModel->where('id_es2', $id_es2_admin)->countAllResults();
        $item_diberkaskan_aktif = $itemAktifModel->where('id_es2', $id_es2_admin)->where('id_berkas IS NOT NULL')->countAllResults();
        $persentase_aktif = ($total_item_aktif > 0) ? ($item_diberkaskan_aktif / $total_item_aktif) * 100 : 0;

        // Inaktif
        $total_item_inaktif = $itemInaktifModel->where('id_es2', $id_es2_admin)->countAllResults();
        $item_diberkaskan_inaktif = $itemInaktifModel->where('id_es2', $id_es2_admin)->where('id_berkas IS NOT NULL')->countAllResults();
        $persentase_inaktif = ($total_item_inaktif > 0) ? ($item_diberkaskan_inaktif / $total_item_inaktif) * 100 : 0;

        // Berkas
        $total_berkas_aktif = $berkasAktifModel->where('id_es2', $id_es2_admin)->countAllResults();
        $total_berkas_inaktif = $berkasInaktifModel->where('id_es2', $id_es2_admin)->countAllResults();

        $rekap_per_es3 = $db->table('unit_kerja_es3 as es3')
            ->select('
                es3.nama_es3, 
                COUNT(DISTINCT ia.id) as jumlah_item_aktif,
                COUNT(DISTINCT ii.id) as jumlah_item_inaktif
            ')
            ->join('item_aktif as ia', 'ia.id_es3 = es3.id', 'left')
            ->join('item_inaktif as ii', 'ii.id_es3 = es3.id', 'left')
            ->where('es3.id_es2', $id_es2_admin)
            ->groupBy('es3.id, es3.nama_es3')
            ->orderBy('es3.nama_es3', 'ASC')
            ->get()->getResultArray();

        $nilai_pengawasan_unit = $nilaiPengawasanModel->builder()
            ->select('np.*, u.name as user_name')
            ->join('users as u', 'u.id = np.id_user')
            ->from('nilai_pengawasan np')
            ->where('np.id_es2', $id_es2_admin)
            ->orderBy('np.tahun', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $pemindahanData = $this->getPemindahanDashboardData(null, $id_es2_admin);

        return array_merge([
            'nama_es2_admin'       => $nama_es2_admin,
            'total_item_aktif'     => $total_item_aktif,
            'total_berkas_aktif'   => $total_berkas_aktif,
            'persentase_aktif'     => round($persentase_aktif),
            'total_item_inaktif'   => $total_item_inaktif,
            'total_berkas_inaktif' => $total_berkas_inaktif,
            'persentase_inaktif'   => round($persentase_inaktif),
            'rekap_per_es3'        => $rekap_per_es3,
            'nilai_pengawasan'     => $nilai_pengawasan_unit,
        ], $pemindahanData);
    }

    public function unconfiguredDashboard()
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        if ($session->get('is_configured') === TRUE) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('dashboard/unconfigured', [
            'title'       => 'Akses Terbatas: Unit Kerja Belum Diatur',
            'username'    => $session->get('name'),
            'user_email'  => $session->get('email'),
            'role_access' => $session->get('role_access'),
        ]);
    }

    private function getUserDashboardData($userId)
    {
        $session         = session();
        $authData        = $session->get('auth_data') ?? [];
        $isConfigured    = $session->get('is_configured');
        $id_es3_filter   = null;
        $namaUnitKerja   = 'Belum Terkonfigurasi';
        $kodeEs2         = '-';
        $namaEs2         = '-';

        $itemAktifModel   = new ItemAktifModel();
        $berkasAktifModel = new BerkasAktifModel();
        $itemInaktifModel = new ItemInaktifModel();
        $berkasInaktifModel = new BerkasInaktifModel();
        $unitKerjaEs3Model = new UnitKerjaEs3Model();
        $unitKerjaEs2Model = new UnitKerjaEs2Model();

        if (!$isConfigured || empty($authData['id_es3'] ?? null)) {
            return [
                'nama_unit_kerja'          => $namaUnitKerja,
                'data_kosong'              => true,
                'total_item_aktif_user'    => 0,
            ];
        }

        $id_es3_filter = $authData['id_es3'];
        $unitEs3 = $unitKerjaEs3Model->find($id_es3_filter);

        if ($unitEs3) {
            $namaUnitKerja = $unitEs3['nama_es3'];
            $unitEs2 = $unitKerjaEs2Model->find($unitEs3['id_es2']);
            $kodeEs2 = $unitEs2['kode'] ?? '-';
            $namaEs2 = $unitEs2['nama_es2'] ?? '-';
        }

        $totalItemAktifUser = $itemAktifModel->where('id_es3', $id_es3_filter)->countAllResults();
        $itemDiberkaskanAktifUser = $itemAktifModel->where('id_es3', $id_es3_filter)->where('id_berkas IS NOT NULL')->countAllResults();
        $persentaseAktif = ($totalItemAktifUser > 0) ? ($itemDiberkaskanAktifUser / $totalItemAktifUser) * 100 : 0;
        $totalBerkasAktifUser = $berkasAktifModel->where('id_es3', $id_es3_filter)->countAllResults();

        $totalItemInaktifUser = $itemInaktifModel->where('id_es3', $id_es3_filter)->countAllResults();
        $itemDiberkaskanInaktifUser = $itemInaktifModel->where('id_es3', $id_es3_filter)->where('id_berkas IS NOT NULL')->countAllResults();
        $persentaseInaktif = ($totalItemInaktifUser > 0) ? ($itemDiberkaskanInaktifUser / $totalItemInaktifUser) * 100 : 0;
        $totalBerkasInaktifUser = $berkasInaktifModel->where('id_es3', $id_es3_filter)->countAllResults();

        $monthlyData = $this->getMonthlyInputData($userId, $id_es3_filter);
        $pemindahanData = $this->getPemindahanDashboardData(null, null, $id_es3_filter);

        return array_merge([
            'total_item_aktif_user'         => $totalItemAktifUser,
            'item_diberkaskan_aktif_user'   => $itemDiberkaskanAktifUser,
            'persentase_aktif'              => round($persentaseAktif),
            'total_berkas_aktif_user'       => $totalBerkasAktifUser,

            'total_item_inaktif_user'       => $totalItemInaktifUser,
            'item_diberkaskan_inaktif_user' => $itemDiberkaskanInaktifUser,
            'persentase_inaktif'            => round($persentaseInaktif),
            'total_berkas_inaktif_user'     => $totalBerkasInaktifUser,

            'nama_unit_kerja'               => $namaUnitKerja,
            'kode_es2_unit'                 => $kodeEs2,
            'nama_es2_unit'                 => $namaEs2,

            'user_name_full'                => $session->get('name'),
            'user_email_full'               => $session->get('email'),
            'user_role_access'              => $session->get('role_access'),
            'user_jabatan_api'              => $session->get('jabatan_api'),

            'chart_labels'                  => $monthlyData['months'],
            'chart_data'                    => $monthlyData['counts'],
            'current_year'                  => $monthlyData['current_year'],
        ], $pemindahanData);
    }

    private function getMonthlyInputData(?int $userId, ?int $idEs3Filter)
    {
        $currentYear = date('Y');

        if (is_null($idEs3Filter) || $idEs3Filter === 0) {
            return [
                'months'       => array_map(fn($m) => date("M", mktime(0, 0, 0, $m, 1)), range(1, 12)),
                'counts'       => array_fill(0, 12, 0),
                'current_year' => $currentYear,
            ];
        }

        $db = \Config\Database::connect();
        $rawQuery = $db->table('item_aktif')
            ->select("MONTH(tgl_dokumen) as month, COUNT(id) as count")
            ->where('id_es3', $idEs3Filter)
            ->where('YEAR(tgl_dokumen)', $currentYear)
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get()->getResultArray();

        $monthlyData = array_fill(1, 12, 0);
        foreach ($rawQuery as $row) {
            $monthlyData[(int)$row['month']] = (int)$row['count'];
        }

        return [
            'months'       => array_map(fn($m) => date("M", mktime(0, 0, 0, $m, 1)), range(1, 12)),
            'counts'       => array_values($monthlyData),
            'current_year' => $currentYear,
        ];
    }
}
