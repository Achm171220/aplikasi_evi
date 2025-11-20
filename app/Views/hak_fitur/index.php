<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <a href="<?= site_url('hak-fitur/new') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Hak Fitur
        </a>
    </div>

    <!-- Tampilkan Notifikasi -->
    <?php if ($session->getFlashdata('success')): ?>
        <div class="alert alert-success" role="alert"><?= $session->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($session->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= $session->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <table id="table-data" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th>Nama Pengguna</th>
                        <th style="width: 50%;">Level Hak Akses</th> <!-- Perlebar kolom ini -->
                        <th style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
                url: "<?= site_url('hak-fitur/list') ?>",
                type: "POST"
            },
            "fnCreatedRow": (nRow, aData, iDataIndex) => $(nRow).find('td:eq(0)').html(iDataIndex + 1),
            columnDefs: [{
                "orderable": false,
                "targets": [0, 3]
            }]
        });

        $('body').on('submit', '.form-delete', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Hak fitur pengguna ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
<?= $this->endSection() ?>