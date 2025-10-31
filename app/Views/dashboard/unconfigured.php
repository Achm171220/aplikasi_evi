<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center mt-5">
        <div class="col-lg-10">

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <strong>Peringatan!</strong> <?= session()->getFlashdata('warning') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg border-danger border-3">
                <div class="card-header bg-danger text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-user-lock me-2"></i>Akses Ditangguhkan Sementara</h4>
                </div>
                <div class="card-body p-5">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <i class="fas fa-exclamation-triangle fa-5x text-danger opacity-75"></i>
                        </div>
                        <div class="col-md-10">
                            <h3 class="card-title text-dark">Selamat Datang, <?= esc($username) ?>.</h3>
                            <p class="lead">Akun Anda berhasil diautentikasi (login sukses), namun akses ke fitur inti sistem arsip dibatasi.</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="text-primary mb-3">Detail Status Akun:</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><strong>Nama Pengguna:</strong> <?= esc($username) ?></li>
                        <li class="list-group-item"><strong>Email Terdaftar:</strong> <?= esc($user_email ?? 'Email Tidak Tersedia') ?></li>
                        <li class="list-group-item"><strong>Peran Akses:</strong> <span class="badge bg-secondary"><?= esc(ucfirst($user_role ?? 'Role Tidak Tersedia')) ?></span></li>
                        <li class="list-group-item bg-light text-danger fw-bold"><strong>Status Unit Kerja:</strong> Belum Ditautkan ke Unit Eselon (Wajib)</li>
                    </ul>

                    <p class="text-dark"><strong>Tindakan yang Perlu Dilakukan:</strong></p>
                    <p>Mohon segera hubungi **Administrator Sistem** atau **Pengelola Kearsipan Unit Induk Anda** (ESELON 2) dan informasikan status akun ini agar mereka dapat menautkan Anda ke Unit Kerja (Eselon 3 atau Eselon 2) melalui modul **Hak Fitur**.</p>
                </div>
                <div class="card-footer text-end bg-light">
                    <a href="<?= base_url('logout') ?>" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>