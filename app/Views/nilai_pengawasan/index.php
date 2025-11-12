<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <a href="<?= site_url('nilai-pengawasan/new') ?>" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Tambah Nilai</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-data" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 30%;">Unit Eselon 2</th>
                            <th style="width: 10%;">Tahun</th>
                            <th style="width: 10%;">Skor</th>
                            <th style="width: 15%;">Kategori</th>
                            <th style="width: 15%;">Dicatat Oleh</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
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
                url: "<?= site_url('nilai-pengawasan/list') ?>",
                type: "POST",
                data: function(d) {
                    d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                },
                error: function(xhr, error, code) {
                    console.log('AJAX Error:', xhr.responseText);
                    alert('Error loading data. Check console for details.');
                }
            },
            columns: [{
                    data: 0,
                    orderable: false,
                    searchable: false
                },
                {
                    data: 1
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4
                },
                {
                    data: 5
                },
                {
                    data: 6,
                    orderable: false
                }
            ],
            columnDefs: [{
                className: "text-center",
                targets: [0, 2, 3, 4, 6]
            }],
            order: [
                [2, 'desc']
            ], // Order by Tahun descending
            language: {
                processing: "Memuat data...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // Handle delete form
        $(document).on('submit', '.form-delete', function(e) {
            e.preventDefault();
            if (confirm('Yakin ingin menghapus data ini?')) {
                const form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        $('#table-data').DataTable().ajax.reload();
                        alert('Data berhasil dihapus');
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>