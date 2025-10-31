<?= $this->extend('layout/template') ?>

<?= $this->section('styles') ?>
<!-- CSS Kustom untuk warna soft -->
<style>
    .bg-success-soft {
        background-color: rgba(25, 135, 84, 0.15);
        /* Warna success dengan opacity 15% */
    }

    .text-success {
        color: #0f5132 !important;
        /* Warna teks success yang lebih gelap */
    }

    .bg-secondary-soft {
        background-color: rgba(108, 117, 125, 0.15);
        /* Warna secondary dengan opacity 15% */
    }

    .text-secondary {
        color: #41464b !important;
    }

    /* Agar kolom aksi tidak wrap di mobile */
    #table-data td .btn-group {
        white-space: nowrap;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>

        <?php if (has_permission('cud_arsip')): ?>
            <div class="btn-group">
                <a href="<?= site_url('item-aktif/new') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Tambah Manual
                </a>
                <!-- Tombol untuk memicu Modal -->
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-excel"></i> Import dari Excel
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div id="import-messages">
        <!-- Pesan sukses -->

        <!-- Pesan error validasi file (misal, bukan xlsx) -->
        <?php if (session()->getFlashdata('errors')) : ?>
            <div class="alert alert-danger">
                <strong>Gagal mengupload:</strong>
                <ul class="mb-0 mt-2" style="padding-left: 20px;">
                    <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Pesan error detail per baris dari proses import -->
        <?php if (session()->getFlashdata('import_errors')) : ?>
            <div class="alert alert-warning">
                <strong>Detail Kegagalan Import:</strong>
                <ul class="mb-0 mt-2" style="padding-left: 20px;">
                    <?php foreach (session()->getFlashdata('import_errors') as $error) : ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- --- FORM FILTER BARU --- -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="filter-form" class="row align-items-end" action="<?= site_url('item-aktif') ?>" method="get">
                <?php
                $isSuperAdmin = session()->get('role_access') === 'superadmin';
                $disableEs2Filter = !$isSuperAdmin; // Admin tidak bisa ganti Es2 (disabled oleh PHP)

                // Gunakan ID untuk prefill
                $prefillEs2Filter = old('es2_id', $current_es2_filter_id ?? $id_es2_filter_prefill ?? '');
                $prefillEs3Filter = old('es3_id', $current_es3_filter_id ?? '');
                ?>
                <div class="col-md-5 mb-3">
                    <label for="filter_es2_id" class="form-label">Filter Unit Eselon 2</label>
                    <select class="form-select select2-filter" id="filter_es2_id" name="es2_id" style="width: 100%;" <?= $disableEs2Filter ? 'disabled' : '' ?>>
                        <option value="">-- Pilih Unit Eselon 2 --</option>
                        <?php foreach ($es2_filter_options as $opt): ?>
                            <option value="<?= esc($opt['id']) ?>" <?= ($prefillEs2Filter == $opt['id']) ? 'selected' : '' ?>><?= esc($opt['nama_es2']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5 mb-3">
                    <label for="filter_es3_id" class="form-label">Filter Unit Eselon 3</label>
                    <!-- PERBAIKAN UTAMA: Hapus atribut disabled dari sini -->
                    <select class="form-select select2-filter" id="filter_es3_id" name="es3_id" style="width: 100%;">
                        <option value="">-- Pilih Eselon 2 Dulu --</option>
                        <?php foreach ($es3_filter_options as $opt): ?>
                            <option value="<?= esc($opt['id']) ?>" <?= ($prefillEs3Filter == $opt['id']) ? 'selected' : '' ?>><?= esc($opt['nama_es3']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
    <!-- --- AKHIR FORM FILTER --- -->

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-data" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 15%;">Kode Unit</th>
                            <th style="width: 10%;">Kode Klasifikasi</th>
                            <th>No. Dokumen & Judul</th>
                            <th style="width: 10%;">Tgl Dokumen</th>
                            <th style="width: 15%;">Status Berkas</th>
                            <th style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- modal import  -->
<!-- MODAL UNTUK IMPORT EXCEL (DIPERBARUI) -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Item Aktif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Ganti <form> dengan yang baru -->
            <form action="<?= site_url('item-aktif/proses-import') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Tampilkan error validasi di sini -->
                    <?php $validation = \Config\Services::validation(); ?>
                    <?php if ($validation->getErrors()): ?>
                        <div class="alert alert-danger">
                            <p class="mb-1 fw-bold">Terjadi kesalahan validasi:</p>
                            <?= $validation->listErrors() ?>
                        </div>
                    <?php endif; ?>
                    <!-- Tampilkan error import (jika ada) -->
                    <?php if (session()->getFlashdata('import_errors')): ?>
                        <div class="alert alert-warning">
                            <p class="mb-1 fw-bold">Detail Kegagalan:</p>
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('import_errors') as $error): ?>
                                    <li><small><?= esc($error) ?></small></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <p>Silakan isi form di bawah dan unggah file Excel (.xlsx) sesuai dengan template.</p>
                    <a href="<?= site_url('item-aktif/download-template') ?>" class="btn btn-sm btn-outline-success mb-3">
                        <i class="bi bi-download me-2"></i> Unduh Template
                    </a>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <input type="number" class="form-control" id="tahun" name="tahun" value="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="file_excel" class="form-label">Pilih file Excel</label>
                        <input class="form-control" type="file" id="file_excel" name="file_excel" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Unggah dan Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.select2-filter').select2({
            theme: 'bootstrap-5'
        });
        const filterEs2Select = $('#filter_es2_id');
        const filterEs3Select = $('#filter_es3_id');
        const filterForm = $('#filter-form');

        function loadEs3Filter(id_es2, selected_es3_id = null) {
            // PERBAIKAN: Jangan disabled saat 'Memuat...'
            filterEs3Select.html('<option value="">Memuat...</option>');
            if (!id_es2) {
                filterEs3Select.html('<option value="">-- Pilih Eselon 2 Dulu --</option>');
                return;
            }

            $.get(`<?= site_url('data/es3-by-es2/') ?>${id_es2}`, function(response) {
                filterEs3Select.html('<option value="">-- Pilih Unit Eselon 3 --</option>');
                if (response && response.length > 0) {
                    filterEs3Select.prop('disabled', false);
                    response.forEach(item => filterEs3Select.append(new Option(item.nama_es3, item.id)));
                } else {
                    // PERBAIKAN: Dropdown tetap aktif, tapi tampilkan pesan "Tidak tersedia"
                    filterEs3Select.prop('disabled', false); // Tetap aktif
                    filterEs3Select.append('<option value="">Unit Eselon 3 tidak tersedia</option>');
                }
                if (selected_es3_id) {
                    filterEs3Select.val(selected_es3_id).trigger('change');
                }
                filterEs3Select.trigger('change'); // Tambahkan trigger change untuk Select2
            }, 'json').fail(function() {
                // Jika AJAX gagal total
                filterEs3Select.html('<option value="">Gagal memuat data</option>').trigger('change');
                filterEs3Select.prop('disabled', false); // Pastikan tetap aktif
            });
        }

        filterEs2Select.on('change', function() {
            loadEs3Filter($(this).val());
        });

        const prefillFilterEs2 = '<?= old('es2_id', $current_es2_filter_id ?? $id_es2_filter_prefill ?? '') ?>';
        const prefillFilterEs3 = '<?= old('es3_id', $current_es3_filter_id ?? '') ?>';

        if (prefillFilterEs2) {
            filterEs2Select.val(prefillFilterEs2).trigger('change');
            $(document).one('ajaxStop', function() {
                if (prefillFilterEs3) {
                    filterEs3Select.val(prefillFilterEs3).trigger('change');
                }
            });
        }

        let table;

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#table-data')) {
                $('#table-data').DataTable().destroy();
            }

            table = $('#table-data').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "<?= site_url('item-aktif/list') ?>",
                    type: "POST",
                    data: function(d) {
                        d.es2_id_filter = filterEs2Select.val();
                        d.es3_id_filter = filterEs3Select.val();
                    }
                },
                columnDefs: [{
                        "targets": 0, // Targetkan kolom pertama (No.)
                        "orderable": false,
                        "searchable": false,
                        // Fungsi render ini akan menghitung dan menampilkan nomor urut
                        "render": function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        "orderable": false,
                        "targets": 6
                    }, // Kolom Aksi (indeks 6)
                    {
                        "className": "text-center",
                        "targets": [0, 4, 5]
                    } // No., Tgl Dokumen, Status Berkas
                ],
                // --- TAMBAHAN BARU: Handle jika tidak ada data ---
                "language": {
                    "zeroRecords": "Tidak ada item arsip yang ditemukan dengan filter yang dipilih.",
                    "emptyTable": "Tidak ada item arsip yang tersedia."
                }
            });
        }

        initializeDataTable();

        filterForm.on('submit', function(e) {
            e.preventDefault();
            const currentUrl = new URL(window.location.href);
            const es2Val = filterEs2Select.val();
            const es3Val = filterEs3Select.val();

            // Hapus parameter jika kosong
            if (es2Val) currentUrl.searchParams.set('es2_id', es2Val);
            else currentUrl.searchParams.delete('es2_id');
            if (es3Val) currentUrl.searchParams.set('es3_id', es3Val);
            else currentUrl.searchParams.delete('es3_id');

            window.history.pushState({}, '', currentUrl.toString());

            table.ajax.reload(function(json) {
                // --- PERBAIKAN UTAMA: SweetAlert jika data kosong setelah filter ---
                if (json.recordsFiltered === 0 && (filterEs2Select.val() || filterEs3Select.val())) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Data Tidak Ditemukan!',
                        text: 'Filter yang Anda terapkan tidak menghasilkan data item arsip. Coba filter lain.',
                        timer: 5000,
                        showConfirmButton: false
                    });
                }
            }, false);
        });

        $('body').on('submit', '.form-delete', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        $('body').on('click', '.btn-lepas-berkas', function() {
            const itemId = $(this).data('id');
            const itemJudul = $(this).data('judul');

            Swal.fire({
                title: 'Konfirmasi',
                html: `Anda yakin ingin melepaskan item "<b>${itemJudul}</b>" dari berkasnya?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Lepaskan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("<?= site_url('item-aktif/lepas-berkas') ?>", {
                        id: itemId
                    }, function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Berhasil!', response.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                        }
                    }, 'json').fail(function() {
                        Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
                    });
                }
            });
        });

        $('body').on('click', '.btn-pinjam-item', function() {
            const id = $(this).data('id');
            const judul = $(this).data('judul');

            $('#pinjamModalLabel').text('Pinjam Item Arsip');
            $('#id_pinjam_target').val(id);
            $('#type_pinjam_target').val('item');
            $('#item-berkas-info').text(judul);

            $('#form-peminjaman')[0].reset();
            $('#tgl_pinjam').val('<?= date('Y-m-d') ?>');

            var pinjamModal = new bootstrap.Modal(document.getElementById('pinjamModal'));
            pinjamModal.show();
        });

        $('#form-peminjaman').on('submit', function(e) {
            e.preventDefault();
            const type = $('#type_pinjam_target').val();
            const url = (type === 'item') ? '<?= site_url('item-aktif/pinjam-item') ?>' : '<?= site_url('berkas-aktif/pinjam-berkas') ?>';

            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#pinjamModal').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function(jqXHR) {
                    const msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Terjadi kesalahan pada server.';
                    Swal.fire('Error!', msg, 'error');
                }
            });
        });

        const importModalEl = document.getElementById('importModal');
        const importModalTrigger = document.getElementById('btn-import-item');
        if (importModalEl && importModalTrigger) {
            importModalEl.addEventListener('hidden.bs.modal', function(event) {
                importModalTrigger.focus();
            });
        }

        <?php if (session()->getFlashdata('show_import_modal')): ?>
            var importModal = new bootstrap.Modal(document.getElementById('importModal'));
            importModal.show();
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>