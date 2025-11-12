<?php

namespace App\Controllers;

use App\Models\HakFiturModel;
use App\Models\UserModel;
use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;

class HakFitur extends BaseController
{
    protected $hakFiturModel;
    protected $userModel;
    protected $unitKerjaEs1Model;
    protected $unitKerjaEs2Model;
    protected $unitKerjaEs3Model;
    protected $session;

    public function __construct()
    {
        $this->hakFiturModel = new HakFiturModel();
        $this->userModel = new UserModel();
        $this->unitKerjaEs1Model = new UnitKerjaEs1Model();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
        $this->session = session();
        helper(['form']);
    }

    public function index()
    {
        $data = ['title' => 'Manajemen Hak Fitur Pengguna', 'session' => $this->session];
        return view('hak_fitur/index', $data);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->hakFiturModel->getDataTablesHakFitur($this->request);

            $data = [];
            foreach ($result['data'] as $row) {
                $level = '';

                if (!empty($row['nama_es3'])) {
                    $level = '<span class="badge bg-info d-inline-flex align-items-center me-1 py-2 px-3 rounded-pill">' . esc($row['kode_es2'] ?? 'N/A') . '</span>' .
                        '<span class="badge bg-primary d-inline-flex align-items-center py-2 px-3 rounded-pill">' . esc($row['nama_es3']) . '</span>';
                } elseif (!empty($row['nama_es2'])) {
                    $level = '<span class="badge bg-success d-inline-flex align-items-center py-2 px-3 rounded-pill">' . esc($row['nama_es2']) . '</span>';
                } elseif (!empty($row['nama_es1'])) {
                    $level = '<span class="badge bg-warning text-dark d-inline-flex align-items-center py-2 px-3 rounded-pill">' . esc($row['nama_es1']) . '</span>';
                } else {
                    $level = '<span class="badge bg-secondary fst-italic py-2 px-3 rounded-pill">Belum Diatur</span>';
                }

                $btn_edit = '<a href="' . site_url('hak-fitur/edit/' . $row['id']) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="fas fa-edit"></i> Edit</a>';
                $form_delete = '<form action="' . site_url('hak-fitur/delete/' . $row['id']) . '" method="post" class="d-inline form-delete">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i> Hapus</button>
                                </form>';

                $rowData = [
                    '',
                    esc($row['user_name']),
                    $level,
                    $btn_edit . ' ' . $form_delete,
                ];

                $data[] = $rowData;
            }

            return $this->response->setJSON([
                'draw'            => $result['draw'],
                'recordsTotal'    => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data'            => $data,
            ]);
        }
    }

    public function new()
    {
        $data = [
            'title' => 'Tambah Hak Fitur',
            'validation' => \Config\Services::validation(),
            'user_options' => $this->userModel->where('role_access !=', 'superadmin')->orderBy('name', 'ASC')->findAll(),
            'es1_options' => $this->unitKerjaEs1Model->orderBy('nama_es1', 'ASC')->findAll(),
        ];
        return view('hak_fitur/form', $data);
    }

    public function create()
    {
        $postData = $this->request->getPost();

        // 1. Validasi Dasar Hak Fitur (id_user harus ada dan unik)
        $validationRules = [
            'id_user' => "required|is_unique[hak_fitur.id_user]",
        ];

        if (!$this->validate($validationRules)) {
            // Jika validasi gagal (id_user sudah ada)
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. Logika Penentuan Level Unit Kerja (Diambil dari form)
        $dataToSave = [
            'id_user' => $postData['id_user'],
            'id_es1'  => null,
            'id_es2'  => null,
            'id_es3'  => null,
        ];

        $id_es1 = $postData['id_es1'] ?? null;
        $id_es2 = $postData['id_es2'] ?? null;
        $id_es3 = $postData['id_es3'] ?? null;

        if (!empty($id_es3)) {
            $dataToSave['id_es3'] = $id_es3;
            $dataToSave['id_es2'] = $id_es2;
            $dataToSave['id_es1'] = $id_es1;
        } elseif (!empty($id_es2)) {
            $dataToSave['id_es2'] = $id_es2;
            $dataToSave['id_es1'] = $id_es1;
        } elseif (!empty($id_es1)) {
            $dataToSave['id_es1'] = $id_es1;
        } else {
            return redirect()->back()->withInput()->with('error', 'Anda harus memilih minimal satu level unit kerja.');
        }

        // 3. Simpan data Hak Fitur (INSERT)
        if ($this->hakFiturModel->insert($dataToSave) === false) {
            return redirect()->back()->withInput()->with('errors', $this->hakFiturModel->errors());
        }

        session()->setFlashdata('success', 'Hak Fitur berhasil ditambahkan.');
        return redirect()->to('/hak-fitur');
    }

    public function edit($id = null)
    {
        $hakFitur = $this->hakFiturModel->getDetail($id);
        if (!$hakFitur) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Hak Fitur',
            'validation' => \Config\Services::validation(),
            'hakFitur' => $hakFitur,
            'user_options' => $this->userModel->where('role_access !=', 'superadmin')->orderBy('name', 'ASC')->findAll(),
            'es1_options' => $this->unitKerjaEs1Model->orderBy('nama_es1', 'ASC')->findAll(),
        ];
        return view('hak_fitur/form', $data);
    }

    /**
     * Metode untuk memperbarui data Hak Fitur yang sudah ada (UPDATE).
     */
    public function update($id = null)
    {
        if (!$id) {
            return redirect()->to('/hak-fitur')->with('error', 'ID Hak Fitur tidak ditemukan.');
        }

        $postData = $this->request->getPost();

        // 1. Validasi: id_user harus unik, kecuali untuk ID yang sedang diupdate
        $validationRules = [
            'id_user' => "required|is_unique[hak_fitur.id_user,id,{$id}]",
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. Logika Penentuan Level Unit Kerja
        $dataToSave = [
            'id_user' => $postData['id_user'],
            'id_es1'  => null,
            'id_es2'  => null,
            'id_es3'  => null,
        ];

        $id_es1 = $postData['id_es1'] ?? null;
        $id_es2 = $postData['id_es2'] ?? null;
        $id_es3 = $postData['id_es3'] ?? null;

        if (!empty($id_es3)) {
            $dataToSave['id_es3'] = $id_es3;
            $dataToSave['id_es2'] = $id_es2;
            $dataToSave['id_es1'] = $id_es1;
        } elseif (!empty($id_es2)) {
            $dataToSave['id_es2'] = $id_es2;
            $dataToSave['id_es1'] = $id_es1;
        } elseif (!empty($id_es1)) {
            $dataToSave['id_es1'] = $id_es1;
        } else {
            return redirect()->back()->withInput()->with('error', 'Anda harus memilih minimal satu level unit kerja.');
        }

        // 3. Update data. Kita menggunakan update($id, $data) untuk memastikan UPDATE
        if ($this->hakFiturModel->update($id, $dataToSave) === false) {
            // Jika update gagal (bukan karena validasi is_unique)
            return redirect()->back()->withInput()->with('errors', $this->hakFiturModel->errors());
        }

        $this->session->setFlashdata('success', 'Hak Fitur berhasil diperbarui.');
        return redirect()->to('/hak-fitur');
    }

    public function delete($id = null)
    {
        $this->hakFiturModel->delete($id);
        $this->session->setFlashdata('success', 'Hak Fitur berhasil dihapus.');
        return redirect()->to('/hak-fitur');
    }
}
