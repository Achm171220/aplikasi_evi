<?php

namespace App\Controllers;

use App\Models\KlasifikasiModel;

class Klasifikasi extends BaseController
{
    protected $klasifikasiModel;
    protected $session;

    public function __construct()
    {
        $this->klasifikasiModel = new KlasifikasiModel();
        $this->session = session();
    }

    // (R)EAD: Menampilkan daftar
    public function index()
    {
        $data = [
            'title'   => 'Manajemen Kode Klasifikasi',
            'session' => $this->session,
        ];
        return view('klasifikasi/index', $data);
    }

    // (R)EAD: Endpoint AJAX untuk DataTables
    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->klasifikasiModel->getDataTablesKlasifikasi($this->request);
            $data = [];
            foreach ($result['data'] as $row) {
                $form_delete = '<form action="' . site_url('klasifikasi/' . $row['id']) . '" method="post" class="d-inline form-delete">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                </form>';
                $btn_edit = '<a href="' . site_url('klasifikasi/edit/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>';

                $data[] = [
                    '',
                    $row['kode'],
                    $row['nama_klasifikasi'],
                    $row['umur_aktif'] . ' tahun',
                    $row['umur_inaktif'] . ' tahun',
                    ucfirst($row['nasib_akhir']),
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
            'title'      => 'Tambah Kode Klasifikasi',
            'validation' => \Config\Services::validation(),
        ];
        return view('klasifikasi/form', $data);
    }

    // (C)REATE: Memproses data dari form tambah
    public function create()
    {
        if (!$this->validate($this->klasifikasiModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }
        $this->klasifikasiModel->save($this->request->getPost());
        $this->session->setFlashdata('success', 'Data klasifikasi berhasil ditambahkan.');
        return redirect()->to('/klasifikasi');
    }

    // (U)PDATE: Menampilkan form edit
    public function edit($id = null)
    {
        $klasifikasi = $this->klasifikasiModel->find($id);
        if (!$klasifikasi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $data = [
            'title'       => 'Edit Kode Klasifikasi',
            'validation'  => \Config\Services::validation(),
            'klasifikasi' => $klasifikasi,
        ];
        return view('klasifikasi/form', $data);
    }

    // (U)PDATE: Memproses data dari form edit
    public function update($id = null)
    {
        // Cek apakah data klasifikasi yang akan diedit ada
        $klasifikasiToUpdate = $this->klasifikasiModel->find($id);
        if (!$klasifikasiToUpdate) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Siapkan data lengkap dari POST, termasuk ID
        $dataToSave = $this->request->getPost();
        $dataToSave['id'] = $id; // Pastikan ID disertakan untuk validasi Model

        // Jalankan validasi dan simpan melalui Model
        if ($this->klasifikasiModel->save($dataToSave) === false) {
            session()->setFlashdata('validation_alert_html', implode('<br>', $this->klasifikasiModel->errors()));
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('success', 'Data klasifikasi berhasil diperbarui.');
        return redirect()->to('/klasifikasi');
    }

    // (D)ELETE: Menghapus data
    public function delete($id = null)
    {
        try {
            $this->klasifikasiModel->delete($id);
            $this->session->setFlashdata('success', 'Data klasifikasi berhasil dihapus.');
        } catch (\Exception $e) {
            $this->session->setFlashdata('error', 'Gagal menghapus, data mungkin terkait dengan item arsip.');
        }
        return redirect()->to('/klasifikasi');
    }
}
