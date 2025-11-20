<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ItemAktifModel;
use App\Models\UserModel;
use App\Models\ItemInaktifModel;
use App\Models\BeritaAcaraModel;
use App\Models\UnitKerjaEs2Model;

use CodeIgniter\Session\Session;

class Pemindahan extends BaseController
{
    protected $itemAktifModel;
    protected $userModel;
    protected $itemInaktifModel;
    protected $beritaAcaraModel;
    protected $unitKerjaEs2Model;
    protected $db;
    protected $session;

    public function __construct()
    {
        // parent::__construct();
        $this->itemAktifModel = new ItemAktifModel();
        $this->userModel = new UserModel();
        $this->itemInaktifModel = new ItemInaktifModel();
        $this->beritaAcaraModel = new BeritaAcaraModel();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
    }

    private function checkPermission(array $rolesNeeded, array $jabatanNeeded = [], string $redirectUrl = '/')
    {
        if (!$this->session->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Anda harus login untuk mengakses halaman ini.');
            return redirect()->to('/login');
        }

        $userRoleAccess = $this->session->get('role_access');
        $userRoleJabatan = $this->session->get('role_jabatan');

        if ($userRoleAccess === 'superadmin') {
            return true;
        }

        if (!in_array($userRoleAccess, $rolesNeeded)) {
            session()->setFlashdata('error', 'Anda tidak memiliki hak akses untuk halaman ini.');
            return redirect()->to($redirectUrl);
        }

        if (!empty($jabatanNeeded) && $userRoleAccess !== 'admin') {
            if (!in_array($userRoleJabatan, $jabatanNeeded)) {
                session()->setFlashdata('error', 'Anda tidak memiliki peran fungsional yang sesuai untuk halaman ini.');
                return redirect()->to($redirectUrl);
            }
        }

        return true;
    }

    public function dashboard()
    {
        if (!$this->session->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Anda harus login untuk mengakses halaman ini.');
            return redirect()->to('/login');
        }

        $data['counts'] = [];
        $data['counts']['total_arsip_aktif'] = $this->itemAktifModel->countAllResults();
        $data['counts']['total_arsip_inaktif'] = $this->itemInaktifModel->countAllResults();
        $data['counts']['total_menunggu_verifikasi'] = $this->itemAktifModel
            ->whereIn('status_pindah', ['menunggu_verif1', 'disetujui_verif1', 'disetujui_verif2', 'menunggu_eksekusi'])
            ->countAllResults();
        $data['counts']['usulan_baru'] = $this->itemAktifModel->where('status_pindah', 'menunggu_verif1')->countAllResults();
        $data['counts']['verifikasi1_menunggu'] = $this->itemAktifModel->where('status_pindah', 'menunggu_verif1')->countAllResults();
        $data['counts']['verifikasi2_menunggu'] = $this->itemAktifModel->where('status_pindah', 'disetujui_verif1')->countAllResults();
        $data['counts']['verifikasi3_menunggu'] = $this->itemAktifModel->where('status_pindah', 'disetujui_verif2')->countAllResults();
        $data['counts']['buatba_menunggu'] = $this->itemAktifModel->where('status_pindah', 'disetujui_verif3')->countAllResults();
        $data['counts']['eksekusi_menunggu'] = $this->itemAktifModel->where('status_pindah', 'menunggu_eksekusi')->countAllResults();
        $data['counts']['ditolak_verif1'] = $this->itemAktifModel->where('status_pindah', 'ditolak_verif1')->countAllResults();
        $data['counts']['ditolak_verif2'] = $this->itemAktifModel->where('status_pindah', 'ditolak_verif2')->countAllResults();
        $data['counts']['ditolak_verif3'] = $this->itemAktifModel->where('status_pindah', 'ditolak_verif3')->countAllResults();
        $data['counts']['total_ditolak'] = $this->itemAktifModel
            ->whereIn('status_pindah', ['ditolak_verif1', 'ditolak_verif2', 'ditolak_verif3'])
            ->countAllResults();
        $data['counts']['total_berita_acara'] = $this->beritaAcaraModel->countAllResults();
        $data['counts']['pemindahan_per_es2'] = $this->itemInaktifModel
            ->select('unit_kerja_es2.nama_es2, COUNT(item_inaktif.id) as total_items_moved')
            ->join('unit_kerja_es2', 'unit_kerja_es2.id = item_inaktif.id_es2', 'left')
            ->where('item_inaktif.status_pindah', 'dipindahkan')
            ->groupBy('unit_kerja_es2.nama_es2')
            ->findAll();

        return view('pemindahan/dashboard', $data);
    }

