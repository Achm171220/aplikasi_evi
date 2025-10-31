$(document).ready(function () {

    // --- Logika Toggle Sidebar dengan Animasi Panah Kanan/Kiri ---
    const sidebarToggle = $('#sidebarToggle'); // Menggunakan ID dari navbar baru
    const sidebarIcon = $('.sidebar-toggle-icon');
    const wrapper = $('#wrapper');

    // Fungsi untuk memeriksa status toggle dan mengatur ikon
    function checkSidebarState() {
        if (wrapper.hasClass('toggled')) {
            sidebarIcon.removeClass('bi-arrow-left').addClass('bi-arrow-right');
        } else {
            sidebarIcon.removeClass('bi-arrow-right').addClass('bi-arrow-left');
        }
    }

    // Panggil saat halaman dimuat untuk mengatur ikon awal
    checkSidebarState();

    sidebarToggle.on('click', function (e) {
        e.preventDefault();
        wrapper.toggleClass('toggled');
        // Panggil lagi setelah toggle untuk mengubah ikon
        checkSidebarState();
    });

    // --- Inisialisasi Library ---
    if ($('#myDataTable').length) {
        $('#myDataTable').DataTable({
            "language": {
                // "url": "https://cdn.datatables.net/plug-ins/2.0.8/i18n/id.json"
            },
            "pagingType": "simple_numbers",
            "dom": 't<"bottom"ip>',
        });
        $('.dataTables_filter, .dataTables_length').hide();
    }
    if ($('#pilihNegara').length) {
        $('#pilihNegara').select2({
            theme: "bootstrap-5",
            placeholder: "Pilih sebuah negara",
            allowClear: true,
            data: [
                { id: 'ID', text: 'Indonesia' }, { id: 'MY', text: 'Malaysia' },
                { id: 'SG', text: 'Singapura' }, { id: 'TH', text: 'Thailand' }
            ]
        });
    }

    // --- Pemicu SweetAlert ---
    $('#showAlert').on('click', function () {
        Swal.fire({
            title: 'Berhasil!',
            text: 'Ini adalah contoh notifikasi.',
            icon: 'success',
            confirmButtonText: 'Keren'
        });
    });

    // --- Jam & Tanggal Real-time (sesuai ID navbar baru) ---
    function updateClock() {
        const now = new Date();
        const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        $('#currentDate').text(now.toLocaleDateString('id-ID', optionsDate));
        $('#currentTime').text(now.toLocaleTimeString('id-ID'));
    }
    setInterval(updateClock, 1000);
    updateClock();

    // --- Konfirmasi Logout SweetAlert2 ---
    function handleLogout(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Anda yakin ingin keluar?',
            text: "Anda akan diarahkan ke halaman login.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, keluar!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Ganti URL ini dengan route logout Anda yang sebenarnya
                window.location.href = "<?= site_url('logout') ?>";
            }
        });
    }
    // Mengikat event ke semua elemen dengan ID #logout-button
    $(document).on('click', '#logout-button', handleLogout);

    // --- Logika Toggle Tema Dark/Light ---
    const themeToggle = $('#theme-toggle');
    const themeIcon = $('#theme-icon');
    const htmlEl = $('html');

    function setTheme(theme) {
        if (theme === 'dark') {
            htmlEl.addClass('dark');
            themeIcon.removeClass('bi-sun-fill').addClass('bi-moon-stars-fill');
            localStorage.setItem('theme', 'dark');
        } else {
            htmlEl.removeClass('dark');
            themeIcon.removeClass('bi-moon-stars-fill').addClass('bi-sun-fill');
            localStorage.setItem('theme', 'light');
        }
    }

    if (localStorage.getItem('theme') === 'dark') {
        setTheme('dark');
    } else {
        setTheme('light');
    }

    themeToggle.on('click', function (e) {
        e.preventDefault();
        htmlEl.hasClass('dark') ? setTheme('light') : setTheme('dark');
    });
});