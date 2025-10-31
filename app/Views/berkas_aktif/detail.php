<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div class="page-title">
            <h1 class="h3 mb-0 text-gray-800">Detail Informasi Berkas</h1>
            <p class="mb-0 text-muted small">No. Berkas: <?= esc($berkas['no_berkas'] ?? 'Belum Ada') ?></p>
        </div>
        <div>
            <!-- Tombol Aksi Tutup/Buka Berkas -->
            <?php if (has_permission('cud_arsip')): ?>
                <?php if ($berkas['status_tutup'] === 'terbuka'): ?>
                    <form action="<?= site_url('berkas-aktif/tutup/' . $berkas['id']) ?>" method="post" class="d-inline form-toggle-status">
                        <button type="submit" class="btn btn-outline-danger me-2"><i class="bi bi-lock-fill me-1"></i> Tutup Berkas</button>
                    </form>
                <?php else: ?>
                    <form action="<?= site_url('berkas-aktif/buka/' . $berkas['id']) ?>" method="post" class="d-inline form-toggle-status">
                        <button type="submit" class="btn btn-outline-success me-2"><i class="bi bi-unlock-fill me-1"></i> Buka Kembali</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?= site_url('berkas-aktif') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Notifikasi sudah ditangani secara global -->

    <!-- === KARTU INFORMASI UTAMA & QR CODE === -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-info-circle-fill me-2"></i>Informasi Berkas: <?= esc($berkas['nama_berkas']) ?></h6>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- KOLOM KIRI (Detail Utama & Retensi) -->
                <div class="col-md-8">
                    <p class="text-uppercase text-muted small mb-2">Detail Utama</p>
                    <table class="table table-borderless table-sm info-table">
                        <tr>
                            <td width="35%">Klasifikasi</td>
                            <td>: <?= esc($berkas['kode_klasifikasi'] . ' - ' . $berkas['nama_klasifikasi']) ?></td>
                        </tr>
                        <tr>
                            <td>Kurun Waktu</td>
                            <td>: <?= $berkas['thn_item_awal'] && $berkas['thn_item_akhir'] ? $berkas['thn_item_awal'] . ' - ' . $berkas['thn_item_akhir'] : '-' ?></td>
                        </tr>
                        <tr>
                            <td>Jumlah Item</td>
                            <td>: <?= $berkas['jumlah_item'] ?></td>
                        </tr>
                        <tr>
                            <td>Nomor Box</td>
                            <td>: <?= esc($berkas['no_box'] ?? '-') ?></td>
                        </tr>
                    </table>

                    <p class="text-uppercase text-muted small my-3">Retensi & Status</p>
                    <table class="table table-borderless table-sm info-table">
                        <tr>
                            <td width="35%">Retensi Aktif</td>
                            <td>: <?= esc($berkas['umur_aktif']) ?> Tahun</td>
                        </tr>
                        <tr>
                            <td>Retensi Inaktif</td>
                            <td>: <?= esc($berkas['umur_inaktif']) ?> Tahun</td>
                        </tr>
                        <tr>
                            <td>Penyusutan Akhir</td>
                            <td>: <?= esc(ucfirst($berkas['nasib_akhir'])) ?></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>:
                                <?php if ($berkas['status_tutup'] === 'tertutup'): ?>
                                    <span class="badge text-bg-danger"><i class="bi bi-lock-fill"></i> Tertutup</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success"><i class="bi bi-unlock-fill"></i> Terbuka</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <!-- KOLOM KANAN (QR Code & Informasi Tambahan) -->
                    <div class="text-center">
                        <p class="text-uppercase text-muted small mb-2">QR Code Berkas</p>
                        <?php if ($berkas['qr_code']): ?>
                            <img src="<?= $berkas['qr_code_url'] ?>" alt="QR Code Berkas" class="img-fluid border  rounded p-2 mb-3" style="max-width: 150px;">
                            <div class="d-grid gap-2">
                                <button class="btn btn-sm btn-outline-primary ms-2" onclick="printQrCode('<?= $berkas['qr_code_url'] ?>', '<?= esc($berkas['nama_berkas']) ?>')">
                                    <i class="bi bi-printer me-1"></i> Cetak QR
                                </button>
                            </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted fst-italic">QR Code belum tersedia</p>
                <?php endif; ?>
                <hr class="my-3">
                <p class="text-uppercase text-muted small mb-2">Informasi Tambahan</p>
                <table class="table table-borderless table-sm info-table" style="max-width: 100%;">
                    <tr>
                        <td width="17%">Dibuat Pada</td>
                        <td>: <?= date('l, d F Y H:i', strtotime($berkas['created_at'])) ?> WIB</td>
                    </tr>
                    <?php if ($berkas['status_tutup'] === 'tertutup' && $berkas['updated_at'] !== $berkas['created_at']): ?>
                        <tr>
                            <td>Ditutup Pada</td>
                            <td>: <?= date('l, d F Y H:i', strtotime($berkas['updated_at'])) ?> WIB</td>
                        </tr>
                    <?php endif; ?>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- === BAGIAN BAWAH DENGAN DUA KOLOM (Daftar Isi & Kosongkan Kolom Kanan) === -->
