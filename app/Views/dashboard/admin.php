<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Dashboard Administrator</h1>

    <?php if (isset($data_kosong)): ?>
        <div class="alert alert-danger shadow-sm">
            <h4 class="alert-heading">Akses Dibatasi!</h4>
            <p>Akun Administrator Anda belum terkonfigurasi ke Unit Eselon 2 manapun.</p>
        </div>
    <?php else: ?>
        <!-- === WELCOME CARD & UNIT INFO === -->
        <div class="card shadow-sm border-0 mb-5 p-3 bg-light-info">
            <div class="card-body p-0">
                <h2 class="fw-light text-primary mb-1">Selamat Datang, Admin!</h2>
                <p class="mb-0 text-dark-50">Unit yang dikelola:
                    <strong class="text-primary"><?= esc($nama_es2_admin) ?></strong>
                </p>
            </div>
        </div>

        <!-- --- BARIS 1 & 2: WIDGET STATISTIK (6 KOLOM GRID) --- -->
        <h4 class="mb-3 text-secondary">STATISTIK KEARSIPAN UNIT</h4>
        <div class="row mb-5">

            <!-- Widget 1.1: Total Item Aktif -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="icon-circle soft-primary"><i class="fas fa-archive fa-lg"></i></div>
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL ITEM (Aktif)</div>
                            <h4 class="fw-bolder mb-0 text-primary"><?= number_format($total_item_aktif) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 1.2: Total Berkas Aktif -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="icon-circle soft-success"><i class="fas fa-folder fa-lg"></i></div>
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL BERKAS (Aktif)</div>
                            <h4 class="fw-bolder mb-0 text-success"><?= number_format($total_berkas_aktif) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 1.3: Persentase Pemberkasan Aktif -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body p-4">
                        <div class="text-xxs text-muted text-uppercase mb-2">PEMBERKASAN AKTIF</div>
                        <h4 class="fw-bolder mb-2 text-danger"><?= $persentase_aktif ?>%</h4>
                        <div class="progress progress-bar-sm">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $persentase_aktif ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2.1: Total Item Inaktif -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="icon-circle soft-info"><i class="fas fa-history fa-lg"></i></div>
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL ITEM (Inaktif)</div>
                            <h4 class="fw-bolder mb-0 text-info"><?= number_format($total_item_inaktif) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2.2: Total Berkas Inaktif -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="icon-circle soft-secondary"><i class="fas fa-box-open fa-lg"></i></div>
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL BERKAS (Inaktif)</div>
                            <h4 class="fw-bolder mb-0 text-secondary"><?= number_format($total_berkas_inaktif) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2.3: Persentase Pemberkasan Inaktif -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-body p-4">
                        <div class="text-xxs text-muted text-uppercase mb-2">PEMBERKASAN INAKTIF</div>
                        <h4 class="fw-bolder mb-2 text-warning"><?= $persentase_inaktif ?>%</h4>
                        <div class="progress progress-bar-sm">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $persentase_inaktif ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- --- BARIS 3: REKAPITULASI ES3 & NILAI PENGAWASAN --- -->
        <div class="row">

            <!-- Kolom Kiri (7/12): Rekapitulasi Item Aktif dan Inaktif per Eselon 3 -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Rekapitulasi Item Arsip (Aktif & Inaktif) per Unit Eselon 3</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover" id="rekap-es3-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Unit Eselon 3</th>
                                        <th class="text-center">Item Aktif</th>
                                        <th class="text-center">Item Inaktif</th>
                                        <th class="text-center">Total Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rekap_per_es3 as $rekap):
                                        $total_unit = $rekap['jumlah_item_aktif'] + $rekap['jumlah_item_inaktif'];
                                    ?>
                                        <tr>
                                            <td><?= esc($rekap['nama_es3']) ?></td>
                                            <td class="text-center text-primary fw-bold"><?= number_format($rekap['jumlah_item_aktif']) ?></td>
                                            <td class="text-center text-secondary fw-bold"><?= number_format($rekap['jumlah_item_inaktif']) ?></td>
                                            <td class="text-center bg-light fw-bold"><?= number_format($total_unit) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan (5/12): Nilai Pengawasan & Pemindahan -->
            <div class="col-lg-5 mb-4">

                <!-- Nilai Pengawasan Card -->
                <?php
                $np = $nilai_pengawasan;
                $skor = $np['skor'] ?? 0;
                $kategori = $np['kategori'] ?? 'N/A (Belum Dinilai)';
                $tahun = $np['tahun'] ?? date('Y');

                // Menentukan warna berdasarkan kategori skor
                $cardClass = match (substr($kategori, 0, 2)) {
                    'AA' => 'bg-success',
                    'A ' => 'bg-info',
                    'BB' => 'bg-primary',
                    'B ' => 'bg-warning',
                    default => 'bg-danger',
                };
                ?>
                <div class="card shadow border-0 mb-4 text-white <?= $cardClass ?> nilai-pengawasan-card">
                    <div class="card-header py-3 border-0 bg-transparent">
                        <h6 class="m-0 font-weight-bold"><i class="fas fa-medal me-2"></i>Nilai Pengawasan Arsip (<?= esc($tahun) ?>)</h6>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <?php if ($np): ?>
                            <h1 class="display-3 fw-bold mb-1 text-white"><?= number_format($skor, 2) ?></h1>
                            <p class="h5 mb-3 fw-bold text-uppercase text-white opacity-75">Kategori: <?= esc($kategori) ?></p>
                            <p class="small text-white opacity-50">Dicatat oleh: <?= esc($np['user_name'] ?? 'Sistem') ?></p>
                        <?php else: ?>
                            <p class="h5 mb-3 fw-bold text-uppercase text-white opacity-75">BELUM DIINPUT</p>
                            <p class="text-white opacity-50 small">Input nilai segera untuk tahun <?= date('Y') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="<?= site_url('nilai-pengawasan') ?>" class="btn btn-sm btn-outline-primary w-100">Kelola Nilai</a>
                    </div>
                </div>

                <!-- Ringkasan Proses Pemindahan -->
                <h6 class="mt-4 mb-3 text-gray-600">Ringkasan Proses Pemindahan</h6>
                <div class="row">
                    <!-- Usulan Baru dan Diproses -->
                    <div class="col-6 mb-3">
                        <div class="card card-body text-center shadow-sm h-100 border-start border-warning border-3">
                            <div class="text-warning text-uppercase small">Usulan Baru</div>
                            <div class="h5 fw-bold mb-0"><?= $jumlah_usulan ?> Berkas</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card card-body text-center shadow-sm h-100 border-start border-info border-3">
                            <div class="text-info text-uppercase small">Sedang Diproses</div>
                            <div class="h5 fw-bold mb-0"><?= $jumlah_diproses ?> Berkas</div>
                        </div>
                    </div>
                    <!-- Ditolak dan Dipindahkan -->
                    <div class="col-6 mb-3">
                        <div class="card card-body text-center shadow-sm h-100 border-start border-danger border-3">
                            <div class="text-danger text-uppercase small">Usulan Ditolak</div>
                            <div class="h5 fw-bold mb-0"><?= $jumlah_ditolak ?> Berkas</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card card-body text-center shadow-sm h-100 border-start border-success border-3">
                            <div class="text-success text-uppercase small">Telah Dipindahkan</div>
                            <div class="h5 fw-bold mb-0"><?= $jumlah_dipindahkan ?> Berkas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables untuk tabel rekap ES3
        $('#rekap-es3-table').DataTable({
            "order": [
                [3, "desc"]
            ], // Urutkan berdasarkan Total Item
            "pageLength": 5
        });
    });
</script>
<?= $this->endSection() ?>