<?= $this->extend('layout_old/main') ?>

<?= $this->section('title') ?>Sistem Pemindahan Arsip BPKP - Usulan<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS (Bootstrap 5 integration) -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 Bootstrap 5 Theme CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    /* Specific column widths for usulan.php table */
    .table-usulan .col-checkbox {
        width: 4%;
    }

    .table-usulan .col-no-dokumen {
        width: 15%;
    }

    .table-usulan .col-judul {
        width: 30%;
        white-space: normal;
        /* Allow text wrapping */
    }

    .table-usulan .col-tahun {
        width: 15%;
    }

    .table-usulan .col-lokasi {
        width: 20%;
    }

    .table-usulan .col-status {
        width: 15%;
    }

    .table-usulan .col-catatan {
        width: 10%;
        white-space: normal;
        /* Allow text wrapping */
    }

    /* DataTables specific styles for better integration with Bootstrap 5 */
    div.dataTables_wrapper div.dataTables_filter {
        text-align: right;
        margin-bottom: 1rem;
    }

    div.dataTables_wrapper div.dataTables_paginate {
        text-align: right;
        margin-top: 1rem;
    }

    div.dataTables_wrapper div.dataTables_length {
        margin-bottom: 1rem;
    }

    div.dataTables_wrapper div.dataTables_info {
        padding-top: 0.85em;
    }

    /* Style for DataTables responsive collapse icon */
    table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
        left: 4px;
        /* Adjust position for checkbox column */
        top: 14px;
        /* Adjust vertical position */
    }

    table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td:first-child:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr.parent>th:first-child:before {
        content: "\f068";
        /* FontAwesome minus icon for expanded */
    }

    table.dataTable.dtr-inline.collapsed>tbody>tr:not(.parent)>td:first-child:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr:not(.parent)>th:first-child:before {
        content: "\f067";
        /* FontAwesome plus icon for collapsed */
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Usulan Pemindahan Arsip Aktif</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Pilih item arsip aktif yang ingin Anda usulkan untuk pemindahan, dan tentukan verifikator untuk setiap tahap.</p>

        <?php if (empty($items)): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> Tidak ada arsip aktif yang tersedia untuk diusulkan pemindahan saat ini.
            </div>
        <?php else: ?>
            <form action="<?= base_url('pemindahan/propose') ?>" method="post" id="formPemindahan">
                <?= csrf_field() ?>

                <!-- Section: Verifikator Selection -->
                <div class="card bg-light p-4 mb-4">
                    <h5 class="card-title text-primary mb-3">Tentukan Verifikator</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="id_verifikator1" class="form-label">Verifikator 1 (Arsiparis)</label>
                            <select class="form-select select2-single" id="id_verifikator1" name="id_verifikator1" required data-placeholder="Pilih Verifikator 1">
                                <option value=""></option>
                                <?php foreach ($verifikator1Users as $user): ?>
                                    <option value="<?= esc($user['id']) ?>" <?= set_select('id_verifikator1', $user['id']) ?>><?= esc($user['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="id_verifikator2" class="form-label">Verifikator 2 (Pengampu)</label>
                            <select class="form-select select2-single" id="id_verifikator2" name="id_verifikator2" required data-placeholder="Pilih Verifikator 2">
                                <option value=""></option>
                                <?php foreach ($verifikator2Users as $user): ?>
                                    <option value="<?= esc($user['id']) ?>" <?= set_select('id_verifikator2', $user['id']) ?>><?= esc($user['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="id_verifikator3" class="form-label">Verifikator 3 (Verifikator)</label>
                            <select class="form-select select2-single" id="id_verifikator3" name="id_verifikator3" required data-placeholder="Pilih Verifikator 3">
                                <option value=""></option>
                                <?php foreach ($verifikator3Users as $user): ?>
                                    <option value="<?= esc($user['id']) ?>" <?= set_select('id_verifikator3', $user['id']) ?>><?= esc($user['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="submit" class="btn btn-success btn-lg" id="proposeButton" disabled>
                        <i class="fas fa-paper-plane me-2"></i> Usulkan Pemindahan Item Terpilih
                    </button>
                </div>

                <h5 class="mb-3">Daftar Item Arsip Aktif</h5>
                <div class="table-responsive">
                    <!-- Tambahkan ID unik ke tabel Anda -->
                    <table class="table table-hover table-striped table-bordered align-middle table-usulan" id="usulanDataTable">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center col-checkbox">
                                    <input type="checkbox" id="selectAllItems" class="form-check-input">
                                </th>
                                <th class="col-no-dokumen">No. Dokumen</th>
                                <th class="col-judul">Judul Dokumen</th>
                                <th class="col-tahun">Tahun Cipta</th>
                                <th class="col-lokasi">Lokasi Simpan</th>
                                <th class="col-status">Status Arsip</th>
                                <th class="col-catatan">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="text-center col-checkbox">
                                        <input type="checkbox" name="selected_items[]" value="<?= esc($item['id']) ?>" class="item-checkbox form-check-input">
                                    </td>
                                    <td class="col-no-dokumen"><?= esc($item['no_dokumen']) ?></td>
                                    <td class="col-judul wrap-text"><?= esc($item['judul_dokumen']) ?></td>
                                    <td class="col-tahun"><?= esc($item['tahun_cipta']) ?></td>
                                    <td class="col-lokasi"><?= esc($item['lokasi_simpan']) ?></td>
                                    <td class="col-status"><span class="badge bg-primary"><?= esc($item['status_arsip']) ?></span></td>
                                    <td class="col-catatan wrap-text"><?= !empty($item['admin_notes']) ? '<span class="text-danger fst-italic">' . esc($item['admin_notes']) . '</span>' : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<!-- ==================================================================== -->
<!-- Penting: Pastikan urutan skrip di bawah ini atau di layout utama Anda benar -->
<!-- jQuery HARUS dimuat PALING AWAL -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap 5 JS Bundle (setelah jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Select2 JS (setelah jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SweetAlert2 JS (setelah jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables JS (setelah jQuery dan Bootstrap JS) -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<!-- ==================================================================== -->

<script>
    $(document).ready(function() {
        // 1. Inisialisasi Select2
        $('.select2-single').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: function() {
                // Mengambil placeholder dari atribut data-placeholder pada elemen <select>
                return $(this).data('placeholder');
            },
            allowClear: true,
            // Opsional: Coba ini jika dropdown Select2 terpotong atau tidak muncul dengan benar.
            // Jika Select2 berada di dalam elemen yang memiliki 'overflow: hidden' atau di modal.
            // dropdownParent: $('body')
        });

        // 2. Inisialisasi DataTables
        $('#usulanDataTable').DataTable({
            "paging": true, // Aktifkan paginasi
            "lengthChange": true, // Aktifkan pilihan jumlah baris per halaman
            "searching": true, // Aktifkan fitur pencarian
            "ordering": true, // Aktifkan fitur pengurutan
            "info": true, // Tampilkan informasi "Showing X to Y of Z entries"
            "autoWidth": false, // Nonaktifkan penyesuaian lebar kolom otomatis
            "responsive": true, // Aktifkan mode responsif
            "language": { // Sesuaikan bahasa DataTables
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" // Bahasa Indonesia
            },
            "columnDefs": [{
                    "orderable": false,
                    "targets": [0]
                }, // Kolom checkbox tidak bisa diurutkan
                // Prioritas responsif untuk kolom-kolom penting
                {
                    "responsivePriority": 1,
                    "targets": 0
                }, // Checkbox
                {
                    "responsivePriority": 2,
                    "targets": 1
                }, // No. Dokumen
                {
                    "responsivePriority": 3,
                    "targets": 2
                }, // Judul Dokumen
                {
                    "responsivePriority": 4,
                    "targets": -1
                } // Catatan (kolom terakhir)
            ]
        });

        // 3. Logika untuk checkbox "Select All" dan tombol "Usulkan Pemindahan"
        const selectAllCheckbox = document.getElementById('selectAllItems');
        const proposeButton = document.getElementById('proposeButton');
        const formPemindahan = document.getElementById('formPemindahan');

        // Fungsi untuk memeriksa kondisi pengaktifan tombol usulan
        function toggleProposeButton() {
            let anyItemSelected = false;
            // Dapatkan semua item checkbox, termasuk yang tidak terlihat oleh paginasi DataTables
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                if (checkbox.checked) {
                    anyItemSelected = true;
                }
            });

            const verif1Selected = $('#id_verifikator1').val() !== '';
            const verif2Selected = $('#id_verifikator2').val() !== '';
            const verif3Selected = $('#id_verifikator3').val() !== '';

            proposeButton.disabled = !(anyItemSelected && verif1Selected && verif2Selected && verif3Selected);
        }

        // Event listener untuk checkbox "Select All"
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleProposeButton(); // Perbarui status tombol setelah mengubah semua checkbox
            });
        }

        // Event listener untuk setiap item checkbox
        // Menggunakan delegasi event dengan $(document).on() agar bekerja dengan baris yang diubah/ditambahkan oleh DataTables
        $(document).on('change', '.item-checkbox', function() {
            let allChecked = true;
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                if (!cb.checked) {
                    allChecked = false;
                }
            });
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked; // Perbaikan dari kesalahan sebelumnya
            }
            toggleProposeButton(); // Perbarui status tombol setelah mengubah checkbox individu
        });

        // Event listener untuk dropdown verifikator (saat nilai berubah)
        $('#id_verifikator1, #id_verifikator2, #id_verifikator3').on('change', function() {
            toggleProposeButton(); // Perbarui status tombol setelah verifikator dipilih
        });

        // Event listener untuk submit form (dengan konfirmasi SweetAlert2)
        if (formPemindahan) {
            formPemindahan.addEventListener('submit', function(e) {
                e.preventDefault(); // Mencegah submit form default

                const verif1Selected = $('#id_verifikator1').val() !== '';
                const verif2Selected = $('#id_verifikator2').val() !== '';
                const verif3Selected = $('#id_verifikator3').val() !== '';
                let anyItemSelected = false;
                document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                    if (checkbox.checked) {
                        anyItemSelected = true;
                    }
                });

                if (!anyItemSelected) {
                    Swal.fire('Peringatan!', 'Tidak ada item yang dipilih untuk diusulkan.', 'warning');
                    return; // Hentikan proses jika tidak ada item yang dipilih
                }

                if (!verif1Selected || !verif2Selected || !verif3Selected) {
                    Swal.fire('Peringatan!', 'Harap pilih semua verifikator (V1, V2, V3) sebelum mengusulkan.', 'warning');
                    return; // Hentikan proses jika verifikator belum lengkap
                }

                // Tampilkan konfirmasi SweetAlert2
                Swal.fire({
                    title: 'Konfirmasi Usulan',
                    html: "Apakah Anda yakin ingin mengusulkan item terpilih untuk pemindahan?<br><br>" +
                        "Verifikator 1: <strong>" + $('#id_verifikator1 option:selected').text() + "</strong><br>" +
                        "Verifikator 2: <strong>" + $('#id_verifikator2 option:selected').text() + "</strong><br>" +
                        "Verifikator 3: <strong>" + $('#id_verifikator3 option:selected').text() + "</strong>",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Usulkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formPemindahan.submit(); // Lanjutkan submit form jika dikonfirmasi
                    }
                });
            });
        }

        // Panggil fungsi ini sekali saat halaman dimuat untuk mengatur status awal tombol
        toggleProposeButton();
    });
</script>
<?= $this->endSection() ?>