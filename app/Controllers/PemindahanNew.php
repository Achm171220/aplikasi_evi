<?php

namespace App\Controllers;

use App\Models\ItemAktifModel;
use App\Models\UserModel;
use App\Models\ItemInaktifModel;
use App\Models\BeritaAcaraModel;
use App\Models\UnitKerjaEs2Model;

class PemindahanNew extends BaseController
{
    protected $itemAktifModel;
    protected $userModel;
    protected $itemInaktifModel;
    protected $beritaAcaraModel;
    protected $unitKerjaEs2Model;
    protected $db;
    protected $session;

    public function __construct()
    {
        // parent::__construct();
        $this->itemAktifModel = new ItemAktifModel();
        $this->userModel = new UserModel();
        $this->itemInaktifModel = new ItemInaktifModel();
        $this->beritaAcaraModel = new BeritaAcaraModel();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model();
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
    }
    private function checkPermission(array $rolesNeeded, array $jabatanNeeded = [], string $redirectUrl = '/')
    {
        if (!$this->session->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Anda harus login untuk mengakses halaman ini.');
            return redirect()->to('/login');
        }

        $userRoleAccess = $this->session->get('role_access');
        $userRoleJabatan = $this->session->get('user_jabatan');

        if ($userRoleAccess === 'superadmin') {
            return true;
        }

        if (!in_array($userRoleAccess, $rolesNeeded)) {
            session()->setFlashdata('error', 'Anda tidak memiliki hak akses untuk halaman ini.');
            return redirect()->to($redirectUrl);
        }

        if (!empty($jabatanNeeded) && $userRoleAccess !== 'admin') {
            if (!in_array($userRoleJabatan, $jabatanNeeded)) {
                session()->setFlashdata('error', 'Anda tidak memiliki peran fungsional yang sesuai untuk halaman ini.');
                return redirect()->to($redirectUrl);
            }
        }

        return true;
    }

    public function index()
    {
        // Contoh data untuk dashboard
        $totalProducts = $this->itemAktifModel->countAllResults(); // Contoh: menghitung total item aktif
        $totalSales = $this->itemInaktifModel->countAllResults(); // Contoh: anggap item inaktif sebagai "sales" atau transaksi selesai
        $totalUsers = $this->userModel->countAllResults(); // Menghitung total user

        $data = [
            'pageTitle' => 'Dashboard Overview',
            'totalProducts' => $totalProducts,
            'totalSales' => $totalSales,
            'totalDelivery' => 340, // Contoh data statis
            'increasePercentage' => 25, // Contoh data statis
            'recentOrders' => [ // Contoh data untuk tabel
                ['id' => 1, 'product' => 'Television', 'customer' => 'Jonny', 'price' => '$1200', 'status' => 'Pending'],
                ['id' => 2, 'product' => 'Laptop', 'customer' => 'Kenny', 'price' => '$750', 'status' => 'Delivered'],
                // ... tambahkan data dari database jika perlu
            ],
            'selectOptions' => [ // Contoh data untuk Select2
                'AL' => 'Alabama',
                'WY' => 'Wyoming',
                'NY' => 'New York',
                'TX' => 'Texas',
                'CA' => 'California',
                'FL' => 'Florida'
            ]
        ];

        return view('pemindahan_new/index', $data);
    }
    public function dataAktif()
    {
        $check = $this->checkPermission(['superadmin', 'admin', 'user']);
        if ($check !== true) return $check;

        $userRoleAccess = $this->session->get('role_access');
        $currentUserId = $this->session->get('user_id');

        $query = $this->itemAktifModel
            ->join('users AS u1', 'u1.id = item_aktif.id_verifikator1', 'left')
            ->join('users AS u2', 'u2.id = item_aktif.id_verifikator2', 'left')
            ->join('users AS u3', 'u3.id = item_aktif.id_verifikator3', 'left')
            ->join('berita_acara AS ba', 'ba.id = item_aktif.id_berita_acara', 'left')
            ->join('klasifikasi', 'klasifikasi.id = item_aktif.id_klasifikasi', 'left')
            ->select('item_aktif.*, u1.name AS verifikator1_name, u2.name AS verifikator2_name, u3.name AS verifikator3_name,
                                         ba.no_ba, ba.tgl_ba, klasifikasi.nama_klasifikasi, klasifikasi.umur_aktif'); // <<< UBAH SELECT ke umur_aktif

        if ($userRoleAccess === 'user') {
            $query->where('item_aktif.id_user', $currentUserId);
        }

        $data['items'] = $query->findAll();
        return view('pemindahan_new/data_aktif', $data);
    }
}
