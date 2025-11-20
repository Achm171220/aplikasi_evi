<?php

namespace App\Models;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

use App\Traits\Loggable; // 1. Panggil Trait

class BerkasInaktifModel extends BaseModel
{
    use Loggable; // 2. Gunakan Trait

    // 3. Daftarkan event-nya
    protected $afterUpdate = ['logAfterUpdate'];
    protected $afterDelete = ['logAfterDelete'];

    protected $table            = 'berkas_inaktif';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Daftar lengkap field yang diizinkan untuk diisi dari form (mass assignment)
    // Ini adalah kunci untuk mencegah silent failure saat create/update.
    protected $allowedFields = [
        'id_user',
        'id_es2',
        'id_es3',
        'id_klasifikasi',
        'no_berkas',
        'nama_berkas',
        'no_box',
        'no_label',
        'link_barcode',
        'qr_code',
        'thn_item_awal',
        'thn_item_akhir', // <-- TAMBAHKAN 'qr_code'
        'status_berkas',
        'status_tutup',
        'pinjam'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';


    protected $beforeInsert = ['generateQrCode'];
    protected $beforeUpdate = ['generateQrCode'];
    protected $afterInsert = ['updateQrCodeId']; // Nama callback yang lebih jelas

    // Aturan validasi untuk form tambah/edit
    protected $validationRules = [
        'nama_berkas'     => 'required|min_length[5]',
        // 'no_berkas_input' => 'required|integer', // field dari form (bukan dari DB)
        'id_klasifikasi'  => 'required|integer',
        'id_es3'          => 'required|integer',
        'no_box'          => 'permit_empty|string',
        'no_berkas'       => 'is_unique[berkas_inaktif.no_berkas,id,{id}]' // Tambahkan ini untuk keunikan
    ];
    protected function generateQrCode(array $data)
    {
        // Hanya generate jika ini adalah insert baru, atau jika nama berkas/no berkas berubah
        // Asumsi QR Code di-link ke halaman detail berkas
        $namaBerkas = $data['data']['nama_berkas'] ?? '';
        $id = $data['data']['id'] ?? null; // ID untuk update

        // Jika ini update dan nama berkas tidak berubah, tidak perlu generate ulang
        if ($id && isset($data['data']['nama_berkas']) && !isset($data['data']['qr_code'])) {
            $oldBerkas = $this->find($id);
            if ($oldBerkas && $oldBerkas['nama_berkas'] === $namaBerkas) {
                // Gunakan QR Code lama jika tidak ada perubahan signifikan
                $data['data']['qr_code'] = $oldBerkas['qr_code'];
                return $data;
            }
        }

        $qrFileName = 'berkas_qr_' . uniqid() . '.png';
        $qrSavePath = WRITEPATH . 'uploads/qrcodes/' . $qrFileName;

        $directory = dirname($qrSavePath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) { // Buat direktori rekursif dengan izin 0777
                throw new \Exception("Tidak dapat membuat direktori: $directory. Periksa izin folder 'writable/uploads'.");
            }
        }

        $qrContentUrl = site_url('berkas-inaktif/detail/' . ($id ?? '{id_placeholder}'));

        // --- Ini adalah bagian yang menggunakan pustaka ---
        $options = new QROptions([
            'version'    => QRCode::VERSION_AUTO,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'   => QRCode::ECC_L,
            'scale'      => 5,
            'addQuietzone' => true,
            'quietzoneSize' => 10,
        ]);

        $qrcode = new QRCode($options);
        $qrcode->render($qrContentUrl, $qrSavePath);
        // --- Akhir penggunaan pustaka ---

        $data['data']['qr_code'] = $qrFileName;

        return $data;
    }
    protected function updateQrCodeId(array $data)
    {
        $id = $data['id']; // ID yang baru saja di-insert
        $qrFileName = $data['data']['qr_code']; // Nama file QR Code yang sudah digenerate
        $qrSavePath = WRITEPATH . 'uploads/qrcodes/' . $qrFileName;

        // Buat ulang QR Code dengan ID yang benar jika awalnya pakai placeholder
        $newQrContentUrl = site_url('berkas-inaktif/detail/' . $id);

        $options = new QROptions([
            'version'    => QRCode::VERSION_AUTO,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'   => QRCode::ECC_L,
            'scale'      => 5,
            'addQuietzone' => true,
            'quietzoneSize' => 10,
        ]);

        $qrcode = new QRCode($options);
        $qrcode->render($newQrContentUrl, $qrSavePath);

        return $data;
    }

