<?= $this->extend('layout/template') ?>

<?= $this->section('styles') ?>
<style>
    /* Styling Kustom untuk Manager Dashboard */
    .bg-light-primary {
        background-color: #eaf6ff;
        border-radius: 8px;
    }

    .card {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    /* Soft Icon Circle Styling */
    .icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Warna Soft Icons */
    .soft-blue {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .soft-green {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .soft-orange {
        background-color: rgba(253, 126, 20, 0.1);
        color: #fd7e14;
    }

    .soft-red {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .text-xxs {
        font-size: 0.7rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Dashboard Manager</h1>

    <!-- === WELCOME CARD === -->
    <div class="card shadow-sm border-0 mb-5 p-3 bg-light-primary">
        <div class="card-body p-4">
            <h2 class="fw-light text-primary mb-1">Selamat Datang, Manager!</h2>
            <p class="mb-0 text-dark-50">Anda memiliki hak akses untuk mengelola data master dan konfigurasi sistem.</p>
        </div>
    </div>

    <!-- --- BARIS WIDGET UTAMA (DATA MASTER) --- -->
    <h4 class="mb-3 text-secondary">STATISTIK DATA MASTER</h4>
    <div class="row mb-5">

        <!-- Widget 1: Total Pengguna -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL PENGGUNA</div>
                            <h2 class="fw-bolder mb-0 text-primary"><?= number_format($total_users) ?></h2>
                        </div>
                        <div class="icon-circle soft-blue"><i class="fas fa-users fa-lg"></i></div>
                    </div>
                </div>
                <a href="<?= site_url('users') ?>" class="card-footer bg-light text-primary small text-decoration-none">
                    Kelola Pengguna <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Widget 2: Total Unit Kerja -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL UNIT KERJA</div>
                            <h2 class="fw-bolder mb-0 text-success"><?= number_format($total_es1 + $total_es2 + $total_es3) ?></h2>
                            <small class="text-muted">(ES1: <?= $total_es1 ?>, ES2: <?= $total_es2 ?>, ES3: <?= $total_es3 ?>)</small>
                        </div>
                        <div class="icon-circle soft-green"><i class="fas fa-sitemap fa-lg"></i></div>
                    </div>
                </div>
                <a href="<?= site_url('unit-kerja-es1') ?>" class="card-footer bg-light text-success small text-decoration-none">
                    Kelola Unit Kerja <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Widget 3: Total Klasifikasi -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL KLASIFIKASI</div>
                            <h2 class="fw-bolder mb-0 text-orange"><?= number_format($total_klasifikasi) ?></h2>
                        </div>
                        <div class="icon-circle soft-orange"><i class="fas fa-tags fa-lg"></i></div>
                    </div>
                </div>
                <a href="<?= site_url('klasifikasi') ?>" class="card-footer bg-light text-orange small text-decoration-none">
                    Kelola Klasifikasi <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Widget 4: Total Jenis Naskah -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xxs text-muted text-uppercase mb-1">TOTAL JENIS NASKAH</div>
                            <h2 class="fw-bolder mb-0 text-danger"><?= number_format($total_jenis_naskah) ?></h2>
                        </div>
                        <div class="icon-circle soft-red"><i class="fas fa-file-alt fa-lg"></i></div>
                    </div>
                </div>
                <a href="<?= site_url('jenis-naskah') ?>" class="card-footer bg-light text-danger small text-decoration-none">
                    Kelola Jenis Naskah <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- --- PENGINGAT TUGAS --- -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tugas Utama Manager</h6>
        </div>
        <div class="card-body">
            <p>Tugas Anda adalah memastikan data master sistem EVI selalu terorganisir dan mutakhir. Modul yang dapat Anda kelola:</p>
            <ul>
                <li><strong>Manajemen Pengguna:</strong> Mengatur peran (role) dan status pengguna.</li>
                <li><strong>Hak Fitur:</strong> Menautkan pengguna ke unit kerja yang sesuai.</li>
                <li><strong>Manajemen Unit Kerja:</strong> Mengelola struktur organisasi Eselon 1, 2, dan 3.</li>
                <li><strong>Klasifikasi Arsip & Jenis Naskah:</strong> Mengelola daftar referensi utama kearsipan.</li>
                <li><strong>Nilai Pengawasan:</strong> Menginput dan mengelola nilai pengawasan tahunan untuk setiap unit.</li>
            </ul>
        </div>
    </div>

</div>
<?= $this->endSection() ?>