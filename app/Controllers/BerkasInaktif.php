<?php

namespace App\Controllers;

use App\Models\BerkasInaktifModel;
use App\Models\ItemInaktifModel;
use App\Models\KlasifikasiModel;
use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Models\PeminjamanModel; // <-- Tambahkan ini

// Tambahkan use statement ini di bagian atas
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BerkasInaktif extends BaseController
{
    protected $berkasInaktifModel;
    protected $itemInaktifModel;
    protected $klasifikasiModel;
    protected $unitKerjaEs1Model;
    protected $unitKerjaEs2Model;
    protected $unitKerjaEs3Model;
    protected $session;
    protected $peminjamanModel; // <-- Deklarasikan ini

    public function __construct()
    {
        $this->berkasInaktifModel = new BerkasInaktifModel();
        $this->itemInaktifModel = new ItemInaktifModel();
        $this->klasifikasiModel = new KlasifikasiModel();
        $this->unitKerjaEs1Model = new UnitKerjaEs1Model();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
        $this->peminjamanModel = new PeminjamanModel();
        $this->session = session();
    }

    /**
     * Menampilkan halaman daftar berkas aktif.
     */
    public function index()
    {
        $data = [
            'title' => 'Daftar Berkas Inaktif',
            'session' => $this->session,
        ];
        return view('berkas_inaktif/index', $data);
    }
    private function prepareUnitKerjaData($berkas = null)
    {
        $authData = session()->get('auth_data');
        $userRole = session()->get('role_access');

        $id_es2_prefill = null;
        $es2_options = [];

        if ($userRole === 'superadmin') {
            $es2_options = $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll();
            if ($berkas) $id_es2_prefill = $berkas['id_es2'];
        } else if ($authData) {
            $id_es2_hak_akses = null;
            if (!empty($authData['id_es3'])) {
                $es3 = $this->unitKerjaEs3Model->find($authData['id_es3']);
                if ($es3) $id_es2_hak_akses = $es3['id_es2'];
            } elseif (!empty($authData['id_es2'])) {
                $id_es2_hak_akses = $authData['id_es2'];
            }

            if ($id_es2_hak_akses) {
                $es2_options = $this->unitKerjaEs2Model->where('id', $id_es2_hak_akses)->findAll();
                $id_es2_prefill = $id_es2_hak_akses;
            }
        }

        return [
            'es2_options' => $es2_options,
            'id_es2_prefill' => $id_es2_prefill,
        ];
    }
    /**
     * Endpoint AJAX untuk DataTables di halaman index.
     */
    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->berkasInaktifModel->getDataTablesList($this->request);
            $data = [];
            foreach ($result['data'] as $item) {

                // --- BAGIAN YANG TIDAK BERUBAH ---
                $kodeUnit = '<span class="badge text-bg-info">' . esc($item['kode_es2']) . '</span>' .
                    '<i class="bi bi-chevron-right mx-1"></i>' .
                    '<span class="badge text-bg-light text-dark">' . esc($item['kode_es3']) . '</span>';
                if (empty($item['kode_es2']) && empty($item['kode_es3'])) {
                    $kodeUnit = '<span class="text-muted">N/A</span>';
                }
                $noDanNamaBerkas = '<div>' . esc($item['no_berkas']) . '</div>' .
                    '<div class="fw-bold text-primary">' . esc($item['nama_berkas']) . '</div>';
                $kurunWaktu = $item['thn_item_awal'] && $item['thn_item_akhir']
                    ? $item['thn_item_awal'] . ' - ' . $item['thn_item_akhir']
                    : '-';

                // Kolom Status
                $statusTutup = ($item['status_tutup'] === 'tertutup')
                    ? '<span class="badge text-bg-danger"><i class="bi bi-lock-fill"></i> Tertutup</span>'
                    : '<span class="badge text-bg-success"><i class="bi bi-unlock-fill"></i> Terbuka</span>';

                // Tombol Aksi
                $btn_detail = '<a href="' . site_url('berkas-inaktif/detail/' . $item['id']) . '" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat Detail Isi Berkas"><i class="bi bi-eye-fill"></i></a>';
                $btn_edit = '';
                $btn_delete = '';
                $btn_pemberkasan = '';
                $btn_toggle_status = '';

                if (has_permission('cud_arsip_inaktif')) { // Asumsi hak akses ini ada
                    $btn_edit = '<a href="' . site_url('berkas-inaktif/edit/' . $item['id']) . '" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit Berkas"><i class="bi bi-pencil-fill"></i></a>';

                    $form_delete = '<form action="' . site_url('berkas-inaktif/delete/' . $item['id']) . '" method="post" class="d-inline form-delete">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus Berkas"><i class="bi bi-trash-fill"></i></button>
                                 </form>';
                    $btn_delete = $form_delete;

                    if ($item['status_tutup'] === 'terbuka') {
                        $btn_pemberkasan = '<a href="' . site_url('berkas-inaktif/pemberkasan/' . $item['id']) . '" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Tambah Item ke Berkas"><i class="bi bi-plus-circle-fill"></i></a>';

                        $form_tutup = '<form action="' . site_url('berkas-inaktif/tutup/' . $item['id']) . '" method="post" class="d-inline form-toggle-status">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Tutup Berkas"><i class="bi bi-lock-fill"></i></button>
                                   </form>';
                        $btn_toggle_status = $form_tutup;
                    } else { // Berkas tertutup
                        $form_buka = '<form action="' . site_url('berkas-inaktif/buka/' . $item['id']) . '" method="post" class="d-inline form-toggle-status">
                                        <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Buka Kembali Berkas"><i class="bi bi-unlock-fill"></i></button>
                                   </form>';
                        $btn_toggle_status = $form_buka;
                    }
                }

                // Gabungkan semua tombol aksi ke dalam satu div
                $aksi = '<div class="d-flex justify-content-center gap-1">' . $btn_detail . $btn_pemberkasan . $btn_edit . $btn_toggle_status . $btn_delete . '</div>';

                // Susun baris sesuai urutan di view Anda
                $row = [
                    '', // No. urut
                    $kodeUnit,
                    esc($item['kode_klasifikasi'] ?? '-'),
                    $noDanNamaBerkas,
                    $kurunWaktu,
                    '<span class="badge bg-primary">' . $item['jumlah_item'] . ' Item</span>',
                    $item['no_box'] ?? '-',
                    $statusTutup,
                    $aksi
                ];
                $data[] = $row;
            }

            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
        }
    }
    public function tutupBerkas($id = null)
    {
        // 1. Ambil informasi berkas
        $berkas = $this->berkasInaktifModel->find($id);
        if (!$berkas) {
            session()->setFlashdata('error', 'Berkas tidak ditemukan.');
            return redirect()->to('/berkas-inaktif');
        }

        // 2. Hitung jumlah item di dalam berkas
        $jumlahItem = $this->itemInaktifModel->where('id_berkas', $id)->countAllResults();

        // --- VALIDASI BARU ---
        if ($jumlahItem === 0) {
            session()->setFlashdata('error', 'Isi berkas 0, tidak bisa tutup berkas.');
            return redirect()->to('/berkas-inaktif');
        }

        // 3. Jika validasi lolos, update status berkas
        try {
            $this->berkasInaktifModel->update($id, ['status_tutup' => 'tertutup']);
            session()->setFlashdata('success', 'Berkas berhasil ditutup.');
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Terjadi kesalahan saat menutup berkas: ' . $e->getMessage());
        }

        return redirect()->to('/berkas-inaktif');
    }

    // --- METHOD BARU: Buka Berkas ---
    public function bukaBerkas($id = null)
    {
        $this->berkasInaktifModel->update($id, ['status_tutup' => 'terbuka']);
        session()->setFlashdata('success', 'Berkas berhasil dibuka kembali.');
        return redirect()->to('/berkas-inaktif');
    }

    /**
     * Menampilkan halaman form untuk menambah berkas baru.
     */
    public function new()
    {
        if (!has_permission('cud_arsip_inaktif')) {
            return redirect()->to('/berkas-inaktif')->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }
        $data = [
            'title'      => 'Tambah Berkas Baru',
            'validation' => \Config\Services::validation(),
            // Kirim data untuk dropdown
            'klasifikasi_options' => $this->klasifikasiModel->getForDropdownInput(),
            'es2_options' => $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll()
        ];
        // Tambahkan logika untuk menyiapkan data unit kerja
        $data = array_merge($data, $this->prepareUnitKerjaData());

        return view('berkas_inaktif/form', $data);
    }

    /**
     * Memproses data dari form tambah.
     */
    public function create()
    {
        if (!has_permission('cud_arsip_inaktif')) {
            return redirect()->to('/berkas-inaktif')->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }
        $formRules = [
            'no_berkas_input' => 'required|integer',
            'id_es3'          => 'required|integer',
            'nama_berkas'     => 'required|min_length[5]',
            'id_klasifikasi'  => 'required|integer',
        ];
        if (!$this->validate($formRules)) {
            return redirect()->back()->withInput();
        }
        // Ambil data yang sudah divalidasi
        $id_es3 = $this->request->getPost('id_es3');
        $no_berkas_input = $this->request->getPost('no_berkas_input');

        // Ambil kode-kode
        $es3 = $this->unitKerjaEs3Model->find($id_es3);
        $es2 = $this->unitKerjaEs2Model->find($es3['id_es2']);

        // Bentuk no_berkas yang akan disimpan
        $no_berkas_final = $es3['kode'] . '-' . $no_berkas_input;

        // Siapkan data untuk disimpan
        $data = [
            'nama_berkas'    => $this->request->getPost('nama_berkas'),
            'id_klasifikasi' => $this->request->getPost('id_klasifikasi'),
            'no_box'         => $this->request->getPost('no_box'),
            'no_berkas'      => $no_berkas_final, // Field ini yang akan dicek is_unique oleh Model
            'id_es3'         => $id_es3,
            'id_es2'         => $es3['id_es2'],
            'id_user'        => session()->get('user_id'),
            'status_berkas'  => 'aktif',
        ];
        // dd($data);
        // Sekarang proses save akan menggunakan validasi dari model (termasuk is_unique)
        if ($this->berkasInaktifModel->save($data)) {
            $this->session->setFlashdata('success', 'Berkas baru berhasil ditambahkan.');
        } else {
            // --- PASANG DEBUGGER DI SINI JIKA SAVE GAGAL ---
            $this->berkasInaktifModel->errors();
            // dd($this->berkasInaktifModel->errors());
            // ------------------------------------------------
        }

        return redirect()->to('/berkas-inaktif');
    }

    /**
     * Menampilkan halaman form untuk mengedit berkas.
     */
    public function edit($id = null)
    {
        if (!has_permission('cud_arsip_inaktif')) {
            return redirect()->to('/berkas-inaktif')->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }
        $berkas = $this->berkasInaktifModel->find($id);
        if (!$berkas) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'      => 'Edit Berkas',
            'validation' => \Config\Services::validation(),
            'berkas'     => $berkas,
            'klasifikasi_options' => $this->klasifikasiModel->getForDropdownInput(),
        ];

        // Tambahkan logika untuk menyiapkan data unit kerja
        $data = array_merge($data, $this->prepareUnitKerjaData($berkas));

        return view('berkas_inaktif/form', $data);
    }

    /**
     * Memproses data dari form edit.
     */
    public function update($id = null)
    {
        if (!has_permission('cud_arsip_inaktif')) {
            return redirect()->to('/berkas-inaktif')->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }
        if (!$this->validate($this->berkasInaktifModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }
        $this->berkasInaktifModel->update($id, $this->request->getPost());
        $this->session->setFlashdata('success', 'Data berkas berhasil diperbarui.');
        return redirect()->to('/berkas-inaktif');
    }

    /**
     * Menghapus data berkas dengan pengecekan keamanan.
     */
    public function delete($id = null)
    {
        if (!has_permission('cud_arsip_inaktif')) {
            return redirect()->to('/berkas-inaktif')->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }
        $itemCount = $this->itemInaktifModel->where('id_berkas', $id)->countAllResults();
        if ($itemCount > 0) {
            $this->session->setFlashdata('error', 'Gagal menghapus! Masih ada ' . $itemCount . ' item di dalam berkas ini.');
            return redirect()->to('/berkas-inaktif');
        }
        $this->berkasInaktifModel->delete($id);
        $this->session->setFlashdata('success', 'Data berkas berhasil dihapus.');
        return redirect()->to('/berkas-inaktif');
    }

    /**
     * Menampilkan halaman detail isi berkas.
     */
    public function detail($id = null)
    {
        $berkas = $this->berkasInaktifModel->builder()
            ->select('berkas_inaktif.*, klasifikasi.kode as kode_klasifikasi, klasifikasi.nama_klasifikasi, klasifikasi.umur_aktif, klasifikasi.umur_inaktif, klasifikasi.nasib_akhir')
            ->join('klasifikasi', 'klasifikasi.id = berkas_inaktif.id_klasifikasi', 'left')
            ->where('berkas_inaktif.id', $id)
            ->get()->getRowArray();

        if (!$berkas) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Hitung jumlah item secara terpisah
        $berkas['jumlah_item'] = $this->itemInaktifModel->where('id_berkas', $id)->countAllResults();
        $berkas['qr_code_url'] = base_url('files/qrcodes/' . $berkas['qr_code']);

        $data = [
            'title' => 'Detail Berkas: ' . esc($berkas['nama_berkas']),
            'berkas' => $berkas,
            'session' => $this->session // Kirim session untuk notifikasi
        ];
        return view('berkas_inaktif/detail', $data);
    }

    /**
     * Endpoint AJAX untuk DataTables di halaman detail.
     */
    public function ajaxListItemsInBerkas($berkasId = null)
    {
        if ($this->request->isAJAX()) {
            $result = $this->itemInaktifModel->getDataTablesItemsInBerkas($this->request, $berkasId);
            $data = [];
            foreach ($result['data'] as $item) {
                $row = [];
                $row[] = $item['no_dokumen'];
                $row[] = esc($item['judul_dokumen']);
                $row[] = $item['tgl_dokumen'];
                $row[] = '<button class="btn btn-sm btn-warning btn-lepas-berkas" data-id="' . $item['id'] . '" data-judul="' . esc($item['judul_dokumen']) . '">Lepas Berkas</button>';
                $data[] = $row;
            }
            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
        }
    }

    // --- METHOD-METHOD BARU UNTUK ALUR PEMBERKASAN ---

    /**
     * Menampilkan halaman khusus untuk proses pemberkasan.
     */
    public function pemberkasanPage($berkasId = null)
    {
        $berkas = $this->berkasInaktifModel
            ->join('klasifikasi', 'klasifikasi.id = berkas_inaktif.id_klasifikasi', 'left')
            ->find($berkasId);
        if (!$berkas) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($berkas['status_tutup'] === 'tertutup') {
            return redirect()->to('berkas-inaktif/detail/' . $berkasId)->with('error', 'Berkas ini sudah ditutup dan tidak bisa ditambahkan item baru.');
        }

        // --- TAMBAHAN BARU: Ambil jumlah item yang sudah ada di berkas ini ---
        $jumlahItemSaatIni = $this->itemInaktifModel->where('id_berkas', $berkasId)->countAllResults();

        $data = [
            'title'   => 'Pemberkasan untuk: ' . esc($berkas['nama_berkas']),
            'berkas'  => $berkas,
            'session' => $this->session,
            'jumlahItemSaatIni' => $jumlahItemSaatIni, // Kirim ke view
        ];
        return view('berkas_inaktif/pemberkasan', $data);
    }

    /**
     * Endpoint AJAX untuk tabel item yang belum diberkaskan.
     */
    public function ajaxListUnfiledItems()
    {
        if ($this->request->isAJAX()) {
            $result = $this->itemInaktifModel->getDataTablesForPemberkasan($this->request);
            $data = [];
            foreach ($result['data'] as $item) {
                $row = [];
                $row[] = $item['id']; // ID untuk checkbox
                $row[] = $item['no_dokumen'] ?? '-';
                $row[] = esc($item['judul_dokumen']);
                $row[] = $item['tahun_cipta'];
                $row[] = $item['kode_klas'] ?? '-'; // Tambahan kode klasifikasi
                $data[] = $row;
            }
            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
        }
    }

    public function addItems($berkasId = null)
    {
        $itemIds = $this->request->getPost('item_ids');
        if (empty($itemIds)) {
            session()->setFlashdata('error', 'Tidak ada item yang dipilih untuk ditambahkan.');
            return redirect()->to('berkas-inaktif/pemberkasan/' . $berkasId);
        }

        $berkas = $this->berkasInaktifModel->find($berkasId);
        if (!$berkas) {
            session()->setFlashdata('error', 'Berkas tujuan tidak ditemukan.');
            return redirect()->to('berkas-inaktif');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Gunakan instance baru untuk operasi update untuk menghindari masalah state
            $cleanItemModel = new \App\Models\ItemInaktifModel();

            // --- PERBAIKAN UTAMA DI SINI ---
            // 1. Ambil data item yang akan diupdate secara spesifik (untuk mendapatkan id_user, status_arsip, id_es2 lama)
            $itemsToUpdate = $cleanItemModel->whereIn('id', $itemIds)->findAll();

            if (empty($itemsToUpdate)) {
                throw new \Exception('Tidak ada item valid yang ditemukan untuk diperbarui.');
            }

            foreach ($itemsToUpdate as $item) {
                // Pastikan item belum diberkaskan
                if (!empty($item['id_berkas'])) {
                    throw new \Exception('Item ' . esc($item['judul_dokumen']) . ' sudah diberkaskan ke berkas lain.');
                }

                $dataToSave = [
                    'id'            => $item['id'], // ID item yang akan diupdate
                    'id_berkas'     => $berkasId,   // Berkas tujuan baru
                    // Kolom-kolom NOT NULL atau yang diisi otomatis harus dipertahankan.
                    // Ini akan diambil dari data existing item agar tidak null.
                    'id_user'       => $item['id_user'],
                    'id_es2'        => $item['id_es2'],
                    'id_es3'        => $item['id_es3'],
                    'id_klasifikasi' => $item['id_klasifikasi'],
                    'id_jenis_naskah' => $item['id_jenis_naskah'],
                    'judul_dokumen' => $item['judul_dokumen'],
                    'tgl_dokumen'   => $item['tgl_dokumen'],
                    'tahun_cipta'   => $item['tahun_cipta'],
                    'jumlah'        => $item['jumlah'],
                    'tk_perkembangan' => $item['tk_perkembangan'],
                    'media_simpan'  => $item['media_simpan'],
                    'status_arsip'  => $item['status_arsip'],
                    'status_pindah' => $item['status_pindah'],
                    'pinjam'        => $item['pinjam'],
                    'dasar_catat'   => $item['dasar_catat'],
                    'no_dokumen'    => $item['no_dokumen'],
                    'lokasi_simpan' => $item['lokasi_simpan'],
                    'no_box'        => $item['no_box'],
                    'nama_file'     => $item['nama_file'],
                    'nama_folder'   => $item['nama_folder'],
                    'nama_link'     => $item['nama_link'],
                ];

                // Lakukan update per item
                if ($cleanItemModel->update($item['id'], $dataToSave) === false) {
                    throw new \Exception('Gagal update item ID ' . $item['id'] . ': ' . implode('<br>', $cleanItemModel->errors()));
                }
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                throw new \Exception('Transaksi database gagal setelah update item.');
            } else {
                $db->transCommit();
                session()->setFlashdata('success', count($itemIds) . ' item berhasil ditambahkan ke dalam berkas.');
                return redirect()->to('berkas-inaktif/detail/' . $berkasId);
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error addItems: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            return redirect()->to('berkas-inaktif/pemberkasan/' . $berkasId);
        }
    }
    public function pinjamBerkas()
    {
        // ... (Kode pinjamBerkas yang sudah ada) ...
        if (!$this->request->isAJAX()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $id_berkas = $this->request->getPost('id_pinjam_target'); // ID dari modal
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

        // Cek berkasnya, pastikan tidak sedang dipinjam
        $berkas = $this->berkasInaktifModel->find($id_berkas);
        if (!$berkas) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Berkas tidak ditemukan.']);
        }
        if ($berkas['pinjam'] == 1) {
            return $this->response->setJSON(['status' => 'warning', 'message' => 'Berkas ini sedang dipinjam.']);
        }
        if ($berkas['status_tutup'] === 'tertutup') {
            return $this->response->setJSON(['status' => 'warning', 'message' => 'Berkas ini tertutup, tidak bisa dipinjam.']);
        }


        // Mulai transaksi
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Catat di tabel peminjaman
            $this->peminjamanModel->save([
                'id_berkas_inaktif'       => $id_berkas,
                'id_user_peminjam'    => session()->get('user_id'),
                'peminjam_nama'       => $peminjam_nama,
                'peminjam_unit'       => $peminjam_unit,
                'tgl_pinjam'          => $tgl_pinjam,
                'tgl_kembali_rencana' => $tgl_kembali_rencana,
                'keterangan'          => $keterangan,
                'created_by'          => session()->get('user_id'),
            ]);
            // Update status di berkas_inaktif
            $this->berkasInaktifModel->update($id_berkas, ['pinjam' => 1]);

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Berkas berhasil dipinjam.']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error pinjamBerkas: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses peminjaman: ' . $e->getMessage()]);
        }
    }
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();

        // ===================================================================
        // SHEET 1: TEMPLATE UNTUK DIISI PENGGUNA
        // ===================================================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Template Import Berkas');

        // Header Kolom
        $headers = [
            'A1' => 'Kode Klasifikasi',
            'B1' => 'Kode Unit Kerja ES2',
            'C1' => 'Kode Unit Kerja ES3',
            'D1' => 'No Berkas',
            'E1' => 'Nama Berkas',
            'F1' => 'Tahun Item Awal',
            'G1' => 'Tahun Item Akhir',
            'H1' => 'No Box',
            'I1' => 'No Label',
            'J1' => 'Link Barcode',
            'K1' => 'Status Berkas',
        ];

        // Tulis header ke sheet dan terapkan styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ];
        $sheet1->getStyle('A1:K1')->applyFromArray($headerStyle)->getAlignment()->setWrapText(true);
        $sheet1->getRowDimension('1')->setRowHeight(40);
        foreach ($headers as $cell => $value) {
            $sheet1->setCellValue($cell, $value);
        }

        // Tambahkan komentar/petunjuk pengisian
        $sheet1->getComment('A1')->getText()->createTextRun("Wajib diisi. Pilih kode dari sheet 'Data Referensi'.");
        $sheet1->getComment('B1')->getText()->createTextRun("Wajib diisi. Pilih kode dari sheet 'Data Referensi'.");
        $sheet1->getComment('C1')->getText()->createTextRun("Wajib diisi. Pilih kode dari sheet 'Data Referensi'.");
        $sheet1->getComment('D1')->getText()->createTextRun("Wajib diisi. Contoh: BERKAS-001");
        $sheet1->getComment('E1')->getText()->createTextRun("Wajib diisi. Nama lengkap berkas.");
        $sheet1->getComment('F1')->getText()->createTextRun("Contoh: 2020 (hanya 4 digit)");
        $sheet1->getComment('G1')->getText()->createTextRun("Contoh: 2023 (hanya 4 digit)");
        $sheet1->getComment('H1')->getText()->createTextRun("Contoh: BX-001");
        $sheet1->getComment('I1')->getText()->createTextRun("Contoh: LBL-A");
        $sheet1->getComment('J1')->getText()->createTextRun("Contoh: http://barcode.contoh.com/123");
        $sheet1->getComment('K1')->getText()->createTextRun("Pilih: aktif atau inaktif.");

        // ===================================================================
        // SHEET 2: DATA REFERENSI
        // ===================================================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Data Referensi');

        // Ambil semua data dari tabel relasi
        $klasifikasiData = $this->klasifikasiModel->select('kode, nama_klasifikasi')->orderBy('kode', 'ASC')->findAll();
        $es2Data = $this->unitKerjaEs2Model->select('kode, nama_es2')->orderBy('kode', 'ASC')->findAll();
        $es3Data = $this->unitKerjaEs3Model->select('kode, nama_es3')->orderBy('kode', 'ASC')->findAll();

        // Fungsi kecil untuk menulis data referensi ke sheet
        $writeData = function ($sheet, $startCol, $title, $headers, $data) {
            // --- PERBAIKAN DI SINI ---
            // getAlignment() harus dipanggil dari getStyle(), bukan getFont()
            $sheet->setCellValue($startCol . '1', $title)->mergeCells($startCol . '1:' . chr(ord($startCol) + count($headers) - 1) . '1');
            $sheet->getStyle($startCol . '1')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle($startCol . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // <-- PANGGILAN SUDAH BENAR DI BARIS BARU INI

            $col = $startCol;
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '2', $header)->getStyle($col . '2')->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setAutoSize(true);
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

        // Auto-size kolom di sheet referensi
        foreach (range('A', 'J') as $columnID) $sheet2->getColumnDimension($columnID)->setAutoSize(true);

        // ===================================================================
        // BUAT DROPDOWN VALIDATION DI SHEET 1
        // ===================================================================
        for ($i = 2; $i <= 501; $i++) { // Terapkan validasi untuk 500 baris data
            $sheet1->getCell('A' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1("='Data Referensi'!\$A\$3:\$A\$$lastRowKlasifikasi");
            $sheet1->getCell('B' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1("='Data Referensi'!\$D\$3:\$D\$$lastRowEs2");
            $sheet1->getCell('C' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1("='Data Referensi'!\$G\$3:\$G\$$lastRowEs3");
            $sheet1->getCell('K' . $i)->getDataValidation()->setType(DataValidation::TYPE_LIST)->setFormula1('"aktif,inaktif"');
        }

        foreach (range('A', 'K') as $columnID) $sheet1->getColumnDimension($columnID)->setAutoSize(true);

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Template_Import_Berkas_Aktif_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
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
            return redirect()->to('/berkas-inaktif')->withInput()->with('validation', $this->validator);
        }

        $fileExcel = $this->request->getFile('file_excel');
        $authData = session()->get('auth_data');

        // --- BAGIAN 2: Simpan File & Catat Riwayat Import ---
        $newFileName = '';
        if ($fileExcel->isValid() && !$fileExcel->hasMoved()) {
            $newFileName = $fileExcel->getRandomName();
            $fileExcel->move(WRITEPATH . 'uploads/imports', $newFileName);

            $importDaftarModel = new \App\Models\ImportDaftarModel();
            $importData = [
                'id_es2'   => $authData['id_es2'] ?? null, // Ambil id_es2 dari hak fitur user
                'tahun'    => $this->request->getPost('tahun'),
                'semester' => $this->request->getPost('semester'),
                'id_user'  => session()->get('user_id'),
                'file'     => $newFileName,
            ];
            $importDaftarModel->save($importData);
        } else {
            session()->setFlashdata('error', 'Gagal memindahkan file yang diunggah: ' . $fileExcel->getErrorString());
            return redirect()->to('/berkas-inaktif');
        }

        // --- BAGIAN 3: Proses Isi File Excel & Transaksi Database ---
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $spreadsheet = IOFactory::load(WRITEPATH . 'uploads/imports/' . $newFileName);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $dataToInsert = [];
            $errors = [];
            $berhasil = 0;
            $gagal = 0;
            $id_user_login = session()->get('user_id');

            $klasifikasiModel = new \App\Models\KlasifikasiModel();
            $unitKerjaEs2Model = new \App\Models\UnitKerjaEs2Model();
            $unitKerjaEs3Model = new \App\Models\UnitKerjaEs3Model();

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex == 0 || empty(array_filter($row))) {
                    continue;
                }

                $kodeKlasifikasi = trim($row[0] ?? '');
                $kodeEs2         = trim($row[1] ?? '');
                $kodeEs3         = trim($row[2] ?? '');
                $noBerkas        = trim($row[3] ?? '');
                $namaBerkas      = trim($row[4] ?? '');
                $thnItemAwal     = trim($row[5] ?? '');
                $thnItemAkhir    = trim($row[6] ?? '');
                $noBox           = trim($row[7] ?? '');
                $noLabel         = trim($row[8] ?? '');
                $linkBarcode     = trim($row[9] ?? '');
                $statusBerkas    = trim($row[10] ?? 'aktif');

                $rowErrors = [];

                // 1. Validasi Kolom Wajib
                if (empty($kodeKlasifikasi)) $rowErrors[] = "Kolom A (Klasifikasi) wajib diisi.";
                if (empty($kodeEs2)) $rowErrors[] = "Kolom B (ES2) wajib diisi.";
                if (empty($kodeEs3)) $rowErrors[] = "Kolom C (ES3) wajib diisi.";
                if (empty($noBerkas)) $rowErrors[] = "Kolom D (No Berkas) wajib diisi.";
                if (empty($namaBerkas)) $rowErrors[] = "Kolom E (Nama Berkas) wajib diisi.";

                // 2. Validasi Format
                if (!empty($thnItemAwal) && !preg_match('/^\d{4}$/', $thnItemAwal)) $rowErrors[] = "Kolom F (Tahun Item Awal) harus YYYY.";
                if (!empty($thnItemAkhir) && !preg_match('/^\d{4}$/', $thnItemAkhir)) $rowErrors[] = "Kolom G (Tahun Item Akhir) harus YYYY.";

                // Jika sudah ada error dasar
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

                // Cek lagi jika ada error setelah lookup DB
                if (!empty($rowErrors)) {
                    $gagal++;
                    $errors[] = "Baris " . ($rowIndex + 1) . ": " . implode(', ', $rowErrors);
                    continue;
                }

                // --- SIAPKAN DATA UNTUK INSERT ---
                $dataToInsert[] = [
                    'id_klasifikasi' => $klasifikasi['id'],
                    'id_user'        => $id_user_login, // User yang menginput
                    'id_es2'         => $es2['id'],
                    'id_es3'         => $es3['id'],
                    'no_berkas'      => $noBerkas,
                    'nama_berkas'    => $namaBerkas,
                    'thn_item_awal'  => $thnItemAwal,
                    'thn_item_akhir' => $thnItemAkhir,
                    'no_box'         => $noBox,
                    'no_label'       => $noLabel,
                    'link_barcode'   => $linkBarcode,
                    'status_berkas'  => $statusBerkas,
                    'status_tutup'   => 'terbuka', // Default saat import
                    'pinjam'         => 0, // Default saat import
                ];
                $berhasil++;
            }

            // --- Cek apakah ada baris yang gagal setelah loop validasi ---
            if ($gagal > 0) {
                $db->transRollback();
                session()->setFlashdata('error', "Proses import dibatalkan. Ditemukan $gagal baris error. Semua data tidak disimpan.");
                session()->setFlashdata('import_errors', $errors);
                session()->setFlashdata('show_import_modal', true);
                return redirect()->to('/berkas-inaktif');
            }

            if (!empty($dataToInsert)) {
                $this->berkasInaktifModel->insertBatch($dataToInsert);
            }
            $db->transCommit(); // Commit jika semua lolos

            $pesan = "Proses import selesai. Berhasil: $berhasil data. Gagal: $gagal data.";
            session()->setFlashdata('success', $pesan);
            session()->setFlashdata('show_import_modal', true);
            return redirect()->to('/berkas-inaktif');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error prosesImport BerkasInaktif: ' . $e->getMessage());
            session()->setFlashdata('show_import_modal', true);
            session()->setFlashdata('error', 'Gagal memproses file Excel: ' . $e->getMessage());
            return redirect()->to('/berkas-inaktif');
        }
    }
}
