<?php

namespace App\Controllers;

use App\Models\ItemAktifModel;
use App\Models\BerkasAktifModel;

class Pemberkasan extends BaseController
{
    protected $itemAktifModel;
    protected $berkasAktifModel;
    protected $session;

    public function __construct()
    {
        $this->itemAktifModel = new ItemAktifModel();
        $this->berkasAktifModel = new BerkasAktifModel();
        $this->session = session();
    }

    // (R)EAD: Menampilkan halaman utama
    public function index()
    {
        $data = [
            'title'   => 'Proses Pemberkasan Arsip',
            'session' => $this->session
        ];
        return view('pemberkasan/index', $data);
    }

    // (C)REATE/(U)PDATE: Memproses submit form biasa
    public function process()
    {
        // Validasi dasar di backend
        $itemIds = $this->request->getPost('item_ids');
        $tipeBerkas = $this->request->getPost('tipe_berkas');

        if (empty($itemIds)) {
            $this->session->setFlashdata('error', 'Tidak ada item yang dipilih untuk diberkaskan.');
            return redirect()->to('/pemberkasan');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $berkasId = null;
            if ($tipeBerkas === 'baru') {
                $namaBerkasBaru = $this->request->getPost('nama_berkas_baru');
                if (empty($namaBerkasBaru)) {
                    throw new \Exception('Nama berkas baru tidak boleh kosong.');
                }

                $dataBerkasBaru = [
                    'nama_berkas'   => $namaBerkasBaru,
                    'id_user'       => 1, // Ganti dengan ID user yg login
                    'status_berkas' => 'aktif'
                ];
                $this->berkasAktifModel->insert($dataBerkasBaru);
                $berkasId = $this->berkasAktifModel->getInsertID();
            } elseif ($tipeBerkas === 'lama') {
                $idBerkasLama = $this->request->getPost('id_berkas_lama');
                if (empty($idBerkasLama)) {
                    throw new \Exception('Berkas lama belum dipilih.');
                }
                $berkasId = $idBerkasLama;
            }

            if (!$berkasId) {
                throw new \Exception('ID Berkas tidak valid.');
            }

            // Update semua item yang dipilih
            $this->itemAktifModel->whereIn('id', $itemIds)->set(['id_berkas' => $berkasId])->update();

            if ($db->transStatus() === false) {
                $db->transRollback();
                $this->session->setFlashdata('error', 'Gagal memproses pemberkasan karena transaksi database gagal.');
            } else {
                $db->transCommit();
                $this->session->setFlashdata('success', 'Pemberkasan berhasil! ' . count($itemIds) . ' item telah dimasukkan ke dalam berkas.');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            $this->session->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->to('/pemberkasan');
    }

    // --- AJAX ENDPOINTS (tidak berubah) ---
   public function ajaxListItems()
{
    if ($this->request->isAJAX()) {
        // Pastikan nama method yang dipanggil sudah benar
        $result = $this->itemAktifModel->getDataTablesForPemberkasan($this->request);
        
        // Tambahkan penanganan error jika query di model gagal
        if (isset($result['error'])) {
            return $this->response->setJSON($result)->setStatusCode(500);
        }

        $data = [];
        foreach ($result['data'] as $item) {
            $row = [];
            $row[] = '<input type="checkbox" class="form-check-input item-checkbox" name="item_ids[]" value="' . $item['id'] . '">';
            $row[] = $item['no_dokumen'];
            $row[] = $item['judul_dokumen'];
            $row[] = $item['tgl_dokumen'];
            $row[] = $item['tahun_cipta'];
            $data[] = $row;
        }
        return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data,]);
    }
}
}
