$(document).ready(function () {
    var el = $("#wrapper");
    var toggleButton = $("#menu-toggle");
    var overlay = $('<div class="sidebar-overlay"></div>');

    $('body').append(overlay);

    // Initialize sidebar state based on screen size
    function setSidebarState() {
        if (window.innerWidth < 992) {
            el.removeClass("toggled"); // Hide sidebar by default on small screens
            overlay.hide();
        } else {
            el.removeClass("toggled"); // Default: Sidebar SHOWN on large screens (remove toggled class)
            overlay.hide();
        }
    }

    // Set initial state
    setSidebarState();

    // Toggle sidebar on button click
    toggleButton.on('click', function () {
        el.toggleClass("toggled");
        if (window.innerWidth < 992) {
            if (el.hasClass('toggled')) { // If now toggled (i.e., sidebar IS hidden)
                overlay.fadeOut();
            } else { // If now NOT toggled (i.e., sidebar IS shown)
                overlay.fadeIn();
            }
        }
    });

    // Close sidebar if clicking outside (on overlay) on small screens
    overlay.on('click', function () {
        if (window.innerWidth < 992) {
            el.removeClass('toggled'); // Remove toggled class to show sidebar
            overlay.fadeOut();
        }
    });

    // Adjust sidebar state on window resize
    $(window).on('resize', function () {
        setSidebarState();
        if (window.innerWidth >= 992) {
            overlay.hide();
        }
    });

    // Toggle icon rotation for submenus
    $('.list-group-item[data-bs-toggle="collapse"]').on('click', function () {
        const targetId = $(this).attr('href');
        const targetCollapse = $(targetId);
        const toggleIcon = $(this).find('.toggle-icon');

        if (targetCollapse.hasClass('show')) {
            targetCollapse.collapse('hide');
        } else {
            targetCollapse.collapse('show');
        }
    });

    // Add event listener for when a collapse is shown/hidden to update icon
    $('.list-group-submenu').on('show.bs.collapse', function () {
        $(this).prev('.list-group-item[data-bs-toggle="collapse"]').attr('aria-expanded', 'true');
    }).on('hide.bs.collapse', function () {
        $(this).prev('.list-group-item[data-bs-toggle="collapse"]').attr('aria-expanded', 'false');
    });


    // --- Global Library Initializations (Moved Select2 here) ---

    // Select2 Initialization
    // Pindahkan ke script.js jika ingin Select2 tersedia di semua halaman secara default
    // Jika hanya ingin di halaman tertentu, biarkan di extra_js masing-masing halaman
    // Untuk contoh ini, saya biarkan di global script.js
    $('.select2-basic').select2({
        theme: "bootstrap-5",
        placeholder: function () { // Gunakan fungsi untuk dynamic placeholder
            return $(this).data('placeholder') || "Select an option";
        },
        allowClear: true
    });

    // SweetAlert2 Example for Logout Button
    $('#logout-button, #logout-button-dropdown').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--bs-danger)',
            cancelButtonColor: 'var(--bs-secondary)',
            confirmButtonText: 'Yes, log me out!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Logged Out!',
                    'You have been successfully logged out.',
                    'success'
                ).then(() => {
                    // window.location.href = '/logout';
                });
            }
        })
    });
});