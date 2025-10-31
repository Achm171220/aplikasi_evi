<?php

namespace App\Controllers;

use App\Models\ImportDaftarModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Import extends BaseController
{
    protected $importDaftarModel;
    protected $session;

    public function __construct()
    {
        $this->importDaftarModel = new ImportDaftarModel();
        $this->session = session();
    }

    public function index()
    {
        $data = [
            'title'   => 'Riwayat Import Data',
            'session' => $this->session,
        ];
        return view('import/index', $data);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->importDaftarModel->getDataTablesList($this->request);

            // --- TAMBAHAN UNTUK DEBUGGING ---
            if (isset($result['error'])) {
                // Jika BaseModel menangkap DatabaseException, kirimkan pesannya
                return $this->response->setJSON($result)->setStatusCode(500);
            }

            $data = [];
            foreach ($result['data'] as $row) {
                $btn_preview = '<a href="' . site_url('riwayat-import/preview/' . $row['id']) . '" class="btn btn-sm btn-info" target="_blank">
                                <i class="bi bi-eye-fill me-1"></i> Preview
                            </a>';

                $data[] = [
                    '', // No
                    $row['tahun'],
                    'Semester ' . $row['semester'],
                    esc($row['nama_es2'] ?? 'N/A'),
                    esc($row['user_name'] ?? 'N/A'),
                    date('d M Y, H:i', strtotime($row['created_at'])),
                    $btn_preview,
                ];
            }
            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
        }
    }

    /**
     * Menampilkan preview isi file Excel yang diimpor.
     */
    public function preview($id = null)
    {
        $importData = $this->importDaftarModel->find($id);
        if (!$importData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $filePath = WRITEPATH . 'uploads/imports/' . $importData['file'];
        if (!file_exists($filePath)) {
            // Tampilkan error jika file fisik tidak ditemukan
            return view('errors/html/error_404', ['message' => 'File fisik untuk riwayat import ini tidak ditemukan di server.']);
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $tableData = $sheet->toArray();

            $header = array_shift($tableData); // Ambil baris pertama sebagai header

            $data = [
                'title'      => 'Preview Import: ' . $importData['file'],
                'importData' => $importData,
                'header'     => $header,
                'tableData'  => $tableData,
            ];

            return view('import/preview', $data);
        } catch (\Exception $e) {
            return view('errors/html/error_404', ['message' => 'Gagal membaca file Excel: ' . $e->getMessage()]);
        }
    }
}
