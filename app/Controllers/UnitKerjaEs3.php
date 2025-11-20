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
            'unitKerjaEs3' => $es3, // PENTING: Pertahankan data ES3 asli
            'validation'   => \Config\Services::validation(),
        ];

        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');

        if ($userRole === 'superadmin') {
            $data['es1_options'] = $this->unitKerjaEs1Model->orderBy('nama_es1', 'ASC')->findAll();
            if ($es3) {
                // Saat edit, cari ID ES1 induk
                $es2 = $this->unitKerjaEs2Model->find($es3['id_es2']);
                if ($es2) {
                    // Simpan ID ES1 induk ke array ES3 untuk prefill view
                    $es3['id_es1'] = $es2['id_es1'];
                    $data['unitKerjaEs3'] = $es3;
                }
            }
        } elseif ($userRole === 'admin' && $authData && !empty($authData['id_es2'])) {
            $es2 = $this->unitKerjaEs2Model->find($authData['id_es2']);
            if ($es2) {
                // Admin hanya bisa melihat ES1 dan ES2 miliknya
                $data['es1_options'] = $this->unitKerjaEs1Model->where('id', $es2['id_es1'])->findAll();
                $data['es2_prefill'] = $es2; // Data ES2 Admin lengkap

                // Jika mode EDIT, pastikan ES3 yang diedit juga mendapatkan ID ES1 dan ES2 yang benar (milik Admin)
                if ($es3) {
                    $es3['id_es1'] = $es2['id_es1'];
                    $es3['id_es2'] = $es2['id'];
                    $data['unitKerjaEs3'] = $es3; // Data ES3 di-update dengan ID Induk
                }
            }
        }
        // Pastikan $isSuperAdmin dikirim ke view untuk logika JS/HTML
        $data['isSuperAdmin'] = $userRole === 'superadmin';

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

        // OTORISASI: Jika Admin, pastikan ES3 ini di bawah ES2-nya
        $userRole = session()->get('role_access');
        $id_es2_admin = session()->get('auth_data')['id_es2'] ?? null;

        if ($userRole === 'admin') {
            if ($es3['id_es2'] !== $id_es2_admin) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak diizinkan mengedit unit kerja di luar yurisdiksi Anda.');
            }
        }
        // END OTORISASI

        $data = $this->prepareFormData($es3);
        $data['title'] = 'Edit Unit Kerja Eselon 3';
        return view('unit_kerja_es3/form', $data);
    }

    // (U)PDATE: Memproses data dari form edit
    public function update($id = null)
    {
        // 1. Otorisasi dan Cek Keberadaan
        $es3ToUpdate = $this->unitKerjaEs3Model->find($id);
        if (!$es3ToUpdate) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $postData = $this->request->getPost();

        // Cek apakah Admin mencoba mengubah unit di luar yurisdiksinya
        $userRole = session()->get('role_access');
        $id_es2_admin = session()->get('auth_data')['id_es2'] ?? null;

        if ($userRole === 'admin') {
            // Admin tidak boleh mengubah id_es2 (harus sesuai dengan ES2 yang terikat)
            if ($es3ToUpdate['id_es2'] !== $id_es2_admin) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak diizinkan mengupdate unit kerja ini.');
            }
        }


        // 2. Kumpulkan data untuk validasi
        $id_es2_final = $userRole === 'admin' ? $id_es2_admin : $postData['id_es2'];

        $dataToValidate = [
            'id'           => $id, // PENTING untuk validasi is_unique
            'id_es2'       => $id_es2_final,
            'nama_es3'     => $postData['nama_es3'],
            'kode'         => $postData['kode'], // Kode lengkap sudah di hidden field
        ];

        // 3. Update aturan validasi 'kode' untuk mengabaikan dirinya sendiri
        $rules = $this->unitKerjaEs3Model->getValidationRules();
        $rules['kode'] = "required|is_unique[unit_kerja_es3.kode,id,$id]";
        $rules['id_es2'] = "required|integer"; // Pastikan id_es2 wajib ada

        if (!$this->validate($rules, $dataToValidate)) {
            // Jika validasi gagal, kembalikan dengan input lama
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // 4. Proses Update
        $dataToSave = [
            'id_es2'       => $id_es2_final,
            'nama_es3'     => $postData['nama_es3'],
            'kode'         => $postData['kode'],
            'id_es2'       => $id_es2_final, // Simpan ID ES2 final
        ];

        // KUNCI PERBAIKAN: Gunakan update($id, $data) secara eksplisit
        if ($this->unitKerjaEs3Model->update($id, $dataToSave) === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data ke database.');
        }

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
