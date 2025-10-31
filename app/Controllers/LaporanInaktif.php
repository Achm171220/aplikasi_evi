<?php

namespace App\Controllers;

// Pastikan semua 'use' statement yang dibutuhkan sudah ada
use App\Models\ItemInaktifModel;
use App\Models\KlasifikasiModel; // Diperlukan untuk JOIN di Model
use App\Models\UnitKerjaEs2Model; // Untuk opsi filter Superadmin
use App\Models\UnitKerjaEs3Model; // Untuk filter hak akses

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Dompdf\Dompdf;
use Dompdf\Options;

class LaporanInaktif extends BaseController
{
    protected $itemInaktifModel;
    protected $klasifikasiModel; // Diperlukan untuk JOIN
    protected $unitKerjaEs2Model; // Untuk opsi filter Superadmin
    protected $unitKerjaEs3Model; // Untuk filter hak akses

    public function __construct()
    {
        $this->itemInaktifModel = new ItemInaktifModel();
        $this->klasifikasiModel = new KlasifikasiModel(); // Inisialisasi
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model(); // Inisialisasi
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model(); // Inisialisasi
    }

    /**
     * Fungsi helper untuk mengambil dan memformat data laporan inaktif,
     * dengan filter hak akses dan filter tambahan.
     * @return array ['nama_es2' => string, 'laporanData' => array]
     */
    private function getLaporanData()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('item_inaktif'); // Langsung query item_inaktif

        // Ambil filter dari URL query string
        $tanggal_mulai = $this->request->getGet('tanggal_mulai');
        $tanggal_akhir = $this->request->getGet('tanggal_akhir');
        $es2_id_filter_url = $this->request->getGet('es2_id'); // Filter Es2 untuk Superadmin

        $builder->select([
            'item_inaktif.judul_dokumen',
            'item_inaktif.no_dokumen',
            'item_inaktif.tahun_cipta',
            'item_inaktif.jumlah',
            'item_inaktif.media_simpan',
            'item_inaktif.lokasi_simpan_new',
            'item_inaktif.no_box',
            'klasifikasi.kode as kode_klasifikasi',
            'klasifikasi.nasib_akhir',
            'es2.nama_es2',
            'es2.id as id_es2',
            'es3.id as id_es3' // Tambahkan untuk filter hak akses
        ])
            ->join('klasifikasi', 'klasifikasi.id = item_inaktif.id_klasifikasi', 'left')
            // JOIN ke unit kerja untuk filter hak akses dan display
            ->join('unit_kerja_es3 as es3', 'es3.id = item_inaktif.id_es3', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = es3.id_es2', 'left');

        // Terapkan filter hak akses
        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');

        if ($userRole !== 'superadmin' && !empty($authData)) {
            // Asumsi tabel item_inaktif memiliki kolom id_es2 dan id_es3
            if (!empty($authData['id_es3'])) {
                $builder->where('item_inaktif.id_es3', $authData['id_es3']);
            } elseif (!empty($authData['id_es2'])) {
                $builder->where('item_inaktif.id_es2', $authData['id_es2']);
            } elseif (!empty($authData['id_es1'])) {
                // Perlu subquery jika hanya ada id_es2 di tabel item_inaktif
                $subQuery = $db->table('unit_kerja_es2')->select('id')->where('id_es1', $authData['id_es1']);
                $builder->whereIn('item_inaktif.id_es2', $subQuery);
            } else {
                $builder->where('1=0'); // Tidak ada hak, jangan tampilkan apa-apa
            }
        }

        // Terapkan filter Es2 jika Superadmin memilih
        if ($userRole === 'superadmin' && !empty($es2_id_filter_url)) {
            $builder->where('item_inaktif.id_es2', $es2_id_filter_url);
        }

        // Terapkan filter tanggal
        if (!empty($tanggal_mulai)) {
            $builder->where('item_inaktif.tgl_dokumen >=', $tanggal_mulai);
        }
        if (!empty($tanggal_akhir)) {
            $builder->where('item_inaktif.tgl_dokumen <=', $tanggal_akhir);
        }

        $query = $builder->orderBy('item_inaktif.tahun_cipta', 'DESC')->get()->getResultArray();

        // Tentukan nama Unit Pengolah untuk judul laporan
        $nama_es2 = 'Semua Unit Eselon 2'; // Default Superadmin tanpa filter
        if ($userRole === 'superadmin' && !empty($es2_id_filter_url)) {
            $unit = $this->unitKerjaEs2Model->find($es2_id_filter_url);
            if ($unit) $nama_es2 = $unit['nama_es2'];
        } elseif ($userRole !== 'superadmin' && !empty($authData['id_es2'])) {
            $nama_es2 = ($this->unitKerjaEs2Model->find($authData['id_es2']))['nama_es2'] ?? 'Tidak Ditemukan';
        } elseif (empty($query)) {
            $nama_es2 = 'Tidak Ada Data';
        }

        return ['nama_es2' => $nama_es2, 'laporanData' => $query];
    }

