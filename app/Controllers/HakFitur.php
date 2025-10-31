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

    /**
     * Metode untuk membuat data Hak Fitur baru (INSERT).
     */
    public function create()
    {
        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');
        $postData = $this->request->getPost();

        $nipPost = $postData['nip'] ?? null;
        $idEs3Post = $postData['id_es3'] ?? null;

        // Cek apakah user sudah ada di DB Lokal (berdasarkan NIP yang ditarik dari API)
        $existingUser = $nipPost ? $this->userModel->getUserByNip($nipPost) : null;

        $isExisting = (bool)$existingUser;
        $userIdToManage = $existingUser['id'] ?? null;

        // --- 1. Validasi Dinamis & Hak Akses Wajib ---

        $rules = [
            'name'          => 'required|min_length[3]',
            'status'        => 'required|in_list[aktif,non-aktif]',
            'nama_jabatan_api' => 'permit_empty|max_length[255]',
            'namaunit'      => 'permit_empty|max_length[255]',
        ];

        // Aturan Unik: Hanya berlaku jika kita akan membuat user baru (tidak ada existing NIP)
        if (!$isExisting) {
            $rules['nip'] = 'required|max_length[50]|is_unique[users.nip]';
            $rules['email'] = 'required|valid_email|is_unique[users.email]';
        } else {
            // Jika sudah ada, validasi bahwa NIP/Email tidak diubah ke nilai milik orang lain
            $rules['nip'] = 'required|max_length[50]'; // Cukup cek required
            $rules['email'] = 'required|valid_email';
        }

        // Aturan Admin Wajib: id_es3
        if ($userRole === 'admin') {
            $rules['id_es3'] = 'required|integer';
        }

        if (!$this->validate($rules)) {
            // Jika validasi form dasar gagal
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- 2. Proses Transaksi ---
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if (!$isExisting) {
                // A. JALUR CREATE (Jika user belum pernah login API)
                $dataUser = [
                    'nip'      => $nipPost,
                    'name'     => $postData['name'],
                    'email'    => $postData['email'],
                    'status'   => $postData['status'],
                    'role_jabatan' => $postData['role_jabatan'],
                    'nama_jabatan_api' => $postData['nama_jabatan_api'],
                    'namaunit' => $postData['namaunit'],
                    'role_access' => ($userRole === 'admin') ? 'user' : $postData['role_access'],
                    'password' => password_hash(random_string('alnum', 16), PASSWORD_DEFAULT) // Set random password
                ];
                $this->userModel->insert($dataUser);
                $userIdToManage = $this->userModel->getInsertID();
            } else {
                // B. JALUR ATTACH/UPDATE HAK FITUR (User sudah ada karena login API)
                // Kita tidak perlu update data users, hanya hak fitur yang diatur Admin
            }

            // --- 3. Update/Create Hak Fitur ---
            if ($userRole === 'admin' && !empty($authData['id_es2'])) {
                $hakFitur = $this->hakFiturModel->where('id_user', $userIdToManage)->first();
                $dataHakFitur = [
                    'id_user' => $userIdToManage,
                    'id_es3'  => $idEs3Post,
                    'id_es2'  => $authData['id_es2'], // ES2 Admin
                    // id_es1 dapat dicari jika diperlukan
                ];

                if ($hakFitur) {
                    // Jika user sudah punya hak fitur, update
                    $this->hakFiturModel->update($hakFitur['id'], $dataHakFitur);
                } else {
                    // Jika user belum punya hak fitur, buat
                    $this->hakFiturModel->insert($dataHakFitur);
                }
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                throw new \Exception('Gagal menyelesaikan transaksi database.');
            } else {
                $db->transCommit();
                $this->session->setFlashdata('success', 'Pengguna dan Hak Akses berhasil ditambahkan/diperbarui.');
                return redirect()->to('/users');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Users Create/Attach Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
