<?php
// Ambil data sesi
$isLoggedIn = session()->get('isLoggedIn');
$userRole = session()->get('user_role');
$userJabatan = session()->get('user_role_jabatan');

// Ambil path URI saat ini
// Gunakan service('request')->getUri()->getPath() untuk mendapatkan path URL bersih
$currentUrlPath = service('request')->getUri()->getPath();

// Helper untuk menentukan apakah link atau grup menu aktif
function isMenuItemActive($targetUrl, $currentPath)
{
    // Konversi target URL ke format path yang bisa dibandingkan
    $targetPathClean = rtrim(str_replace(base_url(), '', $targetUrl), '/'); // Hapus base_url dan trailing slash

    // Bersihkan currentPath juga dari trailing slash
    $currentPathClean = rtrim($currentPath, '/');

    // Jika targetPathClean kosong (misal: hanya base_url()), artinya ini home/dashboard
    if (empty($targetPathClean) && (empty($currentPathClean) || $currentPathClean === 'dashboard')) {
        return true;
    }

    // Jika path persis sama (untuk item menu tunggal)
    if ($targetPathClean === $currentPathClean) {
        return true;
    }

    // Jika currentPath dimulai dengan targetPath (untuk grup menu/sub-menu)
    // Pastikan targetPath memiliki trailing slash jika itu adalah awal dari grup
    if (strpos($currentPathClean, $targetPathClean . '/') === 0) {
        return true;
    }

    return false;
}
?>