    /**
     * AfterInsert callback untuk mengupdate QR Code dengan ID yang benar.
     */
    protected $validationMessages = [
        'nama_berkas' => [
            'required'   => 'Nama Berkas tidak boleh kosong.',
            'min_length' => 'Nama Berkas minimal 5 karakter.'
        ],
        'no_berkas' => [
            'is_unique' => 'Kombinasi Nomor Berkas ini sudah terdaftar. Silakan gunakan Nomor Berkas (Input Angka) yang lain.'
        ],
        'id_klasifikasi'  => ['required' => 'Klasifikasi wajib dipilih.'],
        'id_es3'          => ['required' => 'Unit Eselon 3 wajib dipilih.'],
    ];

    /**
     * Menyediakan data untuk DataTables di halaman index
     * dengan subquery dan join kompleks.
     */
    public function getDataTablesList($request)
    {
        // Query builder baru yang mengambil data langsung dari relasi berkas
        $builder = $this->db->table($this->table . ' as berkas_inaktif')
            ->select([
                'berkas_inaktif.id',
                'berkas_inaktif.no_berkas',
                'berkas_inaktif.nama_berkas',
                'berkas_inaktif.thn_item_awal',
                'berkas_inaktif.thn_item_akhir',
                'berkas_inaktif.no_box',
                'berkas_inaktif.status_tutup',
                'berkas_inaktif.pinjam', // <-- PASTIKAN KOLOM INI TERPILIH
                '(SELECT COUNT(*) FROM item_aktif WHERE item_aktif.id_berkas = berkas_inaktif.id) as jumlah_item',
                'klasifikasi.kode as kode_klasifikasi',
                'es2.kode as kode_es2',
                'es3.kode as kode_es3'
            ])
            ->join('klasifikasi', 'klasifikasi.id = berkas_inaktif.id_klasifikasi', 'left')
            ->join('unit_kerja_es3 as es3', 'es3.id = berkas_inaktif.id_es3', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = berkas_inaktif.id_es2', 'left');

        // Terapkan filter hak akses (logika ini sudah benar)
        $session = session();
        if ($session->get('role_access') !== 'superadmin') {
            $authData = $session->get('auth_data');
            if (empty($authData)) {
                $builder->where('1=0');
            } else {
                if (!empty($authData['id_es3'])) {
                    $builder->where('berkas_inaktif.id_es3', $authData['id_es3']);
                } elseif (!empty($authData['id_es2'])) {
                    $builder->where('berkas_inaktif.id_es2', $authData['id_es2']);
                } elseif (!empty($authData['id_es1'])) {
                    $db = \Config\Database::connect();
                    $subQueryEs2 = $db->table('unit_kerja_es2')->select('id')->where('id_es1', $authData['id_es1']);
                    $builder->whereIn('berkas_inaktif.id_es2', $subQueryEs2);
                } else {
                    $builder->where('1=0');
                }
            }
        }


        $this->builder = $builder;

        $column_search = [
            'es2.kode',
            'es3.kode',
            'klasifikasi.kode',
            'berkas_inaktif.no_berkas',
            'berkas_inaktif.nama_berkas',
            'berkas_inaktif.status_tutup' // Tambahkan status ke pencarian
        ];
        // Tambahkan kolom status ke column_order
        $column_order  = [
            null,
            null,
            'klasifikasi.kode',
            null,
            'thn_item_awal',
            'jumlah_item',
            'no_box',
            'status_tutup',
            null
        ];
        $order = ['berkas_inaktif.id' => 'DESC'];

        return $this->getDataTables(
            $request,
            $column_search,
            $column_order,
            $order
        );
    }

    /**
     * Method deskriptif untuk mengambil detail satu berkas.
     * 
     * @param int $id ID Berkas
     * @return array|null Data berkas jika ditemukan, atau null jika tidak.
     */
    public function getDetailBerkas($id)
    {
        return $this->find($id);
    }
    public function getDataTablesAvailableBerkas($request)
    {
        $builder = $this->db->table($this->table)
            ->select('id, no_berkas, nama_berkas, thn_item_awal, thn_item_akhir')
            ->where('pinjam', 0)
            ->where('status_tutup', 'terbuka');

        // --- PASTIKAN FILTER HAK AKSES JUGA DITERAPKAN DI SINI ---
        // Karena method ini dipanggil dari PeminjamanController, dan PeminjamanController
        // menggunakan service('uri') untuk applyAuthFilter, ini harus diaktifkan.
        $builder = $this->applyAuthFilterToBuilder($builder); // <-- Pastikan ini aktif!

        $this->builder = $builder; // Penting untuk getDataTables di BaseModel

        $column_search = ['no_berkas', 'nama_berkas'];
        $column_order  = [null, 'no_berkas', 'thn_item_awal'];
        $order = ['id' => 'DESC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }
}
