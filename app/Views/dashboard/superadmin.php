<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($pageTitle ?? 'Dashboard') ?></h1>

    <!-- BARIS 1: REKAPITULASI ARSIP AKTIF -->
    <h5 class="mb-3 text-gray-600">Statistik Arsip Aktif</h5>
    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-start-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Item Aktif</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($total_item_aktif) ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-file-earmark-text-fill fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-start-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Berkas Aktif</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($total_berkas_aktif) ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-folder-fill fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-start-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Persentase Pemberkasan</div>
                            <div class="row g-0 align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 me-3 fw-bold text-gray-800"><?= $persentase_aktif ?>%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $persentase_aktif ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto"><i class="bi bi-clipboard-data-fill fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS 2: REKAPITULASI ARSIP INAKTIF (Gantilah dengan data asli jika sudah ada) -->
    <h5 class="mb-3 text-gray-600">Statistik Arsip Inaktif</h5>
    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-start-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-secondary text-uppercase mb-1">Total Item Inaktif</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($total_item_inaktif) ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-archive-fill fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-start-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-dark text-uppercase mb-1">Total Berkas Inaktif</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($total_berkas_inaktif) ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-inbox-fill fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-start-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Persentase Pemberkasan</div>
                            <div class="row g-0 align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 me-3 fw-bold text-gray-800"><?= $persentase_inaktif ?>%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $persentase_aktif ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto"><i class="bi bi-clipboard-data-fill fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Anda bisa menambahkan card persentase inaktif di sini -->
    </div>

    <hr class="my-4">

    <!-- BARIS 3: REKAPITULASI DATA MASTER -->
    <h5 class="mb-3 text-gray-600">Data Master & Pengguna</h5>
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-body text-center shadow-sm h-100">
                <div class="text-xs text-danger text-uppercase mb-1">Pengguna</div>
                <div class="h4 fw-bold mb-0"><?= number_format($total_users) ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-body text-center shadow-sm h-100">
                <div class="text-xs text-warning text-uppercase mb-1">Klasifikasi</div>
                <div class="h4 fw-bold mb-0"><?= number_format($total_klasifikasi) ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-body text-center shadow-sm h-100">
                <div class="text-xs text-primary text-uppercase mb-1">Jenis Naskah</div>
                <div class="h4 fw-bold mb-0"><?= number_format($total_jenis_naskah) ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-body text-center shadow-sm h-100">
                <div class="text-xs text-success text-uppercase mb-1">Unit Eselon (1/2/3)</div>
                <div class="h4 fw-bold mb-0"><?= $total_es1 ?> / <?= $total_es2 ?> / <?= $total_es3 ?></div>
            </div>
        </div>
    </div>

    <!-- BARIS 4: RINGKASAN PROSES PEMINDAHAN -->
    <h5 class="mb-3 mt-4 text-gray-600">Ringkasan Proses Pemindahan Arsip</h5>
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="card card-body text-center shadow-sm h-100">
                <div class="text-primary text-uppercase small">Usulan Baru</div>
                <div class="h4 fw-bold mb-0"><?= $jumlah_usulan ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card card-body text-center shadow-sm h-100">
                <div class="text-danger text-uppercase small">Usulan Ditolak</div>
                <div class="h4 fw-bold mb-0"><?= $jumlah_ditolak ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card card-body text-center shadow-sm h-100">
                <div class="text-info text-uppercase small">Sedang Diproses</div>
                <div class="h4 fw-bold mb-0"><?= $jumlah_diproses ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card card-body text-center shadow-sm h-100">
                <div class="text-success text-uppercase small">Telah Dipindahkan</div>
                <div class="h4 fw-bold mb-0"><?= $jumlah_dipindahkan ?></div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <!-- TABEL REKAP PER ESELON 2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Jumlah Item Aktif per Unit Eselon 2</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="rekap-table">
                    <thead>
                        <tr>
                            <th>Nama Unit Eselon 2</th>
                            <th class="text-center">Jumlah Item Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap_per_es2 as $rekap): ?>
                            <tr>
                                <td><?= esc($rekap['nama_es2']) ?></td>
                                <td class="text-center"><?= number_format($rekap['jumlah_item_aktif']) ?></td>
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
    // Tambahkan DataTables ke tabel rekap agar bisa di-sort dan dicari
    $(document).ready(function() {
        $('#rekap-table').DataTable({
            "order": [
                [1, "desc"]
            ], // Urutkan berdasarkan jumlah item terbanyak
            "pageLength": 10
        });
    });
</script>
<?= $this->endSection() ?>