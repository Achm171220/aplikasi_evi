<!-- app/Views/layout/navbar.php -->
<?php
// Ambil service URI dan data sesi
$uri = service('uri');
$segments = $uri->getSegments();
$current_segment1 = $uri->getSegment(1) ?? 'dashboard';
$role_access = session()->get('role_access');
$user_jabatan = session()->get('role_jabatan'); // Untuk pengecekan izin tampilan menu

// Pastikan is_parent_active tersedia (kita definisikan di sidebar.php)
if (!function_exists('is_parent_active')) {
    function is_parent_active($current_segment, $sub_segments)
    {
        return in_array($current_segment, $sub_segments, true);
    }
}

// Definisikan nama-nama yang lebih ramah pengguna untuk segmen URL
$segmentNames = [
    'dashboard' => 'Dashboard',
    'item-aktif' => 'Item Berkas', // Ubah nama ini agar lebih spesifik
    'berkas-aktif' => 'Berkas',
    'laporan-aktif' => 'Laporan',
    'item-inaktif' => 'Item',
    'berkas-inaktif' => 'Berkas',
    'laporan-inaktif' => 'Laporan Inaktif',
    'pemindahan' => 'Pemindahan',
    'usulan' => 'Usulan', // Untuk pemindahan/usulan
    'baru' => 'Buat Baru', // Untuk pemindahan/usulan/baru
    'pantau' => 'Pantau Usulan',
    'buat-ba' => 'Buat BA',
    'tugas' => 'Tugas Saya',
    'verifikasi' => 'Verifikasi',
    'eksekusi' => 'Eksekusi',
    'riwayat' => 'Riwayat',
    'klasifikasi' => 'Klasifikasi',
    'jenis-naskah' => 'Jenis Naskah',
    'riwayat-import' => 'Riwayat Import',
    'users' => 'Manajemen User',
    'pegawai' => 'Pegawai',
    'aktif' => 'Aktif', // Untuk users/pegawai/aktif
    'hak-fitur' => 'Hak Fitur',
    'unit-kerja-es1' => 'Eselon 1',
    'unit-kerja-es2' => 'Eselon 2',
    'unit-kerja-es3' => 'Eselon 3',
    'profil' => 'Profil',
    'peminjaman' => 'Peminjaman',
    'monitoring' => 'Monitoring', // Untuk peminjaman/monitoring
    // ... Tambahkan nama lain sesuai kebutuhan
];

// --- LOGIKA BREADCRUMB YANG DIPERBAIKI ---
$breadcrumbItems = [];
$currentPath = '';

// Tambahkan "Dashboard" sebagai item pertama (root)
$breadcrumbItems[] = '<li class="breadcrumb-item"><a href="' . site_url('dashboard') . '">Dashboard</a></li>';

// --- Cek untuk menu induk dropdown ---
// Ini adalah logika utama untuk menambahkan parent menu seperti "Arsip Aktif"
// Pastikan kondisi visibility parent menu di sidebar sama di sini
// Superadmin diperlakukan sebagai pimpinan untuk pengecekan ini
if ($role_access === 'superadmin') {
    $temp_user_jabatan = 'pimpinan';
} else {
    $temp_user_jabatan = $user_jabatan;
}

// ARSIP AKTIF
$can_view_arsip_aktif = in_array($temp_user_jabatan, ['sekretaris', 'pengelola arsip', 'arsiparis', 'pengampu', 'verifikator', 'pimpinan']);
$arsip_aktif_sub_uris = ['item-aktif', 'berkas-aktif', 'laporan-aktif'];
if ($can_view_arsip_aktif && is_parent_active($current_segment1, $arsip_aktif_sub_uris)) {
    $breadcrumbItems[] = '<li class="breadcrumb-item"><a href="#">Arsip Aktif</a></li>';
}

// ARSIP INAKTIF
$can_view_arsip_inaktif = in_array($temp_user_jabatan, ['arsiparis', 'pengampu', 'verifikator', 'pimpinan']);
$arsip_inaktif_sub_uris = ['item-inaktif', 'berkas-inaktif', 'laporan-inaktif'];
if ($can_view_arsip_inaktif && is_parent_active($current_segment1, $arsip_inaktif_sub_uris)) {
    $breadcrumbItems[] = '<li class="breadcrumb-item"><a href="#">Arsip Inaktif</a></li>';
}

// PEMINDAHAN
$can_view_pemindahan = in_array($temp_user_jabatan, ['sekretaris', 'pengelola arsip', 'arsiparis', 'pengampu', 'verifikator', 'pimpinan']);
$pemindahan_sub_uris = ['pemindahan', 'usulan', 'pantau', 'buat-ba', 'tugas', 'verifikasi', 'eksekusi', 'riwayat']; // Ini harus mencakup semua segmen level 1 dan 2
if ($can_view_pemindahan && is_parent_active($current_segment1, $pemindahan_sub_uris)) {
    $breadcrumbItems[] = '<li class="breadcrumb-item"><a href="#">Pemindahan</a></li>';
}

// DATA MASTER / ADMINISTRASI (JIKA PERLU DI BREADCRUMB)
// Contoh: Manajemen User
if (in_array($current_segment1, ['users', 'klasifikasi', 'jenis-naskah', 'riwayat-import', 'hak-fitur', 'unit-kerja-es1', 'unit-kerja-es2', 'unit-kerja-es3'])) {
    $breadcrumbItems[] = '<li class="breadcrumb-item"><a href="#">Administrasi</a></li>'; // Atau Data Master
}


