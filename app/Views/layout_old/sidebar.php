<?php
// Ambil string URI saat ini dan role user
$current_uri = uri_string();
$role_access = session()->get('role_access');
?>
<nav id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-light">
    <div class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
        <span class="fs-4 fw-bold">Menu Navigasi</span>
    </div>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">

        <!-- MENU UNTUK SEMUA ROLE (USER, ADMIN, SUPERADMIN) -->
        <li>
            <a href="<?= site_url('dashboard') ?>" class="nav-link <?= ($current_uri == 'dashboard' || $current_uri == '') ? 'active' : 'link-dark' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <!-- === Arsip Aktif === -->
        <?php $is_arsip_aktif = str_contains($current_uri, 'item-aktif') || str_contains($current_uri, 'berkas-aktif') || str_contains($current_uri, 'laporan-aktif'); // Ganti 'laporan-aktif' jika URL-nya berbeda 
        ?>
        <li>
            <a href="#arsip-aktif-collapse" data-bs-toggle="collapse" class="nav-link link-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-folder-check me-2"></i> Arsip Aktif</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= $is_arsip_aktif ? 'show' : '' ?>" id="arsip-aktif-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                    <li><a href="<?= site_url('item-aktif') ?>" class="nav-link <?= (str_contains($current_uri, 'item-aktif')) ? 'active text-white' : 'link-dark' ?> rounded">Item</a></li>
                    <li><a href="<?= site_url('berkas-aktif') ?>" class="nav-link <?= (str_contains($current_uri, 'berkas-aktif')) ? 'active text-white' : 'link-dark' ?> rounded">Berkas</a></li>
                    <li><a href="<?= site_url('laporan') ?>" class="nav-link <?= (str_contains($current_uri, 'laporan')) ? 'active text-white' : 'link-dark' ?> rounded">Laporan</a></li>
                </ul>
            </div>
        </li>

        <!-- === Arsip Inaktif === -->
        <!-- === Arsip Aktif === -->
        <?php $is_arsip_inaktif = str_contains($current_uri, 'item-inaktif') || str_contains($current_uri, 'berkas-inaktif') || str_contains($current_uri, 'laporan-inaktif'); // Ganti 'laporan-aktif' jika URL-nya berbeda 
        ?>
        <li>
            <a href="#arsip-inaktif-collapse" data-bs-toggle="collapse" class="nav-link <?= $is_arsip_inaktif ? '' : 'collapsed' ?> link-dark d-flex justify-content-between align-items-center" aria-expanded="<?= $is_arsip_inaktif ? 'true' : 'false' ?>">
                <span><i class="bi bi-archive me-2"></i> Arsip Inaktif</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= $is_arsip_inaktif ? 'show' : '' ?>" id="arsip-inaktif-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                    <!-- Tambahkan juga logika 'active' untuk sub-menu -->
                    <li><a href="<?= site_url('item-inaktif') ?>" class="nav-link <?= (str_contains($current_uri, 'item-inaktif')) ? 'active text-white' : 'link-dark' ?> rounded">Item</a></li>
                    <li><a href="#" class="nav-link link-dark rounded">Berkas</a></li>
                    <li><a href="#" class="nav-link link-dark rounded">Laporan</a></li>
                </ul>
            </div>
        </li>

        <li>
            <a href="#pemindahan-collapse" data-bs-toggle="collapse" class="nav-link <?= str_contains($current_uri, 'pemindahan') ? '' : 'collapsed' ?> link-dark d-flex justify-content-between align-items-center" aria-expanded="<?= str_contains($current_uri, 'pemindahan') ? 'true' : 'false' ?>">
                <span><i class="bi bi-arrow-down-up me-2"></i> Pemindahan</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= str_contains($current_uri, 'pemindahan') ? 'show' : '' ?>" id="pemindahan-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">

                    <!-- ====== MENU UNTUK USER BIASA ====== -->
                    <!-- ====== MENU UNTUK USER BIASA ====== -->
                    <?php if (session()->get('role_access') === 'user'): ?>
                        <li>
                            <a href="<?= site_url('pemindahan/usulan') ?>" class="nav-link <?= (str_contains($current_uri, 'pemindahan/usulan') || $current_uri === 'pemindahan') ? 'active text-white' : 'link-dark' ?> rounded">
                                <i class="bi bi-plus-circle me-1"></i> Buat Usulan Baru
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('pemindahan/pantau') ?>" class="nav-link <?= (str_contains($current_uri, 'pemindahan/pantau')) ? 'active text-white' : 'link-dark' ?> rounded">
                                <i class="bi bi-hourglass-split me-1"></i> Pantau Proses Usulan
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('pemindahan/buat-ba') ?>" class="nav-link <?= (str_contains($current_uri, 'pemindahan/buat-ba')) ? 'active text-white' : 'link-dark' ?> rounded">
                                <i class="bi bi-file-earmark-text me-1"></i> Buat Berita Acara
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- ====== MENU UNTUK ADMIN / SUPERADMIN ====== -->
                    <?php if (has_permission('pemindahan_verifikasi')): ?>
                        <li>
                            <a href="<?= site_url('pemindahan/verifikasi') ?>" class="nav-link <?= (str_contains($current_uri, 'pemindahan/verifikasi')) ? 'active text-white' : 'link-dark' ?> rounded">
                                <i class="bi bi-list-check me-1"></i> Verifikasi Usulan
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('pemindahan/finalisasi') ?>" class="nav-link <?= (str_contains($current_uri, 'pemindahan/finalisasi')) ? 'active text-white' : 'link-dark' ?> rounded">
                                <i class="bi bi-check-circle-fill me-1"></i> Finalisasi & Eksekusi
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- ====== MENU BERSAMA UNTUK SEMUA ROLE ====== -->
                    <hr class="my-1">
                    <li>
                        <a href="<?= site_url('pemindahan/riwayat') ?>" class="nav-link <?= (str_contains($current_uri, 'pemindahan/riwayat')) ? 'active text-white' : 'link-dark' ?> rounded">
                            <i class="bi bi-clock-history me-1"></i> Riwayat Pemindahan
                        </a>
                    </li>

                </ul>
            </div>
        </li>

        <!-- MENU UNTUK ADMIN & SUPERADMIN -->
        <?php if (has_permission('access_master_data')): ?>
            <hr>
            <li>
                <a href="<?= site_url('klasifikasi') ?>" class="nav-link <?= (str_contains($current_uri, 'klasifikasi')) ? 'active' : 'link-dark' ?>">
                    <i class="bi bi-tags-fill me-2"></i> Klasifikasi
                </a>
            </li>
            <li>
                <a href="<?= site_url('jenis-naskah') ?>" class="nav-link <?= (str_contains($current_uri, 'jenis-naskah')) ? 'active' : 'link-dark' ?>">
                    <i class="bi bi-journal-text me-2"></i> Jenis Naskah
                </a>
            </li>
        <?php endif; ?>


        <!-- MENU HANYA UNTUK SUPERADMIN -->
        <?php if (session()->get('role_access') === 'superadmin'): ?>
            <hr>
            <li>
                <a href="<?= site_url('users') ?>" class="nav-link <?= (str_contains($current_uri, 'users')) ? 'active' : 'link-dark' ?>">
                    <i class="bi bi-people-fill me-2"></i> Manajemen User
                </a>
            </li>
            <li>
                <a href="<?= site_url('hak-fitur') ?>" class="nav-link <?= (str_contains($current_uri, 'hak-fitur')) ? 'active' : 'link-dark' ?>">
                    <i class="bi bi-shield-lock-fill me-2"></i> Hak Fitur
                </a>
            </li>

            <?php $is_unit_kerja = str_contains($current_uri, 'unit-kerja'); ?>
            <li>
                <a href="#unit-kerja-collapse" data-bs-toggle="collapse" class="nav-link link-dark d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-building me-2"></i> Unit Kerja</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_unit_kerja ? 'show' : '' ?>" id="unit-kerja-collapse">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                        <li><a href="<?= site_url('unit-kerja-es1') ?>" class="nav-link <?= (str_contains($current_uri, 'unit-kerja-es1')) ? 'active text-white' : 'link-dark' ?> rounded">Eselon 1</a></li>
                        <li><a href="<?= site_url('unit-kerja-es2') ?>" class="nav-link <?= (str_contains($current_uri, 'unit-kerja-es2')) ? 'active text-white' : 'link-dark' ?> rounded">Eselon 2</a></li>
                        <li><a href="<?= site_url('unit-kerja-es3') ?>" class="nav-link <?= (str_contains($current_uri, 'unit-kerja-es3')) ? 'active text-white' : 'link-dark' ?> rounded">Eselon 3</a></li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <hr>
        <li>
            <a href="#" class="nav-link link-dark">
                <i class="bi bi-gear-fill me-2"></i> Pengaturan
            </a>
        </li>
    </ul>
    <hr>
    <div>
        <a href="<?= site_url('logout') ?>" class="nav-link text-danger">
            <i class="bi bi-box-arrow-left me-2"></i> Logout
        </a>
    </div>
</nav>