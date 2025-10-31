<?php
$userRole = session()->get('role_access');
$userJabatan = session()->get('role_jabatan');
// PERBAIKAN: Gunakan getUri()->getPath() untuk menghindari akses properti protected
$currentUrlPath = service('request')->getUri()->getPath();

// Helper untuk menentukan apakah link atau grup menu aktif
function isMenuItemActive($linkPath, $currentPath)
{
    $linkPathClean = rtrim($linkPath, '/');
    $currentPathClean = rtrim($currentPath, '/');

    // Jika path link persis sama dengan current path, itu aktif
    if ($linkPathClean === $currentPathClean) {
        return true;
    }
    // Check if current path starts with the link path for sub-menus
    // Use strict comparison (===) to avoid issues with 0 position
    return strpos($currentPathClean, $linkPathClean) === 0;
}
?>

<?php if (session()->get('isLoggedIn')): ?>
    <div class="menu-bar-container">
        <div class="row g-3"> <!-- g-3 adds gutter space between columns -->

            <!-- Menu Box: Data Arsip -->
            <?php
            $isActiveDataArsipGroup = isMenuItemActive('pemindahan/data_aktif', $currentUrlPath) || isMenuItemActive('pemindahan/data_inaktif', $currentUrlPath);
            $dataArsipMenuId = 'dataArsipMenu'; // ID unik untuk box menu ini
            ?>
            <div class="col-md-4">
                <div class="card menu-box <?= $isActiveDataArsipGroup ? 'active' : '' ?>" id="<?= $dataArsipMenuId ?>">
                    <div class="card-header menu-toggle"> <!-- Tambahkan kelas menu-toggle -->
                        <i class="fas fa-database me-2"></i> Data Arsip
                        <i class="fas fa-chevron-down float-end collapse-icon"></i>
                    </div>
                    <div class="menu-dropdown-content"> <!-- Tidak lagi pakai kelas 'collapse' Bootstrap -->
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item <?= isMenuItemActive('pemindahan/data_aktif', $currentUrlPath) ? 'active' : '' ?>">
                                <a href="<?= base_url('pemindahan/data_aktif') ?>">
                                    <i class="fas fa-folder-open me-2"></i> Data Arsip Aktif
                                </a>
                            </li>
                            <li class="list-group-item <?= isMenuItemActive('pemindahan/data_inaktif', $currentUrlPath) ? 'active' : '' ?>">
                                <a href="<?= base_url('pemindahan/data_inaktif') ?>">
                                    <i class="fas fa-archive me-2"></i> Data Arsip Inaktif
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Menu Box: User -->
            <?php
            $canAccessUserFeatures = (($userRole === 'user'));
            $isActiveUserGroup = isMenuItemActive('pemindahan', $currentUrlPath) || isMenuItemActive('pemindahan/monitoring', $currentUrlPath) || isMenuItemActive('pemindahan/buat_ba', $currentUrlPath);
            $userMenuId = 'userMenu'; // ID unik untuk box menu ini
            ?>
            <?php if ($canAccessUserFeatures): ?>
                <div class="col-md-4">
                    <div class="card menu-box <?= $isActiveUserGroup ? 'active' : '' ?>" id="<?= $userMenuId ?>">
                        <div class="card-header menu-toggle">
                            <i class="fas fa-user me-2"></i> Task User
                            <i class="fas fa-chevron-down float-end collapse-icon"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item <?= isMenuItemActive('pemindahan/usul', $currentUrlPath) && (rtrim($currentUrlPath, '/') === 'pemindahan/usul') ? 'active' : '' ?>">
                                    <a href="<?= base_url('pemindahan/usul') ?>">
                                        <i class="fas fa-paper-plane me-2"></i> Usulan Pemindahan
                                    </a>
                                </li>
                                <li class="list-group-item <?= isMenuItemActive('pemindahan/monitoring', $currentUrlPath) ? 'active' : '' ?>">
                                    <a href="<?= base_url('pemindahan/monitoring') ?>">
                                        <i class="fas fa-chart-line me-2"></i> Monitoring Usulan
                                    </a>
                                </li>
                                <li class="list-group-item <?= isMenuItemActive('pemindahan/buat_ba', $currentUrlPath) ? 'active' : '' ?>">
                                    <a href="<?= base_url('pemindahan/buat_ba') ?>">
                                        <i class="fas fa-file-alt me-2"></i> Buat Berita Acara
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Menu Box: Admin -->
            <?php

            $canAccessAdminFeatures = ($userRole === 'superadmin' || $userRole === 'admin');
            $isActiveAdminGroup = isMenuItemActive('pemindahan/verifikasi', $currentUrlPath) || isMenuItemActive('pemindahan/eksekusi', $currentUrlPath);
            $adminMenuId = 'adminMenu'; // ID unik untuk box menu ini
            ?>
            <?php if ($canAccessAdminFeatures): ?>
                <div class="col-md-4">
                    <div class="card menu-box <?= $isActiveAdminGroup ? 'active' : '' ?>" id="<?= $adminMenuId ?>">
                        <div class="card-header menu-toggle">
                            <i class="fas fa-user-cog me-2"></i> My Task
                            <i class="fas fa-chevron-down float-end collapse-icon"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <ul class="list-group list-group-flush">
                                <?php if ($userRole === 'superadmin' || ($userRole === 'admin' && $userJabatan === 'arsiparis')): ?>
                                    <li class="list-group-item <?= isMenuItemActive('pemindahan/verifikasi/1', $currentUrlPath) ? 'active' : '' ?>">
                                        <a href="<?= base_url('pemindahan/verifikasi/1') ?>">
                                            <i class="fas fa-check-double me-2"></i> Verifikasi 1
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($userRole === 'superadmin' || ($userRole === 'admin' && $userJabatan === 'pengampu')): ?>
                                    <li class="list-group-item <?= isMenuItemActive('pemindahan/verifikasi/2', $currentUrlPath) ? 'active' : '' ?>">
                                        <a href="<?= base_url('pemindahan/verifikasi/2') ?>">
                                            <i class="fas fa-check-double me-2"></i> Verifikasi 2
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($userRole === 'superadmin' || ($userRole === 'admin' && $userJabatan === 'verifikator')): ?>
                                    <li class="list-group-item <?= isMenuItemActive('pemindahan/verifikasi/3', $currentUrlPath) ? 'active' : '' ?>">
                                        <a href="<?= base_url('pemindahan/verifikasi/3') ?>">
                                            <i class="fas fa-check-double me-2"></i> Verifikasi 3
                                        </a>
                                    </li>
                                    <li class="list-group-item <?= isMenuItemActive('pemindahan/eksekusi', $currentUrlPath) ? 'active' : '' ?>">
                                        <a href="<?= base_url('pemindahan/eksekusi') ?>">
                                            <i class="fas fa-exchange-alt me-2"></i> Eksekusi Pemindahan
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Menu Box: Kembali ke Menu Sebelumnya/Dashboard -->
            <div class="col-md-4">
                <div class="card menu-box bg-danger"> <!-- Tidak perlu 'active' class -->
                    <a class="card-header text-decoration-none text-light d-flex align-items-center justify-content-center" href="<?= base_url('/dashboard'); ?>" style="height: 100%; border-bottom: none; border-radius: 0.75rem;">
                        <i class="fas fa-arrow-left me-2"></i> Keluar dari Menu Pemindahan
                    </a>
                </div>
            </div>

        </div>
    </div>
    <!-- JavaScript for custom dropdown functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggles = document.querySelectorAll('.menu-box .menu-toggle');

            menuToggles.forEach(toggle => {
                const menuBox = toggle.closest('.menu-box');
                const dropdownContent = menuBox.querySelector('.menu-dropdown-content');
                const collapseIcon = toggle.querySelector('.collapse-icon');

                // Set initial state based on 'active' class (from PHP)
                // If the menu box is active, show its content initially
                if (menuBox.classList.contains('active')) {
                    dropdownContent.classList.add('show');
                    toggle.classList.add('expanded'); // Add 'expanded' for icon rotation
                }

                toggle.addEventListener('click', function() {
                    // Tutup semua dropdown lain yang sedang terbuka
                    document.querySelectorAll('.menu-dropdown-content.show').forEach(openDropdown => {
                        // Tutup hanya jika bukan dropdown yang sedang diklik
                        if (openDropdown !== dropdownContent) {
                            openDropdown.classList.remove('show');
                            // Cari toggle (card-header) dari dropdown yang ditutup dan hapus 'expanded'
                            const associatedToggle = openDropdown.closest('.menu-box').querySelector('.menu-toggle');
                            if (associatedToggle) {
                                associatedToggle.classList.remove('expanded');
                            }
                        }
                    });

                    // Toggle dropdown ini
                    dropdownContent.classList.toggle('show');
                    toggle.classList.toggle('expanded'); // Toggle class for icon rotation
                });
            });

            // Tutup dropdown jika klik di luar area menu boxes
            document.addEventListener('click', function(event) {
                let clickedInsideMenuBox = false;
                document.querySelectorAll('.menu-box').forEach(menuBox => {
                    if (menuBox.contains(event.target)) {
                        clickedInsideMenuBox = true;
                    }
                });

                if (!clickedInsideMenuBox) {
                    document.querySelectorAll('.menu-dropdown-content.show').forEach(openDropdown => {
                        openDropdown.classList.remove('show');
                        const associatedToggle = openDropdown.closest('.menu-box').querySelector('.menu-toggle');
                        if (associatedToggle) {
                            associatedToggle.classList.remove('expanded');
                        }
                    });
                }
            });
        });
    </script>
<?php endif; ?>