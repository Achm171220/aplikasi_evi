<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <?php if ($session->getFlashdata('success')): ?>
        <div class="alert alert-success" role="alert"><?= $session->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($session->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= $session->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= site_url('pemberkasan/process') ?>" method="post" id="form-pemberkasan">
        <div class="row">
            <!-- Kolom Kiri: Item yang akan diberkaskan -->
            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">1. Pilih Item Arsip</h6>
                    </div>
                    <div class="card-body">
                        <table id="table-unfiled-items" class="table table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%;"><input type="checkbox" id="check-all-items" class="form-check-input"></th>
                                    <th>No. Dokumen</th>
                                    <th>Judul Dokumen</th>
                                    <th>Tahun</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Berkas Tujuan -->
            <div class="col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">2. Pilih Berkas Tujuan</h6>
                    </div>
                    <div class="card-body">
                        <table id="table-berkas" class="table table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">Pilih</th>
                                    <th>Nama Berkas</th>
                                    <th>Isi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check-circle me-2"></i> Proses Pemberkasan
                </button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Tabel Kiri: Item
        const tableItems = $('#table-unfiled-items').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('pemberkasan/list-unfiled-items') ?>",
                type: "POST"
            },
            columnDefs: [{
                targets: 0,
                orderable: false
            }],
            language: {
                emptyTable: "Semua item sudah diberkaskan."
            }
        });

        // Tabel Kanan: Berkas
        const tableBerkas = $('#table-berkas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('pemberkasan/list-berkas') ?>",
                type: "POST"
            },
            columnDefs: [{
                targets: 0,
                orderable: false
            }]
        });

        // Event listener
        $('#check-all-items').on('click', function() {
            $('.item-checkbox').prop('checked', this.checked);
        });

        $('#form-pemberkasan').on('submit', function(e) {
            e.preventDefault();
            const selectedItems = $('.item-checkbox:checked').length;
            const selectedBerkas = $('.berkas-radio:checked').length;

            if (selectedItems === 0) {
                Swal.fire('Peringatan', 'Pilih minimal satu item dari tabel kiri!', 'warning');
                return;
            }
            if (selectedBerkas === 0) {
                Swal.fire('Peringatan', 'Pilih satu berkas tujuan dari tabel kanan!', 'warning');
                return;
            }

            this.submit();
        });
    });
</script>
<?= $this->endSection() ?>