    /**
     * Menampilkan halaman utama laporan di web.
     */
    public function index()
    {
        $report = $this->getLaporanData();
        $userRole = session()->get('role_access');
        $authData = session()->get('auth_data');

        // Untuk Superadmin, kirim semua opsi Es2
        $es2_filter_options = [];
        if ($userRole === 'superadmin') {
            $es2_filter_options = $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll();
        } elseif ($userRole === 'admin' && !empty($authData['id_es2'])) {
            // Untuk Admin, kirim Es2 yang menjadi haknya (terbatas 1)
            $es2_filter_options = $this->unitKerjaEs2Model->where('id', $authData['id_es2'])->findAll();
        }

        $data = [
            'title'         => 'Laporan Arsip Inaktif',
            'laporanData'   => $report['laporanData'],
            'nama_es2'      => $report['nama_es2'],
            'tanggal_mulai' => $this->request->getGet('tanggal_mulai'),
            'tanggal_akhir' => $this->request->getGet('tanggal_akhir'),
            'es2_filter_options' => $es2_filter_options,
            'current_es2_filter_id' => $this->request->getGet('es2_id'),
        ];
        return view('laporan-inaktif/index', $data);
    }

    /**
     * Fungsi untuk mengekspor data ke format Excel (XLSX).
     */
    public function exportExcel()
    {
        $report = $this->getLaporanData();
        $laporanData = $report['laporanData'];
        $nama_es2 = $report['nama_es2'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Judul Laporan
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'DAFTAR ARSIP INAKTIF');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Identitas Unit Pengolah
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'UNIT PENGOLAH: ' . strtoupper($nama_es2));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


        // Header Tabel (mulai dari baris 4)
        $headers = ['No.', 'Kode Klasifikasi', 'Judul Dokumen', 'No. Dokumen', 'Tahun', 'Jumlah', 'Media', 'Lokasi Simpan (Rak/Box)', 'Nasib Akhir'];
        $sheet->fromArray($headers, NULL, 'A4');

        // Mengisi Data (mulai dari baris 5)
        $rowNum = 5;
        foreach ($laporanData as $index => $data) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $data['kode_klasifikasi']);
            $sheet->setCellValue('C' . $rowNum, $data['judul_dokumen']);
            $sheet->setCellValue('D' . $rowNum, $data['no_dokumen']);
            $sheet->setCellValue('E' . $rowNum, $data['tahun_cipta']);
            $sheet->setCellValue('F' . $rowNum, $data['jumlah']);
            $sheet->setCellValue('G' . $rowNum, ucfirst($data['media_simpan']));
            $sheet->setCellValue('H' . $rowNum, $data['lokasi_simpan_new'] . ' / ' . $data['no_box']);
            $sheet->setCellValue('I' . $rowNum, ucfirst($data['nasib_akhir']));
            $rowNum++;
        }

        // Styling Header
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        // Styling Data (Opsional)
        $sheet->getStyle('A5:I' . ($rowNum - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        // Auto-size kolom
        foreach (range('A', 'I') as $columnID) $sheet->getColumnDimension($columnID)->setAutoSize(true);

        // Blok Tanda Tangan
        $lastDataRow = $rowNum - 1;
        $signatureRow = $lastDataRow + 3;
        $sheet->setCellValue('B' . $signatureRow, 'Disusun Oleh:');
        $sheet->setCellValue('H' . $signatureRow, 'Mengetahui:'); // Contoh di kolom H
        $sheet->setCellValue('B' . ($signatureRow + 4), '..................................');
        $sheet->setCellValue('H' . ($signatureRow + 4), '..................................');

        // Output ke browser
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_arsip_inaktif_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    /**
     * Fungsi untuk mengekspor data ke format PDF.
     */
    public function exportPdf()
    {
        $report = $this->getLaporanData();
        $data = [
            'title'       => 'Laporan Arsip Inaktif',
            'laporanData' => $report['laporanData'],
            'nama_es2'    => $report['nama_es2'],
        ];
        $html = view('laporan-inaktif/pdf_template', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'laporan_arsip_inaktif_' . date('YmdHis') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => 1]);
    }
}
