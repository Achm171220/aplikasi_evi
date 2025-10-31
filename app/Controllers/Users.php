<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\HakFiturModel;
use App\Models\UnitKerjaEs3Model;
// Tambahkan use statement ini di bagian atas
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use CodeIgniter\RESTful\ResourceController; // Jika Anda menggunakan ResourceController
use CodeIgniter\HTTP\CURLRequest; // Untuk curlrequest

class Users extends BaseController
{
    protected $userModel;
    protected $session;
    protected $hakFiturModel;
    protected $unitKerjaEs3Model;

    private $apiUrl = 'https://api-stara.bpkp.go.id/api/pegawai';
    private $apiToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1dWlkIjoiY2QxYjU4ZmMtMDdlYi00NmIyLWIyM2MtMzUxZmZmZTNmNTllIiwibmFtYV9hcGxpa2FzaSI6IkVWSSAoRXZhbHVhc2kgSW50ZXJuYWwgS2VhcnNpcGFuKSIsInVzZXJuYW1lIjoiLSIsImlhdCI6MTc1ODc4NzY4MSwiaXNzIjoiIyMkLjRwMVIzZjNyM241aS4kIyMifQ.0D727o2kTeLKPDW5xjMT1qvhz8LKSHVx9NFkixI7PSw'; // Token Anda

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->hakFiturModel = new HakFiturModel();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
        $this->session = session();
    }

    // (R)EAD: Menampilkan daftar user
    public function index()
    {
        $data = [
            'title'   => 'Manajemen Users',
            'session' => $this->session,
        ];
        return view('users/index', $data);
    }

    // (R)EAD: Endpoint AJAX untuk DataTables
    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->userModel->getDataTablesUsers($this->request);

            $data = [];
            foreach ($result['data'] as $user) {
                $statusBadge = $user['status'] == 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>';

                // Logika untuk menampilkan Unit Kerja (tidak berubah)
                $unitKerja = '<span class="text-muted fst-italic">Belum diatur</span>';
                if ($user['role_access'] === 'user' && !empty($user['nama_es3'])) {
                    $unitKerja = '<strong>' . esc($user['nama_es3']) . '</strong><br><small class="text-muted">' . esc($user['kode_es2']) . '</small>';
                } elseif ($user['role_access'] === 'admin' && !empty($user['nama_es2_admin'])) {
                    $unitKerja = '<span class="badge bg-info text-dark">Admin Es. 2:</span><br><strong>' . esc($user['nama_es2_admin']) . '</strong>';
                } elseif ($user['role_access'] === 'superadmin') {
                    $unitKerja = '<span class="badge bg-primary">Akses Penuh</span>';
                }

                // --- LOGIKA BARU UNTUK KOLOM ROLE JABATAN ---
                $roleJabatan = '<span class="badge bg-secondary fst-italic">Belum Diatur</span>';
                // Pengecekan: if (ada dan tidak NULL)
                if (isset($user['role_jabatan']) && $user['role_jabatan'] !== null && $user['role_jabatan'] !== '') {
                    $badgeClass = 'bg-dark';
                    switch ($user['role_jabatan']) {
                        case 'sekretaris':
                            $badgeClass = 'bg-primary';
                            break;
                        case 'pengelola_arsip':
                            $badgeClass = 'bg-info';
                            break;
                        case 'arsiparis':
                            $badgeClass = 'bg-success';
                            break;
                        case 'pengampu':
                            $badgeClass = 'bg-warning text-dark';
                            break;
                        case 'verifikator':
                            $badgeClass = 'bg-danger';
                            break;
                        case 'pimpinan':
                            $badgeClass = 'bg-dark';
                            break;
                    }
                    $roleJabatan = '<span class="badge ' . $badgeClass . '">' . ucfirst($user['role_jabatan']) . '</span>';
                }
                // Siapkan tombol aksi (tidak berubah)
                $btn_edit = '<a href="' . site_url('users/edit/' . $user['id']) . '" class="btn btn-sm btn-warning me-1" title="Edit">Edit</a>';
                $form_delete = '<form action="' . site_url('users/' . $user['id']) . '" method="post" class="d-inline form-delete">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">Hapus</button>
                </form>';

                // Susun baris sesuai urutan baru di view
                $row = [
                    '',
                    esc($user['name']),
                    esc($user['email']),
                    ucfirst($user['role_access']),
                    $roleJabatan, // <-- KOLOM BARU ROLE JABATAN
                    $unitKerja,   // Posisi Unit Kerja bergeser
                    $statusBadge,
                    $btn_edit . ' ' . $form_delete
                ];
                $data[] = $row;
            }
            return $this->response->setJSON([
                'draw' => $result['draw'],
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $data,
            ]);
        }
    }
    // Metode untuk mengambil data pegawai dari API untuk Select2 (Sudah Anda sediakan)
    public function getPegawaiFromApiForSelect2()
    {
        // Ambil parameter pencarian (term) dan halaman (page) dari Select2
        $term = $this->request->getGet('term');
        $page = $this->request->getGet('page') ?? 1;

        // Gunakan HTTP Client CodeIgniter
        $client = \Config\Services::curlrequest([
            'timeout' => 8, // Tambahkan timeout yang memadai
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept'        => 'application/json',
            ]
        ]);

        // *Asumsi:* Endpoint API mendukung pencarian via query parameter 'search'
        // dan pagination (limit/offset)
        $limit = 10;
        $offset = ($page - 1) * $limit;
        // Sesuaikan URL jika API memiliki endpoint khusus untuk pencarian daftar pegawai.
        $searchUrl = $this->apiUrl . '?search=' . urlencode($term) . '&limit=' . $limit . '&offset=' . $offset;

        try {
            $response = $client->get($searchUrl);
            $apiData = json_decode($response->getBody(), true);
            $select2Results = [];

            // Cek status dan struktur data API (sesuai screenshot: data.result)
            if ($response->getStatusCode() === 200 && isset($apiData['status']) && $apiData['status'] === 'Success' && isset($apiData['data']['result'])) {
                $pegawaiList = $apiData['data']['result'];

                foreach ($pegawaiList as $pegawai) {
                    $nip = $pegawai['nip'] ?? $pegawai['niplama'] ?? null;
                    $namaLengkap = $pegawai['s_nama_lengkap'] ?? 'Nama Tidak Ada';
                    $jabatanLengkap = $pegawai['jabatan'] ?? 'Jabatan Tidak Diketahui';
                    $emailDinas = $pegawai['s_email_dinas'] ?? '';
                    $namaUnit = $pegawai['namaunit'] ?? 'Unit Tidak Diketahui'; // <--- TAMBAH INI

                    // Lakukan mapping jabatan lengkap ke format enum DB lokal
                    $role_jabatan_mapped = $this->mapJabatanToRoleEnum($jabatanLengkap);

                    $select2Results[] = [
                        'id'            => $nip,
                        'text'          => $namaLengkap . ' - ' . $jabatanLengkap,
                        'email'         => $emailDinas,
                        'jabatan_text'  => $jabatanLengkap,
                        'role_jabatan'  => $role_jabatan_mapped,
                        'namaunit'      => $namaUnit, // <--- TAMBAH INI
                    ];
                }

                // Atur Pagination (asumsi 'more' jika jumlah hasil sama dengan limit)
                $more = count($pegawaiList) == $limit;

                return $this->response->setJSON([
                    'results' => $select2Results,
                    'pagination' => ['more' => $more]
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'API error in getPegawaiFromApiForSelect2: ' . $e->getMessage());
            // Lanjutkan untuk mengembalikan hasil kosong jika terjadi error
        }

        return $this->response->setJSON([
            'results' => [],
            'pagination' => ['more' => false]
        ]);
    }

    // Helper function untuk memetakan nama jabatan dari API ke role_jabatan lokal
    private function mapJabatanToRoleEnum(string $jabatanLengkap): string
    {
        $jabatanLower = strtolower($jabatanLengkap);

        // Sesuaikan logika mapping ini sesuai kebutuhan bisnis Anda
        if (strpos($jabatanLower, 'sekretaris') !== false) {
            return 'sekretaris';
        }
        if (strpos($jabatanLower, 'arsip') !== false) {
            // Cek apakah pengelola arsip atau arsiparis
            return strpos(
                $jabatanLower,
                'pengelola'
            ) !== false ? 'pengelola_arsip' : 'arsiparis';
        }
        // Contoh untuk role lain
        if (
            strpos($jabatanLower, 'ketua') !== false || strpos($jabatanLower, 'kepala') !== false
        ) {
            return 'pimpinan';
        }

        // Kembali ke string kosong jika tidak ada yang cocok (form akan set ke "-- Pilih Jabatan --")
        return '';
    }

    // Tambahkan metode ini untuk memastikan $hak_fitur didefinisikan di edit
    // (Jika Anda tidak memiliki HakFiturModel atau HakFiturModel tidak memiliki metode ini, sesuaikan)
    protected function getEselon3Options()
    {
        // Sesuaikan dengan cara Anda mengambil daftar Eselon 3
        return [
            ['id' => 1, 'nama_es3' => 'Unit Eselon 3 A'],
            ['id' => 2, 'nama_es3' => 'Unit Eselon 3 B'],
        ];
    }
    private function prepareFormData($user = null)
    {
        $data = ['user' => $user, 'validation' => \Config\Services::validation()];
        $userRole = $this->session->get('role_access');
        $authData = $this->session->get('auth_data');

        if ($userRole === 'admin' && !empty($authData['id_es2'])) {
            // Admin hanya bisa membuat/melihat user untuk Es.3 di bawah wewenangnya
            $data['es3_options'] = $this->unitKerjaEs3Model
                ->where('id_es2', $authData['id_es2'])
                ->orderBy('nama_es3', 'ASC')
                ->findAll();
        }

        // Ambil hak fitur dari user yang sedang diedit (jika ada)
        if ($user) {
            $data['hak_fitur'] = $this->hakFiturModel->where('id_user', $user['id'])->first();
        }

        return $data;
    }

    public function new()
    {
        $data = $this->prepareFormData();
        $data['title'] = 'Tambah Pengguna Baru';
        return view('users/form', $data);
    }

    // (C)REATE: Memproses data dari form tambah
    public function create()
    {
        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');
        $postData = $this->request->getPost();

        $nipPost = $postData['nip'] ?? null;
        $idEs3Post = $postData['id_es3'] ?? null;

        // Cek apakah user sudah ada di DB Lokal (berdasarkan NIP yang ditarik dari API)
        $existingUser = $nipPost ? $this->userModel->getUserByNip($nipPost) : null;
        $isExisting = (bool)$existingUser;
        $userIdToManage = $existingUser['id'] ?? null;

        // --- 1. TENTUKAN ATURAN VALIDASI DINAMIS ---

        $validationRules = [
            'name'             => 'required|min_length[3]',
            'status'           => 'required|in_list[aktif,non-aktif]',
            'nama_jabatan_api' => 'permit_empty|max_length[255]',
            'namaunit'         => 'permit_empty|max_length[255]',
        ];

        if (!$isExisting) {
            // JIKA BENAR-BENAR BARU: Terapkan is_unique
            $validationRules['nip'] = 'required|max_length[50]|is_unique[users.nip]';
            $validationRules['email'] = 'required|valid_email|is_unique[users.email]';
        } else {
            // JIKA SUDAH ADA (ATTACH HAK FITUR): Hapus is_unique
            $validationRules['nip'] = 'required|max_length[50]';
            $validationRules['email'] = 'required|valid_email';
        }

        // Aturan Admin Wajib
        if (
            $userRole === 'admin'
        ) {
            $validationRules['id_es3'] = 'required|integer';
        }

        // --- 2. JALANKAN VALIDASI ---
        if (!$this->validate($validationRules)) {
            // Jika validasi form dasar gagal
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- 3. Proses Transaksi ---
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if (!$isExisting) {
                // A. JALUR CREATE (User baru)
                $dataUser = [
                    'nip'      => $nipPost,
                    'name'     => $postData['name'],
                    'email'    => $postData['email'],
                    'status'   => $postData['status'],
                    'role_jabatan' => $postData['role_jabatan'],
                    'nama_jabatan_api' => $postData['nama_jabatan_api'],
                    'namaunit' => $postData['namaunit'],
                    'role_access' => ($userRole === 'admin') ? 'user' : $postData['role_access'],
                    // 'password' => password_hash(random_string('alnum', 16), PASSWORD_DEFAULT)
                ];
                // Menggunakan insert() karena kita tahu ini baru
                $this->userModel->insert($dataUser);
                $userIdToManage = $this->userModel->getInsertID();
            } else {
                // B. JALUR ATTACH HAK FITUR (User sudah ada)
                // Pastikan status user diupdate (jika form mengizinkan)
                $this->userModel->update($userIdToManage, [
                    'status' => $postData['status'],
                    'role_jabatan' => $postData['role_jabatan'],
                ]);
            }

            // --- 4. Update/Create Hak Fitur (Wajib untuk Admin) ---
            if ($userRole === 'admin' && !empty($authData['id_es2'])) {
                $hakFitur = $this->hakFiturModel->where('id_user', $userIdToManage)->first();
                $dataHakFitur = [
                    'id_user' => $userIdToManage,
                    'id_es3'  => $idEs3Post,
                    'id_es2'  => $authData['id_es2'],
                ];

                if ($hakFitur) {
                    $this->hakFiturModel->update($hakFitur['id'], $dataHakFitur);
                } else {
                    $this->hakFiturModel->insert($dataHakFitur);
                }
            }

            if (
                $db->transStatus() === false
            ) {
                $db->transRollback();
                throw new \Exception('Gagal menyelesaikan transaksi database.');
            } else {
                $db->transCommit();
                $this->session->setFlashdata(
                    'success',
                    'Pengguna dan Hak Akses berhasil ditambahkan/diperbarui.'
                );
                return redirect()->to('/users');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Users Create/Attach Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // (U)PDATE: Menampilkan form edit
    public function edit($id = null)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->prepareFormData($user);
        $data['title'] = 'Edit Pengguna';
        return view('users/form', $data);
    }

    public function update($id = null)
    {
        // Cek dulu apakah user yang akan diedit ada
        $userToUpdate = $this->userModel->find($id);
        if (!$userToUpdate) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userRole = $this->session->get('role_access'); // Gunakan role_access dari sesi
        $authData = $this->session->get('auth_data');
        $postData = $this->request->getPost();

        // --- 1. Siapkan data yang akan divalidasi dan disimpan ---

        // Data yang WAJIB ada di form
        $dataForSave = [
            'id'           => $id,
            'name'         => $postData['name'],
            'email'        => $postData['email'],
            'status'       => $postData['status'],

            'nip'                => $postData['nip'] ?? $userToUpdate['nip'],
            'nama_jabatan_api'   => $postData['nama_jabatan_api'] ?? $userToUpdate['nama_jabatan_api'],

            // Role Jabatan Lokal (Ambil dari POST jika ada, jika tidak, pakai yang lama)
            'role_jabatan'       => $postData['role_jabatan'] ?? $userToUpdate['role_jabatan'],

            // PERBAIKAN DI SINI (Line 411): Gunakan nilai lama jika tidak ada di POST
            'role_access'        => $postData['role_access'] ?? $userToUpdate['role_access'],
        ];

        // dd($dataForSave);
        // --- A. Penanganan Perubahan Password ---
        if (!empty($postData['password'])) {
            // Password akan di-hash otomatis oleh Callbacks di Model
            $dataForSave['password'] = $postData['password'];
        }

        // --- B. Hak Akses (Role Access) - Hanya boleh diubah Superadmin ---
        if ($userRole === 'superadmin') {
            // PERBAIKAN: Ambil role_access dari POST
            $dataForSave['role_access'] = $postData['role_access']; // Ini sudah diatasi oleh perbaikan di atas
            // Role Jabatan (Jika Superadmin mengedit, gunakan nilai POST yang sudah divalidasi ENUM)
            $dataForSave['role_jabatan'] = $postData['role_jabatan']; // Ini juga sudah diatasi oleh perbaikan di atas
        }

        // --- C. Hak Fitur (Jika Admin yang bertugas) ---
        if ($userRole === 'admin' && isset($postData['id_es3'])) {
            // Admin hanya dapat menetapkan id_es3 dan role_jabatan lokal
            // Validasi required untuk id_es3 harus dilakukan di sini atau di form request
            if (empty($postData['id_es3'])) {
                session()->setFlashdata('error', 'Admin wajib menugaskan user ke Unit Eselon 3.');
                return redirect()->back()->withInput();
            }
        }

        // --- 2. Jalankan validasi dan save utama melalui Model ---

        if ($this->userModel->save($dataForSave) === false) {
            // Model akan mengisi $this->userModel->errors() jika validasi gagal
            session()->setFlashdata('validation_alert_html', implode('<br>', $this->userModel->errors()));
            return redirect()->back()->withInput();
        }

        // --- 3. Jika save utama berhasil, lanjutkan transaksi Hak Fitur ---
        $db = \Config\Database::connect();
        $db->transBegin();

        $success = true;

        try {
            // --- 4. Update hak fitur jika Admin (atau Superadmin yang mengelola unit kerja) ---

            if ($userRole === 'admin' && !empty($authData['id_es2'])) {
                // Admin hanya dapat mengelola Hak Fitur di bawah ES2-nya sendiri
                $hakFitur = $this->hakFiturModel->where('id_user', $id)->first();

                // Data Hak Fitur menggunakan id_es2 admin yang sedang login
                $dataHakFitur = [
                    'id_user' => $id,
                    'id_es3'  => $postData['id_es3'],
                    'id_es2'  => $authData['id_es2'], // ID ES2 Admin
                    // Anda mungkin perlu mencari ID ES1 induknya di sini jika Anda mengizinkan perubahan ES2 Admin
                    'id_es1'  => $authData['id_es1'] ?? null,
                ];

                if ($hakFitur) {
                    $this->hakFiturModel->update($hakFitur['id'], $dataHakFitur);
                } else {
                    $this->hakFiturModel->insert($dataHakFitur);
                }
            }

            // --- 5. Finalisasi transaksi dan redirect ---
            if ($db->transStatus() === false) {
                $db->transRollback();
                $success = false;
                session()->setFlashdata('error', 'Gagal memperbarui data karena transaksi database gagal.');
            } else {
                $db->transCommit();
                session()->setFlashdata('success', 'Data pengguna berhasil diperbarui.');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            $success = false;
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        if (!$success) {
            return redirect()->back()->withInput();
        }

        return redirect()->to('/users');
    }
    // (D)ELETE: Menghapus data
    public function delete($id = null)
    {
        // 1. Ambil data lengkap user yang akan dihapus
        $userToDelete = $this->userModel->find($id);
        if (!$userToDelete) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // --- PERBAIKAN: Kirim data lengkap ke has_permission ---
        // $contextData harus mencakup role_access dan id_es3
        $contextDataForPermission = [
            'role_access' => $userToDelete['role_access']
        ];
        // Jika user yang dihapus adalah user biasa, ambil id_es3-nya dari hak_fitur
        if ($userToDelete['role_access'] === 'user') {
            $hakFiturUserToDelete = $this->hakFiturModel->where('id_user', $id)->first();
            if ($hakFiturUserToDelete) {
                $contextDataForPermission['id_es3'] = $hakFiturUserToDelete['id_es3'];
            }
        }

        // 2. Cek izin penghapusan
        if (!has_permission('delete_user', $contextDataForPermission)) {
            // --- PERBAIKAN: Pesan error lebih umum ---
            return redirect()->to('/users')->with('error', 'Anda tidak memiliki izin untuk menghapus user ini.');
        }

        // 3. Jika diizinkan, hapus data
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Hapus hak fiturnya terlebih dahulu (jika ada)
            $this->hakFiturModel->where('id_user', $id)->delete();
            // Kemudian hapus user
            $this->userModel->delete($id);

            if (
                $db->transStatus() === false
            ) {
                $db->transRollback();
                throw new \Exception('Gagal menghapus user karena transaksi database gagal.');
            } else {
                $db->transCommit();
                session()->setFlashdata('success', 'Pengguna berhasil dihapus.');
                return redirect()->to('/users');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error delete user: ' . $e->getMessage());
            return redirect()->to('/users')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header (Kolom F ditambahkan)
        $sheet->setCellValue('A1', 'Nama Lengkap');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Password');
        $sheet->setCellValue('D1', 'Role Access');
        $sheet->setCellValue('E1', 'Role Jabatan'); // <-- KOLOM BARU
        $sheet->setCellValue('F1', 'Status');

        // Styling Header
        $headerStyle = [ /* ... (style array Anda) ... */];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        // Daftar opsi untuk dropdown validasi
        $roleJabatanOptions = '"sekretaris,pengelola arsip,arsiparis,pengampu,verifikator,pimpinan"';

        // Buat dropdown validasi untuk Role dan Status
        for ($i = 2; $i <= 101; $i++) {
            $sheet->getCell('D' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1('"admin,user"');
            $sheet->getCell('E' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1($roleJabatanOptions);
            $sheet->getCell('F' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1('"aktif,non-aktif"');
        }

        // Komentar/Petunjuk
        $sheet->getComment('A1')->getText()->createTextRun("Wajib diisi. Nama lengkap pengguna.");
        $sheet->getComment('B1')->getText()->createTextRun("Wajib diisi. Harus unik dan format email valid.");
        $sheet->getComment('C1')->getText()->createTextRun("Wajib diisi. Minimal 6 karakter.");
        $sheet->getComment('D1')->getText()->createTextRun("Wajib diisi. Pilih: admin atau user.");
        $sheet->getComment('E1')->getText()->createTextRun("Wajib diisi. Pilih dari dropdown."); // <-- KOMENTAR BARU
        $sheet->getComment('F1')->getText()->createTextRun("Wajib diisi. Pilih: aktif atau non-aktif.");

        foreach (range('A', 'F') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Template_Import_Users.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    /**
     * Memproses file Excel yang diunggah untuk import data user.
     */
    public function prosesImport()
    {
        $validationRule = ['file_excel' => ['label' => 'File Excel', 'rules' => 'uploaded[file_excel]|ext_in[file_excel,xlsx,xls]']];
        if (!$this->validate($validationRule)) {
            session()->setFlashdata('show_import_modal', true);
            return redirect()->to('/users')->withInput()->with('errors', $this->validator->getErrors());
        }

        $fileExcel = $this->request->getFile('file_excel');
        $spreadsheet = IOFactory::load($fileExcel);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $dataToInsert = [];
        $errors = [];
        $berhasil = 0;
        $gagal = 0;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex == 0 || empty(array_filter($row))) continue;

            $data = [
                'name'         => trim($row[0] ?? ''),
                'email'        => trim($row[1] ?? ''),
                'password'     => trim($row[2] ?? ''),
                'role_access'  => strtolower(trim($row[3] ?? '')),
                'role_jabatan' => strtolower(trim($row[4] ?? '')), // <-- DATA BARU
                'status'       => strtolower(trim($row[5] ?? 'aktif')),
            ];

            // Validasi per baris menggunakan aturan dari UserModel
            // Pastikan UserModel sudah diperbarui
            if ($this->userModel->validate($data) === false) {
                $gagal++;
                $errorMessages = $this->userModel->errors();
                $errors[] = "Baris " . ($rowIndex + 1) . ": " . implode(', ', $errorMessages);
            } else {
                $berhasil++;
                $dataToInsert[] = $data;
            }
        }

        if (!empty($dataToInsert)) {
            $this->userModel->insertBatch($dataToInsert);
        }

        $pesan = "Proses import selesai. Berhasil: $berhasil data. Gagal: $gagal data.";
        session()->setFlashdata('success', $pesan);
        if (!empty($errors)) {
            session()->setFlashdata('import_errors', $errors);
        }
        session()->setFlashdata('show_import_modal', true);
        return redirect()->to('/users');
    }
    public function exportExcel()
    {
        // Pengecekan keamanan: hanya Superadmin yang boleh mengakses
        if (session()->get('role_access') !== 'superadmin') {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki izin untuk mengekspor data pengguna.');
        }

        $userModel = new \App\Models\UserModel();
        $users = $userModel->select('id, name, email, password, role_access, role_jabatan, status, created_at, updated_at')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pengguna');

        // Header Kolom
        $headers = [
            'ID',
            'Nama Lengkap',
            'Email',
            'Password (Hashed)',
            'Role Access',
            'Role Jabatan',
            'Status',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Styling Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ];
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);
        $sheet->getRowDimension('1')->setRowHeight(30);

        // Mengisi Data
        $rowNum = 2;
        foreach ($users as $user) {
            $rowData = [
                $user['id'],
                $user['name'],
                $user['email'],
                $user['password'], // Password hashed
                ucfirst($user['role_access']),
                ucfirst($user['role_jabatan'] ?? 'Belum Diatur'), // Handle null
                ucfirst($user['status']),
                $user['created_at'],
                $user['updated_at'],
            ];
            $sheet->fromArray($rowData, NULL, 'A' . $rowNum);
            $rowNum++;
        }

        // Auto-size kolom
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output ke browser
        $writer = new Xlsx($spreadsheet);
        $filename = 'data_pengguna_' . date('Y-m-d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
}
