<?php

namespace App\Controllers;

use App\Models\JenisNaskahModel;

class JenisNaskah extends BaseController
{
    protected $jenisNaskahModel;
    protected $session;

    public function __construct()
    {
        $this->jenisNaskahModel = new JenisNaskahModel();
        $this->session = session();
    }

    // (R)EAD: Menampilkan daftar
    public function index()
    {
        $data = [
            'title'   => 'Manajemen Jenis Naskah',
            'session' => $this->session,
        ];
        return view('jenis_naskah/index', $data);
    }

    // (R)EAD: Endpoint AJAX untuk DataTables
    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->jenisNaskahModel->getDataTablesJenisNaskah($this->request);
            $data = [];
            foreach ($result['data'] as $row) {
                $form_delete = '<form action="' . site_url('jenis-naskah/delete/' . $row['id']) . '" method="post" class="d-inline form-delete">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                </form>';
                // --- LOGIKA BARU UNTUK KOLOM AKSI ---
                $btn_edit = '<a href="' . site_url('jenis-naskah/edit/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>';

                $data[] = [
                    '', // No
                    $row['kode_naskah'],
                    $row['nama_naskah'],
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
            'title'      => 'Tambah Jenis Naskah',
            'validation' => \Config\Services::validation(),
        ];
        return view('jenis_naskah/form', $data);
    }

    // (C)REATE: Memproses data dari form tambah
    public function create()
    {
        if (!$this->validate($this->jenisNaskahModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }
        $this->jenisNaskahModel->save($this->request->getPost());
        $this->session->setFlashdata('success', 'Jenis Naskah berhasil ditambahkan.');
        return redirect()->to('/jenis-naskah');
    }

    // (U)PDATE: Menampilkan form edit
    public function edit($id = null)
    {
        $jenisNaskah = $this->jenisNaskahModel->find($id);
        if (!$jenisNaskah) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $data = [
            'title'        => 'Edit Jenis Naskah',
            'validation'   => \Config\Services::validation(),
            'jenis_naskah' => $jenisNaskah,
        ];
        return view('jenis_naskah/form', $data);
    }

    // (U)PDATE: Memproses data dari form edit
    public function update($id = null)
    {
        // Siapkan data yang akan disimpan, pastikan menyertakan ID
        $dataToSave = [
            'id'          => $id,
            'kode_naskah' => $this->request->getPost('kode_naskah'),
            'nama_naskah' => $this->request->getPost('nama_naskah'),
        ];

        // Sekarang, panggil method save() dari model dengan data yang sudah lengkap.
        // Model akan secara otomatis menggunakan aturan validasi dari propertinya,
        // dan placeholder {id} akan terisi dengan benar.
        if ($this->jenisNaskahModel->save($dataToSave) === false) {
            // Jika validasi gagal, kembalikan dengan error dari model.
            return redirect()->back()->withInput()->with('errors', $this->jenisNaskahModel->errors());
        }

        // Jika berhasil
        $this->session->setFlashdata('success', 'Jenis Naskah berhasil diperbarui.');
        return redirect()->to('/jenis-naskah');
    }

    // (D)ELETE: Menghapus data
    public function delete($id = null)
    {
        try {
            $this->jenisNaskahModel->delete($id);
            $this->session->setFlashdata('success', 'Jenis Naskah berhasil dihapus.');
        } catch (\Exception $e) {
            $this->session->setFlashdata('error', 'Gagal menghapus, data mungkin terkait dengan item arsip.');
        }
        return redirect()->to('/jenis-naskah');
    }
}
