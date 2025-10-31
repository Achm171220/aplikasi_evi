<?php

namespace App\Controllers;

use App\Models\UnitKerjaEs1Model;

class UnitKerjaEs1 extends BaseController
{
    protected $unitKerjaEs1Model;

    public function __construct()
    {
        $this->unitKerjaEs1Model = new UnitKerjaEs1Model();
    }

    public function index()
    {
        return view('unit_kerja_es1/index', ['title' => 'Manajemen Unit Kerja Eselon 1']);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->unitKerjaEs1Model->getDataTablesEs1($this->request);
            $data = [];
            foreach ($result['data'] as $row) {
                $btn_edit = '<a href="' . site_url('unit-kerja-es1/edit/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>';
                $btn_delete = '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row['id'] . '" data-name="' . esc($row['nama_es1']) . '"><i class="fas fa-trash"></i> Hapus</button>';

                $data[] = [
                    '', // No
                    $row['kode'],
                    $row['nama_es1'],
                    $btn_edit . ' ' . $btn_delete,
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

    public function edit($id = null)
    {
        $unitKerjaEs1 = $this->unitKerjaEs1Model->find($id);
        if (!$unitKerjaEs1) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'        => 'Edit Unit Kerja Eselon 1',
            'validation'   => \Config\Services::validation(),
            'unitKerjaEs1' => $unitKerjaEs1 // Gunakan nama variabel yang konsisten
        ];

        return view('unit_kerja_es1/form', $data);
    }

    public function save()
    {
        if ($this->request->isAJAX()) {
            if ($this->unitKerjaEs1Model->save($this->request->getPost()) === false) {
                return $this->response->setJSON(['status' => 'error', 'errors' => $this->unitKerjaEs1Model->errors()])->setStatusCode(400);
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data Eselon 1 berhasil disimpan.']);
        }
    }

    public function delete($id)
    {
        if ($this->request->isAJAX()) {
            try {
                $this->unitKerjaEs1Model->delete($id);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Data Eselon 1 berhasil dihapus.']);
            } catch (\Exception $e) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus, data mungkin terkait dengan unit Eselon 2.'])->setStatusCode(500);
            }
        }
    }
}
