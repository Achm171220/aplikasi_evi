<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <a href="<?= site_url('unit-kerja-es3/new') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Eselon 3
        </a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            <table id="table-data" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th>Kode</th>
                        <th>Nama Unit Eselon 3</th>
                        <th>Induk Eselon 2</th>
                        <th>Induk Eselon 1</th>
                        <th style="width: 15%;">Aksi</th>
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
                url: "<?= site_url('unit-kerja-es3/list') ?>",
                type: "POST"
            },
            "fnCreatedRow": function(nRow, aData, iDataIndex) {
                $(nRow).find('td:eq(0)').html(iDataIndex + 1);
            },
            columnDefs: [{
                "orderable": false,
                "targets": [0, 5]
            }]
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
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>