<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($pageTitle) ?></h1>

    <div class="alert alert-primary shadow-sm border-0">
        <p class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2"></i>Ringkasan Data Kearsipan Seluruh Unit Eselon 2 (Global)</p>
    </div>

    <!-- --- BARIS 1: GLOBAL WIDGETS --- -->
    <div class="row mb-5">

        <!-- Total Item Aktif -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100 p-2 bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="widget-icon-circle soft-blue me-3"><i class="fas fa-file-alt fa-lg"></i></div>
                    <div>
                        <div class="text-xs text-muted mb-1">TOTAL ITEM (AKTIF)</div>
                        <h4 class="fw-bolder mb-0 text-primary"><?= number_format($global_total_item_aktif) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Berkas Aktif -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100 p-2 bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="widget-icon-circle soft-green me-3"><i class="fas fa-folder-open fa-lg"></i></div>
                    <div>
                        <div class="text-xs text-muted mb-1">TOTAL BERKAS (AKTIF)</div>
                        <h4 class="fw-bolder mb-0 text-success"><?= number_format($global_total_berkas_aktif) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Item Inaktif -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100 p-2 bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="widget-icon-circle soft-secondary me-3"><i class="fas fa-archive fa-lg"></i></div>
                    <div>
                        <div class="text-xs text-muted mb-1">TOTAL ITEM (INAKTIF)</div>
                        <h4 class="fw-bolder mb-0 text-secondary"><?= number_format($global_total_item_inaktif) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Berkas Inaktif -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100 p-2 bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="widget-icon-circle soft-danger me-3"><i class="fas fa-box-open fa-lg"></i></div>
                    <div>
                        <div class="text-xs text-muted mb-1">TOTAL BERKAS (INAKTIF)</div>
                        <h4 class="fw-bolder mb-0 text-danger"><?= number_format($global_total_berkas_inaktif) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- --- AKHIR BARIS CARD GLOBAL --- -->

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Perbandingan Kinerja Unit Eselon 2 (Tahun <?= esc($tahun_data) ?>)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-rekap-pimpinan" class="table table-bordered table-hover table-rekap table-sm" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 30%;">Kode ES1 - Unit Eselon 2</th> <!-- HEADER BARU -->
                            <th class="text-center" style="width: 15%;">Item Aktif Tercatat</th>
                            <th class="text-center" style="width: 15%;">Item Inaktif Tercatat</th>
                            <th class="text-center" style="width: 15%;">Nilai Pengawasan (Skor)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($rekap_unit as $unit):
                            $skor_np = $unit['nilai_pengawasan'] ?? null;
                            $kategori_np = $unit['kategori_np'];

                            $np_display = '<span class="badge soft-danger">Belum Dinilai</span>';

                            if ($skor_np !== null) {
                                $np_class = match (substr($kategori_np, 0, 2)) {
                                    'AA' => 'soft-primary',
                                    'A ' => 'soft-info',
                                    'BB' => 'soft-success',
                                    'B ' => 'soft-warning',
                                    default => 'soft-danger',
                                };
                                $np_display = '<span class="fw-bold me-1">' . number_format($skor_np, 2) . '</span><br>' .
                                    '<span class="badge ' . $np_class . '">' . esc($kategori_np) . '</span>';
                            }
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($unit['nama_es2_lengkap']) ?></td>

                                <td class="text-center text-primary fw-bold">
                                    <?= number_format($unit['total_item_aktif']) ?>
                                </td>

                                <td class="text-center text-secondary fw-bold">
                                    <?= number_format($unit['total_item_inaktif']) ?>
                                </td>

                                <td class="text-center">
                                    <?= $np_display ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables untuk sorting dan pencarian
        $('#table-rekap-pimpinan').DataTable({
            "order": [
                [2, "desc"]
            ], // Urutkan berdasarkan Item Aktif Terbanyak
            "pageLength": 10,
            "searching": true,
            "lengthChange": true,
            "info": true
        });
    });
</script>
<?= $this->endSection() ?>