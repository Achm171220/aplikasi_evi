<?php

namespace App\Controllers;

// Pastikan semua 'use' statement yang dibutuhkan sudah ada
use App\Models\BerkasAktifModel;
use App\Models\UnitKerjaEs2Model; // Pastikan ini di-use
use App\Models\UnitKerjaEs3Model; // Pastikan ini di-use

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Dompdf\Dompdf;
use Dompdf\Options;

class Laporan extends BaseController
{
    protected $berkasAktifModel;
    protected $unitKerjaEs2Model; // <-- Deklarasikan ini
    protected $unitKerjaEs3Model; // <-- Deklarasikan ini
    protected $session; // <-- Deklarasikan ini

    public function __construct()
    {
        // Model ini hanya digunakan sebagai titik awal query builder
        $this->berkasAktifModel = new BerkasAktifModel();
        $this->unitKerjaEs2Model = new UnitKerjaEs2Model(); // <-- Inisialisasi ini
        $this->unitKerjaEs3Model = new UnitKerjaEs3Model(); // <-- Inisialisasi ini
    }

    private function getLaporanData()
    {
        // Mengambil service Session
        $session = session();
        $userRole = $session->get('role_access');
        $authData = $session->get('auth_data');

        $tanggal_mulai = $this->request->getGet('tanggal_mulai');
        $tanggal_akhir = $this->request->getGet('tanggal_akhir');
        // Filter dari URL
        $es2_id_filter_url = $this->request->getGet('es2_id');
        $es3_id_filter_url = $this->request->getGet('es3_id');

        $db = \Config\Database::connect();
        $builder = $db->table('item_aktif');

        $builder->select([
            // Data Item Aktif
            'item_aktif.id as item_id',
            'item_aktif.no_dokumen',
            'item_aktif.judul_dokumen',
            'item_aktif.tgl_dokumen',
            'item_aktif.tk_perkembangan',
            'item_aktif.media_simpan',
            'item_aktif.jumlah',
            'item_aktif.status_arsip',
            'item_aktif.lokasi_simpan',
            'item_aktif.nama_file',
            'item_aktif.nama_folder',
            'item_aktif.nama_link',
            'item_aktif.dasar_catat',

            // Data Berkas Aktif (Opsional)
            'berkas_aktif.id as berkas_id',
            'berkas_aktif.no_berkas',
            'berkas_aktif.nama_berkas',
            'berkas_aktif.thn_item_awal',
            'berkas_aktif.thn_item_akhir',
            'berkas_aktif.no_box as berkas_no_box',

            // Data Join lainnya
            'es2.nama_es2',
            'es2.kode as kode_es2',
            'es3.kode as kode_es3',
            'klasifikasi.kode as kode_klasifikasi',
            'klasifikasi.umur_aktif',
            'klasifikasi.nasib_akhir',
            'klasifikasi.skkad'
        ])
            ->join('berkas_aktif', 'berkas_aktif.id = item_aktif.id_berkas', 'left')
            ->join('klasifikasi', 'klasifikasi.id = item_aktif.id_klasifikasi', 'left')
            ->join('unit_kerja_es3 as es3', 'es3.id = item_aktif.id_es3', 'left')
            ->join('unit_kerja_es2 as es2', 'es2.id = item_aktif.id_es2', 'left')
            ->join('users as u', 'u.id = item_aktif.id_user', 'left');

        // --- PENERAPAN FILTER BERDASARKAN ROLE DAN HAK AKSES ---
        $isFilteredByHakAkses = false;

        if ($userRole === 'superadmin') {
            if (!empty($es3_id_filter_url)) {
                $builder->where('item_aktif.id_es3', $es3_id_filter_url);
            } elseif (!empty($es2_id_filter_url)) {
                $builder->where('item_aktif.id_es2', $es2_id_filter_url);
            }
        } else {
            if (empty($authData) || (empty($authData['id_es3']) && empty($authData['id_es2']))) {
                $builder->where('1=0');
                $isFilteredByHakAkses = true;
            } elseif ($userRole === 'user' && !empty($authData['id_es3'])) {
                $builder->where('item_aktif.id_es3', $authData['id_es3']);
                $isFilteredByHakAkses = true;
            } elseif (in_array($userRole, ['admin', 'admin_unit']) && !empty($authData['id_es2'])) {
                $builder->where('item_aktif.id_es2', $authData['id_es2']);
                $isFilteredByHakAkses = true;
            }
        }

        // Filtering tanggal
        if (!empty($tanggal_mulai)) {
            $builder->where('item_aktif.tgl_dokumen >=', $tanggal_mulai);
        }
        if (!empty($tanggal_akhir)) {
            $builder->where('item_aktif.tgl_dokumen <=', $tanggal_akhir);
        }

        // Sorting
        $query = $builder->orderBy('berkas_aktif.id', 'ASC')
            ->orderBy('item_aktif.tgl_dokumen', 'ASC')
            ->get()->getResultArray();

        // --- Penentuan nama_es2 di judul laporan ---
        $nama_es2 = 'Semua Unit Eselon 2'; // Default

        if ($userRole === 'superadmin') {
            if (!empty($es3_id_filter_url)) {
                $es3 = $this->unitKerjaEs3Model->find($es3_id_filter_url);
                if ($es3 && $es3['id_es2']) {
                    $nama_es2 = $this->unitKerjaEs2Model->find($es3['id_es2'])['nama_es2'] ?? 'Filter Unit Khusus';
                }
            } elseif (!empty($es2_id_filter_url)) {
                $unit = $this->unitKerjaEs2Model->find($es2_id_filter_url);
                if ($unit) $nama_es2 = $unit['nama_es2'];
            }
        } elseif ($isFilteredByHakAkses) {
            $id_es2_tampil = $authData['id_es2'] ?? null;

            if ($userRole === 'user' && !empty($authData['id_es3'])) {
                $es3 = $this->unitKerjaEs3Model->find($authData['id_es3']);
                $id_es2_tampil = $es3['id_es2'] ?? null;
            }

            if ($id_es2_tampil) {
                $nama_es2 = $this->unitKerjaEs2Model->find($id_es2_tampil)['nama_es2'] ?? 'Unit Tidak Dikenal';
            } else {
                $nama_es2 = 'Unit Kerja Khusus';
            }
        } elseif (empty($query)) {
            $nama_es2 = 'Tidak Ada Data';
        }


        // --- Formatting Data (Logika Berkas NULL) ---
        $formattedData = [];
        $currentBerkasId = null;
        $itemCounter = 1;

        foreach ($query as $row) {

            if ($row['berkas_id'] !== null) {
                // ITEM SUDAH DIBERKASKAN
                if ($currentBerkasId !== $row['berkas_id']) {
                    $currentBerkasId = $row['berkas_id'];
                    $itemCounter = 1; // Reset nomor urut item untuk berkas baru
                }

                $no_berkas = $row['no_berkas'];
                $judul_berkas = $row['nama_berkas'];
                $kurun_waktu = $row['thn_item_awal'] && $row['thn_item_akhir'] ? $row['thn_item_awal'] . ' - ' . $row['thn_item_akhir'] : '-';
                $no_item = $itemCounter++;
                $no_box_item = $row['berkas_no_box'] ?? $row['no_box'] ?? '-';
            } else {
                // ITEM BELUM DIBERKASKAN
                $currentBerkasId = null;
                $no_berkas = 'BELUM DIBERKASKAN';
                $judul_berkas = 'BELUM DIBERKASKAN';
                $kurun_waktu = '-';
                $no_item = '-';
                $no_box_item = $row['no_box'] ?? '-';
            }

            // --- 1. Kolom Uraian Informasi: no_dokumen | judul_dokumen ---
            $uraian_informasi = implode(' | ', array_filter([$row['no_dokumen'], $row['judul_dokumen']]));

            // --- 2. Kolom Lokasi Simpan ---
            if ($row['media_simpan'] === 'elektronik') {
                // Jika elektronik: dasar_catat | nama_link
                $lokasiSimpanParts = array_filter([
                    ($row['dasar_catat'] ? '[' . $row['dasar_catat'] . ']' : null),
                ]);
                $lokasi_simpan_output = empty($lokasiSimpanParts) ? '-' : implode(' | ', $lokasiSimpanParts);
            } else {
                // Jika kertas: Lokasi Simpan + File/Folder (jika ada)
                $lokasiSimpanParts = array_filter([
                    ($row['lokasi_simpan'] ?? null),
                    ($row['nama_file'] ? 'File: ' . $row['nama_file'] : null),
                    ($row['nama_folder'] ? 'Folder: ' . $row['nama_folder'] : null),
                ]);
                $lokasi_simpan_output = empty($lokasiSimpanParts) ? '-' : implode(', ', $lokasiSimpanParts);
            }

            $formattedData[] = [
                'no_berkas_lengkap'    => $no_berkas,
                'kode_klasifikasi'     => $row['kode_klasifikasi'] ?? '-',
                'judul_berkas'         => $judul_berkas,
                'kurun_waktu'          => $kurun_waktu,
                'no_item'              => $no_item,
                'uraian_informasi'     => $uraian_informasi, // Diubah
                'tanggal'              => $row['tgl_dokumen'],
                'tingkat_perkembangan' => ucfirst($row['tk_perkembangan'] ?? '-'),
                'media_arsip'          => ucfirst($row['media_simpan'] ?? '-'),
                'kondisi_arsip'         => 'baik',
                'jumlah'               => $row['jumlah'],
                'jangka_simpan_nasib'  => ($row['umur_aktif'] ?? '-') . ' / ' . ucfirst($row['nasib_akhir'] ?? '-'),
                'klasifikasi_keamanan' => ucfirst($row['skkad'] ?? '-'),
                'kategori_arsip'       => ucfirst($row['status_arsip'] ?? '-'),
                'lokasi_simpan'        => $lokasi_simpan_output, // Diubah
                'no_box'               => $no_box_item,
                'keterangan'           => 'Baik' // Diubah menjadi nilai statis "Baik"
            ];
        }

        return ['nama_es2' => $nama_es2, 'laporanData' => $formattedData];
    }
    public function index()
    {
        $report = $this->getLaporanData();
        $data = [
            'title'         => 'Laporan Arsip Aktif',
            'laporanData'   => $report['laporanData'],
            'nama_es2'      => $report['nama_es2'],
            'tanggal_mulai' => $this->request->getGet('tanggal_mulai'),
            'tanggal_akhir' => $this->request->getGet('tanggal_akhir'),
            // --- TAMBAHAN BARU: Kirim opsi Es2 untuk filter ---
            'es2_filter_options' => $this->unitKerjaEs2Model->orderBy('nama_es2', 'ASC')->findAll(),
            'current_es2_filter_id' => $this->request->getGet('es2_id'),
        ];
        return view('laporan/index', $data);
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
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'DAFTAR ARSIP AKTIF');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Identitas Unit Pengolah
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PENGOLAH: ' . strtoupper($nama_es2));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Tabel (mulai dari baris 4)
        $headers = [
            'No Berkas',
            'Kode Klasifikasi',
            'Judul Berkas',
            'Kurun Waktu',
            'No. Item',
            'Uraian Informasi',
            'Tanggal',
            'Tingkat Perkembangan',
            'Media Arsip',
            'Jumlah',
            'Jangka Simpan dan Nasib Akhir',
            'Klasifikasi Keamanan dan Akses Arsip',
            'Kategori Arsip',
            'Lokasi Simpan',
            'No Boks',
            'Keterangan'
        ];
        // Menggunakan array_values untuk memastikan data adalah indexed array
        $sheet->fromArray(array_values($headers), NULL, 'A4');

        // Mengisi data (mulai dari baris 5)
        $sheet->fromArray($laporanData, NULL, 'A5');

        // Styling
        $sheet->getStyle('A4:P4')->getFont()->setBold(true);
        foreach (range('A', 'P') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Blok Tanda Tangan
        $lastRow = $sheet->getHighestRow();
        $signatureRow = $lastRow + 3;
        $sheet->setCellValue('B' . $signatureRow, 'Disusun Oleh:');
        $sheet->setCellValue('O' . $signatureRow, 'Mengetahui:');
        $sheet->setCellValue('B' . ($signatureRow + 4), '..................................');
        $sheet->setCellValue('O' . ($signatureRow + 4), '..................................');

        // Output ke browser
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_arsip_aktif_' . date('YmdHis') . '.xlsx';
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
            'title'       => 'Laporan Arsip Aktif',
            'laporanData' => $report['laporanData'],
            'nama_es2'    => $report['nama_es2'],
        ];
        $html = view('laporan/pdf_template', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        $filename = 'laporan_arsip_aktif_' . date('YmdHis') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => 1]);
    }
}