    public function usulan()
    {
        // --- START DEBUGGING ---
        // Sementara untuk debugging, nonaktifkan atau modifikasi checkPermission
        // jika Anda curiga itu mengganggu respons AJAX.
        // Jika checkPermission melakukan redirect, ini akan mengacaukan AJAX.
        // Pastikan checkPermission mengembalikan TRUE atau Response JSON error.

        $check = $this->checkPermission(['superadmin', 'admin', 'user']);
        if ($check !== true) {
            // Untuk permintaan AJAX, kembalikan JSON error daripada redirect/HTML
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak. Anda tidak memiliki izin untuk melihat ini.']);
            }
            return $check; // Untuk permintaan non-AJAX, lanjutkan perilaku default
        }
        // --- END DEBUGGING ---


        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('id');

        $searchQuery = $this->request->getGet('search');

        // Pastikan model diinisialisasi dan tabelnya ada
        if (!$this->itemAktifModel) {
            log_message('error', 'ItemAktifModel not initialized.');
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Model ItemAktifModel tidak terinisialisasi.']);
            }
            throw new \Exception('ItemAktifModel not initialized.');
        }

        $query = $this->itemAktifModel
            ->where('id_user', $currentUserId);

        if ($userRoleAccess === 'user') {
            $query->where('id_user', $currentUserId);
        }

        if (!empty($searchQuery)) {
            $query->groupStart()
                ->like('no_dokumen', $searchQuery, 'both') // 'both' untuk %search%
                ->orLike('judul_dokumen', $searchQuery, 'both')
                ->orLike('tahun_cipta', $searchQuery, 'both')
                ->orLike('lokasi_simpan', $searchQuery, 'both')
                ->groupEnd();
        }

        try {
            $data['items'] = $query->findAll();
            // Tambahkan log untuk melihat data apa yang dikirim
            // log_message('debug', 'AJAX response data: ' . json_encode($data['items']));

            if ($this->request->isAJAX()) {
                return $this->response->setJSON($data['items']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in fetching items for usulan: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Terjadi kesalahan database: ' . $e->getMessage()]);
            }
            throw $e; // Untuk permintaan non-AJAX, tampilkan error normal
        }


        // Ini hanya akan dieksekusi jika BUKAN permintaan AJAX
        $data['verifikator1Users'] = $this->userModel->getVerifikator1Users();
        $data['verifikator2Users'] = $this->userModel->getVerifikator2Users();
        $data['verifikator3Users'] = $this->userModel->getVerifikator3Users();

        return view('pemindahan/usulan', $data);
    }

    public function propose()
    {
        $check = $this->checkPermission(['superadmin', 'admin', 'user']);
        if ($check !== true) return $check;

        $selectedItems = $this->request->getPost('selected_items');
        $idVerifikator1 = $this->request->getPost('id_verifikator1');
        $idVerifikator2 = $this->request->getPost('id_verifikator2');
        $idVerifikator3 = $this->request->getPost('id_verifikator3');

        if (empty($selectedItems)) {
            session()->setFlashdata('error', 'Tidak ada item yang dipilih untuk diusulkan.');
            return redirect()->to(base_url('pemindahan'));
        }

        if (empty($idVerifikator1) || empty($idVerifikator2) || empty($idVerifikator3)) {
            session()->setFlashdata('error', 'Silakan pilih semua verifikator (V1, V2, V3) sebelum mengusulkan.');
            return redirect()->to(base_url('pemindahan'))->withInput();
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($selectedItems as $itemId) {
            if (is_numeric($itemId)) {
                $dataToUpdate = [
                    'status_arsip'    => 'usul_pindah',
                    'status_pindah'   => 'menunggu_verif1',
                    'admin_notes'     => null,
                    'id_verifikator1' => $idVerifikator1,
                    'id_verifikator2' => $idVerifikator2,
                    'id_verifikator3' => $idVerifikator3,
                ];

                $updated = $this->itemAktifModel->update($itemId, $dataToUpdate);

                if ($updated) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            session()->setFlashdata('success', "$successCount item berhasil diusulkan untuk pemindahan.");
        }
        if ($errorCount > 0) {
            session()->setFlashdata('error', "$errorCount item gagal diusulkan. Mohon coba lagi.");
        }

        return redirect()->to(base_url('pemindahan'));
    }

    public function monitoring()
    {
        $check = $this->checkPermission(['superadmin', 'admin', 'user']);
        if ($check !== true) return $check;

        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('id');

        $query = $this->itemAktifModel
            ->where('status_pindah !=', 'belum')
            ->join('users AS u1', 'u1.id = item_aktif.id_verifikator1', 'left')
            ->join('users AS u2', 'u2.id = item_aktif.id_verifikator2', 'left')
            ->join('users AS u3', 'u3.id = item_aktif.id_verifikator3', 'left')
            ->join('berita_acara AS ba', 'ba.id = item_aktif.id_berita_acara', 'left')
            ->select('item_aktif.*, u1.name AS verifikator1_name, u2.name AS verifikator2_name, u3.name AS verifikator3_name,
                                         ba.no_ba, ba.tgl_ba, ba.nama_pemindah, ba.nama_penerima');

        if ($userRoleAccess === 'user') {
            $query->where('item_aktif.id_user', $currentUserId);
        }

        $data['items'] = $query->findAll();

        return view('pemindahan/monitoring', $data);
    }

    public function verifikasi($stage = 1)
    {
        $check = $this->checkPermission(['superadmin', 'admin']);
        if ($check !== true) return $check;

        $stage = (int) $stage;
        $allowedStages = [1, 2, 3];
        if (!in_array($stage, $allowedStages)) {
            return redirect()->to(base_url('pemindahan/verifikasi/1'));
        }

        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('id');

        $statusToFetch = '';
        $verifierIdColumn = '';
        switch ($stage) {
            case 1:
                $statusToFetch = 'menunggu_verif1';
                $verifierIdColumn = 'id_verifikator1';
                break;
            case 2:
                $statusToFetch = 'disetujui_verif1';
                $verifierIdColumn = 'id_verifikator2';
                break;
            case 3:
                $statusToFetch = 'disetujui_verif2';
                $verifierIdColumn = 'id_verifikator3';
                break;
        }

        // --- Perbaikan di sini: Cara mengambil ID ES2 yang relevan ---
        // 1. Bangun query dasar untuk menemukan ID ES2 yang unik.
        $baseQueryForEs2List = $this->itemAktifModel
            ->select('id_es2') // HANYA SELECT id_es2
            ->where('status_pindah', $statusToFetch);

        // 2. Terapkan filter verifikator jika user bukan superadmin
        if ($userRoleAccess !== 'superadmin') {
            $baseQueryForEs2List->where("item_aktif.$verifierIdColumn", $currentUserId);
        }

        // 3. Eksekusi query dan ambil hasil sebagai array of IDs (menggunakan pluck)
        // pluck() adalah cara efektif untuk mengambil satu kolom dari hasil query sebagai array.
        $es2Ids = $baseQueryForEs2List->distinct()->findColumn('id_es2'); // <<< PERBAIKAN PENTING DI SINI

        // Pastikan $es2Ids tidak null atau kosong sebelum digunakan di whereIn
        if (empty($es2Ids)) {
            $es2Ids = [0]; // Jika tidak ada ID, gunakan ID yang tidak mungkin ada agar query WHERE IN tidak error
        }

        // 4. Ambil detail unit kerja dari tabel unit_kerja_es2 menggunakan ID yang relevan
        $data['unitKerjaList'] = $this->unitKerjaEs2Model
            ->whereIn('id', $es2Ids)
            ->findAll();

        $data['selectedEs2Id'] = $this->request->getVar('es2_filter');

        // --- Bangun query utama untuk item yang akan ditampilkan di tabel ---
        $query = $this->itemAktifModel
            ->where('status_pindah', $statusToFetch)
            ->join('users AS u1', 'u1.id = item_aktif.id_verifikator1', 'left')
            ->join('users AS u2', 'u2.id = item_aktif.id_verifikator2', 'left')
            ->join('users AS u3', 'u3.id = item_aktif.id_verifikator3', 'left')
            ->join('berita_acara AS ba', 'ba.id = item_aktif.id_berita_acara', 'left')
            ->join('unit_kerja_es2', 'unit_kerja_es2.id = item_aktif.id_es2', 'left')
            ->join('unit_kerja_es3', 'unit_kerja_es3.id = item_aktif.id_es3', 'left')
            ->select('item_aktif.*, u1.name AS verifikator1_name, u2.name AS verifikator2_name, u3.name AS verifikator3_name, ba.no_ba, ba.tgl_ba, unit_kerja_es2.kode as kode_es2, unit_kerja_es3.kode as kode_es3');

        // Terapkan filter verifikator yang ditunjuk
        if ($userRoleAccess !== 'superadmin') {
            $query->where("item_aktif.$verifierIdColumn", $currentUserId);
        }

        // Terapkan filter Unit Kerja jika dipilih (dari dropdown)
        if (!empty($data['selectedEs2Id']) && is_numeric($data['selectedEs2Id'])) {
            $query->where('item_aktif.id_es2', $data['selectedEs2Id']);
        }

        $data['stage'] = $stage;
        $data['items'] = $query->findAll();

        return view('pemindahan/verifikasi', $data);
    }
    public function processVerification($stage = 1)
    {
        $check = $this->checkPermission(['superadmin', 'admin']);
        if ($check !== true) return $check;

        $stage = (int) $stage;
        $allowedStages = [1, 2, 3];
        if (!in_array($stage, $allowedStages)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tahap verifikasi tidak valid.']);
        }

        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('id');

        $verifierIdColumn = '';
        switch ($stage) {
            case 1:
                $verifierIdColumn = 'id_verifikator1';
                break;
            case 2:
                $verifierIdColumn = 'id_verifikator2';
                break;
            case 3:
                $verifierIdColumn = 'id_verifikator3';
                break;
        }

        // --- Perubahan di sini: Menerima satu atau beberapa item_id ---
        $itemIds = $this->request->getPost('item_id'); // Bisa berupa single ID atau array of IDs
        $action = $this->request->getPost('action');
        $notes = $this->request->getPost('notes');

        // Pastikan $itemIds selalu array untuk iterasi yang konsisten
        if (!is_array($itemIds)) {
            $itemIds = [$itemIds];
        }

        if (empty($itemIds) || !in_array($action, ['approve', 'reject'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permintaan tidak valid. Tidak ada item dipilih atau aksi tidak dikenal.']);
        }

        $successCount = 0;
        $errorCount = 0;
        $messages = [];

        $this->db->transBegin(); // Mulai transaksi untuk seluruh batch

        foreach ($itemIds as $itemId) {
            if (!is_numeric($itemId)) {
                $messages[] = "Item ID $itemId: ID tidak valid.";
                $errorCount++;
                continue;
            }

            // Cek kepemilikan item (item harus ditujukan untuk user ini, kecuali superadmin)
            if ($userRoleAccess !== 'superadmin') {
                $itemCheck = $this->itemAktifModel->select($verifierIdColumn)->find($itemId);
                if (!$itemCheck || $itemCheck[$verifierIdColumn] != $currentUserId) {
                    $messages[] = "Item ID $itemId: Anda tidak berhak memverifikasi item ini.";
                    $errorCount++;
                    continue;
                }
            }

            $dataToUpdate = [];
            $itemMessage = '';

            if ($action === 'approve') {
                switch ($stage) {
                    case 1:
                        $dataToUpdate['status_pindah'] = 'disetujui_verif1';
                        break;
                    case 2:
                        $dataToUpdate['status_pindah'] = 'disetujui_verif2';
                        break;
                    case 3:
                        $dataToUpdate['status_pindah'] = 'disetujui_verif3';
                        break;
                }
                $dataToUpdate['admin_notes'] = null;
                $itemMessage = 'berhasil disetujui';
            } elseif ($action === 'reject') {
                switch ($stage) {
                    case 1:
                        $dataToUpdate['status_pindah'] = 'ditolak_verif1';
                        break;
                    case 2:
                        $dataToUpdate['status_pindah'] = 'ditolak_verif2';
                        break;
                    case 3:
                        $dataToUpdate['status_pindah'] = 'ditolak_verif3';
                        break;
                }
                $dataToUpdate['admin_notes'] = $notes;
                $itemMessage = 'berhasil ditolak';
            }

            $updated = $this->itemAktifModel->update($itemId, $dataToUpdate);

            if ($updated) {
                $successCount++;
                $messages[] = "Item ID $itemId $itemMessage.";
            } else {
                $errorCount++;
                $messages[] = "Item ID $itemId: Gagal memperbarui.";
            }
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Transaksi gagal. Semua perubahan dibatalkan.']);
        } else {
            $this->db->transCommit();
            // Jika ada campuran sukses dan error, tampilkan warning
            if ($successCount > 0 && $errorCount > 0) {
                session()->setFlashdata('warning', "$successCount item berhasil diproses, namun $errorCount item gagal: " . implode('; ', $messages));
                return $this->response->setJSON(['status' => 'warning', 'message' => "Beberapa item gagal diproses."]);
            } elseif ($successCount > 0) {
                session()->setFlashdata('success', "$successCount item berhasil diproses.");
                return $this->response->setJSON(['status' => 'success', 'message' => 'Semua item berhasil diproses.']);
            } else {
                session()->setFlashdata('error', "Semua item gagal diproses: " . implode('; ', $messages));
                return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada item yang berhasil diproses.']);
            }
        }
    }

    public function buatBa()
    {
        $check = $this->checkPermission(['superadmin', 'admin', 'user']);
        if ($check !== true) return $check;

        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('id');

        $query = $this->itemAktifModel
            ->where('status_pindah', 'disetujui_verif3');

        if ($userRoleAccess === 'user') {
            $query->where('id_user', $currentUserId);
        }

        $data['items'] = $query->findAll();

        return view('pemindahan/buat_ba', $data);
    }

    public function processBuatBa()
    {
        $check = $this->checkPermission(['superadmin', 'user'], ['arsiparis', 'pengelola_arsip']);
        if ($check !== true) return $check;

        $selectedItems = $this->request->getPost('selected_items');
        $noBa = $this->request->getPost('no_ba');
        $tglBa = $this->request->getPost('tgl_ba');
        $namaPemindah = $this->request->getPost('nama_pemindah');
        $jabatanPemindah = $this->request->getPost('jabatan_pemindah');
        $namaPenerima = $this->request->getPost('nama_penerima');
        $jabatanPenerima = $this->request->getPost('jabatan_penerima');
        $catatanBa = $this->request->getPost('catatan_ba');
        $fileBaScan = $this->request->getPost('file_ba_scan');

        if (empty($selectedItems)) {
            session()->setFlashdata('error', 'Tidak ada item yang dipilih untuk dibuat Berita Acara.');
            return redirect()->to(base_url('pemindahan/buat_ba'));
        }

        $baData = [
            'id_user'          => $this->session->get('id'),
            'no_ba'            => $noBa,
            'tgl_ba'           => $tglBa,
            'nama_pemindah'    => $namaPemindah,
            'jabatan_pemindah' => $jabatanPemindah,
            'nama_penerima'    => $namaPenerima,
            'jabatan_penerima' => $jabatanPenerima,
            'catatan'          => $catatanBa,
            'file_ba_scan'     => $fileBaScan,
        ];

        if (!$this->beritaAcaraModel->validate($baData)) {
            session()->setFlashdata('errors', $this->beritaAcaraModel->errors());
            session()->setFlashdata('error', 'Gagal membuat Berita Acara. Mohon periksa isian Anda.');
            return redirect()->to(base_url('pemindahan/buat_ba'))->withInput();
        }

        $beritaAcaraId = $this->beritaAcaraModel->insert($baData, true);

        if (!$beritaAcaraId) {
            session()->setFlashdata('error', 'Gagal menyimpan data Berita Acara utama.');
            return redirect()->to(base_url('pemindahan/buat_ba'))->withInput();
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($selectedItems as $itemId) {
            if (is_numeric($itemId)) {
                $dataToUpdate = [
                    'id_berita_acara'   => $beritaAcaraId,
                    'status_pindah'     => 'menunggu_eksekusi',
                    'admin_notes'       => null,
                ];

                $updated = $this->itemAktifModel->update($itemId, $dataToUpdate);

                if ($updated) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            session()->setFlashdata('success', "$successCount item berhasil dibuatkan Berita Acara dan diteruskan untuk eksekusi.");
        }
        if ($errorCount > 0) {
            session()->setFlashdata('error', "$errorCount item gagal dibuatkan Berita Acara. Mohon coba lagi.");
        }

        return redirect()->to(base_url('pemindahan/buat_ba'));
    }

    public function eksekusi()
    {
        $check = $this->checkPermission(['superadmin', 'admin']);
        if ($check !== true) return $check;

        $data['items'] = $this->itemAktifModel
            ->where('status_pindah', 'menunggu_eksekusi')
            ->join('berita_acara AS ba', 'ba.id = item_aktif.id_berita_acara', 'left')
            ->select('item_aktif.*, ba.no_ba, ba.tgl_ba, ba.nama_pemindah, ba.nama_penerima, ba.jabatan_pemindah, ba.jabatan_penerima')
            ->findAll();

        $uniqueBas = [];
        foreach ($data['items'] as $item) {
            if (!empty($item['id_berita_acara'])) {
                $uniqueBas[$item['id_berita_acara']] = [
                    'no_ba' => $item['no_ba'],
                    'tgl_ba' => $item['tgl_ba'] ? date('d-m-Y', strtotime($item['tgl_ba'])) : 'Tanggal tidak tersedia',
                    'nama_pemindah' => $item['nama_pemindah'],
                    'nama_penerima' => $item['nama_penerima'],
                ];
            }
        }
        $data['uniqueBas'] = $uniqueBas;

        return view('pemindahan/eksekusi', $data);
    }

    public function processEksekusi()
    {
        $check = $this->checkPermission(['superadmin', 'admin']);
        if ($check !== true) return $check;

        $selectedItemIds = $this->request->getPost('selected_items');
        $itemData = $this->request->getPost('item_data');

        if (empty($selectedItemIds)) {
            session()->setFlashdata('error', 'Tidak ada item yang dipilih untuk dieksekusi.');
            return redirect()->to(base_url('pemindahan/eksekusi'));
        }

        $successCount = 0;
        $errorCount = 0;
        $generalValidationErrors = [];
        $modelAndDbErrors = [];

        foreach ($selectedItemIds as $itemId) {
            if (!is_numeric($itemId)) {
                $errorCount++;
                continue;
            }

            $noNewBerkas = $itemData[$itemId]['no_berkas_baru'] ?? '';
            $lokasiSimpanNew = $itemData[$itemId]['lokasi_simpan_new'] ?? '';

            if (empty(trim($noNewBerkas)) || empty(trim($lokasiSimpanNew))) {
                $generalValidationErrors[] = "Nomor Berkas Baru dan Lokasi Simpan Baru wajib diisi untuk item ID: $itemId.";
                $errorCount++;
                continue;
            }

            $item = $this->itemAktifModel->find($itemId);

            if ($item) {
                $dataToInsert = [];
                foreach ($item as $key => $value) {
                    if (in_array($key, $this->itemInaktifModel->allowedFields) && $key != 'lokasi_simpan') {
                        $dataToInsert[$key] = $value;
                    }
                }

                $dataToInsert['lokasi_simpan_aktif'] = $item['lokasi_simpan'];
                $dataToInsert['no_berkas_baru'] = trim($noNewBerkas);
                $dataToInsert['lokasi_simpan_new'] = trim($lokasiSimpanNew);

                $dataToInsert['status_pindah'] = 'dipindahkan';
                $dataToInsert['status_arsip'] = 'inaktif';
                $dataToInsert['admin_notes'] = $item['admin_notes'];

                if (in_array('id_berita_acara', $this->itemInaktifModel->allowedFields)) {
                    $dataToInsert['id_berita_acara'] = $item['id_berita_acara'];
                }
                if (in_array('id_ba', $this->itemInaktifModel->allowedFields)) {
                    $dataToInsert['id_ba'] = $item['id_berita_acara'];
                }


                $this->db->transBegin();
                try {
                    if (!$this->itemInaktifModel->validate($dataToInsert)) {
                        $errorsFromModel = $this->itemInaktifModel->errors();
                        foreach ($errorsFromModel as $field => $msg) {
                            $modelAndDbErrors[] = "Item ID $itemId: Kolom '$field' bermasalah - $msg";
                        }
                        $errorCount++;
                        $this->db->transRollback();
                        continue;
                    }

                    $inserted = $this->itemInaktifModel->insert($dataToInsert);

                    if ($inserted) {
                        $deleted = $this->itemAktifModel->delete($itemId);
                        if ($deleted) {
                            $this->db->transCommit();
                            $successCount++;
                        } else {
                            $this->db->transRollback();
                            $errorCount++;
                            $modelAndDbErrors[] = "Item ID $itemId berhasil disalin tetapi gagal dihapus dari arsip aktif. Transaksi dibatalkan.";
                            log_message('error', 'Eksekusi: Gagal menghapus item_aktif ' . $itemId . ' setelah insert ke item_inaktif.');
                        }
                    } else {
                        $this->db->transRollback();
                        $error = $this->db->error();
                        $dbErrorMsg = $error['message'] ?? 'Unknown database error.';
                        $modelAndDbErrors[] = "Item ID $itemId gagal disalin ke arsip inaktif. Error database: " . $dbErrorMsg;
                        log_message('error', 'Eksekusi: Gagal insert item ' . $itemId . ' ke item_inaktif. DB Error: ' . $dbErrorMsg . ' Data: ' . json_encode($dataToInsert));
                    }
                } catch (\Exception $e) {
                    $this->db->transRollback();
                    $errorCount++;
                    $modelAndDbErrors[] = "Item ID $itemId: Kesalahan sistem saat eksekusi - " . $e->getMessage();
                    log_message('error', 'Eksekusi: Exception saat memproses item ' . $itemId . ': ' . $e->getMessage() . "\nStack Trace: " . $e->getTraceAsString());
                }
            } else {
                $errorCount++;
                $modelAndDbErrors[] = "Item ID $itemId tidak ditemukan di arsip aktif.";
                log_message('error', 'Eksekusi: Item ID ' . $itemId . ' tidak ditemukan di item_aktif.');
            }
        }

        $allErrors = array_merge($generalValidationErrors, $modelAndDbErrors);
        if (!empty($allErrors)) {
            session()->setFlashdata('errors', $allErrors);
            session()->setFlashdata('error', 'Terdapat masalah saat eksekusi. Mohon periksa kembali.');
        }

        if ($successCount > 0) {
            session()->setFlashdata('success', "$successCount item berhasil dieksekusi dan dipindahkan ke arsip inaktif.");
        }
        if ($errorCount > 0 && $successCount == 0 && empty($allErrors)) {
            session()->setFlashdata('error', "$errorCount item gagal dieksekusi. Mohon coba lagi.");
        }

        return redirect()->to(base_url('pemindahan/eksekusi'))->withInput();
    }

    public function dataAktif()
    {
        $check = $this->checkPermission(['superadmin', 'admin', 'user']);
        if ($check !== true) return $check;

        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('id');

        $query = $this->itemAktifModel
            ->join('users AS u1', 'u1.id = item_aktif.id_verifikator1', 'left')
            ->join('users AS u2', 'u2.id = item_aktif.id_verifikator2', 'left')
            ->join('users AS u3', 'u3.id = item_aktif.id_verifikator3', 'left')
            ->join('berita_acara AS ba', 'ba.id = item_aktif.id_berita_acara', 'left')
            ->join('klasifikasi', 'klasifikasi.id = item_aktif.id_klasifikasi', 'left')
            ->select('item_aktif.*, u1.name AS verifikator1_name, u2.name AS verifikator2_name, u3.name AS verifikator3_name,
                                         ba.no_ba, ba.tgl_ba, klasifikasi.nama_klasifikasi, klasifikasi.umur_aktif'); // <<< UBAH SELECT ke umur_aktif

        if ($userRoleAccess === 'user') {
            $query->where('item_aktif.id_user', $currentUserId);
        }

        $data['items'] = $query->findAll();
        return view('pemindahan/data_aktif', $data);
    }



    /**
     * Mengembalikan data arsip inaktif dalam format JSON untuk DataTables (server-side processing).
     * URL: /pemindahan/get_data_inaktif_json
     * Hak Akses: Superadmin, Admin, User
     */

    public function dataInaktif()
    {
        $check = $this->checkPermission(['superadmin', 'admin', 'user']);
        if ($check !== true) return $check;

        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('id');

        $query = $this->itemInaktifModel
            ->join('users AS u1', 'u1.id = item_inaktif.id_verifikator1', 'left')
            ->join('users AS u2', 'u2.id = item_inaktif.id_verifikator2', 'left')
            ->join('users AS u3', 'u3.id = item_inaktif.id_verifikator3', 'left')
            ->join('berita_acara AS ba', 'ba.id = item_inaktif.id_berita_acara', 'left')
            ->select('item_inaktif.*, u1.name AS verifikator1_name, u2.name AS verifikator2_name, u3.name AS verifikator3_name,
                                         ba.no_ba, ba.tgl_ba');

        if ($userRoleAccess === 'user') {
            $query->where('item_inaktif.id_user', $currentUserId);
        }

        $data['items'] = $query->findAll();
        return view('pemindahan/data_inaktif', $data);
    }
    public function detailAktif($id = null)
    {
        $check = $this->checkPermission(['superadmin', 'admin', 'user']);
        if ($check !== true) return $check;

        if ($id === null || !is_numeric($id)) {
            session()->setFlashdata('error', 'ID Arsip tidak valid.');
            return redirect()->to(base_url('pemindahan/data_aktif'));
        }

        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('id');

        $query = $this->itemAktifModel
            ->join('users AS u1', 'u1.id = item_aktif.id_verifikator1', 'left')
            ->join('users AS u2', 'u2.id = item_aktif.id_verifikator2', 'left')
            ->join('users AS u3', 'u3.id = item_aktif.id_verifikator3', 'left')
            ->join('berita_acara AS ba', 'ba.id = item_aktif.id_berita_acara', 'left')
            ->join('klasifikasi', 'klasifikasi.id = item_aktif.id_klasifikasi', 'left')
            ->select('item_aktif.*, u1.name AS verifikator1_name, u2.name AS verifikator2_name, u3.name AS verifikator3_name,
                                         ba.no_ba, ba.tgl_ba, klasifikasi.nama_klasifikasi, klasifikasi.umur_aktif')
            ->where('item_aktif.id', $id);

        // Filter id_user jika user biasa
        if ($userRoleAccess === 'user') {
            $query->where('item_aktif.id_user', $currentUserId);
        }

        $data['item'] = $query->first();

        if (empty($data['item'])) {
            session()->setFlashdata('error', 'Arsip tidak ditemukan atau Anda tidak memiliki akses.');
            return redirect()->to(base_url('pemindahan/data_aktif'));
        }

        return view('pemindahan/detail_aktif', $data);
    }
    public function restoreItem()
    {
        $check = $this->checkPermission(['superadmin', 'admin']);
        if ($check !== true) return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk melakukan aksi ini.']);

        $itemId = $this->request->getPost('id');

        if (!is_numeric($itemId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID item tidak valid.']);
        }

        $itemToRestore = $this->itemInaktifModel->find($itemId);

        if (!$itemToRestore) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Item tidak ditemukan di arsip inaktif.']);
        }

        $this->db->transBegin();

        try {
            $dataForAktifTable = [];
            foreach ($itemToRestore as $key => $value) {
                if (in_array($key, $this->itemAktifModel->allowedFields) && $key != 'lokasi_simpan_aktif' && $key != 'no_berkas_baru' && $key != 'lokasi_simpan_new' && $key != 'id_ba') {
                    $dataForAktifTable[$key] = $value;
                }
            }

            $dataForAktifTable['lokasi_simpan'] = $itemToRestore['lokasi_simpan_aktif'] ?? null;
            $dataForAktifTable['status_arsip'] = 'aktif';
            $dataForAktifTable['status_pindah'] = 'belum';
            $dataForAktifTable['admin_notes'] = null;

            $dataForAktifTable['id_verifikator1'] = null;
            $dataForAktifTable['id_verifikator2'] = null;
            $dataForAktifTable['id_verifikator3'] = null;
            $dataForAktifTable['id_berita_acara'] = null;
            $dataForAktifTable['proposal_id'] = null;

            unset($dataForAktifTable['id']);
            unset($dataForAktifTable['created_at']);
            unset($dataForAktifTable['updated_at']);

            if (!$this->itemAktifModel->validate($dataForAktifTable)) {
                $errorsFromModel = $this->itemAktifModel->errors();
                $errorMessages = [];
                foreach ($errorsFromModel as $field => $msg) {
                    $errorMessages[] = "Kolom '$field' bermasalah: $msg";
                }
                $this->db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyalin item ke arsip aktif. Detail: ' . implode(', ', $errorMessages)]);
            }

            $inserted = $this->itemAktifModel->insert($dataForAktifTable);

            if ($inserted) {
                $deleted = $this->itemInaktifModel->delete($itemId);

                if ($deleted) {
                    $this->db->transCommit();
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Item berhasil dikembalikan ke arsip aktif.']);
                } else {
                    $this->db->transRollback();
                    log_message('error', 'Restore: Gagal menghapus item_inaktif ' . $itemId . ' setelah insert ke item_aktif.');
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus item dari arsip inaktif setelah penyalinan.']);
                }
            } else {
                $this->db->transRollback();
                $error = $this->db->error();
                $dbErrorMsg = $error['message'] ?? 'Unknown database error.';
                log_message('error', 'Restore: Gagal insert item ' . $itemId . ' ke item_aktif. DB Error: ' . $dbErrorMsg . ' Data: ' . json_encode($dataForAktifTable));
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyalin item ke arsip aktif. Error database: ' . $dbErrorMsg]);
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Restore: Exception saat memproses item ' . $itemId . ': ' . $e->getMessage() . "\nStack Trace: " . $e->getTraceAsString());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }
}
