<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-hash" content="<?= csrf_hash() ?>">

    <title><?= $title ?? 'Aplikasi Arsip' ?></title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Aset lain seperti DataTables, Select2, dll. -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- === TAMBAHAN BARU: Google Fonts === -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- === AKHIR TAMBAHAN === -->
    <!-- === PERUBAHAN: Memuat file CSS Kustom === -->
    <link rel="stylesheet" href="<?= site_url(); ?>/css/style.css">
</head>

<body>

    <div class="wrapper">
        <!-- Sidebar -->
        <?= $this->include('layout/sidebar') ?>

        <!-- Konten Utama -->
        <div id="content">
            <!-- Navbar -->
            <?= $this->include('layout/navbar') ?>

            <!-- Main Content Section -->
            <main class="mt-4">
                <?= $this->renderSection('content') ?>
            </main>

            <!-- Footer -->
            <?= $this->include('layout/footer') ?>
        </div>
    </div>

    <!-- JQuery, Bootstrap JS, dan pustaka lainnya -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- === PERUBAHAN: Memuat file JS Kustom === -->
    <script src="<?= site_url(); ?>/js/app.js"></script> <!-- Berisi fungsi-fungsi helper -->
    <script src="<?= site_url(); ?>/js/script.js"></script> <!-- Berisi script yang berjalan otomatis -->

    <!-- Custom JS untuk Toggle Sidebar -->
    <script>
        // Tambahkan ini di script global (misal: public/js/script.js)
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token-hash"]').attr('content')
            },
            data: {
                [$('meta[name="csrf-token-name"]').attr('content')]: $('meta[name="csrf-token-hash"]').attr('content')
            }
        });

        // Refresh token setelah setiap request AJAX berhasil
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                $('meta[name="csrf-token-hash"]').attr('content', xhr.responseJSON.csrf_token);
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (session()->getFlashdata('success_login')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: '<?= addslashes(session()->getFlashdata('success_login')) ?>',
                    timer: 2500,
                    showConfirmButton: false
                });
            <?php endif; ?>
        });
    </script>
    <script>
        (function() {
            'use strict';

            // --- Logika Tema (Light/Dark Mode) ---
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;

            // Fungsi untuk menerapkan tema
            const applyTheme = (theme) => {
                document.documentElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('theme', theme);
                if (themeIcon) {
                    themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
                }
            };

            // Terapkan tema saat halaman dimuat
            if (currentTheme) {
                applyTheme(currentTheme);
            } else {
                // Gunakan preferensi sistem jika ada
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                applyTheme(prefersDark ? 'dark' : 'light');
            }

            // Event listener untuk tombol toggle
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', () => {
                    const newTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                    applyTheme(newTheme);
                });
            }

            // --- Logika Sidebar Toggle (Gunakan Vanilla JS agar tidak bergantung jQuery) ---
            const sidebarToggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            if (sidebarToggleBtn && sidebar) {
                sidebarToggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                });
            }
        })();
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>