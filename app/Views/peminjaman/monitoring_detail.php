<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Peminjaman</h1>
        <a href="<?= site_url('peminjaman/monitoring') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Notifikasi sudah ditangani secara global -->

    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-info-circle-fill me-2"></i>Informasi Peminjaman</h6>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">Detail Peminjam</h5>
                    <table class="table table-borderless table-sm info-table">
                        <tr>
                            <td width="35%">Nama Peminjam</td>
                            <td>: <?= esc($peminjaman['peminjam_nama']) ?></td>
                        </tr>
                        <tr>
                            <td>Unit Peminjam</td>
                            <td>: <?= esc($peminjaman['peminjam_unit']) ?></td>
                        </tr>
                        <tr>
                            <td>Tanggal Pinjam</td>
                            <td>: <?= date('d F Y', strtotime($peminjaman['tgl_pinjam'])) ?></td>
                        </tr>
                        <tr>
                            <td>Tgl. Kembali Rencana</td>
                            <td>: <?= date('d F Y', strtotime($peminjaman['tgl_kembali_rencana'])) ?></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>:
                                <?php if ($peminjaman['status'] === 'dipinjam'): ?>
                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                <?php elseif ($peminjaman['status'] === 'dikembalikan'): ?>
                                    <span class="badge bg-success">Dikembalikan</span>
                                <?php else: // Terlambat 
                                ?>
                                    <span class="badge bg-danger">Terlambat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">Detail Arsip</h5>
                    <table class="table table-borderless table-sm info-table">
                        <?php if (!empty($peminjaman['id_item_aktif'])): ?>
                            <tr>
                                <td width="35%">Tipe Arsip</td>
                                <td>: Item</td>
                            </tr>
                            <tr>
                                <td>No. Dokumen</td>
                                <td>: <?= esc($peminjaman['no_dokumen'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Judul Item</td>
                                <td>: <?= esc($peminjaman['judul_dokumen'] ?? '-') ?></td>
                            </tr>
                        <?php elseif (!empty($peminjaman['id_berkas_aktif'])): ?>
                            <tr>
                                <td width="35%">Tipe Arsip</td>
                                <td>: Berkas</td>
                            </tr>
                            <tr>
                                <td>No. Berkas</td>
                                <td>: <?= esc($peminjaman['no_berkas'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Nama Berkas</td>
                                <td>: <?= esc($peminjaman['nama_berkas'] ?? '-') ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-muted fst-italic">Arsip sudah tidak terhubung.</td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td>Dicatat oleh</td>
                            <td>: <?= esc($peminjaman['created_by_name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td>: <?= esc($peminjaman['keterangan'] ?? '-') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <hr>
            <p class="text-uppercase text-muted small mb-2">Proses Pengembalian</p>
            <?php if ($peminjaman['status'] === 'dipinjam' || $peminjaman['status'] === 'terlambat'): ?>
                <form action="<?= site_url('peminjaman/monitoring/kembalikan') ?>" method="post" id="form-pengembalian">
                    <input type="hidden" name="id" value="<?= $peminjaman['id'] ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tgl_kembali_aktual" class="form-label">Tanggal Pengembalian Aktual</label>
                            <input type="date" class="form-control" id="tgl_kembali_aktual" name="tgl_kembali_aktual" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-8 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-auto"><i class="bi bi-arrow-return-left me-2"></i>Konfirmasi Pengembalian</button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-success fw-bold">Arsip ini sudah dikembalikan pada: <?= date('d F Y', strtotime($peminjaman['tgl_kembali_aktual'])) ?>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Proses submit form pengembalian (AJAX)
        $('#form-pengembalian').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);

            Swal.fire({
                title: 'Konfirmasi Pengembalian',
                html: 'Anda yakin ingin mencatat pengembalian arsip ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kembalikan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Berhasil!', response.message, 'success')
                                    .then(() => {
                                        window.location.reload(); // Refresh halaman untuk update status
                                    });
                            } else {
                                Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                            }
                        },
                        error: function(jqXHR) {
                            const msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Terjadi kesalahan pada server.';
                            Swal.fire('Error!', msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>