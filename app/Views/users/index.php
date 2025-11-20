<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <?php if (session()->get('role_access') === 'superadmin' ): // Hanya Superadmin yang bisa melihat tombol ini 
        ?>
            <div class="btn-group">
                <a href="<?= site_url('users/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah User
                </a>
                <button type="button" id="btn-import-user" class="btn btn-success me-1" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-excel"></i> Import User
                </button>
                <!-- --- TOMBOL BARU DI SINI --- -->
                <a href="<?= site_url('users/export-excel') ?>" class="btn btn-info">
                    <i class="bi bi-download"></i> Ekspor Excel
                </a>
            </div>
        <?php elseif (session()->get('role_access') === 'admin'|| session()->get('role_access') === 'manager'): // Admin hanya bisa tambah dan import 
        ?>
            <div class="btn-group">
                <a href="<?= site_url('users/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah User
                </a>
                <button type="button" id="btn-import-user" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-excel"></i> Import User
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tampilkan Notifikasi Sukses -->
    <?php if ($session->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $session->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tampilkan Notifikasi Error -->
    <?php if ($session->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $session->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-users" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role Access</th> <!-- <-- Judul kolom disesuaikan -->
                            <th>Role Jabatan</th> <!-- <-- KOLOM BARU -->
                            <th>Unit Kerja (Es. 3)</th>
                            <th>Status</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Isi tabel akan dimuat oleh DataTables melalui AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('users/import') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Tampilkan error validasi & import -->
                    <?php $validation = \Config\Services::validation(); ?>
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger pb-0">
                            <ul>
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('import_errors')): ?>
                        <div class="alert alert-warning">
                            <p class="fw-bold">Detail Kegagalan:</p>
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('import_errors') as $error): ?>
                                    <li><small><?= esc($error) ?></small></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <p>Unggah file Excel (.xlsx) sesuai template. Role yang diizinkan hanya 'admin' dan 'user'.</p>
                    <a href="<?= site_url('users/template') ?>" class="btn btn-sm btn-outline-success mb-3">
                        <i class="bi bi-download me-2"></i> Unduh Template
                    </a>
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Pilih file Excel</label>
                        <input class="form-control" type="file" name="file_excel" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Unggah dan Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables
        const table = $('#table-users').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('users/list') ?>",
                type: "POST"
            },
            "fnCreatedRow": function(nRow, aData, iDataIndex) {
                $(nRow).find('td:eq(0)').html(iDataIndex + 1);
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
                    "targets": 7 // Aksi (kolom ke-8, indeks 7)
                }
            ]
        });
        // Event delegation untuk menangani submit pada form hapus
        $('body').on('submit', '.form-delete', function(e) {
            e.preventDefault(); // Mencegah form langsung disubmit
            const form = this;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang akan dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user menekan "Ya, Hapus!", maka submit form-nya
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