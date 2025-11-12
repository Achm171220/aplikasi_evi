<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Manager implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek jika role user BUKAN superadmin
        if (session()->get('role_access') !== 'manager') {
            // Redirect ke halaman utama dengan pesan error
            return redirect()->to('/')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed
    }
}
