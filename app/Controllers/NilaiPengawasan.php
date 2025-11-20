<?php

namespace App\Controllers;

use App\Models\NilaiPengawasanModel;
use App\Models\UnitKerjaEs2Model;
use App\Controllers\BaseController;

class NilaiPengawasan extends BaseController
{
    protected $nilaiPengawasanModel;
    protected $unitKerjaEs2Model;

    public function __construct()
    {
        $this->nilaiPengawasanModel = new NilaiPengawasanModel();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        helper(['form']);

        // Authorization check in constructor to protect the entire module
        if (!has_permission('access_nilai_pengawasan')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak memiliki hak akses ke modul ini.');
        }
    }

    public function index()
    {
        $data = ['title' => 'Manajemen Nilai Pengawasan', 'session' => session()];
        return view('nilai_pengawasan/index', $data);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->nilaiPengawasanModel->getDataTablesList($this->request);
            $data = [];
            $no = $this->request->getPost('start') ?? 0;

            foreach ($result['data'] as $np) {
                $no++;
                $badgeClass = match (substr($np['kategori'], 0, 1)) {
                    'A' => 'bg-success',
                    'B' => 'bg-info',
                    'C' => 'bg-warning text-dark',
                    default => 'bg-secondary',
                };

                $kategoriDisplay = '<span class="badge ' . $badgeClass . '">' . esc($np['kategori']) . '</span>';

                $btn_edit = '<a href="' . site_url('nilai-pengawasan/edit/' . $np['id']) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="fas fa-edit"></i></a>';
                $form_delete = '<form action="' . site_url('nilai-pengawasan/' . $np['id']) . '" method="post" class="d-inline form-delete">
                            ' . csrf_field() . '
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>';

                $data[] = [
                    $no, // Numbering
                    esc($np['nama_es2']),
                    esc($np['tahun']),
                    number_format($np['skor'], 2),
                    $kategoriDisplay,
                    esc($np['user_name']),
                    $btn_edit . ' ' . $form_delete
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($result['draw']),
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $data,
            ]);
        }

        // Jika bukan AJAX request
        return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
    }

    public function new()
    {
        $data = [
            'title' => 'Tambah Nilai Pengawasan',
            'validation' => \Config\Services::validation(),
            'es2_options' => $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll(),
        ];
        return view('nilai_pengawasan/form', $data);
    }

    public function create()
    {
        $postData = $this->request->getPost();

        // 1. Validate standard rules
        if (!$this->validate($this->nilaiPengawasanModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. Validate unique combination rule
        if (!$this->nilaiPengawasanModel->validateUniqueEs2Tahun($postData['id_es2'], $postData['tahun'])) {
            session()->setFlashdata('error', "Nilai untuk unit kerja ini pada tahun tersebut sudah ada.");
            return redirect()->back()->withInput();
        }

        // 3. Insert data (callbacks will handle category and user_id)
        if ($this->nilaiPengawasanModel->insert($postData) === false) {
            return redirect()->back()->withInput()->with('errors', $this->nilaiPengawasanModel->errors());
        }

        session()->setFlashdata('success', 'Nilai Pengawasan berhasil ditambahkan.');
        return redirect()->to('nilai-pengawasan');
    }

    public function edit($id = null)
    {
        $np = $this->nilaiPengawasanModel->find($id);
        if (!$np) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $data = [
            'title' => 'Edit Nilai Pengawasan',
            'validation' => \Config\Services::validation(),
            'np' => $np,
            'es2_options' => $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll(),
        ];
        return view('nilai_pengawasan/form', $data);
    }

    public function update($id = null)
    {
        $postData = $this->request->getPost();

        // 1. Validate standard rules
        if (!$this->validate($this->nilaiPengawasanModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. Validate unique combination rule (excluding current ID)
        if (!$this->nilaiPengawasanModel->validateUniqueEs2Tahun($postData['id_es2'], $postData['tahun'], $id)) {
            session()->setFlashdata('error', "Kombinasi Unit Kerja dan Tahun ini sudah digunakan oleh data lain.");
            return redirect()->back()->withInput();
        }

        // 3. Update data
        if ($this->nilaiPengawasanModel->update($id, $postData) === false) {
            return redirect()->back()->withInput()->with('errors', $this->nilaiPengawasanModel->errors());
        }

        session()->setFlashdata('success', 'Nilai Pengawasan berhasil diperbarui.');
        return redirect()->to('nilai-pengawasan');
    }

    public function delete($id = null)
    {
        if ($this->nilaiPengawasanModel->delete($id)) {
            session()->setFlashdata('success', 'Nilai Pengawasan berhasil dihapus.');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus data.');
        }
        return redirect()->to('nilai-pengawasan');
    }
}
