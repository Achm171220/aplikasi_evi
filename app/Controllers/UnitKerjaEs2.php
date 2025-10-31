<?php

namespace App\Controllers;

use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;

class UnitKerjaEs2 extends BaseController
{
    protected $unitKerjaEs1Model;
    protected $unitKerjaEs2Model;
    protected $session;

    public function __construct()
    {
        $this->unitKerjaEs1Model = new UnitKerjaEs1Model();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->session = session();
    }

    // (R)EAD: Menampilkan daftar
    public function index()
    {
        $data = [
            'title'   => 'Manajemen Unit Kerja Eselon 2',
            'session' => $this->session,
        ];
        return view('unit_kerja_es2/index', $data);
    }

    // (R)EAD: Endpoint AJAX untuk DataTables
    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->unitKerjaEs2Model->getDataTablesEs2($this->request);
            $data = [];
            foreach ($result['data'] as $row) {
                $form_delete = '<form action="' . site_url('unit-kerja-es2/' . $row['id']) . '" method="post" class="d-inline form-delete">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                </form>';
                // PERBAIKAN URL EDIT
                $btn_edit = '<a href="' . site_url('unit-kerja-es2/edit/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>';

                $data[] = [
                    '', // No
                    $row['kode'],
                    $row['nama_es2'],
                    $row['nama_induk'] ?? '<span class="text-danger">Induk Hilang</span>',
                    $btn_edit . ' ' . $form_delete,
                ];
            }
            return $this->response->setJSON([
                'draw' => $result['draw'],
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $data,
            ]);
        }
    }

    // (C)REATE: Menampilkan form tambah
    public function new()
    {
        $data = [
            'title'       => 'Tambah Unit Kerja Eselon 2',
            'es1_options' => $this->unitKerjaEs1Model->orderBy('nama_es1', 'ASC')->findAll(),
            'validation'  => \Config\Services::validation(),
        ];
        return view('unit_kerja_es2/form', $data);
    }

    // (C)REATE: Memproses data dari form tambah
    public function create()
    {
        if (!$this->validate($this->unitKerjaEs2Model->getValidationRules())) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $this->unitKerjaEs2Model->save($this->request->getPost());
        $this->session->setFlashdata('success', 'Data Eselon 2 berhasil ditambahkan.');
        return redirect()->to('/unit-kerja-es2');
    }

    // (U)PDATE: Menampilkan form edit
    public function edit($id = null)
    {
        $es2 = $this->unitKerjaEs2Model->find($id);
        if (!$es2) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'       => 'Edit Unit Kerja Eselon 2',
            'validation'  => \Config\Services::validation(),
            'es2'         => $es2,
            'es1_options' => $this->unitKerjaEs1Model->orderBy('nama_es1', 'ASC')->findAll(),
        ];
        return view('unit_kerja_es2/form', $data);
    }

    // (U)PDATE: Memproses data dari form edit
    public function update($id = null)
    {
        $rules = $this->unitKerjaEs2Model->getValidationRules();
        $rules['kode'] = "required|is_unique[unit_kerja_es2.kode,id,$id]";

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $this->unitKerjaEs2Model->save($this->request->getPost());
        $this->session->setFlashdata('success', 'Data Eselon 2 berhasil diperbarui.');
        return redirect()->to('/unit-kerja-es2');
    }

    // (D)ELETE: Menghapus data
    public function delete($id = null)
    {
        // Tambahkan try-catch untuk menangani foreign key constraint
        try {
            $this->unitKerjaEs2Model->delete($id);
            $this->session->setFlashdata('success', 'Data Eselon 2 berhasil dihapus.');
        } catch (\Exception $e) {
            $this->session->setFlashdata('error', 'Gagal menghapus, data mungkin terkait dengan unit Eselon 3.');
        }
        return redirect()->to('/unit-kerja-es2');
    }
}