<div class="row">
    <!-- KOLOM KIRI: DAFTAR ISI BERKAS -->
    <div class="col-lg-12"> <!-- Mengambil lebar penuh -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-list-task me-2"></i>Daftar Isi Berkas Arsip Aktif</h6>
            </div>
            <div class="card-body">
                <table id="table-items-in-berkas" class="table table-hover table-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th>No. Dokumen</th>
                            <th>Judul Dokumen</th>
                            <th>Tgl Dokumen</th>
                            <th style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .info-table td {
        padding-top: 0.1rem;
        padding-bottom: 0.1rem;
    }

    .page-header .page-title {
        line-height: 1.2;
    }
</style>
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
                url: "<?= site_url('berkas-aktif/ajaxListItemsInBerkas/' . $berkas['id']) ?>",
                type: "POST"
            },
            columnDefs: [{
                targets: -1,
                orderable: false
            }],
            language: {
                emptyTable: "Belum ada item arsip yang diberkaskan di sini."
            }
        });

        // Tabel 2: Item yang belum diberkaskan (hanya diinisialisasi jika ada formnya)
        if ($('#table-unfiled-items').length > 0) {
            const tableUnfiledItems = $('#table-unfiled-items').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "<?= site_url('berkas-aktif/ajaxListUnfiledItems') ?>",
                    type: "POST"
                },
                columnDefs: [{
                    targets: 0,
                    orderable: false
                }],
                language: {
                    emptyTable: "Semua item arsip yang tersedia sudah diberkaskan."
                }
            });

            $('#check-all-unfiled').on('click', function() {
                $('.item-checkbox-unfiled').prop('checked', this.checked);
            });

            $('#form-add-items').on('submit', function(e) {
                e.preventDefault();
                const selectedCount = $('.item-checkbox-unfiled:checked').length;
                if (selectedCount === 0) {
                    Swal.fire('Peringatan', 'Silakan pilih minimal satu item untuk ditambahkan.', 'warning');
                    return;
                }
                this.submit();
            });
        }

        // Konfirmasi untuk tutup/buka berkas
        $('body').on('submit', '.form-toggle-status', function(e) {
            e.preventDefault();
            const form = this;
            const isOpening = form.action.includes('/buka/');
            const actionText = isOpening ? 'dibuka kembali' : 'ditutup';
            Swal.fire({
                title: 'Konfirmasi',
                html: `Anda yakin berkas ini akan <strong>${actionText}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
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
                    $.post("<?= site_url('item-aktif/lepas-berkas') ?>", {
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

    function printQrCode(imageUrl, title) {
        const printWindow = window.open('', '_blank', 'height=300,width=300');
        printWindow.document.write('<html><head><title>Cetak QR Code</title>');
        printWindow.document.write('<style>body{font-family:sans-serif; text-align:center; padding:20px;}' +
            'img{max-width:100%; height:auto; display:block; margin:0 auto;}' +
            'p{margin-top:10px; font-weight:bold;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<img src="' + imageUrl + '">');
        printWindow.document.write('<p>' + title + '</p>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
</script>
<?= $this->endSection() ?>