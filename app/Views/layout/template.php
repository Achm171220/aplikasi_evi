<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<?= $this->include('layout/header') ?>

<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <div class="sidebar-backdrop"></div>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?= $this->include('layout/sidebar') ?>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid px-4">
                <?= $this->include('layout/navbar') ?>
                <?= $this->renderSection('breadcrumb') ?>
            </div>

            <!-- === BREADCRUMB GLOBAL === -->
            <div class="container-fluid text-end">
                <?= $this->renderSection('breadcrumb') ?>
            </div>
            <!-- === AKHIR BREADCRUMB GLOBAL === -->

            <div class="container-fluid flex-grow-1 p-4">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <!-- Modal Pencarian -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-0">
                    <form action="<?= site_url('search') ?>" method="get">
                        <div class="input-group">
                            <input type="search" name="q" class="form-control form-control-lg border-0"
                                placeholder="Ketik kata kunci dan tekan Enter..." style="height:60px;" autofocus>
                            <button class="btn btn-primary px-4" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor JS -->
    <!-- Di dalam <head> atau sebelum </body> di layout/template.php -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <?= $this->renderSection('scripts') ?>

    <script>
        // Sidebar & Jam/Tanggal
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('wrapper'),
                sidebarToggle = document.getElementById('sidebarToggle'),
                sidebarClose = document.getElementById('sidebarCloseButton'),
                backdrop = document.querySelector('.sidebar-backdrop'),
                MOBILE_BREAKPOINT = 992;

            function toggleSidebar() {
                wrapper.classList.toggle('toggled');
                if (window.innerWidth < MOBILE_BREAKPOINT) {
                    backdrop.classList.toggle('show', wrapper.classList.contains('toggled'));
                }
            }

            sidebarToggle?.addEventListener('click', toggleSidebar);
            sidebarClose?.addEventListener('click', toggleSidebar);
            backdrop?.addEventListener('click', toggleSidebar);

            window.addEventListener('resize', () => {
                if (window.innerWidth >= MOBILE_BREAKPOINT) backdrop.classList.remove('show');
            });

            // Jam real-time
            const currentDateEl = document.getElementById('currentDate'),
                currentTimeEl = document.getElementById('currentTime');

            function updateClock() {
                if (!currentDateEl || !currentTimeEl) return;
                const now = new Date();
                currentDateEl.textContent = now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                currentTimeEl.textContent = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
            }
            setInterval(updateClock, 1000);
            updateClock();
        });

        // jQuery Plugins & App Logic
        $(function() {
            // DataTables
            if ($('#myDataTable').length) {
                $('#myDataTable').DataTable({
                    pagingType: "simple_numbers",
                    dom: 't<"d-flex justify-content-between align-items-center mt-3"ip>',
                    pageLength: 10
                });
            }

            // Select2
            if ($('.select2-basic').length) {
                $('.select2-basic').select2({
                    theme: "bootstrap-5",
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Pilih salah satu'
                });
            }

            // Logout Confirmation
            $(document).on('click', '#logout-button', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Anda yakin ingin keluar?',
                    text: "Anda akan diarahkan ke halaman login.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--bs-danger)',
                    cancelButtonColor: 'var(--bs-secondary)',
                    confirmButtonText: 'Ya, keluar!',
                    cancelButtonText: 'Batal'
                }).then(res => {
                    if (res.isConfirmed) window.location.href = "<?= site_url('logout') ?>";
                });
            });

            // Theme Toggle
            const htmlEl = $('html'),
                themeIcon = $('#theme-icon'),
                savedTheme = localStorage.getItem('bs-theme');

            function setTheme(theme) {
                htmlEl.attr('data-bs-theme', theme);
                themeIcon.toggleClass('bi-sun-fill', theme === 'light').toggleClass('bi-moon-stars-fill', theme === 'dark');
                localStorage.setItem('bs-theme', theme);
            }
            if (savedTheme) setTheme(savedTheme);
            else setTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

            $('#theme-toggle').on('click', e => {
                e.preventDefault();
                setTheme(htmlEl.attr('data-bs-theme') === 'dark' ? 'light' : 'dark');
            });
        });

        // Preloader & Tooltip
        window.onload = () => document.getElementById('preloader')?.classList.add('fade-out');
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    </script>

    <?php if (session()->get('isLoggedIn_AppB') || session()->get('isLoggedIn')): ?>
        <script>
            // Auto Logout setelah idle 10 menit
            document.addEventListener('DOMContentLoaded', function() {
                const TIMEOUT = 10 * 60 * 1000,
                    WARNING = 60 * 1000;
                let logoutTimer, warningTimer;

                function resetTimer() {
                    clearTimeout(logoutTimer);
                    clearTimeout(warningTimer);
                    warningTimer = setTimeout(showWarning, TIMEOUT - WARNING);
                    logoutTimer = setTimeout(autoLogout, TIMEOUT);
                }

                function showWarning() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesi Akan Berakhir!',
                        text: 'Anda tidak aktif. Sesi akan berakhir dalam 1 menit.',
                        timer: WARNING,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didClose: resetTimer
                    });
                }

                function autoLogout() {
                    sessionStorage.setItem('autoLogoutTriggered', 'true');
                    window.location.href = "<?= site_url('logout') ?>";
                }
                ['mousemove', 'keypress', 'scroll', 'touchstart'].forEach(evt => document.addEventListener(evt, resetTimer));
                resetTimer();
            });
        </script>
    <?php endif; ?>

    <script>
        // SweetAlert Flash Messages
        document.addEventListener('DOMContentLoaded', function() {
            const flash = <?= json_encode(session()->getFlashdata()) ?>;

            function showAlert(icon, title, msg, timer = 3000) {
                Swal.fire({
                    icon,
                    title,
                    html: msg,
                    timer,
                    showConfirmButton: false
                });
            }
            if (flash.success) showAlert('success', 'Berhasil!', flash.success);
            else if (flash.error) showAlert('error', 'Gagal!', flash.error, 5000);
            else if (flash.warning) showAlert('warning', 'Peringatan!', flash.warning, 4000);
            else if (flash.success_login) showAlert('success', 'Login Berhasil!', flash.success_login, 2500);
            else if (flash._ci_validation_errors && Object.keys(flash._ci_validation_errors).length) {
                let msg = '';
                for (const field in flash._ci_validation_errors) {
                    let name = field.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                    msg += `<strong>${name}:</strong> ${flash._ci_validation_errors[field]}<br>`;
                }
                showAlert('error', 'Validasi Gagal!', msg, 7000);
            }

            // Konfirmasi logout tambahan (sidebar/navbar)
            $(document).on('click', '#logout-button, #logout-button-navbar', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: "Anda yakin ingin keluar?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Logout!',
                    cancelButtonText: 'Batal'
                }).then(r => {
                    if (r.isConfirmed) window.location.href = $(this).attr('href');
                });
            });
        });
    </script>
</body>

</html>