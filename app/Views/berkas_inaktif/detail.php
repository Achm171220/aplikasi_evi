<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Detail Isi Berkas</h1>
            <p class="mb-0 text-muted"><?= esc($berkas['nama_berkas']) ?></p>
        </div>
        <a href="<?= site_url('berkas-inaktif') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Berkas
        </a>
    </div>

    <!-- Tampilkan Notifikasi -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Layout baru dengan dua kolom -->
    <div class="row">
        <!-- Kolom Kiri: Informasi dan Daftar Isi Berkas -->
        <div class="col-lg-7">
            <!-- Informasi Berkas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-info-circle-fill me-2"></i>Informasi Berkas</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nama Berkas</dt>
                        <dd class="col-sm-8">: <?= esc($berkas['nama_berkas']) ?></dd>
                        <dt class="col-sm-4">Nomor Berkas</dt>
                        <dd class="col-sm-8">: <?= esc($berkas['no_berkas'] ?? '-') ?></dd>
                        <dt class="col-sm-4">Nomor Box</dt>
                        <dd class="col-sm-8">: <?= esc($berkas['no_box'] ?? '-') ?></dd>
                        <dt class="col-sm-4">Kurun Waktu</dt>
                        <dd class="col-sm-8">: <?= $berkas['thn_item_awal'] && $berkas['thn_item_akhir'] ? $berkas['thn_item_awal'] . ' - ' . $berkas['thn_item_akhir'] : '-' ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Daftar Item yang SUDAH ADA di dalam Berkas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-collection-fill me-2"></i>Daftar Item di Dalam Berkas Ini</h6>
                </div>
                <div class="card-body">
                    <table id="table-items-in-berkas" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>No. Dokumen</th>
                                <th>Judul Dokumen</th>
                                <th>Tgl Dokumen</th>
                                <th style="width: 20%;" >Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tambah Item ke Berkas -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success"><i class="bi bi-plus-circle-fill me-2"></i>Tambah Item ke Berkas Ini</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Pilih dari daftar item di bawah yang belum diberkaskan.</p>
                    <form id="form-add-items" action="<?= site_url('berkas-inaktif/add-items/' . $berkas['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <table id="table-unfiled-items" class="table table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%;"><input type="checkbox" id="check-all-unfiled" class="form-check-input"></th>
                                    <th>No Dokumen</th>
                                    <th>Judul Dokumen</th>
                                    <th>Tahun Cipta</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success mt-3">
                                <i class="fas fa-plus-circle me-2"></i> Tambahkan Item Terpilih
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Tabel 1: Item yang sudah ada di dalam berkas
        const tableItemsInBerkas = $('#table-items-in-berkas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('berkas-inaktif/ajaxListItemsInBerkas/' . $berkas['id']) ?>",
                type: "POST"
            },
            columnDefs: [{
                targets: -1,
                orderable: false
            }],
            language: {
                emptyTable: "Belum ada item arsip yang diberkaskan di sini.",
                zeroRecords: "Item arsip tidak ditemukan.",
                infoEmpty: ""
            }
        });

        // Tabel 2: Item yang belum diberkaskan (untuk ditambahkan)
        const tableUnfiledItems = $('#table-unfiled-items').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('berkas-inaktif/ajaxListUnfiledItems') ?>",
                type: "POST"
            },
            columnDefs: [{
                targets: 0,
                orderable: false,
                className: 'text-center'
            }],
            language: {
                emptyTable: "Semua item arsip sudah diberkaskan."
            }
        });

        // --- EVENT LISTENERS ---

        // Tombol Lepas Berkas (untuk tabel 1)
        $('#table-items-in-berkas tbody').on('click', '.btn-lepas-berkas', function() {
            const itemId = $(this).data('id');
            const itemJudul = $(this).data('judul');

            Swal.fire({
                title: 'Konfirmasi',
                html: `Anda yakin ingin melepaskan item "<b>${itemJudul}</b>" dari berkas ini?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Lepaskan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("<?= site_url('item-inaktif/lepas-berkas') ?>", {
                        id: itemId
                    }, function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Berhasil!', response.message, 'success');
                            // Muat ulang kedua tabel untuk sinkronisasi data
                            tableItemsInBerkas.ajax.reload();
                            tableUnfiledItems.ajax.reload();
                        } else {
                            Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                        }
                    }, 'json').fail(function() {
                        Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
                    });
                }
            });
        });

        // Check All untuk tabel 2 (unfiled items)
        $('#check-all-unfiled').on('click', function() {
            // Cari checkbox di dalam tabel yang relevan saja
            const checkboxes = $(this).closest('table').find('.item-checkbox-unfiled');
            checkboxes.prop('checked', this.checked);
        });

        // Konfirmasi sebelum submit form penambahan item
        $('#form-add-items').on('submit', function(e) {
            e.preventDefault();
            const selectedCount = $('.item-checkbox-unfiled:checked').length;

            if (selectedCount === 0) {
                Swal.fire('Peringatan', 'Silakan pilih minimal satu item untuk ditambahkan.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi',
                html: `Anda akan menambahkan <b>${selectedCount}</b> item ke dalam berkas ini.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tambahkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Lanjutkan submit form biasa
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>