<?php

namespace App\Models;

use App\Traits\Loggable;

class ItemAktifModel extends BaseModel
{
    use Loggable;

    protected $table      = 'item_aktif';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Allowed Fields berdasarkan item_aktif (Gambar 1)
    protected $allowedFields = [
        'id_klasifikasi',
        'id_user',
        'id_es2',
        'id_es3',
        'id_berkas',
        'no_dokumen',
        'judul_dokumen',
        'tgl_dokumen',
        'tahun_cipta',
        'jumlah',
        'tk_perkembangan',
        'lokasi_simpan',
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
        'proposal_id',
        'id_berita_acara' // Tambahkan proposal_id dan id_berita_acara
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation Rules berdasarkan NOT NULL di item_aktif (Gambar 1)
    protected $validationRules    = [
        'id_klasifikasi'    => 'required|integer',
        // 'id_user'           => 'required|integer',
        'id_es2'            => 'required|integer',
        'id_es3'            => 'required|integer',
        // 'status_arsip'      => 'required|in_list[aktif,inaktif,usul_pindah,usul_musnah,usul_alih_media,tidak_ditemukan]', // Sesuaikan ENUM Anda
        // Status pindah bersifat NULLable di item_aktif, jadi tidak required, tapi disesuaikan di controller
        'status_pindah'     => 'permit_empty|in_list[belum,menunggu_verif1,disetujui_verif1,ditolak_verif1,disetujui_verif2,ditolak_verif2,disetujui_verif3,ditolak_verif3,menunggu_eksekusi,dipindahkan]', // Semua ENUM status_pindah di item_aktif
        // Kolom lain yang NULLable di DB bisa pakai 'permit_empty'
        'no_dokumen'        => 'permit_empty|string|max_length[255]',
        'judul_dokumen'     => 'permit_empty|string|max_length[255]',
        'tgl_dokumen'       => 'permit_empty|valid_date',
        'tahun_cipta'       => 'permit_empty|integer|min_length[4]|max_length[4]',
        'jumlah'            => 'permit_empty|integer',
        'tk_perkembangan'   => 'permit_empty|in_list[asli,copy]', // Sesuaikan ENUM
        'lokasi_simpan'     => 'permit_empty|string|max_length[255]',
        'media_simpan'      => 'permit_empty|in_list[kertas,elektronik]', // Sesuaikan ENUM
        'no_box'            => 'permit_empty|string|max_length[50]',
        'id_verifikator1'   => 'permit_empty|integer',
        'id_verifikator2'   => 'permit_empty|integer',
        'id_verifikator3'   => 'permit_empty|integer',
        'id_jenis_naskah'   => 'permit_empty|integer',
        'nama_file'         => 'permit_empty|string|max_length[255]',
        'nama_folder'       => 'permit_empty|string|max_length[255]',
        'nama_link'         => 'permit_empty|string|max_length[255]',
        'tgl_temuan'        => 'permit_empty|integer|in_list[0,1]', // Tinyint (0 atau 1)
        'dasar_catat'       => 'permit_empty|string',
        'pinjam'            => 'permit_empty|integer|in_list[0,1]', // Tinyint (0 atau 1)
        'sumber_data'       => 'permit_empty|in_list[manual,import]', // Sesuaikan ENUM
        'admin_notes'       => 'permit_empty|string',
        'proposal_id'       => 'permit_empty|integer',
        'id_berita_acara'   => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'unique_dokumen_tahun' => [
            'uniqueDokumenTahun' => 'Arsip dengan Nomor Dokumen ini ({value}) dan Tahun Cipta ({param}) sudah ada di sistem.'
        ],
        // ... (Pesan validasi lainnya) ...
    ];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    public $tempFilterEs2 = null; // Properti publik sementara untuk filter
    public $tempFilterEs3 = null; // Properti publik sementara untuk filter
    // DataTables: Daftar item
    public function getDataTablesList($request)
    {
        // --- PERBAIKAN UTAMA: Mulai dengan builder bersih dan JOIN yang diperlukan ---
        $builder = $this->db->table($this->table . ' as item_aktif')
            ->select('item_aktif.id, item_aktif.no_dokumen, item_aktif.judul_dokumen, item_aktif.tgl_dokumen, item_aktif.tahun_cipta, item_aktif.id_berkas, item_aktif.pinjam, item_aktif.status_arsip, berkas_aktif.nama_berkas, klasifikasi.kode as kode_klasifikasi, es2.kode as kode_es2, es3.kode as kode_es3')
            ->join('berkas_aktif', 'berkas_aktif.id = item_aktif.id_berkas', 'left')
            ->join('klasifikasi', 'klasifikasi.id = item_aktif.id_klasifikasi', 'left')
            ->join('unit_kerja_es3 as es3', 'es3.id = item_aktif.id_es3', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = es3.id_es2', 'left');

        // Filter hak akses (ini akan menambahkan WHERE ke builder)
        $builder = $this->applyAuthFilterToBuilder($builder);

        // --- Perbaikan: Ambil filter dari request DataTables (request POST dari AJAX) ---
        $es2_id_filter = $request->getPost('es2_id_filter'); // Ini adalah ID, bukan kode
        $es3_id_filter = $request->getPost('es3_id_filter'); // Ini adalah ID, bukan kode

        if (!empty($es3_id_filter)) {
            $builder->where('item_aktif.id_es3', $es3_id_filter); // Filter berdasarkan ID
        } elseif (!empty($es2_id_filter)) {
            $builder->where('item_aktif.id_es2', $es2_id_filter); // Filter berdasarkan ID
        }

        $this->builder = $builder; // Set builder Model ke builder yang sudah disiapkan

        $column_search = [
            'item_aktif.no_dokumen',
            'item_aktif.judul_dokumen',
            'item_aktif.tgl_dokumen',
            'item_aktif.tahun_cipta',
            'klasifikasi.kode',
            'es2.kode',
            'es3.kode'
        ];
        $column_order  = [null, 'es2.kode', 'klasifikasi.kode', null, 'item_aktif.tgl_dokumen', null, null];
        $order = ['item_aktif.id' => 'DESC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
    public function getDataTablesForThematicTagging($request)
    {
        // --- 1. Bangun Query Builder Dasar ---
        $builder = $this->db->table('item_aktif as ia')
            ->select("
                ia.id, ia.judul_dokumen, ia.no_dokumen, ia.tgl_dokumen, ia.tahun_cipta,
                ia.tk_perkembangan, ia.jumlah, ia.media_simpan,
                k.kode as kode_klasifikasi,
                es2.nama_es2 as pencipta_arsip,
                GROUP_CONCAT(DISTINCT CONCAT(t.id, ':', t.nama_tema) SEPARATOR ',') as themes_concat
            ")
            ->join('arsip_tema_link as atl', 'atl.id_item_aktif = ia.id', 'left')
            ->join('tema as t', 't.id = atl.id_tema', 'left')
            ->join('klasifikasi as k', 'k.id = ia.id_klasifikasi', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = ia.id_es2', 'left')
            ->groupBy('ia.id');

        // --- 2. Terapkan Filter Tema (jika ada) ---
        $themeIdFilter = $request->getPost('tema_id');
        if (!empty($themeIdFilter)) {
            $subQuery = $this->db->table('arsip_tema_link')->select('id_item_aktif')->where('id_tema', $themeIdFilter);
            $builder->whereIn('ia.id', $subQuery);
        }

        // --- 3. Hitung Total Records (Sebelum Pencarian) ---
        $totalBuilder = clone $builder;
        $recordsTotal = $totalBuilder->countAllResults();

        // --- 4. Terapkan Filter Pencarian (Search) ---
        $searchValue = $request->getPost('search')['value'] ?? '';
        $column_search = [
            'ia.judul_dokumen',
            'ia.no_dokumen',
            'es2.nama_es2',
            'k.kode'
        ];
        if ($searchValue) {
            $builder->groupStart();
            foreach ($column_search as $col) {
                $builder->orLike($col, $searchValue);
            }
            $builder->groupEnd();
        }

        // --- 5. Hitung Records yang Difilter ---
        $filteredBuilder = clone $builder;
        $recordsFiltered = $filteredBuilder->countAllResults();

        // --- 6. Terapkan Ordering ---
        $order = $request->getPost('order');
        if ($order) {
            $orderColumnIndex = $order[0]['column'];
            $orderColumnName = $request->getPost('columns')[$orderColumnIndex]['data'];
            $orderDir = $order[0]['dir'];

            $allowedOrderCols = ['pencipta_arsip', 'kode_klasifikasi', 'judul_dokumen', 'tgl_dokumen', 'tahun_cipta'];
            if (in_array($orderColumnName, $allowedOrderCols)) {
                // Map ke nama kolom DB yang benar
                $dbColumnMap = [
                    'pencipta_arsip' => 'es2.nama_es2',
                    'kode_klasifikasi' => 'k.kode',
                    'judul_dokumen' => 'ia.judul_dokumen',
                    'tgl_dokumen' => 'ia.tgl_dokumen',
                    'tahun_cipta' => 'ia.tahun_cipta'
                ];
                $builder->orderBy($dbColumnMap[$orderColumnName], $orderDir);
            }
        } else {
            $builder->orderBy('ia.id', 'DESC'); // Default order
        }

        // --- 7. Terapkan Paging (Limit & Offset) ---
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        // --- 8. Eksekusi Query dan Kembalikan Hasil ---
        $data = $builder->get()->getResultArray();

        return [
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    // DataTables: Item tanpa berkas
    public function getDataTablesForPemberkasan($request)
    {
        $builder = $this->db->table($this->table)
            ->where('id_berkas IS NULL');

        $builder = apply_auth_filter($builder, 'item_aktif');
        $this->builder = $builder;

        return $this->getDataTables(
            $request,
            ['no_dokumen', 'judul_dokumen', 'tahun_cipta'],
            [null, 'no_dokumen', 'judul_dokumen', 'tgl_dokumen', 'tahun_cipta'],
            ['id' => 'DESC']
        );
    }

    // DataTables: Item dalam berkas tertentu
    public function getDataTablesItemsInBerkas($request, $berkasId)
    {
        $builder = $this->db->table($this->table)
            ->where('id_berkas', $berkasId);

        $builder = apply_auth_filter($builder, 'item_aktif');
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
            'no_berkas',
            'judul_dokumen',
            'tahun_cipta',
            'lokasi_simpan',
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
                'no_berkas' => $item['no_berkas'],
                'judul_dokumen' => $item['judul_dokumen'],
                'tahun_cipta' => $item['tahun_cipta'],
                'lokasi_simpan' => $item['lokasi_simpan'],
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
    public function getDataTablesList_pemindahan($request)
    {
        $builder = $this->db->table($this->table . ' as item_inaktif')
            ->select('
                item_inaktif.id,
                item_inaktif.no_dokumen,
                item_inaktif.judul_dokumen,
                item_inaktif.tgl_dokumen,
                item_inaktif.tahun_cipta,
                item_inaktif.lokasi_simpan_aktif,
                item_inaktif.lokasi_simpan_new,
                item_inaktif.no_new_berkas,
                item_inaktif.status_arsip,
                item_inaktif.status_pindah,
                item_inaktif.id_klasifikasi,
                klasifikasi.nama_klasifikasi,
                klasifikasi.usia_aktif,
                unit_kerja_es2.nama_es2 AS es2_name,
                unit_kerja_es3.nama_es3 AS es3_name
            ')
            ->join('klasifikasi', 'klasifikasi.id = item_inaktif.id_klasifikasi', 'left')
            ->join('unit_kerja_es3', 'unit_kerja_es3.id = item_inaktif.id_es3', 'left')
            ->join('unit_kerja_es2', 'unit_kerja_es2.id = item_inaktif.id_es2', 'left');

        $builder = $this->applyAuthFilterToBuilder($builder); // Menerapkan filter otorisasi

        $es2_id_filter = $request->getPost('es2_id_filter');
        $es3_id_filter = $request->getPost('es3_id_filter');

        if (!empty($es3_id_filter)) {
            $builder->where('item_inaktif.id_es3', $es3_id_filter);
        } elseif (!empty($es2_id_filter)) {
            $builder->where('item_inaktif.id_es2', $es2_id_filter);
        }

        $this->builder = $builder; // Set builder Model ke builder yang sudah disiapkan

        $column_search = [
            'item_inaktif.no_dokumen',
            'item_inaktif.judul_dokumen',
            'item_inaktif.tgl_dokumen',
            'item_inaktif.tahun_cipta',
            'klasifikasi.nama_klasifikasi',
            'unit_kerja_es2.nama_es2',
            'unit_kerja_es3.nama_es3'
        ];
        $column_order  = [
            null, // No.
            'unit_kerja_es2.nama_es2',
            'klasifikasi.nama_klasifikasi',
            'item_inaktif.no_dokumen',
            'item_inaktif.judul_dokumen',
            'item_inaktif.tgl_dokumen',
            'item_inaktif.tahun_cipta',
            'item_inaktif.lokasi_simpan_aktif',
            'item_inaktif.lokasi_simpan_new',
            'item_inaktif.no_new_berkas',
            'item_inaktif.status_arsip',
            'item_inaktif.status_pindah',
            null // Aksi
        ];
        $order = ['item_inaktif.id' => 'DESC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
    public function uniqueDokumenTahun(string $noDokumen, string $fields, array $data): bool
    {
        // $fields akan berisi 'tahun_cipta'
        $tahunCiptaField = $fields;
        $tahunCipta = $data[$tahunCiptaField] ?? null;

        if (empty($tahunCipta)) {
            // Jika tahun cipta kosong, kita tidak bisa validasi unik kombinasi
            return true;
        }

        // Jalankan Query untuk mencari duplikasi
        $query = $this->where('no_dokumen', $noDokumen)
            ->where('tahun_cipta', $tahunCipta);

        // Catatan: Jika ini operasi UPDATE, kita harus mengecualikan ID saat ini
        if (isset($data['id'])) {
            $query->where('id !=', $data['id']);
        }

        return $query->countAllResults() === 0;
    }
}
