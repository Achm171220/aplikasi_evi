<?php

namespace App\Models;

use App\Traits\Loggable;

class ItemInaktifModel extends BaseModel
{
    use Loggable;

    protected $table      = 'item_inaktif';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Allowed Fields berdasarkan item_inaktif (Gambar 1)
    protected $allowedFields = [
        'id_klasifikasi',
        'id_user',
        'id_es2',
        'id_es3',
        'id_berkas',
        'no_berkas_baru',
        'no_dokumen',
        'judul_dokumen',
        'tgl_dokumen',
        'tahun_cipta',
        'jumlah',
        'tk_perkembangan',
        'lokasi_simpan_aktif',
        'lokasi_simpan_new',
        'media_simpan',
        'no_box',
        'status_arsip',
        'status_pindah',
        'id_verifikator1',
        'id_verifikator2',
        'id_verifikator3',
        'id_jenis_naskah',
        'nama_file',
        'nama_folder',
        'nama_link',
        'tgl_temuan',
        'dasar_catat',
        'pinjam',
        'sumber_data',
        'admin_notes',
        'id_berita_acara' // Tambahkan proposal_id dan id_berita_acara
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id'                => 'permit_empty|is_natural_no_zero',
        'id_klasifikasi'    => 'required|integer',
        'id_jenis_naskah'   => 'required|integer',
        'id_es3'            => 'required|integer',
        // --- PERBAIKAN: Hapus 'required' dari field yang diisi otomatis dan bisa bernilai 0/false/default ---
        'id_user'           => 'permit_empty|integer', // 'permit_empty' karena 0 bisa jadi ID valid atau null
        'status_arsip'      => 'permit_empty|in_list[aktif,inaktif,vital,negara]',
        'status_pindah'     => 'permit_empty|in_list[belum,usul_pindah,verifikasi,ba,ditolak]',
        'pinjam'            => 'permit_empty|is_natural', // 0 adalah nilai natural, tapi required akan gagal
        // --- AKHIR PERBAIKAN ---
        'judul_dokumen'     => 'required|min_length[5]',
        'tgl_dokumen'       => 'required|valid_date',
        'tahun_cipta'       => 'required|exact_length[4]|integer',
        'jumlah'            => 'required|integer|greater_than[0]',
        'tk_perkembangan'   => 'required|in_list[asli,copy]',
        'media_simpan'      => 'required|in_list[kertas,elektronik]',
        'lokasi_simpan_new' => 'permit_empty|string',
        'no_box'            => 'permit_empty|string',
        'no_dokumen'        => 'permit_empty|string',
        'nama_file'         => 'permit_empty|string',
        'nama_folder'       => 'permit_empty|string',
        'nama_link'         => 'permit_empty|valid_url_strict',
        'dasar_catat'       => 'required|in_list[srikandi,sima,map,bisma,sadewa,pos,lainnya]'
    ];

