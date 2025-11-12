<?php

namespace App\Controllers;

use App\Models\ItemAktifModel;
use App\Models\KlasifikasiModel;
use App\Models\JenisNaskahModel;
use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;
use App\Models\ImportDaftarModel;
use App\Models\PeminjamanModel;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;


class ItemAktif extends BaseController
{
    protected $itemAktifModel;
    protected $klasifikasiModel;
    protected $jenisNaskahModel;
    protected $unitKerjaEs1Model;
    protected $unitKerjaEs2Model;
    protected $unitKerjaEs3Model;
    protected $importDaftarModel;
    protected $peminjamanModel;
    protected $session;
    // URL API
    protected $simaApiUrl = 'https://api-stara.bpkp.go.id/api/sima/pkau/laporan';
    protected $sadewaApiUrl = 'https://api-stara.bpkp.go.id/api/surat-masuk';

    // Token Anda
    private $apiToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1dWlkIjoiY2QxYjU4ZmMtMDdlYi00NmIyLWIyM2MtMzUxZmZmZTNmNTllIiwibmFtYV9hcGxpa2FzaSI6IkVWSSAoRXZhbHVhc2kgSW50ZXJuYWwgS2VhcnNpcGFuKSIsInVzZXJuYW1lIjoiLSIsImlhdCI6MTc1ODc4NzY4MSwiaXNzIjoiIyMkLjRwMVIzZjNyM241aS4kIyMifQ.0D727o2kTeLKPDW5xjMT1qvhz8LKSHVx9NFkixI7PSw';