<?php if ($isLoggedIn): ?>
    <div class="sidebar-wrapper" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 primary-bg text-white">
            <i class="bi bi-folder-fill me-2"></i> Arsip BPKP
        </div>
        <div class="list-group list-group-flush my-3">

            <!-- Dashboard -->
            <?php $linkDashboard = base_url('dashboard'); ?>
            <a href="<?= $linkDashboard ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary <?= isMenuItemActive($linkDashboard, $currentUrlPath) ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
            </a>

            <!-- Menu Box: Data Arsip -->
            <?php
            $linkArsipAktif = base_url('pemindahan-new/data_aktif');
            $linkArsipInaktif = base_url('pemindahan-new/data_inaktif');
            $baseLinkArsipGroup = base_url('item'); // Basis URL untuk grup ini

            $isActiveDataArsipGroup = isMenuItemActive($baseLinkArsipGroup, $currentUrlPath) || isMenuItemActive($linkArsipAktif, $currentUrlPath) || isMenuItemActive($linkArsipInaktif, $currentUrlPath);
            ?>
            <a href="#submenu-dataarsip" class="list-group-item list-group-item-action bg-transparent custom-text-secondary d-flex justify-content-between align-items-center" data-bs-toggle="collapse" aria-expanded="<?= $isActiveDataArsipGroup ? 'true' : 'false' ?>">
                <div><i class="bi bi-database me-2"></i> Data Arsip</div>
                <i class="bi bi-chevron-down toggle-icon"></i>
            </a>
            <div class="collapse list-group-submenu <?= $isActiveDataArsipGroup ? 'show' : '' ?>" id="submenu-dataarsip">
                <a href="<?= $linkArsipAktif ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkArsipAktif, $currentUrlPath) ? 'active' : '' ?>">
                    Data Arsip Aktif
                </a>
                <a href="<?= $linkArsipInaktif ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkArsipInaktif, $currentUrlPath) ? 'active' : '' ?>">
                    Data Arsip Inaktif
                </a>
            </div>

            <!-- Menu Box: Task User -->
            <?php if (($userRole === 'user')): ?>
                <?php
                $linkUsul = base_url('usul');
                $linkMonitoring = base_url('monitoring');
                $linkBuatBA = base_url('buat_ba');
                $baseLinkUserTaskGroup = base_url(''); // Jika semua task user ada di root, atau base_url('task-user') jika ada prefix

                $isActiveUserTaskGroup = isMenuItemActive($linkUsul, $currentUrlPath) || isMenuItemActive($linkMonitoring, $currentUrlPath) || isMenuItemActive($linkBuatBA, $currentUrlPath);
                ?>
                <a href="#submenu-usertask" class="list-group-item list-group-item-action bg-transparent custom-text-secondary d-flex justify-content-between align-items-center" data-bs-toggle="collapse" aria-expanded="<?= $isActiveUserTaskGroup ? 'true' : 'false' ?>">
                    <div><i class="bi bi-person-fill me-2"></i> Task User</div>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse list-group-submenu <?= $isActiveUserTaskGroup ? 'show' : '' ?>" id="submenu-usertask">
                    <a href="<?= $linkUsul ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkUsul, $currentUrlPath) ? 'active' : '' ?>">
                        <i class="bi bi-send-fill me-2"></i> Usulan Pemindahan
                    </a>
                    <a href="<?= $linkMonitoring ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkMonitoring, $currentUrlPath) ? 'active' : '' ?>">
                        <i class="bi bi-graph-up me-2"></i> Monitoring Usulan
                    </a>
                    <a href="<?= $linkBuatBA ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkBuatBA, $currentUrlPath) ? 'active' : '' ?>">
                        <i class="bi bi-file-earmark-text-fill me-2"></i> Buat Berita Acara
                    </a>
                </div>
            <?php endif; ?>

            <!-- Menu Box: My Task (Admin/Superadmin) -->
            <?php if (($userRole === 'superadmin' || $userRole === 'admin')): ?>
                <?php
                $linkVerifikasi1 = base_url('verifikasi/1');
                $linkVerifikasi2 = base_url('verifikasi/2');
                $linkVerifikasi3 = base_url('verifikasi/3');
                $linkEksekusi = base_url('eksekusi');
                $baseLinkAdminTaskGroup = base_url('verifikasi'); // Basis URL untuk grup ini

                $isActiveAdminTaskGroup = isMenuItemActive($baseLinkAdminTaskGroup, $currentUrlPath) || isMenuItemActive($linkEksekusi, $currentUrlPath);
                ?>
                <a href="#submenu-admintask" class="list-group-item list-group-item-action bg-transparent custom-text-secondary d-flex justify-content-between align-items-center" data-bs-toggle="collapse" aria-expanded="<?= $isActiveAdminTaskGroup ? 'true' : 'false' ?>">
                    <div><i class="bi bi-person-gear me-2"></i> My Task</div>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse list-group-submenu <?= $isActiveAdminTaskGroup ? 'show' : '' ?>" id="submenu-admintask">
                    <?php if ($userRole === 'superadmin' || ($userRole === 'admin' && $userJabatan === 'arsiparis')): ?>
                        <a href="<?= $linkVerifikasi1 ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkVerifikasi1, $currentUrlPath) ? 'active' : '' ?>">
                            <i class="bi bi-check-all me-2"></i> Verifikasi 1
                        </a>
                    <?php endif; ?>
                    <?php if ($userRole === 'superadmin' || ($userRole === 'admin' && $userJabatan === 'pengampu')): ?>
                        <a href="<?= $linkVerifikasi2 ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkVerifikasi2, $currentUrlPath) ? 'active' : '' ?>">
                            <i class="bi bi-check-all me-2"></i> Verifikasi 2
                        </a>
                    <?php endif; ?>
                    <?php if ($userRole === 'superadmin' || ($userRole === 'admin' && $userJabatan === 'verifikator')): ?>
                        <a href="<?= $linkVerifikasi3 ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkVerifikasi3, $currentUrlPath) ? 'active' : '' ?>">
                            <i class="bi bi-check-all me-2"></i> Verifikasi 3
                        </a>
                        <a href="<?= $linkEksekusi ?>" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5 <?= isMenuItemActive($linkEksekusi, $currentUrlPath) ? 'active' : '' ?>">
                            <i class="bi bi-arrow-left-right me-2"></i> Eksekusi Pemindahan
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Kembali ke Menu Utama -->
            <?php $linkKembaliUtama = base_url(''); ?>
            <a href="<?= $linkKembaliUtama ?>" class="list-group-item list-group-item-action bg-transparent text-custom-danger <?= isMenuItemActive($linkKembaliUtama, $currentUrlPath) ? 'active' : '' ?>">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Menu Utama
            </a>

            <!-- Logout Button -->
            <a href="<?= site_url('logout') ?>" class="list-group-item list-group-item-action bg-transparent text-custom-danger" id="logout-button-navbar">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
    </div>
<?php endif; ?>