    protected $validationMessages = [
        'id_klasifikasi'  => ['required' => 'Klasifikasi wajib dipilih.'],
        'id_jenis_naskah' => ['required' => 'Jenis Naskah wajib dipilih.'],
        'id_es3'          => ['required' => 'Unit Kerja Pencipta (Eselon 3) wajib dipilih.'],
        'id_user'         => ['required' => 'ID Pengguna wajib diisi.'],
        'status_arsip'    => ['required' => 'Status Arsip wajib diisi.'],
        'status_pindah'   => ['required' => 'Status Pindah wajib diisi.'],
        'pinjam'          => ['required' => 'Status Pinjam wajib diisi.'],
        'judul_dokumen'   => ['required' => 'Judul Dokumen wajib diisi.', 'min_length' => 'Judul minimal 5 karakter.'],
        'tgl_dokumen'     => ['required' => 'Tanggal Dokumen wajib diisi.', 'valid_date' => 'Format tanggal tidak valid.'],
        'tahun_cipta'     => ['required' => 'Tahun Cipta wajib diisi.', 'exact_length' => 'Format tahun harus YYYY.'],
        'jumlah'          => ['required' => 'Jumlah wajib diisi.', 'greater_than' => 'Jumlah minimal harus 1.'],
        'tk_perkembangan' => ['required' => 'Tingkat Perkembangan wajib dipilih.'],
        'media_simpan'    => ['required' => 'Media Simpan wajib dipilih.'],
        'dasar_catat'     => ['in_list' => 'Dasar Pencatatan tidak valid.'],
        'nama_link'       => ['valid_url_strict' => 'Format link tidak valid.']
    ];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    public $tempFilterEs2 = null; // Properti publik sementara untuk filter
    public $tempFilterEs3 = null; // Properti publik sementara untuk filter
    // DataTables: Daftar item
    public function getDataTablesList($request)
    {
        // --- PERBAIKAN UTAMA: Mulai dengan builder bersih dan JOIN yang diperlukan ---
        $builder = $this->db->table($this->table . ' as item_inaktif')
            ->select('item_inaktif.id, item_inaktif.no_dokumen, item_inaktif.judul_dokumen, item_inaktif.tgl_dokumen, item_inaktif.tahun_cipta, item_inaktif.id_berkas, item_inaktif.pinjam, item_inaktif.status_arsip, berkas_inaktif.nama_berkas, klasifikasi.kode as kode_klasifikasi, es2.kode as kode_es2, es3.kode as kode_es3')
            ->join('berkas_inaktif', 'berkas_inaktif.id = item_inaktif.id_berkas', 'left')
            ->join('klasifikasi', 'klasifikasi.id = item_inaktif.id_klasifikasi', 'left')
            ->join('unit_kerja_es3 as es3', 'es3.id = item_inaktif.id_es3', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = es3.id_es2', 'left');

        // Filter hak akses (ini akan menambahkan WHERE ke builder)
        $builder = $this->applyAuthFilterToBuilder($builder);

        // --- Perbaikan: Ambil filter dari request DataTables (request POST dari AJAX) ---
        $es2_id_filter = $request->getPost('es2_id_filter'); // Ini adalah ID, bukan kode
        $es3_id_filter = $request->getPost('es3_id_filter'); // Ini adalah ID, bukan kode

        if (!empty($es3_id_filter)) {
            $builder->where('item_inaktif.id_es3', $es3_id_filter); // Filter berdasarkan ID
        } elseif (!empty($es2_id_filter)) {
            $builder->where('item_inaktif.id_es2', $es2_id_filter); // Filter berdasarkan ID
        }

        $this->builder = $builder; // Set builder Model ke builder yang sudah disiapkan

        $column_search = [
            'item_inaktif.no_dokumen',
            'item_inaktif.judul_dokumen',
            'item_inaktif.tgl_dokumen',
            'item_inaktif.tahun_cipta',
            'klasifikasi.kode',
            'es2.kode',
            'es3.kode'
        ];
        $column_order  = [null, 'es2.kode', 'klasifikasi.kode', null, 'item_inaktif.tgl_dokumen', null, null];
        $order = ['item_inaktif.id' => 'DESC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
    // DataTables: Item tanpa berkas
    public function getDataTablesForPemberkasan($request)
    {
        $builder = $this->db->table($this->table)
            ->select("{$this->table}.*, klasifikasi.kode as kode_klas")
            ->join('klasifikasi', 'klasifikasi.id = ' . $this->table . '.id_klasifikasi', 'left')
            ->where('id_berkas IS NULL')
            ->orderBy('tgl_dokumen', 'DESC');

        $builder = apply_auth_filter($builder, 'item_inaktif');
        $this->builder = $builder;

        return $this->getDataTables(
            $request,
            ['no_dokumen', 'judul_dokumen', 'tahun_cipta', 'kode_klas'],
            [null, 'no_dokumen', 'judul_dokumen', 'tgl_dokumen', 'tahun_cipta', 'kode_klas'],
            ['id' => 'DESC']
        );
    }

    // DataTables: Item dalam berkas tertentu
    public function getDataTablesItemsInBerkas($request, $berkasId)
    {
        $builder = $this->db->table($this->table)
            ->where('id_berkas', $berkasId);

        $builder = apply_auth_filter($builder, 'item_inaktif');
        $this->builder = $builder;

        return $this->getDataTables(
            $request,
            ['no_dokumen', 'judul_dokumen'],
            [null, 'no_dokumen', 'judul_dokumen', 'tgl_dokumen', null],
            ['id' => 'DESC']
        );
    }

    // AJAX endpoint untuk DataTables (server-side processing)
    public function getUserProposalItems()
    {
        $request = service('request');

        // Parameter DataTables
        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'];
        $orderColumnIndex = $request->getPost('order')[0]['column'];
        $columnName = $request->getPost('columns')[$orderColumnIndex]['data'];
        $orderDir = $request->getPost('order')[0]['dir'];

        // Kolom yang bisa dicari/diurutkan (pastikan ini sesuai dengan yang ada di database)
        $columns = [
            'id',
            'no_berkas_baru',
            'judul_dokumen',
            'tahun_cipta',
            'lokasi_simpan_new_new_new',
            'status_arsip',
            'status_pindah'
        ];

        // Memastikan nama kolom yang diterima dari DataTables adalah valid
        if (!in_array($columnName, $columns)) {
            $columnName = 'id'; // Default ke 'id' jika kolom tidak valid
        }

        // --- Perbaikan Logika Query Builder untuk DataTables ---

        // 1. Hitung total record yang memenuhi kriteria dasar ('aktif', 'belum')
        // Ini adalah 'recordsTotal' untuk DataTables
        $totalRecords = $this->itemAktifModel
            ->where('status_arsip', 'aktif')
            ->where('status_pindah', 'belum')
            ->countAllResults(); // Tanpa 'false' akan mereset builder setelah count

        // 2. Buat query untuk data yang akan ditampilkan (sudah difilter oleh search)
        $query = $this->itemAktifModel
            ->where('status_arsip', 'aktif')
            ->where('status_pindah', 'belum');

        // Terapkan filter pencarian dari DataTables
        if (!empty($search)) {
            $query->groupStart();
            foreach ($columns as $col) {
                // Hindari mencari di kolom 'action' jika itu adalah kolom buatan di view
                if ($col !== 'action') {
                    $query->orLike($col, $search);
                }
            }
            $query->groupEnd();
        }

        // 3. Hitung jumlah record setelah filter pencarian (recordsFiltered)
        // Gunakan clone jika Anda ingin tetap menggunakan builder yang sama setelah count,
        // atau jalankan countAllResults(false) yang akan mempertahankan WHERE dan LIKE clauses.
        $filteredRecords = $query->countAllResults(false); // 'false' agar WHERE/LIKE clauses tetap ada

        // 4. Ambil data dengan paginasi dan sorting
        $items = $query->orderBy($columnName, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        // Siapkan data untuk DataTables
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'id' => $item['id'],
                'no_berkas_baru' => $item['no_berkas_baru'],
                'judul_dokumen' => $item['judul_dokumen'],
                'tahun_cipta' => $item['tahun_cipta'],
                'lokasi_simpan_new_new_new' => $item['lokasi_simpan_new_new_new'],
                'status_arsip' => $item['status_arsip'],
                'status_pindah' => $item['status_pindah'],
                'action' => '<input type="checkbox" class="form-check-input select-item" value="' . $item['id'] . '">'
            ];
        }

        $output = [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];

        return $this->response->setJSON($output);
    }
    // Menangani pengajuan pemindahan item
    public function proposeTransfer()
    {
        $request = service('request');
        if ($request->isAJAX() && $request->getMethod() === 'post') {
            $selectedIds = $request->getPost('selected_ids');

            if (empty($selectedIds)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak ada item yang dipilih untuk diajukan.'
                ]);
            }

            $successCount = 0;
            $failedIds = [];

            foreach ($selectedIds as $id) {
                $dataToUpdate = [
                    'status_arsip' => 'usul_pindah',
                    'status_pindah' => 'dalam_usulan'
                ];

                // Gunakan update dengan where clause untuk memastikan hanya item yang memenuhi kriteria awal yang diupdate
                $updated = $this->itemAktifModel
                    ->where('id', $id)
                    ->where('status_arsip', 'aktif') // Pastikan hanya item aktif yang diusulkan
                    ->where('status_pindah', 'belum') // Pastikan hanya item yang belum dalam proses
                    ->set($dataToUpdate)
                    ->update();


                if ($updated) {
                    $successCount++;
                } else {
                    $failedIds[] = $id;
                }
            }

            if ($successCount > 0) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "Berhasil mengusulkan $successCount item untuk pemindahan. " . (count($failedIds) > 0 ? "Gagal untuk ID: " . implode(', ', $failedIds) : "")
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengusulkan pemindahan untuk semua item yang dipilih.'
                ]);
            }
        }

        return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
    }
    public function getDataTablesAvailableItems($request)
    {
        $builder = $this->db->table($this->table)
            ->select('id, no_dokumen, judul_dokumen, tahun_cipta')
            ->where('pinjam', 0); // Hanya yang belum dipinjam

        // Terapkan filter hak akses standar
        $builder = $this->applyAuthFilterToBuilder($builder);

        $this->builder = $builder; // Penting untuk getDataTables di BaseModel

        $column_search = ['no_dokumen', 'judul_dokumen'];
        $column_order  = [null, 'no_dokumen', 'tahun_cipta'];
        $order = ['id' => 'DESC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
    public function searchItems(?string $keyword, ?int $userId = null, string $role = 'superadmin')
    {
        $builder = $this->where('status_arsip', 'aktif');

        // Jika role user biasa → hanya ambil arsip miliknya
        if ($role === 'user' && $userId !== null) {
            $builder->where('id_user', $userId);
        }

        // Jika ada pencarian
        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('no_dokumen', $keyword)
                ->orLike('judul_dokumen', $keyword)
                ->groupEnd();
        }

        return $builder->findAll();
    }
}