    public function __construct()
    {
        $this->itemAktifModel = new ItemAktifModel();
        $this->klasifikasiModel = new KlasifikasiModel();
        $this->jenisNaskahModel = new JenisNaskahModel();
        $this->unitKerjaEs1Model = new UnitKerjaEs1Model();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
        $this->importDaftarModel = new ImportDaftarModel();
        $this->peminjamanModel = new PeminjamanModel(); // Inisialisasi
        $this->session = session();
    }
    //HALAMAN AWAL  
    public function index()
    {
        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');

        // --- PERBAIKAN UTAMA: INISIALISASI VARIABEL DI AWAL ---
        $es2_filter_options = [];
        $es3_filter_options = [];
        $id_es2_filter_prefill = null;
        // --- AKHIR PERBAIKAN ---

        // --- Logika untuk mengisi dropdown filter Eselon 2 & 3 ---
        if ($userRole === 'superadmin') {
            $es2_filter_options = $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll();
        } elseif ($userRole === 'admin' && !empty($authData['id_es2'])) {
            // Admin hanya melihat Es2 dan Es3 di lingkupnya
            $id_es2_filter_prefill = $authData['id_es2']; // Ini ID Es2 Admin
            $es2_filter_options = $this->unitKerjaEs2Model->where('id', $id_es2_filter_prefill)->findAll();
            $es3_filter_options = $this->unitKerjaEs3Model->where('id_es2', $id_es2_filter_prefill)->orderBy('nama_es3', 'ASC')->findAll();
        }
        // User biasa dengan hak Es3 tidak perlu filter, karena data sudah difilter di model
        // Jadi, es2_filter_options dan es3_filter_options akan tetap kosong untuk mereka, yang sudah benar.

        $data = [
            'title'            => 'Manajemen Item Arsip Aktif',
            'session'          => $this->session,
            'es2_filter_options' => $es2_filter_options,
            'es3_filter_options' => $es3_filter_options, // Sekarang ini dijamin ada
            'id_es2_filter_prefill' => $id_es2_filter_prefill,
            'disable_es2_filter' => ($userRole === 'admin'),
            // Nilai filter yang sedang aktif (untuk mempertahankan pilihan di form)
            'current_es2_filter_id' => $this->request->getGet('es2_id'),
            'current_es3_filter_id' => $this->request->getGet('es3_id'),
        ];
        return view('item_aktif/index', $data);
    }
    //FUNGSI "READ DATA" DENGAN METODE AJAX LIST DATA
    public function listData()
    {
        if ($this->request->isAJAX()) {
            $userRole = session()->get('role_access');
            $authData = session()->get('auth_data');

            // --- PERBAIKAN: Ambil filter sebagai ID ---
            $es2_id_filter = $this->request->getPost('es2_id_filter');
            $es3_id_filter = $this->request->getPost('es3_id_filter');

            // Terapkan filter dari form filter di atas tabel
            if (!empty($es3_id_filter)) {
                $this->itemAktifModel->where('item_aktif.id_es3', $es3_id_filter); // Filter berdasarkan ID
            } elseif (!empty($es2_id_filter)) {
                $this->itemAktifModel->where('item_aktif.id_es2', $es2_id_filter); // Filter berdasarkan ID
            }

            // Panggil getDataTablesList yang akan menerapkan filter hak akses Model
            $result = $this->itemAktifModel->getDataTablesList($this->request);

            $data = [];
            foreach ($result['data'] as $item) {

                // Kolom 2: Kode Unit
                $kodeUnit = '<span class="badge text-bg-info">' . esc($item['kode_es2']) . '</span>' .
                    '<i class="bi bi-chevron-right mx-1"></i>' .
                    '<span class="badge text-bg-light text-dark">' . esc($item['kode_es3']) . '</span>';

                // Kolom 4: No Dokumen - Judul Dokumen
                $noDanJudul = '<div>' . esc($item['no_dokumen']) . '</div>' .
                    '<div class="fw-bold text-primary">' . esc($item['judul_dokumen']) . '</div>';

                // Kolom 5: Tanggal Dokumen (Format dd-mm-yyyy)
                $tglDokumen = date('d-m-Y', strtotime($item['tgl_dokumen']));

                // Logika Status Berkas
                $statusBerkas = $item['id_berkas']
                    ? '<span class="badge badge-soft-success text-bg-success"><i class="bi bi-folder-check me-2"></i><span>' . esc($item['nama_berkas']) . '</span></span>'
                    : '<span class="badge text-bg-secondary"><i class="bi bi-x-circle me-2"></i><span>Belum Diberkaskan</span></span>';

                // --- PENYESUAIAN LOGIKA AKSI DENGAN HELPER ---
                $aksi = '';

                if (has_permission('cud_arsip')) {
                    // Tampilan untuk User & Superadmin
                    $btn_edit = '<a href="' . site_url('item-aktif/edit/' . $item['id']) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>';
                    $btn_lepas = '';
                    if ($item['id_berkas']) {
                        $btn_lepas = '<button type="button" class="btn btn-sm btn-info btn-lepas-berkas" data-id="' . $item['id'] . '" data-judul="' . esc($item['judul_dokumen']) . '" title="Lepas dari Berkas"><i class="bi bi-box-arrow-up-left"></i> Lepas</button>';
                    }
                    $form_delete = '';
                    if (!$item['id_berkas']) {
                        $form_delete = '<form action="' . site_url('item-aktif/delete/' . $item['id']) . '" method="post" class="d-inline form-delete">'
                            . csrf_field() // Panggil helper di sini
                            . '<input type="hidden" name="_method" value="DELETE">'
                            . '<button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i> Hapus</button>'
                            . '</form>';
                    }
                    $aksi = '<div class="d-flex justify-content-center gap-2">' . $btn_edit . $btn_lepas . $form_delete . '</div>';
                } else {
                    // Tampilan untuk Admin (read-only)
                    $aksi = '<a href="' . site_url('item-aktif/detail/' . $item['id']) . '" class="btn btn-sm btn-light">Lihat Detail</a>';
                }

                // Gabungkan tombol aksi jika ada
                if (empty($aksi)) {
                    $aksi = '<div class="d-flex justify-content-center gap-2">' . $btn_edit . $btn_lepas . $form_delete . '</div>';
                }
                // Susun baris sesuai urutan baru
                $row = [
                    '', // No. urut
                    $kodeUnit,
                    esc($item['kode_klasifikasi']),
                    $noDanJudul,
                    $tglDokumen,
                    $statusBerkas,
                    $aksi
                ];
                $data[] = $row;
            }
            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data,]);
        }
    }
    public function getEs2ForFilter()
    {
        if ($this->request->isAJAX()) {
            $userRole = session()->get('role_access');
            $authData = session()->get('auth_data');

            if ($userRole === 'superadmin') {
                $es2Options = (new \App\Models\UnitKerjaEs2Model())->orderBy('nama_es2', 'ASC')->findAll();
            } elseif ($userRole === 'admin' && !empty($authData['id_es2'])) {
                $es2Options = (new \App\Models\UnitKerjaEs2Model())->where('id', $authData['id_es2'])->findAll();
            } else {
                $es2Options = [];
            }

            // Format untuk Select2
            $formattedOptions = [['id' => '', 'text' => '-- Semua Eselon 2 --']];
            foreach ($es2Options as $opt) {
                $formattedOptions[] = ['id' => $opt['id'], 'text' => $opt['nama_es2']];
            }
            return $this->response->setJSON(['results' => $formattedOptions]);
        }
    }

    /**
     * AJAX: Menyediakan daftar Eselon 3 untuk dropdown filter.
     */
    public function getEs3ForFilter($id_es2 = null)
    {
        if ($this->request->isAJAX()) {
            $userRole = session()->get('role_access');
            $authData = session()->get('auth_data');

            $es3Options = [];
            if ($id_es2) { // Jika Es2 dipilih
                $es3Options = (new \App\Models\UnitKerjaEs3Model())->where('id_es2', $id_es2)->orderBy('nama_es3', 'ASC')->findAll();
            } elseif ($userRole === 'admin' && !empty($authData['id_es2'])) {
                // Jika admin tapi Es2 tidak dipilih, tampilkan Es3 di bawah Es2 admin
                $es3Options = (new \App\Models\UnitKerjaEs3Model())->where('id_es2', $authData['id_es2'])->orderBy('nama_es3', 'ASC')->findAll();
            } else if ($userRole === 'superadmin' && !$id_es2) {
                // Jika superadmin dan Es2 belum dipilih, tampilkan semua Es3
                $es3Options = (new \App\Models\UnitKerjaEs3Model())->orderBy('nama_es3', 'ASC')->findAll();
            }

            // Format untuk Select2
            $formattedOptions = [['id' => '', 'text' => '-- Semua Eselon 3 --']];
            foreach ($es3Options as $opt) {
                $formattedOptions[] = ['id' => $opt['id'], 'text' => $opt['nama_es3']];
            }
            return $this->response->setJSON(['results' => $formattedOptions]);
        }
    }

    public function detail($id = null)
    {
        // Query untuk mengambil data lengkap dengan join
        $item = $this->itemAktifModel->builder()
            ->select('item_aktif.*, klasifikasi.kode as kode_klasifikasi, klasifikasi.nama_klasifikasi, jenis_naskah.nama_naskah, es1.nama_es1, es2.nama_es2, es3.nama_es3, users.name as user_creator, berkas_aktif.nama_berkas')
            ->join('klasifikasi', 'klasifikasi.id = item_aktif.id_klasifikasi', 'left')
            ->join('jenis_naskah', 'jenis_naskah.id = item_aktif.id_jenis_naskah', 'left')
            ->join('unit_kerja_es3 as es3', 'es3.id = item_aktif.id_es3', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = item_aktif.id_es2', 'left')
            ->join('unit_kerja_es1 as es1', 'es1.id = es2.id_es1', 'left')
            ->join('users', 'users.id = item_aktif.id_user', 'left')
            ->join('berkas_aktif', 'berkas_aktif.id = item_aktif.id_berkas', 'left')
            ->where('item_aktif.id', $id)
            ->get()->getRowArray();

        if (!$item) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Detail Item Arsip: ' . esc($item['judul_dokumen']),
            'item'  => $item,
        ];

        return view('item_aktif/detail', $data);
    }

    private function prepareFormData($item = null)
    {
        $data = [
            'item'       => $item,
            'validation' => \Config\Services::validation(),
            // 'klasifikasi_options' => $this->klasifikasiModel->orderBy('kode', 'ASC')->findAll(),
            'klasifikasi_options' => $this->klasifikasiModel->getForDropdownInput(), // Panggil method baru
            'jenis_naskah_options' => $this->jenisNaskahModel->orderBy('nama_naskah', 'ASC')->findAll(),
        ];

        $authData = session()->get('auth_data');
        $userRole = session()->get('role_access');

        $id_es2_prefill = null;
        $es2_options = [];

        if ($userRole === 'superadmin') {
            // Superadmin bisa memilih semua Eselon 2
            $es2_options = $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll();
            if ($item) { // Jika mode edit, temukan Es2 dari Es3
                $es3 = $this->unitKerjaEs3Model->find($item['id_es3']);
                if ($es3) $id_es2_prefill = $es3['id_es2'];
            }
        } else if ($authData) {
            // Pengguna biasa
            $id_es2_hak_akses = null;
            if (!empty($authData['id_es3'])) {
                $es3 = $this->unitKerjaEs3Model->find($authData['id_es3']);
                if ($es3) $id_es2_hak_akses = $es3['id_es2'];
            } elseif (!empty($authData['id_es2'])) {
                $id_es2_hak_akses = $authData['id_es2'];
            }

            if ($id_es2_hak_akses) {
                // Ambil HANYA Eselon 2 yang sesuai dengan hak aksesnya
                $es2_options = $this->unitKerjaEs2Model->where('id', $id_es2_hak_akses)->findAll();
                $id_es2_prefill = $id_es2_hak_akses;
            }
        }

        $data['es2_options'] = $es2_options;
        $data['id_es2_prefill'] = $id_es2_prefill;

        return $data;
    }
    public function new()
    {
        if (!has_permission('cud_arsip')) {
            return redirect()->to('/item-aktif')->with('error', 'Anda tidak memiliki izin untuk menambah data.');
        }
        $data = $this->prepareFormData();
        $data['title'] = 'Tambah Item Arsip Aktif';
        return view('item_aktif/form', $data);
    }

    public function create()
    {
        if (!has_permission('cud_arsip')) {
            return redirect()->to('/item-aktif')->with('error', 'Anda tidak memiliki izin untuk menyimpan data.');
        }

        // --- 1. Siapkan semua data yang dibutuhkan untuk validasi dan penyimpanan ---
        $postData = $this->request->getPost();

        $dataForValidation = [
            'id_klasifikasi'    => $postData['id_klasifikasi'] ?? null,
            'id_jenis_naskah'   => $postData['id_jenis_naskah'] ?? null,
            'id_es3'            => $postData['id_es3'] ?? null,
            'no_dokumen'        => $postData['no_dokumen'] ?? null,
            'judul_dokumen'     => $postData['judul_dokumen'] ?? null,
            'tgl_dokumen'       => $postData['tgl_dokumen'] ?? null,
            'tahun_cipta'       => date('Y', strtotime($postData['tgl_dokumen'] ?? date('Y-m-d'))), // Ambil tahun dari tgl_dokumen
            'jumlah'            => $postData['jumlah'] ?? null,
            'tk_perkembangan'   => $postData['tk_perkembangan'] ?? null,
            'lokasi_simpan'     => $postData['lokasi_simpan'] ?? null,
            'media_simpan'      => $postData['media_simpan'] ?? null,
            'no_box'            => $postData['no_box'] ?? null,
            'nama_file'         => $postData['nama_file'] ?? null,
            'nama_folder'       => $postData['nama_folder'] ?? null,
            'nama_link'         => $postData['nama_link'] ?? null,
            'dasar_catat'       => $postData['dasar_catat'] ?? null,

            // <<< INI KUNCI PERBAIKAN: Sertakan id_user dan status_arsip SEBELUM VALIDASI >>>
            'id_user'           => session()->get('id'), // Dari session
            'status_arsip'      => 'aktif', // Default
            'status_pindah'     => 'belum', // Default
        ];
        // dd($dataForValidation);
        // --- 2. Ambil aturan validasi LENGKAP dari model ---
        $rules = $this->itemAktifModel->getValidationRules();

        // --- 3. Jalankan validasi dengan data lengkap ---
        if (!$this->validate($rules, [], $dataForValidation)) {
            $validationErrors = $this->validator->getErrors();
            $formattedErrors = '';
            foreach ($validationErrors as $field => $message) {
                $readableField = ucfirst(str_replace('_', ' ', $field));
                $formattedErrors .= "<strong>{$readableField}:</strong> {$message}<br>";
            }
            session()->setFlashdata('validation_alert_html', $formattedErrors);
            return redirect()->back()->withInput();
        }

        // --- 4. Jika validasi lolos, siapkan data yang bersih untuk disimpan ---
        // Kali ini, $dataForValidation sudah lengkap, jadi bisa langsung pakai.
        // Hapus field yang tidak perlu disimpan di allowedFields (jika ada)
        // Pastikan id_es2 juga terisi jika Superadmin
        if (session()->get('role_access') === 'superadmin') {
            $es3 = $this->unitKerjaEs3Model->find($dataForValidation['id_es3']);
            if ($es3) {
                $dataForValidation['id_es2'] = $es3['id_es2'];
            }
        } else {
            // Untuk user biasa, id_es2 diambil dari hak fitur mereka
            $authData = session()->get('auth_data');
            if ($authData && !empty($authData['id_es2'])) {
                $dataForValidation['id_es2'] = $authData['id_es2'];
            }
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Panggil save() dengan data yang sudah bersih dan lengkap
            if ($this->itemAktifModel->save($dataForValidation) === false) {
                throw new \Exception('Gagal menyimpan item arsip: ' . implode('<br>', $this->itemAktifModel->errors()));
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data karena transaksi database gagal.');
            } else {
                $db->transCommit();
                $this->session->setFlashdata('success', 'Item arsip berhasil ditambahkan.');
                return redirect()->to('/item-aktif');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error create ItemAktif: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function edit($id = null)
    {
        if (!has_permission('cud_arsip')) {
            return redirect()->to('/item-aktif')->with('error', 'Anda tidak memiliki izin untuk mengubah data.');
        }
        $item = $this->itemAktifModel->find($id);
        if (!$item) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // --- PERBAIKAN DI SINI ---
        // Panggil helper prepareFormData() yang sudah menyiapkan semua data,
        // termasuk $es2_options.
        $data = $this->prepareFormData($item);
        $data['title'] = 'Edit Item Arsip Aktif';

        // Logika untuk menemukan id_es2_for_form dari item sudah ada
        // di dalam prepareFormData, jadi kita tidak perlu mengulanginya di sini.

        return view('item_aktif/form', $data);
    }

    public function update($id = null)
    {
        if (!has_permission('cud_arsip')) {
            return redirect()->to('/item-aktif')->with('error', 'Anda tidak memiliki izin untuk mengubah data.');
        }

        // --- 1. Ambil data item LAMA terlebih dahulu ---
        // Ini penting untuk mendapatkan id_user dan status_arsip yang sudah ada
        $existingItem = $this->itemAktifModel->find($id);
        if (!$existingItem) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');
        $postData = $this->request->getPost(); // Data dari form POST

        // --- 2. Ambil aturan validasi LENGKAP dari model ---
        $validationRules = $this->itemAktifModel->getValidationRules();

        // --- 3. Sesuaikan aturan validasi kondisional ---
        // Jika password kosong, aturan password tidak perlu
        if (empty($postData['password'])) {
            unset($validationRules['password']);
        }

        // Aturan `id` untuk `is_unique`
        $validationRules['id'] = 'permit_empty|is_natural_no_zero';

        // Karena id_user dan status_arsip tidak ada di form,
        // dan status_pindah juga tidak ada di form update,
        // kita tidak perlu aturan validasi 'required' untuknya jika tidak ada di POST.
        unset($validationRules['id_user']);
        unset($validationRules['status_arsip']);
        unset($validationRules['status_pindah']);


        // --- 4. Siapkan data LENGKAP untuk VALIDASI ---
        // Data ini mencakup data dari form, dan data lama yang tidak ada di form.
        $dataForValidation = [
            'id'                => $id, // ID untuk is_unique
            'no_dokumen'        => $postData['no_dokumen'],
            'judul_dokumen'     => $postData['judul_dokumen'],
            'tgl_dokumen'       => $postData['tgl_dokumen'],
            'tahun_cipta'       => date('Y', strtotime($postData['tgl_dokumen'])), // Tahun dari tgl_dokumen
            'id_klasifikasi'    => $postData['id_klasifikasi'],
            'id_jenis_naskah'   => $postData['id_jenis_naskah'],
            'id_es3'            => $postData['id_es3'],
            'jumlah'            => $postData['jumlah'],
            'tk_perkembangan'   => $postData['tk_perkembangan'],
            'media_simpan'      => $postData['media_simpan'],
            'lokasi_simpan'     => $postData['lokasi_simpan'] ?? null,
            'no_box'            => $postData['no_box'],
            'nama_file'         => $postData['nama_file'] ?? null,
            'nama_folder'       => $postData['nama_folder'] ?? null,
            'nama_link'         => $postData['nama_link'] ?? null,

            // <<< INI KUNCI PERBAIKAN >>>
            // Tambahkan kembali id_user dan status_arsip dari data lama
            'id_user'           => $existingItem['id_user'],
            'status_arsip'      => $existingItem['status_arsip'],
            'status_pindah'     => $existingItem['status_pindah'], // Jika ini juga wajib
            // id_berkas tidak perlu karena hanya berubah saat pemberkasan
        ];

        // Jalankan validasi
        if (!$this->validate($validationRules, [], $dataForValidation)) {
            $validationErrors = $this->validator->getErrors();
            $formattedErrors = '';
            foreach ($validationErrors as $field => $message) {
                $readableField = ucfirst(str_replace('_', ' ', $field));
                $formattedErrors .= "<strong>{$readableField}:</strong> {$message}<br>";
            }
            session()->setFlashdata('validation_alert_html', $formattedErrors);
            return redirect()->back()->withInput();
        }

        // --- 5. Siapkan data BERSIH untuk disimpan ke database ---
        // Ini adalah data yang akan masuk ke kolom 'allowedFields'
        $dataToSave = [
            'id_klasifikasi'    => $postData['id_klasifikasi'],
            'id_jenis_naskah'   => $postData['id_jenis_naskah'],
            'id_es3'            => $postData['id_es3'],
            'no_dokumen'        => $postData['no_dokumen'],
            'judul_dokumen'     => $postData['judul_dokumen'],
            'tgl_dokumen'       => $postData['tgl_dokumen'],
            'tahun_cipta'       => date('Y', strtotime($postData['tgl_dokumen'])),
            'jumlah'            => $postData['jumlah'],
            'tk_perkembangan'   => $postData['tk_perkembangan'],
            'lokasi_simpan'     => $postData['lokasi_simpan'] ?? null,
            'media_simpan'      => $postData['media_simpan'],
            'no_box'            => $postData['no_box'],
            'nama_file'         => $postData['nama_file'],
            'nama_folder'       => $postData['nama_folder'],
            'nama_link'         => $postData['nama_link'],
            // ID user dan status_arsip TIDAK perlu di sini, karena tidak diubah
            // Mereka akan tetap sama di database.
        ];

        // Jika Superadmin yang mengubah, pastikan id_es2 juga terupdate
        if ($userRole === 'superadmin') {
            $es3 = $this->unitKerjaEs3Model->find($dataToSave['id_es3']);
            if ($es3) {
                $dataToSave['id_es2'] = $es3['id_es2'];
            }
        } else {
            // Untuk user biasa, id_es2 sudah ter-set saat create
            // Ambil dari item yang sudah ada
            $dataToSave['id_es2'] = $existingItem['id_es2'];
        }

        // --- 6. Eksekusi Update & Transaksi ---
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if ($this->itemAktifModel->update($id, $dataToSave) === false) {
                throw new \Exception('Gagal memperbarui item arsip: ' . implode(', ', $this->itemAktifModel->errors()));
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data karena transaksi database gagal.');
            } else {
                $db->transCommit();
                $this->session->setFlashdata('success', 'Item arsip berhasil diperbarui.');
                return redirect()->to('/item-aktif');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id = null)
    {
        if (!has_permission('cud_arsip')) {
            return redirect()->to('/item-aktif')->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }
        // Cek dulu apakah item sudah diberkaskan
        $item = $this->itemAktifModel->find($id);
        if ($item && $item['id_berkas']) {
            $this->session->setFlashdata('error', 'Gagal menghapus! Item masih berada di dalam berkas. Lepaskan dari berkas terlebih dahulu.');
        } else {
            $this->itemAktifModel->delete($id);
            $this->session->setFlashdata('success', 'Item arsip berhasil dihapus.');
        }
        return redirect()->to('/item-aktif');
    }

    // Method lepasBerkas tidak berubah
    public function lepasBerkas()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');
            if (!$id) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'ID item tidak valid.'])->setStatusCode(400);
            }

            // Ambil dulu ID berkas sebelum di-update
            $item = $this->itemAktifModel->find($id);
            $berkasId = $item['id_berkas'];

            $db = \Config\Database::connect();
            $db->transBegin();

            try {
                // Lepaskan item
                $this->itemAktifModel->update($id, ['id_berkas' => null]);

                // Hitung ulang rentang tahun untuk berkas yang ditinggalkan
                $tahunCiptaItems = $this->itemAktifModel
                    ->where('id_berkas', $berkasId)
                    ->findColumn('tahun_cipta');

                $thn_item_awal = null;
                $thn_item_akhir = null;
                if (!empty($tahunCiptaItems)) {
                    $thn_item_awal = min($tahunCiptaItems);
                    $thn_item_akhir = max($tahunCiptaItems);
                }

                // Update berkas yang ditinggalkan
                (new \App\Models\BerkasAktifModel())->update($berkasId, [
                    'thn_item_awal' => $thn_item_awal,
                    'thn_item_akhir' => $thn_item_akhir,
                ]);

                if ($db->transStatus() === false) {
                    $db->transRollback();
                    throw new \Exception('Transaksi database gagal.');
                } else {
                    $db->transCommit();
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Item berhasil dilepas dari berkas.']);
                }
            } catch (\Exception $e) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses: ' . $e->getMessage()])->setStatusCode(500);
            }
        }
        return redirect()->to('/item-aktif');
    }
    // Menampilkan halaman form import
    public function import()
    {
        return view('item_aktif/import');
    }

    // Memproses file excel yang diupload
    public function prosesImport()
    {
        // --- BAGIAN 1: Validasi Input Form & File ---
        $validationRules = [
            'tahun'      => 'required|exact_length[4]|integer',
            'semester'   => 'required|in_list[1,2]',
            'file_excel' => [
                'label' => 'File Excel',
                'rules' => 'uploaded[file_excel]'
                    . '|ext_in[file_excel,xlsx,xls]'
                    . '|max_size[file_excel,5120]', // Maks 5MB
            ],
        ];

        if (!$this->validate($validationRules)) {
            session()->setFlashdata('show_import_modal', true);
            return redirect()->to('/item-aktif')->withInput()->with('validation', $this->validator);
        }

        $fileExcel = $this->request->getFile('file_excel');
        $authData = session()->get('auth_data');

        // --- BAGIAN 2: Simpan File & Catat Riwayat Import ---
        $newFileName = '';
        if ($fileExcel->isValid() && !$fileExcel->hasMoved()) {
            $newFileName = $fileExcel->getRandomName();
            $fileExcel->move(WRITEPATH . 'uploads/imports', $newFileName);

            $importDaftarModel = new \App\Models\ImportDaftarModel(); // Pastikan di-use
            $importData = [
                'id_es2'   => $authData['id_es2'] ?? null,
                'tahun'    => $this->request->getPost('tahun'),
                'semester' => $this->request->getPost('semester'),
                'id_user'  => session()->get('user_id'),
                'file'     => $newFileName,
            ];
            $importDaftarModel->save($importData);
        } else {
            session()->setFlashdata('error', 'Gagal memindahkan file yang diunggah: ' . $fileExcel->getErrorString());
            return redirect()->to('/item-aktif');
        }

        // --- BAGIAN 3: Proses Isi File Excel & Transaksi Database ---
        $db = \Config\Database::connect();
        $db->transBegin(); // <<< MULAI TRANSAKSI DATABASE >>>

        try {
            $spreadsheet = IOFactory::load(WRITEPATH . 'uploads/imports/' . $newFileName);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $dataToInsert = [];
            $errors = [];
            $berhasil = 0;
            $gagal = 0;
            $id_user_login = session()->get('user_id');

            // Instansiasi model di luar loop untuk efisiensi
            $klasifikasiModel = new \App\Models\KlasifikasiModel();
            $unitKerjaEs2Model = new \App\Models\UnitKerjaEs2Model();
            $unitKerjaEs3Model = new \App\Models\UnitKerjaEs3Model();
            $jenisNaskahModel = new \App\Models\JenisNaskahModel();

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex == 0 || empty(array_filter($row))) {
                    continue;
                }

                // Ambil data dari kolom Excel (pastikan indeksnya benar)
                $kodeKlasifikasi    = trim($row[0] ?? '');
                $kodeEs2            = trim($row[1] ?? '');
                $kodeEs3            = trim($row[2] ?? '');
                $noDokumen          = trim($row[3] ?? '');
                $judulDokumen       = trim($row[4] ?? '');
                $tglDokumenRaw      = trim($row[5] ?? '');
                $tahunCipta         = trim($row[6] ?? '');
                $jumlah             = trim($row[7] ?? '1');
                $tkPerkembangan     = trim($row[8] ?? 'asli');
                $lokasiSimpan       = trim($row[9] ?? '');
                $mediaSimpan        = trim($row[10] ?? 'kertas');
                $noBox              = trim($row[11] ?? '');
                $statusArsip        = trim($row[12] ?? 'aktif'); // Default, tapi cek validasi
                $statusPindah       = trim($row[13] ?? 'belum'); // Default, tapi cek validasi
                $kodeJenisNaskah    = trim($row[14] ?? '');
                $namaFile           = trim($row[15] ?? '');
                $namaFolder         = trim($row[16] ?? '');
                $namaLink           = trim($row[17] ?? '');
                $dasarCatat         = trim($row[18] ?? ''); // <-- Pastikan ini indeks kolom yang benar

                $rowErrors = [];

                // 1. Validasi Kolom Wajib dari Excel
                if (empty($kodeKlasifikasi)) $rowErrors[] = "Kolom A (Klasifikasi) wajib diisi.";
                if (empty($kodeEs2)) $rowErrors[] = "Kolom B (ES2) wajib diisi.";
                if (empty($kodeEs3)) $rowErrors[] = "Kolom C (ES3) wajib diisi.";
                if (empty($judulDokumen)) $rowErrors[] = "Kolom E (Judul) wajib diisi.";
                if (empty($kodeJenisNaskah)) $rowErrors[] = "Kolom O (Jenis Naskah) wajib diisi.";
                if (empty($tahunCipta)) $rowErrors[] = "Kolom G (Tahun Cipta) wajib diisi.";
                if (!is_numeric($jumlah)) $rowErrors[] = "Kolom H (Jumlah) harus angka.";

                // 2. Validasi Format & Penanganan Tanggal
                $tglDokumenFinal = null;
                if (!empty($tglDokumenRaw) && $tglDokumenRaw !== '-') {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglDokumenRaw)) {
                        $tglDokumenFinal = $tglDokumenRaw;
                    } else {
                        $rowErrors[] = "Kolom F (Tgl Dokumen) harus YYYY-MM-DD.";
                    }
                } else {
                    // Jika tgl kosong/strip, set ke '0000-00-00'
                    // Ini aman jika kolom tgl_dokumen di DB TIDAK NULL dan bisa menerima nilai ini
                    $tglDokumenFinal = '0000-00-00';
                }

                if (!preg_match(
                    '/^\d{4}$/',
                    $tahunCipta
                )) $rowErrors[] = "Kolom G (Tahun Cipta) harus YYYY.";

                // --- Validasi ENUM atau nilai spesifik ---
                $validTkPerkembangan = ['asli', 'copy'];
                if (!in_array(strtolower($tkPerkembangan), $validTkPerkembangan)) {
                    $rowErrors[] = "Kolom I (Tk Perkembangan) tidak valid. Pilih: " . implode(',', $validTkPerkembangan);
                } else {
                    $tkPerkembangan = strtolower($tkPerkembangan);
                }

                $validMediaSimpan = ['kertas', 'elektronik'];
                if (!in_array(strtolower($mediaSimpan), $validMediaSimpan)) {
                    $rowErrors[] = "Kolom K (Media Simpan) tidak valid. Pilih: " . implode(',', $validMediaSimpan);
                } else {
                    $mediaSimpan = strtolower($mediaSimpan);
                }

                $validStatusArsip = [
                    'aktif',
                    'inaktif',
                    'vital',
                    'negara'
                ]; // Sesuaikan dengan ENUM Anda
                if (!in_array(strtolower($statusArsip), $validStatusArsip)) {
                    $rowErrors[] = "Kolom M (Status Arsip) tidak valid. Pilih: " . implode(',', $validStatusArsip);
                } else {
                    $statusArsip = strtolower($statusArsip);
                }

                $validStatusPindah = ['belum', 'usul_pindah', 'verifikasi', 'ba', 'ditolak']; // Sesuaikan
                if (!in_array(strtolower($statusPindah), $validStatusPindah)) {
                    $rowErrors[] = "Kolom N (Status Pindah) tidak valid. Pilih: " . implode(',', $validStatusPindah);
                } else {
                    $statusPindah = strtolower($statusPindah);
                }

                $validDasarCatat = ['srikandi', 'sima', 'map', 'bisma', 'sadewa', 'pos', 'lainnya']; // Sesuaikan
                if (!in_array(strtolower($dasarCatat), $validDasarCatat)) {
                    $rowErrors[] = "Kolom S (Dasar Catat) tidak valid. Pilih: " . implode(',', $validDasarCatat);
                } else {
                    $dasarCatat = strtolower($dasarCatat);
                }


                // Jika sudah ada error dari validasi dasar/format, lewati cek DB
                if (!empty($rowErrors)) {
                    $gagal++;
                    $errors[] = "Baris " . ($rowIndex + 1) . ": " . implode(', ', $rowErrors);
                    continue;
                }

                // 3. Validasi Relasi ke Database & Hierarki Unit Kerja
                $klasifikasi = $klasifikasiModel->where('kode', $kodeKlasifikasi)->first();
                if (!$klasifikasi) $rowErrors[] = "Kode Klasifikasi '{$kodeKlasifikasi}' tidak ditemukan.";

                $es2 = $unitKerjaEs2Model->where('kode', $kodeEs2)->first();
                if (!$es2) $rowErrors[] = "Kode ES2 '{$kodeEs2}' tidak ditemukan.";

                $es3 = $unitKerjaEs3Model->where('kode', $kodeEs3)->first();
                if (!$es3) $rowErrors[] = "Kode ES3 '{$kodeEs3}' tidak ditemukan.";
                else {
                    if ($es3['id_es2'] !== $es2['id']) $rowErrors[] = "Kode ES3 '{$kodeEs3}' tidak berada di bawah Kode ES2 '{$kodeEs2}'.";
                }

                $jenisNaskah = $jenisNaskahModel->where('kode_naskah', $kodeJenisNaskah)->first();
                if (!$jenisNaskah) $rowErrors[] = "Kode Jenis Naskah '{$kodeJenisNaskah}' tidak ditemukan.";

                if (!empty($rowErrors)) {
                    $gagal++;
                    $errors[] = "Baris " . ($rowIndex + 1) . ": " . implode(', ', $rowErrors);
                    continue;
                }

                // --- SIAPKAN DATA UNTUK INSERT ---
                $dataToInsert[] = [
                    'id_klasifikasi'    => $klasifikasi['id'],
                    'id_user'           => $id_user_login,
                    'id_es2'            => $es2['id'],
                    'id_es3'            => $es3['id'],
                    'id_jenis_naskah'   => $jenisNaskah['id'],
                    'no_dokumen'        => $noDokumen,
                    'judul_dokumen'     => $judulDokumen,
                    'tgl_dokumen'       => $tglDokumenFinal, // Tanggal yang sudah diformat
                    'tahun_cipta'       => $tahunCipta,
                    'jumlah'            => $jumlah,
                    'tk_perkembangan'   => $tkPerkembangan,
                    'lokasi_simpan'     => $lokasiSimpan,
                    'media_simpan'      => $mediaSimpan,
                    'no_box'            => $noBox,
                    'status_arsip'      => $statusArsip,
                    'status_pindah'     => $statusPindah,
                    'dasar_catat'       => $dasarCatat, // <-- Field baru
                    'nama_file'         => $namaFile,
                    'nama_folder'       => $namaFolder,
                    'nama_link'         => $namaLink,
                    'id_berkas'         => null, // Default: belum masuk berkas
                    'pinjam'            => 0,    // Default: tidak dipinjam
                    // Pastikan semua kolom allowedFields diisi atau punya default di DB
                ];
                $berhasil++;
            }
            log_message('debug', 'Data final sebelum insertBatch: ' . json_encode($dataToInsert));
            // --- 4. Cek apakah ada baris yang gagal validasi di Excel ---
            if ($gagal > 0) {
                $db->transRollback();
                session()->setFlashdata('error', "Proses import dibatalkan. Ditemukan $gagal baris error. Semua data tidak disimpan.");
                session()->setFlashdata('import_errors', $errors);
                session()->setFlashdata('show_import_modal', true);
                return redirect()->to('/item-aktif');
            }

            // --- 5. EKSEKUSI INSERT BATCH DAN COMMIT TRANSAKSI ---
            $inserted = 0;
            if (!empty($dataToInsert)) {
                $inserted = $this->itemAktifModel->insertBatch($dataToInsert);
            }

            $db->transCommit();

            // Verifikasi jumlah yang diinsert
            if ($inserted > 0) {
                $pesan = "Proses import selesai. Berhasil: $inserted data. Gagal: $gagal data.";
                session()->setFlashdata('success', $pesan);
            } else {
                session()->setFlashdata('warning', "Proses import selesai. Tidak ada data yang berhasil diinsert ke database. Mungkin semua data tidak valid.");
            }

            if (!empty($errors)) {
                session()->setFlashdata('import_errors', $errors);
            }

            session()->setFlashdata('show_import_modal', true);
            return redirect()->to('/item-aktif');
        } catch (\Exception $e) {
            // <<< ROLLBACK TRANSAKSI JIKA ADA ERROR >>>
            $db->transRollback();

            log_message('error', 'Error prosesImport ItemAktif: ' . $e->getMessage());
            session()->setFlashdata('show_import_modal', true);
            session()->setFlashdata('error', 'Gagal memproses file Excel: ' . $e->getMessage());
            // Jika ada error validasi per baris, tetap tampilkan
            if (!empty($errors)) {
                session()->setFlashdata('import_errors', $errors);
            }
            return redirect()->to('/item-aktif');
        }
    }
    public function downloadTemplate()
    {
        // Inisialisasi Spreadsheet
        $spreadsheet = new Spreadsheet();

        // ===================================================================
        // SHEET 1: TEMPLATE UNTUK DIISI PENGGUNA
        // ===================================================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Template Import');

        // Definisikan Header Kolom sesuai urutan tabel dan panduan pengisian
        $headers = [
            // A-C: Relasi Unit Kerja & Klasifikasi
            'A1' => 'Kode Klasifikasi',
            'B1' => 'Kode Unit Kerja ES2',
            'C1' => 'Kode Unit Kerja ES3',
            // D-G: Informasi Dasar Dokumen
            'D1' => 'No Dokumen',
            'E1' => 'Judul Dokumen',
            'F1' => 'Tgl Dokumen',
            'G1' => 'Tahun Cipta',
            // H-L: Fisik & Lokasi
            'H1' => 'Jumlah',
            'I1' => 'Tingkat Perkembangan',
            'J1' => 'Lokasi Simpan',
            'K1' => 'Media Simpan',
            'L1' => 'No Box',
            // M-N: Status Internal
            'M1' => 'Status Arsip',
            'N1' => 'Status Pindah',
            // O-R: Relasi & File Digital
            'O1' => 'Kode Jenis Naskah',
            'P1' => 'Nama File',
            'Q1' => 'Nama Folder',
            'R1' => 'Nama Link',
            // S: Dasar Catat (KOLOM BARU)
            'S1' => 'Dasar Pencatatan',
            // T-V: Informasi Tambahan (Jika diperlukan, sudah ada di prosesImport)
            'T1' => 'Tindak Lanjut Temuan', // Contoh
            'U1' => 'Dasar Pencatatan (Lama)', // Contoh
            'V1' => 'Status Pinjam (Lama)', // Contoh
            // Note: Kolom T,U,V di prosesImport adalah indeks 18,19,20.
            // Di sini saya asumsikan Dasar Pencatatan adalah Kolom S (indeks 18)
        ];
        // Tulis header ke sheet dan terapkan styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ];
        $sheet1->getStyle('A1:S1')->applyFromArray($headerStyle)->getAlignment()->setWrapText(true); // Sesuaikan range
        $sheet1->getRowDimension('1')->setRowHeight(40);

        foreach ($headers as $cell => $value) {
            $sheet1->setCellValue($cell, $value);
        }

        // Menulis teks header
        foreach ($headers as $cell => $value) {
            $sheet1->setCellValue($cell, $value);
        }

        // Tambahkan komentar/petunjuk pengisian pada setiap header
        $sheet1->getComment('A1')->getText()->createTextRun("Wajib. Pilih kode dari 'Data Referensi'.");
        $sheet1->getComment('B1')->getText()->createTextRun("Wajib. Pilih kode dari 'Data Referensi'.");
        $sheet1->getComment('C1')->getText()->createTextRun("Wajib. Pilih kode dari 'Data Referensi'.");
        $sheet1->getComment('D1')->getText()->createTextRun("Opsional. No. Dokumen.");
        $sheet1->getComment('E1')->getText()->createTextRun("Wajib. Judul Dokumen.");
        $sheet1->getComment('F1')->getText()->createTextRun("Opsional. YYYY-MM-DD atau '-'");
        $sheet1->getComment('G1')->getText()->createTextRun("Wajib. YYYY (Otomatis dari F jika ada).");
        $sheet1->getComment('H1')->getText()->createTextRun("Wajib. Angka (default 1).");
        $sheet1->getComment('I1')->getText()->createTextRun("Pilih: asli, copy.");
        $sheet1->getComment('J1')->getText()->createTextRun("Opsional. Lokasi fisik.");
        $sheet1->getComment('K1')->getText()->createTextRun("Pilih: kertas, elektronik.");
        $sheet1->getComment('L1')->getText()->createTextRun("Opsional. No. Box.");
        $sheet1->getComment('M1')->getText()->createTextRun("Pilih: aktif, inaktif, vital, negara.");
        $sheet1->getComment('N1')->getText()->createTextRun("Pilih: belum, usul_pindah, verifikasi, ba, ditolak.");
        $sheet1->getComment('O1')->getText()->createTextRun("Wajib. Pilih kode dari 'Data Referensi'.");
        $sheet1->getComment('P1')->getText()->createTextRun("Opsional. Nama file digital.");
        $sheet1->getComment('Q1')->getText()->createTextRun("Opsional. Nama folder digital.");
        $sheet1->getComment('R1')->getText()->createTextRun("Opsional. Link/URL digital.");
        $sheet1->getComment('S1')->getText()->createTextRun("Wajib. Pilih: Srikandi,SIMA,...,Lainnya."); // <-- KOMENTAR BARU


        // ===================================================================
        // SHEET 2: DATA REFERENSI UNTUK MEMBANTU PENGGUNA
        // ===================================================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Data Referensi');

        // Ambil semua data dari tabel relasi
        $klasifikasiData = $this->klasifikasiModel->select('kode, nama_klasifikasi')->orderBy('kode', 'ASC')->findAll();
        $es2Data = $this->unitKerjaEs2Model->select('kode, nama_es2')->orderBy('kode', 'ASC')->findAll();
        $es3Data = $this->unitKerjaEs3Model->select('kode, nama_es3')->orderBy('kode', 'ASC')->findAll();
        $jenisNaskahData = $this->jenisNaskahModel->select('kode_naskah, nama_naskah')->orderBy('kode_naskah', 'ASC')->findAll();

        // Fungsi kecil untuk menulis data ke sheet referensi
        $writeData = function ($sheet, $startCol, $title, $headers, $data) use ($headerStyle) {
            $sheet->setCellValue($startCol . '1', $title)->mergeCells($startCol . '1:' . chr(ord($startCol) + count($headers) - 1) . '1')->getStyle($startCol . '1')->applyFromArray($headerStyle);
            $col = $startCol;
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '2', $header)->getStyle($col . '2')->getFont()->setBold(true);
                $col++;
            }
            $row = 3;
            foreach ($data as $item) {
                $col = $startCol;
                foreach ($item as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
            return $row - 1; // Return last row number
        };

        // Tulis semua data referensi ke Sheet 2
        $lastRowKlasifikasi = $writeData($sheet2, 'A', 'DATA KLASIFIKASI', ['Kode', 'Nama Klasifikasi'], $klasifikasiData);
        $lastRowEs2 = $writeData($sheet2, 'D', 'DATA UNIT KERJA ES2', ['Kode', 'Nama ES2'], $es2Data);
        $lastRowEs3 = $writeData($sheet2, 'G', 'DATA UNIT KERJA ES3', ['Kode', 'Nama ES3'], $es3Data);
        $lastRowJenisNaskah = $writeData($sheet2, 'J', 'DATA JENIS NASKAH', ['Kode', 'Nama Naskah'], $jenisNaskahData);

        // Auto-size kolom di sheet referensi
        foreach (range('A', 'K') as $columnID) {
            $sheet2->getColumnDimension($columnID)->setAutoSize(true);
        }

        // ===================================================================
        // BUAT DROPDOWN VALIDATION DI SHEET 1
        // ===================================================================
        $validTkPerkembangan = '"asli,copy"';
        $validMediaSimpan = '"kertas,elektronik"';
        $validStatusArsip = '"aktif,inaktif,vital,negara"';
        $validStatusPindah = '"belum,usul_pindah,verifikasi,ba,ditolak"';
        $validDasarCatat = '"Srikandi,SIMA,MAP,BISMA,SADEWA,POS,Lainnya"'; // <-- OPSI BARU

        for ($i = 2; $i <= 501; $i++) { // Terapkan validasi untuk 500 baris data
            $sheet1->getCell('A' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1("='Data Referensi'!\$A\$3:\$A\$$lastRowKlasifikasi");
            $sheet1->getCell('B' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1("='Data Referensi'!\$D\$3:\$D\$$lastRowEs2");
            $sheet1->getCell('C' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1("='Data Referensi'!\$G\$3:\$G\$$lastRowEs3");
            $sheet1->getCell('O' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1("='Data Referensi'!\$J\$3:\$J\$$lastRowJenisNaskah");

            // Enum Dropdowns (data langsung ditulis)
            $sheet1->getCell('I' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1($validTkPerkembangan);
            $sheet1->getCell('K' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1($validMediaSimpan);
            $sheet1->getCell('M' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1($validStatusArsip);
            $sheet1->getCell('N' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1($validStatusPindah);
            $sheet1->getCell('S' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1($validDasarCatat); // <-- VALIDASI BARU
        }

        foreach (range('A', 'S') as $columnID) {
            $sheet1->getColumnDimension($columnID)->setAutoSize(true);
        } // Sesuaikan range


        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Template_Import_Arsip_Aktif_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
    public function pinjamItem()
    {
        // ... (Kode pinjamItem yang sudah ada) ...
        if (!$this->request->isAJAX()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $id_item = $this->request->getPost('id_pinjam_target'); // ID dari modal
        $peminjam_nama = $this->request->getPost('peminjam_nama');
        $peminjam_unit = $this->request->getPost('peminjam_unit');
        $tgl_pinjam = $this->request->getPost('tgl_pinjam');
        $tgl_kembali_rencana = $this->request->getPost('tgl_kembali_rencana');
        $keterangan = $this->request->getPost('keterangan');

        // Validasi input form
        $rules = [
            'id_pinjam_target'    => 'required|integer',
            'peminjam_nama'       => 'required|min_length[3]',
            'peminjam_unit'       => 'required|min_length[3]',
            'tgl_pinjam'          => 'required|valid_date',
            'tgl_kembali_rencana' => 'required|valid_date|after_than[tgl_pinjam]',
        ];
        $messages = [
            'tgl_kembali_rencana' => [
                'after_than' => 'Tanggal kembali rencana harus setelah tanggal pinjam.'
            ]
        ];
        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON(['status' => 'error', 'message' => implode('<br>', $this->validator->getErrors())]);
        }

        // Cek itemnya, pastikan tidak sedang dipinjam
        $item = $this->itemAktifModel->find($id_item);
        if (!$item) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Item tidak ditemukan.']);
        }
        if ($item['pinjam'] == 1) {
            return $this->response->setJSON(['status' => 'warning', 'message' => 'Item ini sedang dipinjam.']);
        }

        // Mulai transaksi
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Catat di tabel peminjaman
            $this->peminjamanModel->save([
                'id_item_aktif'       => $id_item,
                'id_user_peminjam'    => session()->get('user_id'), // User yang mencatat
                'peminjam_nama'       => $peminjam_nama,
                'peminjam_unit'       => $peminjam_unit,
                'tgl_pinjam'          => $tgl_pinjam,
                'tgl_kembali_rencana' => $tgl_kembali_rencana,
                'keterangan'          => $keterangan,
                'created_by'          => session()->get('user_id'),
            ]);
            // Update status di item_aktif
            $this->itemAktifModel->update($id_item, ['pinjam' => 1]);

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Item berhasil dipinjam.']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error pinjamItem: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses peminjaman: ' . $e->getMessage()]);
        }
    }
    public function searchSimaApi()
    {
        // --- 1. Ambil data User dan Unit Kerja dari Session ---
        $session = \Config\Services::session();
        $authData = $session->get('auth_data') ?? [];

        $id_es3_filter = $authData['id_es3'] ?? null;

        // Asumsi: UnitKerjaEs3Model dan UnitKerjaEs2Model sudah terdefinisi di Controller properties
        $unitKerjaEs3Model = new \App\Models\UnitKerjaEs3Model();
        $unitKerjaEs2Model = new \App\Models\UnitKerjaEs2Model();

        $kodeEs2 = null;
        if ($id_es3_filter) {
            $unitEs3 = $unitKerjaEs3Model->find($id_es3_filter);
            if ($unitEs3) {
                $unitEs2 = $unitKerjaEs2Model->find($unitEs3['id_es2']);
                $kodeEs2 = $unitEs2['kode'] ?? null;
            }
        }

        // Jika kode_es2 tidak ditemukan, tolak akses API untuk keamanan.
        if (empty($kodeEs2)) {
            log_message('warning', 'SIMA API search denied: Kode ES2 not found for user.');
            return $this->response->setJSON(['results' => []]);
        }

        // --- 2. Ambil Parameter Pencarian ---
        $keyword = $this->request->getVar('term');
        $tahun_filter = $this->request->getVar('tahun_filter');

        if (empty($keyword) || strlen($keyword) < 3 || empty($tahun_filter)) {
            return $this->response->setJSON(['results' => []]);
        }

        $client = \Config\Services::curlrequest();

        try {
            // --- 3. Kirim Kode Unit ke API SIMA ---
            $response = $client->request('GET', $this->simaApiUrl, [
                'headers' => ['Authorization' => 'Bearer ' . $this->apiToken, 'Accept' => 'application/json'],
                'query' => [
                    'keyword' => $keyword,
                    'thang' => $tahun_filter,
                    'kode_unit' => $kodeEs2, // <--- PARAMETER BARU DITERAPKAN DI SINI
                ],
            ]);

            $apiData = json_decode($response->getBody(), true);

            // ... (sisa logika filtering manual tetap sama) ...

            if (!isset($apiData['data']) || !is_array($apiData['data'])) {
                return $this->response->setJSON(['results' => []]);
            }

            $results = [];
            $lowerKeyword = strtolower($keyword);

            foreach ($apiData['data'] as $item) {
                // Walaupun sudah difilter API, pastikan data yang kembali cocok
                if (isset($item['thang']) && $item['thang'] != $tahun_filter) {
                    continue;
                }
                // Tambahkan filter internal berdasarkan kode_unit jika API gagal memfilter
                if (isset($item['kode_unit']) && $item['kode_unit'] != $kodeEs2) {
                    continue;
                }

                // ... (Logika filter keyword dan display text Select2) ...
                $nomorLaporan = strtolower($item['nomor_laporan'] ?? '');
                $keterangan = strtolower($item['keterangan_penugasan'] ?? '');

                if (
                    strpos($nomorLaporan, $lowerKeyword) !== false ||
                    strpos($keterangan, $lowerKeyword) !== false
                ) {

                    $uniqueId = !empty($item['nomor_laporan']) ? $item['nomor_laporan'] : ($item['id_st'] ?? uniqid());
                    $displayNomor = !empty($item['nomor_laporan']) ? $item['nomor_laporan'] : 'Laporan Tanpa Nomor';
                    $displayText = $displayNomor . ' - ' . $item['keterangan_penugasan'];

                    $results[] = [
                        'id'   => $uniqueId,
                        'text' => $displayText,
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
                    'tahun' => $tahun_filter, // Parameter Query yang diterima API SADEWA adalah 'tahun'
                    'row' => 100,
                ],
            ]);

            $apiData = json_decode($response->getBody(), true);

            if (!isset($apiData['data']) || !is_array($apiData['data'])) {
                return $this->response->setJSON(['results' => []]);
            }

            $results = [];
            $lowerKeyword = strtolower($keyword);

            // Filter Manual: Memeriksa keyword dan tahun_terima_surat
            foreach ($apiData['data'] as $item) {
                // Konfirmasi: Filter manual untuk tahun_terima_surat
                // Meskipun kita sudah memfilter di query, ini adalah lapisan pengamanan
                if (isset($item['tahun_terima_surat']) && $item['tahun_terima_surat'] != $tahun_filter) {
                    continue;
                }

                $nomorSurat = strtolower($item['nomor_surat'] ?? '');
                $perihal = strtolower($item['perihal'] ?? '');

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
    // --- FUNGSI BARU UNTUK DATATABLES SIMA ---
    public function loadSimaData()
    {
        $session = \Config\Services::session();
        $authData = $session->get('auth_data') ?? [];
        $id_es3_filter = $authData['id_es3'] ?? null;

        // Asumsi model Unit Kerja sudah dimuat/didefinisikan
        $unitKerjaEs3Model = new \App\Models\UnitKerjaEs3Model();
        $unitKerjaEs2Model = new \App\Models\UnitKerjaEs2Model();
        $kodeEs2 = null;

        if ($id_es3_filter) {
            $unitEs3 = $unitKerjaEs3Model->find($id_es3_filter);
            if ($unitEs3) {
                $unitEs2 = $unitKerjaEs2Model->find($unitEs3['id_es2']);
                $kodeEs2 = $unitEs2['kode'] ?? null;
            }
        }

        // Parameter DataTables
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $searchValue = $this->request->getPost('search')['value'];
        $tahunFilter = $this->request->getPost('tahun_filter'); // Parameter tahun dari JS

        if (empty($kodeEs2) || empty($tahunFilter)) {
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Filter tahun atau kode unit tidak valid.'
            ]);
        }

        $client = \Config\Services::curlrequest();

        // Catatan: API SIMA mungkin tidak mendukung paginasi DataTables (start/length) secara langsung. 
        // Kita hanya akan menerapkan filter dasar: tahun, kode_unit, dan keyword. 
        // Jika data > 1000 baris, ini akan tetap berat. Kita asumsikan API mengembalikan semua data yang difilter.

        try {
            $response = $client->request('GET', $this->simaApiUrl, [
                'headers' => ['Authorization' => 'Bearer ' . $this->apiToken, 'Accept' => 'application/json'],
                'query' => [
                    'thang' => $tahunFilter,
                    'kode_unit' => $kodeEs2,
                    'keyword' => $searchValue, // Gunakan fitur pencarian bawaan DataTables
                ],
            ]);

            $apiData = json_decode($response->getBody(), true);
            $rawItems = $apiData['data'] ?? [];

            $filteredItems = [];
            $lowerSearchValue = strtolower($searchValue);

            // Lakukan filter manual (hanya jika API tidak mendukung filtering keyword)
            foreach ($rawItems as $item) {

                // --- KUNCI PERBAIKAN: Cek Nomor Laporan ---
                if (empty($item['nomor_laporan'])) {
                    continue; // Skip item ini jika nomor laporan kosong
                }
                // --- END PERBAIKAN ---

                // ... (Filter manual jika keyword ada di nomor_laporan atau keterangan_penugasan) ...
                $nomorLaporan = strtolower($item['nomor_laporan']); // Kita tahu ini tidak kosong
                $keterangan = strtolower($item['keterangan_penugasan'] ?? '');

                if (
                    empty($searchValue) ||
                    strpos($nomorLaporan, $lowerSearchValue) !== false ||
                    strpos($keterangan, $lowerSearchValue) !== false
                ) {

                    // ... (Penambahan tombol aksi) ...
                    $item['aksi'] = '<button type ... </button>';

                    $filteredItems[] = $item;
                }
            }

            // DataTables memerlukan paginasi di sisi server jika data banyak.
            // Karena API tidak menyediakan paginasi DataTables, kita harus membatasi output:
            $totalRecords = count($filteredItems);
            $dataSlice = array_slice($filteredItems,
                $start,
                $length
            );

            // Format ulang data untuk DataTables SIMA
            $outputData = [];
            foreach ($dataSlice as $item) {
                $outputData[] = [
                    'thang' => $item['thang'],
                    'nomor_laporan' => $item['nomor_laporan'] ?? 'N/A',
                    'keterangan_penugasan' => $item['keterangan_penugasan'],
                    'tanggal_laporan' => date('d-M-Y', strtotime(substr($item['tanggal_laporan'], 0, 10))),
                    'aksi' => $item['aksi'],
                    'raw_data' => $item // Simpan data mentah untuk JS
                ];
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords, // Total data sebelum paginasi
                'recordsFiltered' => $totalRecords, // Karena kita filter semua data
                'data' => $outputData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'API SIMA DataTables Error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => $e->getMessage(), 'data' => []]);
        }
    }
    public function loadSadewaData()
    {
        // Parameter DataTables
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $searchValue = $this->request->getPost('search')['value'];
        $tahunFilter = $this->request->getPost('tahun_filter'); // Parameter tahun dari JS

        if (empty($tahunFilter)) {
            return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
        }

        $client = \Config\Services::curlrequest();

        try {
            $response = $client->request('GET', $this->sadewaApiUrl, [
                'headers' => ['Authorization' => 'Bearer ' . $this->apiToken, 'Accept' => 'application/json'],
                'query' => [
                    'tahun' => $tahunFilter,
                    'row' => 2000, // Ambil jumlah maksimal data per tahun
                ],
            ]);

            $apiData = json_decode($response->getBody(), true);
            $rawItems = $apiData['data'] ?? [];

            $filteredItems = [];
            $lowerSearchValue = strtolower($searchValue);

            // Filter manual (untuk keyword pencarian DataTables)
            foreach ($rawItems as $item) {
                // --- KUNCI PERBAIKAN: Cek Nomor Surat ---
                if (empty($item['nomor_surat'])) {
                    continue; // Skip item ini jika nomor surat kosong
                }
                // --- END PERBAIKAN ---

                $nomorSurat = strtolower($item['nomor_surat']); // Kita tahu ini tidak kosong
                $perihal = strtolower($item['perihal'] ?? '');

                if (
                    empty($searchValue) ||
                    strpos($nomorSurat, $lowerSearchValue) !== false ||
                    strpos($perihal, $lowerSearchValue) !== false
                ) {

                    // ... (Penambahan tombol aksi) ...
                    $item['aksi'] = '<button type ... </button>';

                    $filteredItems[] = $item;
                }
            }

            $totalRecords = count($filteredItems);
            $dataSlice = array_slice($filteredItems, $start, $length);

            // Format ulang data untuk DataTables SADEWA
            $outputData = [];
            foreach ($dataSlice as $item) {
                $outputData[] = [
                    'tahun_terima_surat' => $item['tahun_terima_surat'],
                    'nomor_surat' => $item['nomor_surat'] ?? 'N/A',
                    'perihal' => $item['perihal'],
                    'tgl_surat' => date('d F Y', strtotime(substr($item['tgl_surat'], 0, 10))),
                    'aksi' => $item['aksi'],
                    'raw_data' => $item
                ];
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $outputData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'API SADEWA DataTables Error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => $e->getMessage(), 'data' => []]);
        }
    }
}
