<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleJabatan implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Jika tidak ada argumen, tolak akses
        if (empty($arguments)) {
            return redirect()->to('/')->with('error', 'Akses Ditolak: Izin tidak dikonfigurasi.');
        }

        $userRoleJabatan = session()->get('role_jabatan');

        // Izinkan Superadmin untuk melewati semua filter jabatan
        if (session()->get('role_access') === 'superadmin') {
            return;
        }

        // Cek apakah jabatan user ada di dalam daftar yang diizinkan
        if (!in_array($userRoleJabatan, $arguments)) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki izin jabatan untuk mengakses halaman ini.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
