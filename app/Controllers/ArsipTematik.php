<?php

namespace App\Controllers;

use App\Models\TemaModel;
use App\Models\ItemAktifModel;
use App\Models\ArsipTemaLinkModel;
use App\Models\ArsipTematikModel;
use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ArsipTematik extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Manajemen Arsip Tematik',
            'themes' => (new TemaModel())->orderBy('nama_tema', 'ASC')->findAll(),
            'selected_theme_id' => $this->request->getGet('tema_id'),
        ];
        return view('arsip_tematik/index', $data);
    }

    public function listData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $itemAktifModel = new ItemAktifModel();
            // Langsung kembalikan hasil dari model
            $result = $itemAktifModel->getDataTablesForThematicTagging($this->request);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'ArsipTematik AJAX Error: ' . $e->getMessage());
            // Kembalikan error 500 jika ada masalah di Model
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Terjadi kesalahan pada server.']);
        }
    }
    // --- METODE AJAX BARU ---

    public function addThemeToItem()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $itemId = $this->request->getPost('item_id');
        $themeId = $this->request->getPost('tema_id');

        if (empty($itemId) || empty($themeId)) {
            return $this->response->setStatusCode(400)->setJSON(['message' => 'ID Item dan Tema wajib diisi.']);
        }

        $linkModel = new ArsipTemaLinkModel();

        // Cek duplikasi
        $exists = $linkModel->where(['id_item_aktif' => $itemId, 'id_tema' => $themeId])->first();
        if ($exists) {
            return $this->response->setJSON(['success' => true, 'message' => 'Tema sudah ditautkan.']);
        }

        $linkModel->insert(['id_item_aktif' => $itemId, 'id_tema' => $themeId]);

        return $this->response->setJSON(['success' => true, 'message' => 'Tema berhasil ditambahkan.']);
    }

    public function removeThemeFromItem()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $itemId = $this->request->getPost('item_id');
        $themeId = $this->request->getPost('tema_id');

        if (empty($itemId) || empty($themeId)) {
            return $this->response->setStatusCode(400)->setJSON(['message' => 'ID Item dan Tema wajib diisi.']);
        }

        $linkModel = new ArsipTemaLinkModel();
        $linkModel->where(['id_item_aktif' => $itemId, 'id_tema' => $themeId])->delete();

        return $this->response->setJSON(['success' => true, 'message' => 'Tema berhasil dihapus.']);
    }
    /**
     * Metode untuk mengekspor data arsip tematik ke Excel.
     */
    public function exportExcel()
    {
        $themeId = $this->request->getGet('tema_id');

        if (empty($themeId)) {
            return redirect()->back()->with('error', 'Silakan pilih tema terlebih dahulu untuk diekspor.');
        }

        $arsipTematikModel = new ArsipTematikModel();
        $arsipData = $arsipTematikModel->searchByTheme((int)$themeId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- STYLING ---
        $sheet->getStyle('A1:K2')->getFont()->setBold(true);
        $sheet->getStyle('A1:K2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:K4')->getFont()->setBold(true);

        // --- JUDUL DAN SUBJUDUL ---
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'DAFTAR ARSIP TEMATIK');
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'BADAN PENGAWASAN KEUANGAN DAN PEMBANGUNAN');

        // --- HEADER TABEL (di baris 4) ---
        $headers = [
            'NO',
            'PENCIPTA ARSIP',
            'KODE KLASIFIKASI',
            'JENIS/URAIAN',
            'NOMOR LAPORAN',
            'TINGKAT PERKEMBANGAN',
            'TANGGAL',
            'KURUN WAKTU',
            'JUMLAH',
            'MEDIA SIMPAN',
            'LOKASI SIMPAN'
        ];
        $sheet->fromArray($headers, NULL, 'A4');

        // --- ISI DATA (mulai dari baris 5) ---
        $rowNumber = 5;
        $no = 1;
        foreach ($arsipData as $row) {
            $sheet->setCellValue('A' . $rowNumber, $no++);
            $sheet->setCellValue('B' . $rowNumber, $row['pencipta_arsip']);
            $sheet->setCellValue('C' . $rowNumber, $row['kode_klasifikasi']);
            $sheet->setCellValue('D' . $rowNumber, $row['judul']); // Jenis/Uraian diisi Judul Dokumen
            $sheet->setCellValue('E' . $rowNumber, $row['nomor_laporan']);
            $sheet->setCellValue('F' . $rowNumber, $row['tingkat_perkembangan']);
            $sheet->setCellValue('G' . $rowNumber, $row['tanggal'] ? date('d-m-Y', strtotime($row['tanggal'])) : '-');
            $sheet->setCellValue('H' . $rowNumber, $row['kurun_waktu']);
            $sheet->setCellValue('I' . $rowNumber, $row['jumlah']);
            $sheet->setCellValue('J' . $rowNumber, $row['media_simpan']);
            $sheet->setCellValue('K' . $rowNumber, $row['lokasi_simpan']);
            $rowNumber++;
        }

        // --- AUTO-SIZE KOLOM ---
        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // --- OUTPUT KE BROWSER ---
        $writer = new Xlsx($spreadsheet);
        $filename = 'daftar_arsip_tematik_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
}