// --- Loop Segmen URI untuk Breadcrumb (Mulai dari segmen pertama) ---
$pathSoFar = '';
// Asumsi segmen [0] adalah nama Controller. Kita mulai dari segmen 1.
for ($i = 0; $i < count($segments); $i++) {
    $segment = $segments[$i];

    // Abaikan segmen root/API yang tidak relevan
    if (in_array($segment, ['public', 'api', 'v1', 'auth', 'files'])) {
        continue;
    }

    // Abaikan segmen yang sudah dihandle sebagai parent di atas
    if (in_array($segment, array_merge($arsip_aktif_sub_uris, $arsip_inaktif_sub_uris, $pemindahan_sub_uris))) {
        // Kecuali jika itu adalah segmen terakhir, maka kita ingin menampilkannya
        if ($i < count($segments) - 1) continue;
    }

    $pathSoFar .= '/' . $segment;
    $displayName = $segmentNames[$segment] ?? ucfirst(str_replace(['-', '_'], [' ', ' '], $segment));

    if ($i === count($segments) - 1) { // Segmen terakhir
        if (is_numeric($segment)) {
            $prevSegmentName = $segmentNames[$segments[$i - 1]] ?? ucfirst(str_replace(['-', '_'], [' ', ' '], $segments[$i - 1]));
            $displayName = "Detail $prevSegmentName";
        }
        $breadcrumbItems[] = '<li class="breadcrumb-item active" aria-current="page">' . esc($displayName) . '</li>';
    } else { // Segmen di tengah
        $breadcrumbItems[] = '<li class="breadcrumb-item"><a href="' . site_url(ltrim($pathSoFar, '/')) . '">' . esc($displayName) . '</a></li>';
    }
}


// Render breadcrumb di section 'breadcrumb' dari template utama
echo $this->section('breadcrumb');
?>
<div class="breadcrumb-capsule shadow-sm">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <?= implode('', $breadcrumbItems) ?>
        </ol>
    </nav>
</div>
<?php
echo $this->endSection();
?>

<nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-0">
    <div class="d-flex align-items-center">
        <!-- Tombol Toggle dari kode lama Anda -->
        <button class="btn btn-link custom-sidebar-toggle-btn me-3" id="sidebarToggle" type="button">
            <i class="bi bi-align-center fs-4 sidebar-toggle-icon"></i>
        </button>
        <!-- Hapus Brand "EVI", karena judul halaman akan ada di bawahnya -->
    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">

        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
            <!-- Tombol Toggle Tema -->
            <li class="nav-item me-3">
                <a href="#" class="nav-link" id="theme-toggle">
                    <i class="bi bi-sun-fill" id="theme-icon"></i>
                </a>
            </li>
            <!-- Jam & Tanggal -->
            <li class="nav-item navbar-datetime me-3">
                <span id="currentDate"></span> | <span id="currentTime"></span>
            </li>
            <a href="#" class="nav-link text-secondary me-3" data-bs-toggle="modal" data-bs-target="#searchModal" title="Pencarian Cepat">
                <i class="bi bi-search fs-5"></i>
            </a>
            <!-- Dropdown User -->
            <li class="nav-item dropdown">
                <!-- Tombol Pemicu Dropdown -->
                <a class="btn btn-primary btn-sm dropdown-toggle d-flex align-items-center rounded-pill shadow-sm" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-6 me-2"></i>
                    <span><?= esc(session()->get('name')) ?></span>
                </a>

                <!-- === KONTEN DROPDOWN BARU (PROFILE CARD) === -->
                <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" aria-labelledby="profileDropdown" style="width: 280px;">
                    <div class="card border-0">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img src="<?= site_url('images/user.png'); ?>" class="rounded-circle shadow-sm" alt="Avatar" width="96" height="96">
                            </div>
                            <h5 class="card-title mb-0"><?= esc(session()->get('name')) ?></h5>
                            <p class="card-text text-muted mb-2">
                                <small><?= esc(ucfirst(session()->get('role_access'))) ?></small>
                                <small class="text-primary"><?= esc(ucfirst(session()->get('role_jabatan'))) ?></small>
                            </p>

                            <?php
                            // Ambil data unit kerja dari session (jika ada)
                            $authData = session()->get('auth_data');
                            $unitKerja = 'Terbatas'; // Default untuk Superadmin
                            if ($authData) {
                                if (!empty($authData['id_es3'])) $unitKerja = (new \App\Models\UnitKerjaEs3Model())->find($authData['id_es3'])['nama_es3'] ?? 'N/A';
                                elseif (!empty($authData['id_es2'])) $unitKerja = (new \App\Models\UnitKerjaEs2Model())->find($authData['id_es2'])['nama_es2'] ?? 'N/A';
                                elseif (!empty($authData['id_es1'])) $unitKerja = (new \App\Models\UnitKerjaEs1Model())->find($authData['id_es1'])['nama_es1'] ?? 'N/A';
                            }
                            ?>
                            <?php if (session()->get('role_access') !== 'superadmin'): ?>
                                <div class="px-3 py-2 bg-light rounded-3 mb-3">
                                    <small class="text-muted d-block">Unit Kerja</small>
                                    <strong class="d-block" style="font-size: 0.9rem;"><?= esc($unitKerja) ?></strong>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-center gap-2">
                                <a href="<?= site_url('profil') ?>" class="btn btn-sm btn-outline-primary" title="profil user">
                                    <i class="bi bi-person-circle"></i>
                                </a>
                                <a href="<?= site_url('activity-logs') ?>" class="btn btn-sm btn-outline-info" title="aktivitas log">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-danger" id="logout-button-navbar" title="logout">
                                    <i class="bi bi-arrow-bar-left"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>