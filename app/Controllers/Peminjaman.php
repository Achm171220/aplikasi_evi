<?php

namespace App\Controllers;

// Pastikan semua model yang dibutuhkan di-use
use App\Models\PeminjamanModel;
use App\Models\ItemAktifModel;
use App\Models\BerkasAktifModel;
use App\Models\UserModel; // Untuk data peminjam
use CodeIgniter\Database\Exceptions\DatabaseException;

class Peminjaman extends BaseController
{
    protected $peminjamanModel;
    protected $itemAktifModel;
    protected $berkasAktifModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->peminjamanModel = new PeminjamanModel();
        $this->itemAktifModel = new ItemAktifModel();
        $this->berkasAktifModel = new BerkasAktifModel();
        $this->userModel = new UserModel();
        $this->session = session();
    }

    public function index()
    {
        $data = [
            'title'       => 'Peminjaman Arsip',
            'session'     => $this->session,
            'validation'  => \Config\Services::validation(),
            'currentDate' => date('Y-m-d'), // Tanggal default form
        ];
        return view('peminjaman/index', $data);
    }

    /**
     * AJAX: Menyediakan data untuk tabel Item Aktif yang bisa dipinjam.
     */
    public function listItems()
    {
        if ($this->request->isAJAX()) {
            $this->itemAktifModel->where('pinjam', 0)->where('status_arsip', 'aktif');
            $result = $this->itemAktifModel->getDataTablesList($this->request);

            if (isset($result['error'])) {
                return $this->response->setJSON($result)->setStatusCode(500);
            }

            $data = [];
            foreach ($result['data'] as $item) {
                // --- KEMBALIKAN CHECKBOX ---
                $checkbox = '<input type="checkbox" class="form-check-input select-item-checkbox" name="selected_items_ids[]" value="' . $item['id'] . '" data-type="item" data-no="' . esc($item['no_dokumen']) . '" data-judul="' . esc($item['judul_dokumen']) . '">';

                $row = [
                    $checkbox, // Checkbox di kolom pertama
                    esc($item['no_dokumen'] ?? '-'),
                    esc($item['judul_dokumen']),
                    esc($item['kode_klasifikasi'] ?? '-'),
                    esc($item['tahun_cipta']),
                ];
                $data[] = $row;
            }
            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
        }
    }

    /**
     * AJAX: Menyediakan data untuk tabel Berkas Aktif yang bisa dipinjam.
     */
    public function listBerkas()
    {
        if ($this->request->isAJAX()) {
            $this->berkasAktifModel->where('pinjam', 0)->where('status_tutup', 'terbuka');
            $result = $this->berkasAktifModel->getDataTablesList($this->request);

            if (isset($result['error'])) {
                return $this->response->setJSON($result)->setStatusCode(500);
            }

            $data = [];
            foreach ($result['data'] as $berkas) {
                // --- KEMBALIKAN CHECKBOX ---
                $checkbox = '<input type="checkbox" class="form-check-input select-berkas-checkbox" name="selected_berkas_ids[]" value="' . $berkas['id'] . '" data-type="berkas" data-no="' . esc($berkas['no_berkas']) . '" data-judul="' . esc($berkas['nama_berkas']) . '">';

                $row = [
                    $checkbox, // Checkbox di kolom pertama
                    esc($berkas['no_berkas'] ?? '-'),
                    esc($berkas['nama_berkas']),
                    '<span class="badge bg-primary">' . $berkas['jumlah_item'] . ' Item</span>',
                ];
                $data[] = $row;
            }
            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
        }
    }

    /**
     * Memproses submit form peminjaman (bisa banyak item/berkas).
     */
    public function prosesPinjam()
    {
        $postData = $this->request->getPost();

        $rules = [
            'peminjam_nama'       => 'required|min_length[3]',
            'peminjam_unit'       => 'required|min_length[3]',
            'tgl_pinjam'          => 'required|valid_date',
            // --- PERBAIKAN DI SINI ---
            'tgl_kembali_rencana' => 'required|valid_date',
            // --- AKHIR PERBAIKAN ---
            'pinjam_id'           => 'required|integer',
            'pinjam_type'         => 'required|in_list[item,berkas]',
        ];
        $messages = [
            'tgl_kembali_rencana' => [
                'after_on' => 'Tanggal kembali rencana harus setelah atau sama dengan tanggal pinjam.', // Pesan validasi disesuaikan
            ],
            'pinjam_id' => [
                'required' => 'Item atau Berkas wajib dipilih dari tabel di bawah.'
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->to('/peminjaman')->withInput();
        }

        // --- VALIDASI BARU: PILIH SALAH SATU JENIS ARSIP ---
        $selectedItemsIds = $postData['selected_items_ids'] ?? [];
        $selectedBerkasIds = $postData['selected_berkas_ids'] ?? [];

        if (empty($selectedItemsIds) && empty($selectedBerkasIds)) {
            session()->setFlashdata('error', 'Pilih minimal satu item atau satu berkas untuk dipinjam.');
            return redirect()->to('/peminjaman')->withInput();
        }
        if (!empty($selectedItemsIds) && !empty($selectedBerkasIds)) {
            session()->setFlashdata('error', 'Tidak bisa meminjam item dan berkas sekaligus dalam satu proses.');
            return redirect()->to('/peminjaman')->withInput();
        }

        // Lanjutkan validasi form utama
        if (!$this->validate($rules, $messages)) {
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->to('/peminjaman')->withInput();
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $peminjamanData = [
                'peminjam_nama'       => $postData['peminjam_nama'],
                'peminjam_unit'       => $postData['peminjam_unit'],
                'tgl_pinjam'          => $postData['tgl_pinjam'],
                'tgl_kembali_rencana' => $postData['tgl_kembali_rencana'],
                'keterangan'          => $postData['keterangan'] ?? null,
                'created_by'          => session()->get('user_id'),
            ];

            $totalDipinjam = 0;

            if (!empty($selectedItemsIds)) {
                foreach ($selectedItemsIds as $itemId) {
                    $item = $this->itemAktifModel->find($itemId);
                    if (!$item || $item['pinjam'] == 1) {
                        throw new \Exception('Item ' . esc($item['judul_dokumen']) . ' tidak tersedia atau sedang dipinjam.');
                    }
                    $this->peminjamanModel->insert(array_merge($peminjamanData, ['id_item_aktif' => $itemId]));
                    $this->itemAktifModel->update($itemId, ['pinjam' => 1]);
                    $totalDipinjam++;
                }
            } elseif (!empty($selectedBerkasIds)) {
                foreach ($selectedBerkasIds as $berkasId) {
                    $berkas = $this->berkasAktifModel->find($berkasId);
                    if (!$berkas || $berkas['pinjam'] == 1 || $berkas['status_tutup'] === 'tertutup') {
                        throw new \Exception('Berkas ' . esc($berkas['nama_berkas']) . ' tidak tersedia, sedang dipinjam, atau tertutup.');
                    }
                    $this->peminjamanModel->insert(array_merge($peminjamanData, ['id_berkas_aktif' => $berkasId]));
                    $this->berkasAktifModel->update($berkasId, ['pinjam' => 1]);
                    $totalDipinjam++;
                }
            }

            if ($totalDipinjam === 0) {
                throw new \Exception('Tidak ada item/berkas valid yang dipilih untuk dipinjam.');
            }

            $db->transCommit();
            session()->setFlashdata('success', "$totalDipinjam item/berkas berhasil dipinjam.");
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error prosesPinjam: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal memproses peminjaman: ' . $e->getMessage());
        }

        return redirect()->to('/peminjaman')->withInput();
    }
    public function monitoringIndex()
    {
        $data = [
            'title'   => 'Monitoring Peminjaman Arsip',
            'session' => $this->session,
        ];
        return view('peminjaman/monitoring_index', $data);
    }

    /**
     * AJAX: Menyediakan data untuk tabel Monitoring Peminjaman.
     */
    public function monitoringListData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->peminjamanModel->getDataTablesList($this->request); // Method ini sudah ada di PeminjamanModel

            $data = [];
            foreach ($result['data'] as $row) {
                // Tentukan nama item/berkas yang dipinjam
                $namaArsip = '';
                if (!empty($row['judul_dokumen'])) { // Item
                    $namaArsip = 'Item: ' . esc($row['judul_dokumen']);
                } elseif (!empty($row['nama_berkas'])) { // Berkas
                    $namaArsip = 'Berkas: ' . esc($row['nama_berkas']);
                } else {
                    $namaArsip = '<span class="text-muted fst-italic">Arsip Dihapus</span>';
                }

                // Status peminjaman dengan badge
                $statusBadgeClass = '';
                if ($row['status'] === 'dipinjam') {
                    $statusBadgeClass = 'bg-warning text-dark';
                } elseif ($row['status'] === 'dikembalikan') {
                    $statusBadgeClass = 'bg-success';
                } elseif ($row['status'] === 'terlambat') {
                    $statusBadgeClass = 'bg-danger';
                }
                $statusDisplay = '<span class="badge ' . $statusBadgeClass . '">' . ucfirst($row['status']) . '</span>';

                // Tombol Aksi
                $btn_detail = '<a href="' . site_url('peminjaman/monitoring/detail/' . $row['id']) . '" class="btn btn-sm btn-info me-1" data-bs-toggle="tooltip" title="Lihat Detail"><i class="bi bi-eye-fill"></i></a>';

                $btn_hapus = '<form action="' . site_url('peminjaman/monitoring/delete/' . $row['id']) . '" method="post" class="d-inline form-delete">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                            </form>';

                $aksi = '<div class="d-flex justify-content-center gap-1">' . $btn_detail . $btn_hapus . '</div>';

                $data[] = [
                    '', // No. urut
                    date('d M Y', strtotime($row['tgl_pinjam'])),
                    esc($row['peminjam_nama']),
                    esc($row['peminjam_unit']),
                    $namaArsip,
                    date('d M Y', strtotime($row['tgl_kembali_rencana'])),
                    ($row['tgl_kembali_aktual']) ? date('d M Y', strtotime($row['tgl_kembali_aktual'])) : '<span class="text-muted fst-italic">Belum</span>',
                    $statusDisplay,
                    $aksi
                ];
            }
            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
        }
    }

    // --- FUNGSI BARU: Halaman Detail Peminjaman & Proses Pengembalian ---
    public function monitoringDetail($id = null)
    {
        $peminjaman = $this->peminjamanModel->builder()
            ->select('peminjaman.*, item_aktif.no_dokumen, item_aktif.judul_dokumen, berkas_aktif.no_berkas, berkas_aktif.nama_berkas, users.name as created_by_name')
            ->join('item_aktif', 'item_aktif.id = peminjaman.id_item_aktif', 'left')
            ->join('berkas_aktif', 'berkas_aktif.id = peminjaman.id_berkas_aktif', 'left')
            ->join('users', 'users.id = peminjaman.created_by', 'left')
            ->where('peminjaman.id', $id)
            ->get()->getRowArray();

        if (!$peminjaman) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'      => 'Detail Peminjaman',
            'peminjaman' => $peminjaman,
            'session'    => $this->session,
        ];
        return view('peminjaman/monitoring_detail', $data);
    }

    /**
     * Memproses pengembalian arsip.
     */
    public function prosesPengembalian()
    {
        if (!$this->request->isAJAX()) { /* ... */
        }

        $peminjamanId = $this->request->getPost('id');
        $tglKembaliAktual = $this->request->getPost('tgl_kembali_aktual');

        $rules = [
            'id'                  => 'required|integer',
            'tgl_kembali_aktual'  => 'required|valid_date',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => implode('<br>', $this->validator->getErrors())]);
        }

        $peminjaman = $this->peminjamanModel->find($peminjamanId);
        if (!$peminjaman || $peminjaman['status'] === 'dikembalikan') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Peminjaman tidak valid atau sudah dikembalikan.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Update status di tabel peminjaman
            $this->peminjamanModel->update($peminjamanId, [
                'tgl_kembali_aktual' => $tglKembaliAktual,
                'status'             => 'dikembalikan',
            ]);

            // Update status pinjam di item_aktif atau berkas_aktif
            if ($peminjaman['id_item_aktif']) {
                $this->itemAktifModel->update($peminjaman['id_item_aktif'], ['pinjam' => 0]);
            } elseif ($peminjaman['id_berkas_aktif']) {
                $this->berkasAktifModel->update($peminjaman['id_berkas_aktif'], ['pinjam' => 0]);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Arsip berhasil dikembalikan.']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error prosesPengembalian: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses pengembalian: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghapus record peminjaman.
     */
    public function deletePeminjaman($id = null)
    {
        $peminjaman = $this->peminjamanModel->find($id);
        if (!$peminjaman) {
            return redirect()->back()->with('error', 'Data peminjaman tidak ditemukan.');
        }

        // Jika status masih 'dipinjam', kembalikan dulu status pinjam di item/berkas
        if ($peminjaman['status'] === 'dipinjam') {
            if ($peminjaman['id_item_aktif']) {
                $this->itemAktifModel->update($peminjaman['id_item_aktif'], ['pinjam' => 0]);
            } elseif ($peminjaman['id_berkas_aktif']) {
                $this->berkasAktifModel->update($peminjaman['id_berkas_aktif'], ['pinjam' => 0]);
            }
        }

        $this->peminjamanModel->delete($id);
        session()->setFlashdata('success', 'Data peminjaman berhasil dihapus.');
        return redirect()->to('/peminjaman/monitoring');
    }
}
