<?php
$uri = service('uri')->setSilent(true);

$current_segment1 = $uri->getSegment(1) ?: 'dashboard';
$current_segment2 = $uri->getSegment(2) ?: '';
$current_segment3 = $uri->getSegment(3) ?: '';

$role_access     = session()->get('role_access');
$user_jabatan  = session()->get('role_jabatan');

if (!function_exists('is_parent_active')) {
    function is_parent_active($current_segment, $sub_segments)
    {
        return in_array($current_segment, $sub_segments, true);
    }
}
?>

<div class="sidebar-container" id="sidebar-wrapper">
    <div class="sidebar-top-section">
        <div class="sidebar-heading border-bottom d-flex justify-content-center align-items-center">
            <a href="<?= site_url('dashboard') ?>">
                <img src="<?= base_url('images/logo.png'); ?>" alt="Logo" style="max-height: 60px; width: auto;">
            </a>
        </div>
        <div class="list-group-container">
            <div class="list-group list-group-flush">

                <!-- ================= MENU UTAMA ================= -->
                <div class="sidebar-heading-menu">MENU UTAMA</div>
                <a href="<?= site_url('dashboard') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-columns-gap me-2"></i> Dashboard
                </a>

                <?php if ($role_access === 'superadmin' || $role_access === 'admin' || $role_access === 'user' || $role_access === 'manager'): ?>
                    <?php
                    // Jabatan yang bisa melihat menu Arsip Inaktif
                    $can_view_arsip_inaktif_by_jabatan = in_array($user_jabatan, ['arsiparis', 'pengelola_arsip', 'sekretaris']);
                    // --- PERBAIKAN DI SINI ---
                    // Izinkan jika jabatannya sesuai, ATAU jika role-nya Superadmin
                    if ($can_view_arsip_inaktif_by_jabatan || $role_access === 'superadmin'): ?>
                        <!-- Arsip Aktif -->
                        <?php $active_arsip_aktif = is_parent_active($current_segment1, ['item-aktif', 'berkas-aktif', 'laporan-aktif']); ?>
                        <a href="#arsip-aktif" data-bs-toggle="collapse" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $active_arsip_aktif ? 'active' : '' ?>">
                            <span><i class="bi bi-folder-check me-2"></i> Arsip Aktif</span>
                            <i class="bi bi-chevron-down arrow-icon"></i>
                        </a>
                        <div class="collapse <?= $active_arsip_aktif ? 'show' : '' ?>" id="arsip-aktif">
                            <div class="submenu-container">
                                <a href="<?= site_url('item-aktif') ?>" class="list-group-item sub-item <?= ($current_segment1 == 'item-aktif') ? 'active' : '' ?>">Item Berkas</a>
                                <a href="<?= site_url('berkas-aktif') ?>" class="list-group-item sub-item <?= ($current_segment1 == 'berkas-aktif') ? 'active' : '' ?>">Berkas</a>
                                <a href="<?= site_url('laporan-aktif') ?>" class="list-group-item sub-item <?= ($current_segment1 == 'laporan-aktif') ? 'active' : '' ?>">Laporan</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Arsip Inaktif -->
                    <?php
                    // Jabatan yang bisa melihat menu Arsip Inaktif
                    $can_view_arsip_inaktif_by_jabatan = in_array($user_jabatan, ['arsiparis', 'pengampu', 'verifikator', 'pimpinan']);
                    // --- PERBAIKAN DI SINI ---
                    // Izinkan jika jabatannya sesuai, ATAU jika role-nya Superadmin
                    if ($can_view_arsip_inaktif_by_jabatan || $role_access === 'superadmin'): ?>
                        <?php $arsip_inaktif_sub_uris = ['item-inaktif', 'berkas-inaktif', 'laporan-inaktif']; ?>
                        <?php $is_arsip_inaktif_parent_active = is_parent_active($current_segment1, $arsip_inaktif_sub_uris); ?>
                        <a href="#arsip-inaktif-collapse" data-bs-toggle="collapse" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $is_arsip_inaktif_parent_active ? 'active' : 'collapsed' ?>" aria-expanded="<?= $is_arsip_inaktif_parent_active ? 'true' : 'false' ?>">
                            <span><i class="bi bi-archive me-2"></i> Arsip Inaktif</span>
                            <i class="bi bi-chevron-down arrow-icon"></i>
                        </a>
                        <div class="collapse <?= $is_arsip_inaktif_parent_active ? 'show' : '' ?>" id="arsip-inaktif-collapse">
                            <div class="submenu-container">
                                <a href="<?= site_url('item-inaktif') ?>" class="list-group-item list-group-item-action sub-item <?= ($current_segment1 == 'item-inaktif') ? 'active' : '' ?>">Item</a>
                                <a href="<?= site_url('berkas-inaktif') ?>" class="list-group-item list-group-item-action sub-item <?= ($current_segment1 == 'berkas-inaktif') ? 'active' : '' ?>">Berkas</a>
                                <a href="<?= site_url('laporan-inaktif') ?>" class="list-group-item list-group-item-action sub-item <?= ($current_segment1 == 'laporan-inaktif') ? 'active' : '' ?>">Laporan</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php
                    if ($role_access !== 'manager') {?>
                        <a href="<?= site_url('pemindahan') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'pemindahan') ? 'active' : '' ?>">
                            <i class="bi bi-collection me-2"></i> Pemindahan
                        </a>
                    <?php } ?>


                    <!-- ================= MANAJEMEN ================= -->
                    <?php if (in_array($role_access, ['superadmin', 'manager'])): ?>
                        <div class="sidebar-heading-menu">MANAJEMEN</div>
                        <a href="<?= site_url('klasifikasi') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'klasifikasi') ? 'active' : '' ?>">
                            <i class="bi bi-tags me-2"></i> Klasifikasi
                        </a>
                        <a href="<?= site_url('users') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'users') ? 'active' : '' ?>">
                            <i class="bi bi-person me-2"></i> Manajemen User
                        </a>

                        <a href="<?= site_url('hak-fitur') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'hak-fitur') ? 'active' : '' ?>">
                            <i class="bi bi-shield me-2"></i> Hak Fitur
                        </a>
                        <a href="<?= site_url('jenis-naskah') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'jenis-naskah') ? 'active' : '' ?>">
                            <i class="bi bi-journal-text me-2"></i> Jenis Naskah
                        </a>
                    <?php elseif (in_array($role_access, ['admin'])): ?>
                        <a href="<?= site_url('users') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'users') ? 'active' : '' ?>">
                            <i class="bi bi-person me-2"></i> Manajemen User
                        </a>
                        <a href="<?= site_url('jenis-naskah') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'jenis-naskah') ? 'active' : '' ?>">
                            <i class="bi bi-journal-text me-2"></i> Jenis Naskah
                        </a>
                    <?php endif; ?>

                    <!-- ================= UNIT KERJA ================= -->
                    <?php if ($role_access === 'superadmin' || $role_access === 'admin' ||$role_access === 'manager'): ?>
                        <?php $active_unit_kerja = is_parent_active($current_segment1, ['unit-kerja-es1', 'unit-kerja-es2', 'unit-kerja-es3']); ?>
                        <a href="#unit-kerja" data-bs-toggle="collapse"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $active_unit_kerja ? 'active' : '' ?>">
                            <span><i class="bi bi-building me-2"></i> Unit Kerja</span>
                            <i class="bi bi-chevron-down arrow-icon"></i>
                        </a>
                        <div class="collapse <?= $active_unit_kerja ? 'show' : '' ?>" id="unit-kerja">
                            <div class="submenu-container">
                                <?php if ($role_access === 'superadmin' || $role_access === 'manager'): ?>
                                    <a href="<?= site_url('unit-kerja-es1') ?>" class="list-group-item sub-item <?= ($current_segment1 == 'unit-kerja-es1') ? 'active' : '' ?>">Eselon 1</a>
                                    <a href="<?= site_url('unit-kerja-es2') ?>" class="list-group-item sub-item <?= ($current_segment1 == 'unit-kerja-es2') ? 'active' : '' ?>">Eselon 2</a>
                                    <a href="<?= site_url('unit-kerja-es3') ?>" class="list-group-item sub-item <?= ($current_segment1 == 'unit-kerja-es3') ? 'active' : '' ?>">Eselon 3</a>
                                    <!-- --- TAMBAHKAN LINK INI --- -->
                                    <a href="<?= site_url('unit-kerja/treeview') ?>" class="list-group-item list-group-item-action sub-item <?= ($current_segment1 == 'unit-kerja' && $current_segment2 == 'treeview') ? 'active' : '' ?>">
                                        Treeview
                                    </a>
                                <?php elseif ($role_access === 'admin' && $user_jabatan === 'arsiparis' || $user_jabatan === 'pengampu'): ?>
                                    <a href="<?= site_url('unit-kerja-es3') ?>" class="list-group-item sub-item <?= ($current_segment1 == 'unit-kerja-es3') ? 'active' : '' ?>">Eselon 3</a>
                                    <!-- --- TAMBAHKAN LINK INI --- -->
                                    <a href="<?= site_url('unit-kerja/treeview') ?>" class="list-group-item list-group-item-action sub-item <?= ($current_segment1 == 'unit-kerja' && $current_segment2 == 'treeview') ? 'active' : '' ?>">
                                        Treeview
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>


                    <!-- ================= FITUR LAIN ================= -->
                    <div class="sidebar-heading-menu">FITUR</div>
                    <a href="<?= site_url('search') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'search') ? 'active' : '' ?>">
                        <i class="bi bi-search me-2"></i> Pencarian
                    </a>
                    <a href="<?= site_url('peminjaman') ?>" class="list-group-item list-group-item-action <?= ($current_segment1 == 'peminjaman') ? 'active' : '' ?>">
                        <i class="bi bi-bookmarks-fill me-2"></i> Peminjaman
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <div class="sidebar-bottom-section">
        <div class="sidebar-upgrade-card">
            <div class="card-icon"><i class="bi bi-shield-check"></i></div>
            <div class="card-info">
                <span class="app-name">EVI 2.0.5</span>
                <span class="copyright">© <?= date("Y"); ?> Biro Umum & PBJ</span>
            </div>
            <a href="<?= site_url('logout') ?>" class="btn btn-danger w-100 mt-3" id="logout-button-navbar">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
    </div>
</div>