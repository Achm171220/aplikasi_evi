<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <table id="table-data" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th>Tahun</th>
                        <th>Semester</th>
                        <th>Unit Eselon 2</th>
                        <th>Diimpor oleh</th>
                        <th>Tanggal Import</th>
                        <th style="width: 10%;">Aksi</th>
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
                url: "<?= site_url('riwayat-import/list') ?>",
                type: "POST"
            },
            "fnCreatedRow": (nRow, aData, iDataIndex) => $(nRow).find('td:eq(0)').html(iDataIndex + 1),
            columnDefs: [{
                "orderable": false,
                "targets": [0, 6]
            }]
        });
    });
</script>
<?= $this->endSection() ?>