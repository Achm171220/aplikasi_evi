<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <!-- Tampilkan Notifikasi -->
    <?php if ($session->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $session->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($session->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $session->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($session->getFlashdata('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= $session->getFlashdata('warning') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Kolom Kiri: Informasi Profil (DARI API) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold text-light">Data Pegawai (Dari MAP BPKP)</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="<?= site_url('images/user.png'); ?>" class="rounded-circle mb-3" alt="Avatar" width="120" height="120">
                        <h4 class="card-title mb-0"><?= esc($pegawai_nama) ?></h4>
                        <p class="text-muted small">NIP: <?= esc($pegawai_nip) ?></p>
                    </div>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <td style="width: 30%;"><strong>Email Dinas</strong></td>
                            <td>: <?= esc($pegawai_email) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Jabatan API</strong></td>
                            <td>: <?= esc($pegawai_jabatan) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Unit Kerja API</strong></td>
                            <td>: <?= esc($pegawai_unit_api) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Hak Akses Lokal (Data Aplikasi) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">Hak Akses dan Peran Lokal</h6>
                </div>
                <div class="card-body">
                    <h5 class="mb-3 text-center">Otorisasi di Aplikasi EVI</h5>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <td style="width: 30%;"><strong>Akses (Role)</strong></td>
                            <td>: <span class="badge bg-primary rounded-pill px-3 py-1"><?= esc(ucfirst($role_access_lokal)) ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Peran Fungsional</strong></td>
                            <td>: <span class="badge bg-secondary rounded-pill px-3 py-1"><?= esc(ucfirst($role_jabatan_fungsional) ?? 'Belum Ditetapkan') ?></span></td>
                        </tr>
                    </table>

                    <hr>
                    <h6 class="mt-4 mb-2">Penautan Unit Kerja (Filtering Data)</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td style="width: 30%;"><strong>Level Unit</strong></td>
                            <td>: <span class="badge bg-success px-2 py-1"><?= esc($level_unit_kerja) ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Unit Terikat</strong></td>
                            <td>: <strong class="text-info"><?= esc($nama_unit_kerja_lokal) ?></strong></td>
                        </tr>
                    </table>

                </div>
                <div class="card-footer bg-light">
                    <p class="text-muted small mb-0">Informasi ini menentukan sejauh mana data arsip yang dapat Anda akses dan kelola.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>