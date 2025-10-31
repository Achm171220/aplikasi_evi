<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class FileController extends Controller
{
    /**
     * Metode untuk menyajikan file QR Code yang disimpan di writable/uploads/qrcodes/
     * @param string $filename Nama file (misal: berkas_qr_68f0ae9043b66.png)
     */
    public function serveQrCode(string $filename)
    {
        // 1. Definisikan path absolut file di direktori writable
        $filePath = WRITEPATH . 'uploads/qrcodes/' . basename($filename);

        // 2. Cek apakah file ada
        if (!is_file($filePath)) {
            // Jika file tidak ditemukan, kembalikan response 404
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 3. Set header yang sesuai untuk gambar PNG
        $this->response->setHeader('Content-Type', 'image/png');
        $this->response->setHeader('Content-Length', filesize($filePath));

        // 4. Kirim konten file ke browser
        return $this->response->setBody(file_get_contents($filePath));
    }
}
