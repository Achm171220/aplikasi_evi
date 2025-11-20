<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title', 'Sistem Pemindahan Arsip BPKP') ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Select2 CSS (hanya jika digunakan pada halaman manapun) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 Bootstrap 5 Theme CSS (hanya jika digunakan pada halaman manapun) -->
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        /* --- Redefinisi Warna Bootstrap 5 --- */
        :root {
            --bs-primary: #007bff;
            /* Biru terang yang segar */
            --bs-secondary: #6c757d;
            /* Abu-abu standar */
            --bs-info: #17a2b8;
            /* Biru toska */
            --bs-success: #28a745;
            /* Hijau standar */
            --bs-warning: #ffc107;
            /* Kuning standar */
            --bs-danger: #dc3545;
            /* Merah standar */
            --bs-dark: #343a40;
            /* Hitam gelap */
            --bs-light: #f8f9fa;
            /* Putih abu-abu terang */
            /* Anda bisa menyesuaikan nilai heksa di atas */
        }

        body {
            background-color: var(--bs-light);
            font-family: 'Inter', sans-serif;
            padding-top: 20px;
        }

        .container {
            max-width: 100%;
        }

        /* --- Navbar Utama Styling --- */
        .main-navbar {
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            padding-left: 1.5rem;
            /* Ensure brand has enough padding */
            padding-right: 1.5rem;
            /* Ensure user dropdown has enough padding */
            /* Flexbox for better alignment of brand, dashboard, and user dropdown */
            display: flex;
            align-items: center;
        }

        /* Style for the new Dashboard link in navbar */
        .dashboard-nav-link {
            color: var(--bs-dark) !important;
            font-weight: 500;
            margin-left: 1rem;
            /* Space from brand */
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            /* Make it clickable area */
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
        }

        .dashboard-nav-link:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary) !important;
        }

        .dashboard-nav-link.active {
            background-color: rgba(var(--bs-primary-rgb), 0.15);
            color: var(--bs-primary) !important;
            font-weight: 600;
        }

        .main-navbar .navbar-brand {
            color: var(--bs-dark) !important;
            font-weight: 600;
        }

        .main-navbar .nav-link {
            color: var(--bs-dark) !important;
        }

        .main-navbar .nav-link.active,
        .main-navbar .nav-link:hover {
            color: var(--bs-primary) !important;
        }

        .main-navbar .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%280, 0, 0, 0.55%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .main-navbar .dropdown-menu {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .main-navbar .dropdown-item.active,
        .main-navbar .dropdown-item:active {
            background-color: var(--bs-primary);
            color: white;
        }

        /* --- Menu Bar (Kotak-kotak di bawah Navbar) --- */
        .menu-bar-container {
            padding: 10px 0;
            margin-bottom: 30px;
        }

        .menu-box {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease-in-out;
            position: relative;
            z-index: 10;
        }

        .menu-box:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .menu-box.active {
            border: 2px solid var(--bs-primary);
            box-shadow: 0 0.5rem 1rem rgba(var(--bs-primary-rgb), 0.2) !important;
        }

        .menu-box .card-header {
            background-color: transparent !important;
            border-bottom: 1px solid #eee;
            color: var(--bs-dark);
            padding: 0.75rem 1.25rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-box .card-header .collapse-icon {
            transition: transform 0.2s ease-in-out;
        }

        .menu-box .card-header.expanded .collapse-icon {
            transform: rotate(180deg);
        }

        .menu-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 9999;
            /* Pastikan di atas konten lain */
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-top: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .menu-dropdown-content.show {
            display: block;
        }

        .menu-box .list-group-item {
            border: none;
            padding: 0.5rem 1.25rem;
            color: var(--bs-dark);
            transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
        }

        .menu-box .list-group-item:hover,
        .menu-box .list-group-item.active {
            background-color: var(--bs-primary);
            color: white;
        }

        .menu-box .list-group-item.active i {
            color: white;
        }

        .menu-box .list-group-item a {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .menu-box .list-group-item a:hover {
            color: inherit;
        }

        /* --- Card Styling for Content Area --- */
        .card {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: var(--bs-primary) !important;
            color: white;
            font-weight: 600;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1.5rem 1.75rem;
        }

        .card-body {
            padding: 1.75rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        /* --- General Table Container for Scrolling --- */
        .table-container-scroll {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }

        .table-container-scroll table {
            width: 100%;
            min-width: 1000px;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-container-scroll thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: var(--bs-dark);
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }

        .table-container-scroll th,
        .table-container-scroll td {
            padding: 0.5rem;
            vertical-align: top;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-container-scroll .wrap-text {
            white-space: normal;
        }

        .table-container-scroll td input.form-control {
            font-size: 0.85rem;
            padding: 0.3rem 0.5rem;
            height: auto;
        }

        /* Custom scrollbar styles */
        .table-container-scroll::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-container-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-container-scroll::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .table-container-scroll::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Select2 override for Bootstrap 5 theme */
        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding-top: 0.375rem;
            padding-bottom: 0.375rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            color: #212529;
            line-height: 1.5;
            padding-left: 0.75rem;
            padding-right: 2.5rem;
        }

        .select2-container .select2-selection--single .select2-selection__clear {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 1.75rem;
            padding: 0;
            margin: 0;
            font-size: 1.25em;
            color: var(--bs-danger);
            cursor: pointer;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
            position: absolute;
            top: 0;
            right: 0.75rem;
            width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .select2-container--focus .select2-selection--single {
            border-color: var(--bs-primary);
            outline: 0;
            box-shadow: 0 0 0 .25rem rgba(var(--bs-primary-rgb), .25);
        }

        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: var(--bs-primary);
            color: white;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: .25rem;
            padding: .375rem .75rem;
        }

        /* Badge colors will automatically pick up new --bs-colors */


        /* --- Loading Spinner Overlay --- */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            /* Latar belakang semi-transparan */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            /* Pastikan di atas semua elemen lain */
            visibility: hidden;
            /* Sembunyikan secara default */
            opacity: 0;
            transition: visibility 0s, opacity 0.3s linear;
        }

        #loadingOverlay.show {
            visibility: visible;
            opacity: 1;
        }

        .spinner-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            text-align: center;
            color: var(--bs-primary);
        }

        .spinner-wrapper .spinner-border {
            width: 3rem;
            height: 3rem;
            margin-bottom: 10px;
        }

        .spinner-wrapper p {
            margin: 0;
            font-weight: 500;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body>
    <div class="container">
        <?= $this->include('layout_old/navbar') ?>
        <?= $this->include('layout_old/menu_bar') ?>
    </div>

    <div class="container mt-4 mb-5">
        <?= $this->renderSection('content') ?>
    </div>

    <?= $this->include('layout_old/footer') ?>

    <!-- HTML untuk Loader Overlay -->
    <div id="loadingOverlay">
        <div class="spinner-wrapper">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Memuat halaman, mohon tunggu...</p>
        </div>
    </div>

    <!-- jQuery (Dibutuhkan oleh Select2, atau jika ada script jQuery lain) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Select2 JS (jika digunakan pada halaman manapun) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // SweetAlert2 for flash messages (global part)
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= session()->getFlashdata('success') ?>',
                showConfirmButton: false,
                timer: 3000
            });
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= session()->getFlashdata('error') ?>',
                showConfirmButton: false,
                timer: 3000
            });
        <?php endif; ?>
        <?php if (session()->getFlashdata('warning')): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: '<?= session()->getFlashdata('warning') ?>',
                showConfirmButton: false,
                timer: 3000
            });
        <?php endif; ?>

        // --- JavaScript untuk Loading Overlay ---
        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('loadingOverlay');

            // Fungsi untuk menampilkan loader
            function showLoader() {
                loadingOverlay.classList.add('show');
            }

            // Fungsi untuk menyembunyikan loader
            function hideLoader() {
                loadingOverlay.classList.remove('show');
            }

            // Tampilkan loader saat halaman mulai di-unload (user klik link)
            document.querySelectorAll('a').forEach(link => {
                // Tambahkan kondisi untuk MENGABAIKAN link dropdown toggle
                // Link dropdown toggle biasanya memiliki atribut data-bs-toggle="dropdown"
                if (link.hasAttribute('data-bs-toggle') && link.getAttribute('data-bs-toggle') === 'dropdown') {
                    return; // Lewati link ini, jangan tampilkan loader
                }

                // Kondisi-kondisi lainnya tetap sama
                if (link.href && link.target !== '_blank' && !link.hasAttribute('download') && !link.getAttribute('href').startsWith('javascript')) {
                    link.addEventListener('click', function(event) {
                        const currentPath = window.location.pathname + window.location.search;

                        if (link.getAttribute('href') && !link.getAttribute('href').startsWith('#') && !link.getAttribute('href').startsWith('javascript') && link.getAttribute('href') !== currentPath) {
                            showLoader();
                        }
                    });
                }
            });

            // Sembunyikan loader saat halaman baru telah dimuat sepenuhnya
            hideLoader();

            // Handle back/forward button clicks, just hide the loader in case it was shown
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    hideLoader();
                }
            });
        });
    </script>
    <?= $this->renderSection('javascript') ?>
</body>

</html>