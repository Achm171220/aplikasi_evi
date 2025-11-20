<?php

namespace App\Controllers;

use App\Models\TemaModel;
use App\Controllers\BaseController;

class Tema extends BaseController
{
    protected $temaModel;

    public function __construct()
    {
        $this->temaModel = new TemaModel();
        helper(['form', 'auth']); // Pastikan helper auth di-load
    }

    public function index()
    {
        return view('tema/index', ['title' => 'Manajemen Tema Arsip']);
    }

    public function listData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $result = $this->temaModel->getDataTablesList($this->request);
        $data = [];

        foreach ($result['data'] as $tema) {
            $btn_edit = '<button type="button" class="btn btn-sm btn-warning me-1 btn-edit" data-id="' . $tema['id'] . '" title="Edit"><i class="fas fa-edit"></i></button>';
            $form_delete = '<form action="' . site_url('tema/' . $tema['id']) . '" method="post" class="d-inline form-delete">
                                ' . csrf_field() . '
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>';
            $data[] = [
                '',
                esc($tema['nama_tema']),
                esc($tema['deskripsi']),
                '<div class="d-flex justify-content-center">' . $btn_edit . $form_delete . '</div>'
            ];
        }

        return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
    }

    public function new()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $data['form_action'] = site_url('tema');
        $data['title'] = 'Tambah Tema Baru';

        return $this->response->setJSON(['success' => true, 'html' => view('tema/_form_modal', $data)]);
    }

    public function create()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        if ($this->temaModel->save($this->request->getPost()) === false) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'errors' => $this->temaModel->errors()]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Tema baru berhasil ditambahkan.']);
    }

    public function edit($id = null)
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $tema = $this->temaModel->find($id);
        if (!$tema) return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);

        $data['tema'] = $tema;
        $data['form_action'] = site_url('tema/' . $id);
        $data['title'] = 'Edit Tema';

        return $this->response->setJSON(['success' => true, 'html' => view('tema/_form_modal', $data)]);
    }

    public function update($id = null)
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $data = $this->request->getPost();
        $data['id'] = $id;

        if ($this->temaModel->save($data) === false) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'errors' => $this->temaModel->errors()]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Tema berhasil diperbarui.']);
    }

    public function delete($id = null)
    {
        if ($this->temaModel->delete($id)) {
            session()->setFlashdata('success', 'Tema berhasil dihapus.');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus data.');
        }
        return redirect()->to('tema');
    }
}
