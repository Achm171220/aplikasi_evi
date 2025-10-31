<?php

namespace App\Controllers;

use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;

class UnitKerjaEs3 extends BaseController
{
    protected $unitKerjaEs1Model;
    protected $unitKerjaEs2Model;
    protected $unitKerjaEs3Model;
    protected $session;

    public function __construct()
    {
        $this->unitKerjaEs1Model = new UnitKerjaEs1Model();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model();
        $this->session = session();
    }

    // (R)EAD: Menampilkan daftar
    public function index()
    {
        $data = [
            'title'   => 'Manajemen Unit Kerja Eselon 3',
            'session' => $this->session,
        ];
        return view('unit_kerja_es3/index', $data);
    }

    // (R)EAD: Endpoint AJAX untuk DataTables
    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->unitKerjaEs3Model->getDataTablesEs3($this->request);
            $data = [];
            foreach ($result['data'] as $row) {
                $form_delete = '<form action="' . site_url('unit-kerja-es3/delete/' . $row['id']) . '" method="post" class="d-inline form-delete">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                </form>';
                $btn_edit = '<a href="' . site_url('unit-kerja-es3/edit/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>';

                $data[] = [
                    '', // No
                    $row['kode'],
                    $row['nama_es3'],
                    $row['nama_es2'] ?? '<span class="text-danger">Induk Hilang</span>',
                    $row['nama_es1'] ?? '<span class="text-danger">Induk Hilang</span>',
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
    public function checkKode()
    {
        if ($this->request->isAJAX()) {
            $kode = $this->request->getPost('kode');
            $id = $this->request->getPost('id'); // ID saat ini (untuk mode edit)

            $builder = $this->unitKerjaEs3Model->where('kode', $kode);

            // Jika dalam mode edit, abaikan data saat ini
            if (!empty($id)) {
                $builder->where('id !=', $id);
            }

            $isExist = $builder->countAllResults() > 0;

            return $this->response->setJSON(['is_exist' => $isExist]);
        }
    }
    private function prepareFormData($es3 = null)
    {
        $data = [
            'unitKerjaEs3' => $es3,
            'validation'   => \Config\Services::validation(),
        ];

        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');

        if ($userRole === 'superadmin') {
            $data['es1_options'] = $this->unitKerjaEs1Model->orderBy('nama_es1', 'ASC')->findAll();
            if ($es3) {
                $es2 = $this->unitKerjaEs2Model->find($es3['id_es2']);
                if ($es2) {
                    $es3['id_es1'] = $es2['id_es1'];
                }
                $data['unitKerjaEs3'] = $es3;
            }
        } elseif ($userRole === 'admin' && $authData && !empty($authData['id_es2'])) {
            $es2 = $this->unitKerjaEs2Model->find($authData['id_es2']);
            if ($es2) {
                $data['es1_options'] = $this->unitKerjaEs1Model->where('id', $es2['id_es1'])->findAll();
                $data['es2_prefill'] = $es2; // Kirim seluruh data es2

                // Tambahkan prefill id_es1 & id_es2 ke variabel utama untuk konsistensi
                if ($es3) {
                    $es3['id_es1'] = $es2['id_es1'];
                    $es3['id_es2'] = $es2['id'];
                    $data['unitKerjaEs3'] = $es3;
                } else {
                    $data['es2_prefill_admin'] = $es2;
                }
            }
        }

        return $data;
    }
    // (C)REATE: Menampilkan form tambah
    public function new()
    {
        $data = $this->prepareFormData();
        $data['title'] = 'Tambah Unit Kerja Eselon 3';
        return view('unit_kerja_es3/form', $data);
    }

    // (C)REATE: Memproses data dari form tambah
    // app/Controllers/UnitKerjaEs3.php

    public function create()
    {
        // Ambil semua data dari POST
        $postData = $this->request->getPost();

        // Validasi manual tambahan untuk field yang krusial
        if (empty($postData['id_es2'])) {
            // Jika id_es2 kosong, kemungkinan besar karena Eselon 1 tidak dipilih
            // Kembalikan dengan pesan error yang jelas
            return redirect()->back()->withInput()->with('error', 'Induk Unit Eselon 2 wajib dipilih.');
        }

        // Gunakan aturan validasi dari model
        if (!$this->validate($this->unitKerjaEs3Model->getValidationRules())) {
            // Jika validasi dari model gagal, kembalikan
            return redirect()->back()->withInput();
        }

        // Jika semua validasi lolos, simpan data
        try {
            $this->unitKerjaEs3Model->save($postData);
            $this->session->setFlashdata('success', 'Data Eselon 3 berhasil ditambahkan.');
            return redirect()->to('/unit-kerja-es3');
        } catch (\Exception $e) {
            // Tangkap jika ada error dari database
            log_message('error', '[UnitKerjaEs3Controller] Error saat create: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data ke database.');
        }
    }

    // (U)PDATE: Menampilkan form edit
    public function edit($id = null)
    {
        $es3 = $this->unitKerjaEs3Model->find($id);
        if (!$es3) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->prepareFormData($es3);
        $data['title'] = 'Edit Unit Kerja Eselon 3';
        return view('unit_kerja_es3/form', $data);
    }
    // (U)PDATE: Memproses data dari form edit
    public function update($id = null)
    {
        $rules = $this->unitKerjaEs3Model->getValidationRules();
        // Update aturan validasi 'kode' untuk mengabaikan dirinya sendiri
        $rules['kode'] = "required|is_unique[unit_kerja_es3.kode,id,$id]";

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $this->unitKerjaEs3Model->save($this->request->getPost());
        $this->session->setFlashdata('success', 'Data Eselon 3 berhasil diperbarui.');
        return redirect()->to('/unit-kerja-es3');
    }

    // (D)ELETE: Menghapus data
    public function delete($id = null)
    {
        $this->unitKerjaEs3Model->delete($id);
        $this->session->setFlashdata('success', 'Data Eselon 3 berhasil dihapus.');
        return redirect()->to('/unit-kerja-es3');
    }

    // Endpoint AJAX untuk chained dropdown (ini tidak berubah)
    public function getEs2ByEs1($id_es1)
    {
        if ($this->request->isAJAX()) {
            // --- PERBAIKAN: Pilih 'id' dan 'nama_es2' ---
            $data = $this->unitKerjaEs2Model->select('id, nama_es2, kode')->where('id_es1', $id_es1)->orderBy('nama_es2', 'ASC')->findAll();
            return $this->response->setJSON($data);
        }
    }

    public function getEs3ByEs2($id_es2)
    {
        if ($this->request->isAJAX()) {
            // --- PERBAIKAN: Pilih 'id' dan 'nama_es3' ---
            $data = $this->unitKerjaEs3Model->select('id, nama_es3, kode')->where('id_es2', $id_es2)->orderBy('nama_es3', 'ASC')->findAll();
            return $this->response->setJSON($data);
        }
    }
}
