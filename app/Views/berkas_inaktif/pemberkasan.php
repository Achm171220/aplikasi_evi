<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <a href="<?= site_url('berkas-inaktif') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Berkas
        </a>
    </div>

    <!-- Tampilkan Notifikasi -->
    <?php if ($session->getFlashdata('success')): ?>
        <div class="alert alert-success" role="alert"><?= $session->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($session->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= $session->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Info Berkas Tujuan -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Detail Berkas Tujuan</h6>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kode Klasifikasi</dt>
                <dd class="col-sm-9">: <span class="badge text-bg-primary"> <?= esc($berkas['kode']) ?> </span></dd>
                <dt class="col-sm-3">Nama Berkas</dt>
                <dd class="col-sm-9">: <?= esc($berkas['nama_berkas']) ?></dd>
                <dt class="col-sm-3">Nomor Berkas</dt>
                <dd class="col-sm-9">: <?= esc($berkas['no_berkas'] ?? '-') ?></dd>
                <dt class="col-sm-3">Nomor Box</dt>
                <dd class="col-sm-9">: <?= esc($berkas['no_box'] ?? '-') ?></dd>
            </dl>
        </div>
    </div>

    <!-- Form untuk memilih dan menambahkan item -->
    <form id="form-add-items" action="<?= site_url('berkas-inaktif/add-items/' . $berkas['id']) ?>" method="post">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Pilih Item yang Akan Ditambahkan</h6>
            </div>
            <div class="card-body">
                <table id="table-unfiled-items" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check-all-unfiled"></th>
                            <th>No Dokumen</th>
                            <th>Judul Dokumen</th>
                            <th>Tahun Cipta</th>
                            <th>Klasifikasi</th> <!-- Tambahan -->
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">Tambahkan Item Terpilih ke Berkas Ini</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Tabel item yang belum diberkaskan
        const tableUnfiledItems = $('#table-unfiled-items').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('berkas-inaktif/ajaxListUnfiledItems') ?>",
                type: "POST"
            },
            columnDefs: [{
                    targets: 0,
                    orderable: false
                } // checkbox tidak bisa di-sort
            ],
            language: {
                emptyTable: "Semua item arsip sudah diberkaskan."
            }
        });

        // Event listener 'Check All'
        $('#check-all-unfiled').on('click', function() {
            $('.item-checkbox').prop('checked', this.checked);
        });

        // Konfirmasi sebelum submit
        $('#form-add-items').on('submit', function(e) {
            e.preventDefault();
            const selectedCount = $('.item-checkbox:checked').length;
            if (selectedCount === 0) {
                Swal.fire('Peringatan', 'Pilih minimal satu item untuk ditambahkan.', 'warning');
                return;
            }
            this.submit();
        });
    });
</script>
<?= $this->endSection() ?>