<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <?php if (has_permission('cud_arsip_inaktif')): // Asumsi hanya yang punya izin CUD bisa import 
        ?>
            <div class="btn-group">
                <a href="<?= site_url('berkas-inaktif/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Berkas
                </a>
                <button type="button" id="btn-import-berkas" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-excel"></i> Import dari Excel
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-data" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 14%;">Kode Unit</th>
                            <th style="width: 10%;">Kode Klasifikasi</th>
                            <th>No. & Nama Berkas</th>
                            <th style="width: 10%;">Kurun Waktu</th>
                            <th style="width: 10%;">Jumlah Item</th>
                            <th style="width: 5%;">No. Box</th>
                            <!-- KOLOM BARU DITAMBAHKAN -->
                            <th style="width: 10%;">Status</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Berkas Aktif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('berkas-inaktif/proses-import') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?php $validation = \Config\Services::validation(); ?>
                    <?php if (session()->getFlashdata('errors') || session()->getFlashdata('import_errors')): ?>
                        <div class="alert alert-danger pb-0">
                            <?php if (session()->getFlashdata('errors')): ?>
                                <p class="mb-1 fw-bold">Terjadi kesalahan validasi:</p>
                                <ul>
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (session()->getFlashdata('import_errors')): ?>
                                <p class="mb-1 fw-bold">Detail Kegagalan:</p>
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('import_errors') as $error): ?>
                                        <li><small><?= esc($error) ?></small></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <p>Silakan isi form di bawah dan unggah file Excel (.xlsx) sesuai dengan template.</p>
                    <a href="<?= site_url('berkas-inaktif/download-template') ?>" class="btn btn-sm btn-outline-success mb-3">
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
        $('#table-data').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('berkas-inaktif/list') ?>",
                type: "POST"
            },
            columnDefs: [{
                    "targets": 0, // No.
                    "orderable": false,
                    "searchable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "orderable": false,
                    "targets": [1, 3, 8] // Kode Unit, No. Berkas, Aksi
                },
                {
                    "className": "text-center",
                    "targets": [0, 4, 5, 6, 7] // No, Kurun Waktu, Jumlah, Box, Status
                }
            ],
            "drawCallback": function(settings) {
                // Cari semua elemen dengan atribut data-bs-toggle="tooltip" di dalam tabel
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('#table-data [data-bs-toggle="tooltip"]'));
                // Inisialisasi setiap tooltip
                const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });

        // Event handler untuk konfirmasi Hapus (tidak berubah)
        $('body').on('submit', '.form-delete', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data berkas akan dihapus. Pastikan tidak ada item di dalamnya.",
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

        // --- SCRIPT BARU UNTUK KONFIRMASI TUTUP/BUKA BERKAS ---
        $('body').on('submit', '.form-toggle-status', function(e) {
            e.preventDefault();
            const form = this;
            // Cek dari URL action apakah ini proses 'buka' atau 'tutup'
            const isOpening = form.action.includes('/buka/');
            const actionText = isOpening ? 'dibuka kembali' : 'ditutup';
            const confirmButtonText = isOpening ? 'Ya, Buka!' : 'Ya, Tutup!';

            Swal.fire({
                title: 'Konfirmasi',
                html: `Anda yakin berkas ini akan <strong>${actionText}</strong>? <br><small>Jika ditutup, item baru tidak bisa ditambahkan.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isOpening ? '#198754' : '#dc3545', // Hijau untuk buka, Merah untuk tutup
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
        <?php if (session()->getFlashdata('show_import_modal')): ?>
            var importModal = new bootstrap.Modal(document.getElementById('importModal'));
            importModal.show();
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>