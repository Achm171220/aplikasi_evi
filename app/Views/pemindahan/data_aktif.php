<?= $this->extend('layout_old/main') ?>

<?= $this->section('title') ?>Sistem Pemindahan Arsip BPKP - Data Arsip Aktif<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<style>
    /* Styling khusus untuk tabel DataTables (jika perlu) */
    #dataAktifTable_wrapper .row {
        margin-bottom: 15px;
        /* Jarak antara elemen DataTables */
    }

    #dataAktifTable_filter input {
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_length select {
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }

    /* Column Widths (DataTables akan coba menyesuaikan, tapi ini bisa jadi panduan) */
    .table-data-aktif .col-no-urut {
        width: 5%;
    }

    .table-data-aktif .col-klasifikasi-umur {
        width: 15%;
        white-space: normal;
    }

    /* Changed name */
    .table-data-aktif .col-no-dokumen {
        width: 10%;
    }

    .table-data-aktif .col-judul {
        width: 25%;
        white-space: normal;
    }

    .table-data-aktif .col-tgl-dokumen {
        width: 10%;
    }

    .table-data-aktif .col-rekomendasi {
        width: 10%;
    }

    .table-data-aktif .col-aksi {
        width: 8%;
    }

    /* Jika ada teks panjang yang perlu wrap di kolom lain */
    .table-data-aktif .wrap-text {
        white-space: normal;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Data Arsip Aktif</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Daftar lengkap semua arsip yang masih berstatus aktif, termasuk rekomendasi usia inaktif.</p>

        <?php if (empty($items)): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> Tidak ada arsip aktif dalam sistem.
            </div>
        <?php else: ?>
            <table id="dataAktifTable" class="table table-hover table-striped table-bordered align-middle table-data-aktif" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th class="col-no-urut text-center">No</th>
                        <th class="col-klasifikasi-umur">Klasifikasi - Umur Aktif</th> <!-- Changed header -->
                        <th class="col-no-dokumen">No. Dokumen</th>
                        <th class="col-judul">Judul Dokumen</th>
                        <th class="col-tgl-dokumen">Tgl Dokumen</th>
                        <th class="col-rekomendasi">Rekomendasi</th>
                        <th class="col-aksi text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="col-no-urut text-center"><?= $no++ ?></td>
                            <td class="col-klasifikasi-umur wrap-text">
                                <?= esc($item['nama_klasifikasi'] ?? 'Tidak Diketahui') ?>
                                <?php if (!empty($item['umur_aktif'])): ?>
                                    <br><small class="text-muted">(Umur Aktif: <?= esc($item['umur_aktif']) ?>)</small> <!-- Display umur_aktif as is -->
                                <?php endif; ?>
                            </td>
                            <td class="col-no-dokumen"><?= esc($item['no_dokumen']) ?></td>
                            <td class="col-judul wrap-text"><?= esc($item['judul_dokumen']) ?></td>
                            <td class="col-tgl-dokumen"><?= esc($item['tgl_dokumen'] ? date('d-m-Y', strtotime($item['tgl_dokumen'])) : '-') ?></td>
                            <td class="col-rekomendasi">
                                <?php
                                $recommendation = 'N/A';
                                $recBadgeClass = 'bg-secondary';
                                if (!empty($item['tgl_dokumen']) && !empty($item['umur_aktif'])) {
                                    $docDateStr = $item['tgl_dokumen'];
                                    $umurAktifStr = $item['umur_aktif'];

                                    // Fungsi JS untuk perhitungan rekomendasi akan dipanggil di DataTables
                                    // Untuk render awal, kita bisa berikan placeholder atau hitung sederhana
                                    // Namun, perhitungan yang akurat akan dilakukan oleh DataTables di JS
                                    $recommendation = 'Memuat rekomendasi...';
                                    $recBadgeClass = 'bg-warning';
                                }
                                ?>
                                <span class="badge <?= $recBadgeClass ?> recommendation-badge"
                                    data-doc-date="<?= esc($item['tgl_dokumen']) ?>"
                                    data-umur-aktif="<?= esc($item['umur_aktif']) ?>">
                                    <?= $recommendation ?>
                                </span>
                            </td>
                            <td class="col-aksi text-center">
                                <a href="<?= base_url('pemindahan/detail/' . esc($item['id'])) ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Detail">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Tombol Kembali di bawah tabel -->
            <div class="d-flex justify-content-start mt-3">
                <a class="btn btn-outline-danger" href="javascript:history.back()"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<!-- jQuery (Penting: DataTables memerlukan jQuery) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi untuk mengekstrak angka tahun dari string umur_aktif
        function extractYears(umurAktifStr) {
            const matches = umurAktifStr.match(/(\d+)\s*Tahun/i);
            if (matches && matches[1]) {
                return parseInt(matches[1]);
            }
            return null; // Tidak dapat mengekstrak angka
        }

        // Fungsi untuk menghitung rekomendasi
        function getRecommendation(docDateStr, umurAktifStr) {
            if (!docDateStr || !umurAktifStr) {
                return {
                    text: 'N/A',
                    class: 'bg-secondary'
                };
            }

            const docDate = new Date(docDateStr);
            if (isNaN(docDate.getTime())) {
                return {
                    text: 'Tanggal tidak valid',
                    class: 'bg-warning'
                };
            }

            const activeYears = extractYears(umurAktifStr);
            if (activeYears !== null) {
                const maturityDate = new Date(docDate.getFullYear() + activeYears, docDate.getMonth(), docDate.getDate());
                const today = new Date();

                if (today >= maturityDate) {
                    return {
                        text: 'Sudah Inaktif',
                        class: 'bg-danger'
                    };
                } else {
                    return {
                        text: 'Belum Inaktif',
                        class: 'bg-success'
                    };
                }
            } else if (umurAktifStr.includes('Selama Berlaku')) {
                return {
                    text: 'Selama Berlaku',
                    class: 'bg-info'
                }; // Tambahkan badge info untuk ini
            } else {
                return {
                    text: 'Kondisi Khusus',
                    class: 'bg-warning'
                }; // Untuk "Setelah Penerbitan Laporan" dll.
            }
        }

        // Inisialisasi Tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Inisialisasi DataTables
        $('#dataAktifTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" // Bahasa Indonesia
            },
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "columnDefs": [{
                    "orderable": false,
                    "targets": [0, -1]
                }, // Disable ordering for 'No' and 'Aksi' columns
                {
                    // Kolom Rekomendasi (index 5) akan di-render menggunakan fungsi JS
                    "targets": 5,
                    "createdCell": function(td, cellData, rowData, row, col) {
                        const docDateStr = $(td).find('.recommendation-badge').data('doc-date');
                        const umurAktifStr = $(td).find('.recommendation-badge').data('umur-aktif');
                        const recommendation = getRecommendation(docDateStr, umurAktifStr);

                        $(td).find('.recommendation-badge')
                            .text(recommendation.text)
                            .removeClass('bg-secondary bg-warning bg-success bg-danger bg-info') // Hapus kelas default
                            .addClass(recommendation.class);
                    }
                }
            ]
        });
    });
</script>
<?= $this->endSection() ?>