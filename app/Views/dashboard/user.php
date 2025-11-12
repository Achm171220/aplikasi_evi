<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Dashboard Pengguna</h1>

    <?php if (isset($data_kosong)): ?>
        <div class="alert alert-danger shadow-sm">
            <h4 class="alert-heading">Akses Dibatasi!</h4>
            <p>Akun Anda belum terkonfigurasi ke Unit Eselon manapun.</p>
        </div>
    <?php else: ?>
        <!-- === WELCOME CARD & HAK AKSES === -->
        <div class="card shadow-sm border-0 mb-5 p-3 bg-light-info">
            <div class="card-body p-4">
                <h2 class="fw-light text-primary mb-1">Halo, <?= esc($user_name_full) ?>!</h2>
                <p class="mb-0 text-dark-50">Anda login sebagai <strong class="text-primary"><?= esc(ucfirst($user_role_access)) ?></strong>.</p>
                <h5 class="fw-bold mt-2"><i class="bi bi-building me-2"></i>Unit Kerja: <?= esc($nama_unit_kerja) ?></h5>
                <small class="text-muted">Unit Induk Eselon 2: <?= esc($kode_es2_unit) ?> - <?= esc($nama_es2_unit) ?></small>
            </div>
        </div>

        <!-- --- BARIS 1: REKAPITULASI ARSIP AKTIF (3 KOLOM WIDGET) --- -->
        <h4 class="mb-3 text-secondary">REKAPITULASI ARSIP AKTIF</h4>
        <div class="row mb-5">

            <!-- Widget 1.1: Total Item Aktif -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="icon-circle soft-primary"><i class="fas fa-file-alt fa-lg"></i></div>
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL ITEM (Aktif)</div>
                            <h4 class="fw-bolder mb-0 text-primary"><?= number_format($total_item_aktif_user) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 1.2: Total Berkas Aktif -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="icon-circle soft-success"><i class="fas fa-folder fa-lg"></i></div>
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL BERKAS (Aktif)</div>
                            <h4 class="fw-bolder mb-0 text-success"><?= number_format($total_berkas_aktif_user) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 1.3: Persentase Pemberkasan Aktif (Vertical Progress) -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex justify-content-between">
                        <div class="flex-grow-1 me-3">
                            <div class="text-xxs text-muted text-uppercase mb-2">PEMBERKASAN AKTIF</div>
                            <h1 class="fw-bolder mb-0 text-danger"><?= $persentase_aktif ?>%</h1>
                           
                        </div>

                        <!-- Progress Bar Vertikal -->
                        <div class="vertical-progress-container">
                            <div class="progress progress-vertical">
                                <div class="progress-bar bg-danger progress-bar-vertical"
                                    role="progressbar"
                                    style="height: <?= $persentase_aktif ?>%"
                                    aria-valuenow="<?= $persentase_aktif ?>"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- --- BARIS 2: MONITORING ARSIP INAKTIF & PINDAH --- -->
        <h4 class="mb-3 text-secondary">MONITORING ARSIP INAKTIF & PINDAH</h4>
        <div class="row mb-5">

            <!-- Widget 2.1: Total Item Inaktif -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="icon-circle soft-info"><i class="fas fa-history fa-lg"></i></div>
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL ITEM (Inaktif)</div>
                            <h4 class="fw-bolder mb-0 text-info"><?= number_format($total_item_inaktif_user) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2.2: Usulan Pemindahan -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="icon-circle soft-warning"><i class="fas fa-paper-plane fa-lg"></i></div>
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">USULAN PEMINDAHAN</div>
                            <h4 class="fw-bolder mb-0 text-warning"><?= number_format($jumlah_usulan) ?> Berkas</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2.3: Sedang Diproses (Vertical Progress) -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow border-0 h-100 bg-white">
                    <div class="card-body p-4 d-flex justify-content-between">
                        <div class="flex-grow-1 me-3">
                            <div class="text-xxs text-muted text-uppercase mb-2">ITEM INAKTIF DIBERKASKAN</div>
                            <h1 class="fw-bolder mb-0 text-secondary"><?= $persentase_inaktif ?>%</h1>
                        </div>

                        <!-- Progress Bar Vertikal Inaktif -->
                        <div class="vertical-progress-container">
                            <div class="progress progress-vertical">
                                <div class="progress-bar bg-secondary progress-bar-vertical"
                                    role="progressbar"
                                    style="height: <?= $persentase_inaktif ?>%"
                                    aria-valuenow="<?= $persentase_inaktif ?>"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- --- BARIS 3: CHART & PROFIL KOMPAK --- -->
        <div class="row">

            <!-- Kolom Kiri: Aktivitas Input 1 Tahun Terakhir (Chart) -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-bar-chart-line me-2"></i>Aktivitas Input Arsip Tahun <?= esc($current_year) ?></h6>
                        <span class="badge bg-secondary">Berdasarkan Tgl. Dokumen</span>
                    </div>
                    <div class="card-body">
                        <!-- Kontainer untuk Chart -->
                        <div id="monthly-input-chart"></div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Profil Kompak (CLEAN DESIGN) -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 h-100 bg-white">
                    <div class="card-header py-3 bg-white border-bottom-0">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-person-circle me-2"></i>Profil</h6>
                    </div>
                    <div class="card-body pt-0 text-center">
                        <img src="<?= site_url('images/user.png'); ?>" class="rounded-circle mb-3 border p-1" alt="Avatar" width="80" height="80">
                        <h5 class="card-title fw-bold mb-1"><?= esc($user_name_full) ?></h5>
                        <p class="card-text text-muted small mb-3"><?= esc($user_email_full) ?></p>

                        <span class="badge bg-primary px-3 py-1 me-2 mb-1"><?= esc(ucfirst($user_role_access)) ?></span>
                        <span class="badge bg-info px-3 py-1 mb-1">NIP: <?= esc(session()->get('nip')) ?></span>
                        <p class="text-muted small mt-2">Jabatan: <?= esc($user_jabatan_api) ?></p>

                    </div>
                    <div class="card-footer text-center bg-light">
                        <a href="<?= site_url('profil') ?>" class="btn btn-sm btn-outline-primary w-100">Lihat Detail Profil</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    $(document).ready(function() {
        // --- LOGIKA CHART (PERLU DISKRIPSI LENGKAP) ---
        const chartLabels = <?= json_encode($chart_labels) ?>;
        const chartData = <?= json_encode($chart_data) ?>;
        const currentYear = '<?= esc($current_year) ?>';

        function renderMonthlyChart() {
            var options = {
                series: [{
                    name: "Jumlah Item Dibuat",
                    data: chartData
                }],
                chart: {
                    height: 350,
                    type: 'area',
                    toolbar: {
                        show: false
                    }
                },
                xaxis: {
                    categories: chartLabels,
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Item'
                    },
                    labels: {
                        formatter: function(val) {
                            return val.toFixed(0);
                        }
                    }
                },
                colors: ['#007bff'],
            };

            var chart = new ApexCharts(document.querySelector("#monthly-input-chart"), options);
            chart.render();
        }

        // Hanya render chart jika data tidak kosong
        <?php if (!isset($data_kosong) && array_sum($chart_data) > 0): ?>
            renderMonthlyChart();
        <?php elseif (!isset($data_kosong) && array_sum($chart_data) === 0): ?>
            $('#monthly-input-chart').html('<div class="text-center p-5 text-muted fst-italic">Tidak ada data input arsip yang tercatat di tahun <?= esc($current_year) ?>.</div>');
        <?php endif; ?>

    });
</script>
<?= $this->endSection() ?>