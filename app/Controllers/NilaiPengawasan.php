<?php

namespace App\Controllers;

use App\Models\NilaiPengawasanModel;
use App\Models\UnitKerjaEs2Model;
use App\Controllers\BaseController;

class NilaiPengawasan extends BaseController
{
    protected $nilaiPengawasanModel;
    protected $unitKerjaEs2Model;
    protected $id_es2_admin;
    protected $userRole;

    public function __construct()
    {
        $this->nilaiPengawasanModel = new NilaiPengawasanModel();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        helper(['form']);

        $session = session();
        $this->userRole = $session->get('role_access');
        $this->id_es2_admin = $session->get('auth_data')['id_es2'] ?? null;

        // Cek Otorisasi Dasar: Jika user atau admin tanpa ES2, batasi akses
        if ($this->userRole === 'user' || ($this->userRole === 'admin' && empty($this->id_es2_admin))) {
            if ($this->userRole !== 'superadmin') {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak memiliki hak akses ke modul ini atau unit kerja Anda belum dikonfigurasi.');
            }
        }
    }

    public function index()
    {
        $data = ['title' => 'Manajemen Nilai Pengawasan', 'session' => session()];
        return view('nilai_pengawasan/index', $data);
    }

    public function listData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        try {
            $result = $this->nilaiPengawasanModel->getDataTablesList($this->request);
            $data = [];

            foreach ($result['data'] as $np) {

                // Logika Badge Kategori
                $badgeClass = match (substr($np['kategori'], 0, 2)) {
                    'AA' => 'bg-success',
                    'A ' => 'bg-info',
                    'BB' => 'bg-primary',
                    'B ' => 'bg-warning text-dark',
                    'CC' => 'bg-secondary',
                    default => 'bg-danger',
                };

                $kategoriDisplay = '<span class="badge ' . $badgeClass . '">' . esc($np['kategori']) . '</span>';

                // Tombol Aksi: Hanya izinkan Edit/Delete jika Superadmin atau Admin unit yang bersangkutan
                $isAuthorized = $this->userRole === 'superadmin' || ($this->userRole === 'admin' && $np['id_es2'] === $this->id_es2_admin);

                $aksi = '';
                if ($isAuthorized) {
                    $btn_edit = '<a href="' . site_url('nilai-pengawasan/edit/' . $np['id']) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="fas fa-edit"></i></a>';
                    $form_delete = '<form action="' . site_url('nilai-pengawasan/' . $np['id']) . '" method="post" class="d-inline form-delete">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>';
                    $aksi = $btn_edit . ' ' . $form_delete;
                } else {
                    $aksi = '<span class="text-muted fst-italic">Read Only</span>';
                }

                $row = [
                    '', // 0. No.
                    esc($np['nama_es2'] ?? 'Unit Tidak Ditemukan'),
                    esc($np['tahun']),
                    number_format($np['skor'] ?? 0, 2),
                    $kategoriDisplay,
                    esc($np['user_name'] ?? 'Sistem'),
                    $aksi
                ];
                $data[] = $row;
            }

            return $this->response->setJSON([
                'draw' => $result['draw'],
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'FATAL AJAX Error (NilaiPengawasan): ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Gagal memuat data. Cek logs server.',
                'debug' => CI_DEBUG ? $e->getMessage() : 'Internal Server Error'
            ]);
        }
    }

    // Helper untuk mendapatkan opsi ES2 sesuai hak akses
    private function getEs2Options()
    {
        if ($this->userRole === 'superadmin') {
            return $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll();
        } elseif ($this->userRole === 'admin' && $this->id_es2_admin) {
            // Admin hanya melihat ES2 miliknya sendiri
            $es2 = $this->unitKerjaEs2Model->find($this->id_es2_admin);
            return $es2 ? [$es2] : [];
        }
        return [];
    }

    public function new()
    {
        $es2Options = $this->getEs2Options();

        $preselectedEs2Id = null;
        if ($this->userRole === 'admin' && count($es2Options) === 1) {
            $preselectedEs2Id = $es2Options[0]['id'];
        }

        $data = [
            'title' => 'Tambah Nilai Pengawasan',
            'validation' => \Config\Services::validation(),
            'es2_options' => $es2Options,
            'preselected_es2_id' => $preselectedEs2Id,
        ];
        return view('nilai_pengawasan/form', $data);
    }

    public function edit($id = null)
    {
        $np = $this->nilaiPengawasanModel->find($id);
        if (!$np) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // --- OTORISASI EDIT: Pastikan Admin hanya edit data di unitnya ---
        if ($this->userRole === 'admin' && $np['id_es2'] !== $this->id_es2_admin) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak diizinkan mengedit nilai unit kerja lain.');
        }
        // --- END OTORISASI ---

        $data = [
            'title' => 'Edit Nilai Pengawasan',
            'validation' => \Config\Services::validation(),
            'np' => $np,
            'es2_options' => $this->getEs2Options(),
        ];
        return view('nilai_pengawasan/form', $data);
    }

    public function create()
    {
        return $this->processSave();
    }

    public function update($id = null)
    {
        return $this->processSave($id);
    }

    private function processSave($id = null)
    {
        $postData = $this->request->getPost();
        $isEditMode = (bool)$id;

        // --- Ambil ID ES2 dari POST atau Hidden Field ---
        // Jika Admin, nilai id_es2 datang dari hidden field
        $id_es2 = $postData['id_es2'];
        $tahun = $postData['tahun'];

        // --- 1. OTORISASI INPUT: Pastikan Admin hanya mengatur ES2 miliknya ---
        if ($this->userRole === 'admin' && $id_es2 !== $this->id_es2_admin) {
            session()->setFlashdata('error', 'Anda hanya dapat mengatur nilai untuk unit kerja yang terikat pada akun Anda.');
            return redirect()->back()->withInput();
        }

        // --- 2. Validasi Kombinasi Unik (id_es2 dan tahun) ---
        if (!$this->nilaiPengawasanModel->validateUniqueEs2Tahun($id_es2, $tahun, $id)) {
            session()->setFlashdata('error', "Nilai Pengawasan untuk Unit Eselon 2 ini pada Tahun {$tahun} sudah tersedia.");
            return redirect()->back()->withInput();
        }

        // --- 3. Validasi Rules Dasar ---
        if (!$this->validate($this->nilaiPengawasanModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- 4. Simpan Data ---
        $dataToSave = [
            'id_es2' => $id_es2,
            'skor'   => $postData['skor'],
            'tahun'  => $tahun,
        ];

        if ($isEditMode) {
            $result = $this->nilaiPengawasanModel->update($id, $dataToSave);
        } else {
            // Callback Model akan mengisi id_user dan kategori secara otomatis
            $result = $this->nilaiPengawasanModel->insert($dataToSave);
        }

        if ($result === false) {
            return redirect()->back()->withInput()->with('errors', $this->nilaiPengawasanModel->errors());
        }

        $message = $isEditMode ? 'Nilai Pengawasan berhasil diperbarui.' : 'Nilai Pengawasan berhasil ditambahkan.';
        session()->setFlashdata('success', $message);
        return redirect()->to('/nilai-pengawasan');
    }

    public function delete($id = null)
    {
        $np = $this->nilaiPengawasanModel->find($id);
        if (!$np) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // --- OTORISASI HAPUS: Pastikan Admin hanya hapus data di unitnya ---
        if ($this->userRole === 'admin' && $np['id_es2'] !== $this->id_es2_admin) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak diizinkan menghapus nilai unit kerja lain.');
        }
        // --- END OTORISASI ---

        if ($this->nilaiPengawasanModel->delete($id)) {
            session()->setFlashdata('success', 'Nilai Pengawasan berhasil dihapus.');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus data.');
        }
        return redirect()->to('/nilai-pengawasan');
    }
}
