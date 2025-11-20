// public/js/app.js

/**
 * Fungsi generik untuk menginisialisasi Select2 pada elemen di dalam modal Bootstrap.
 * Ini memastikan Select2 berfungsi dengan benar meskipun berada di dalam modal.
 *
 * @param {string} modalId ID dari modal (contoh: '#data-modal')
 * @param {string} selectClass Kelas CSS untuk elemen <select> yang akan diubah (contoh: '.select2-modal')
 */
function initializeSelect2InModal(modalId, selectClass) {
    const modalElement = $(modalId);

    // Gunakan event 'shown.bs.modal' untuk memastikan modal sudah tampil
    modalElement.on('shown.bs.modal', function () {
        $(this).find(selectClass).select2({
            theme: 'bootstrap-5',
            // Ini penting: Memberitahu Select2 untuk 'melekat' pada modal
            // agar kotak pencarian dan dropdown berfungsi dengan benar.
            dropdownParent: modalElement
        });
    });

    // Reset Select2 saat modal ditutup
    modalElement.on('hidden.bs.modal', function () {
        const selectElement = $(this).find(selectClass);
        if (selectElement.length > 0) {
            // Hancurkan instance Select2 untuk membersihkan event listener
            selectElement.select2('destroy');
        }
    });
}