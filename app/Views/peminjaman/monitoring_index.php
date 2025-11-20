<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <!-- Notifikasi sudah ditangani secara global -->

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-data" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th>Tgl Pinjam</th>
                            <th>Nama Peminjam</th>
                            <th>Unit Peminjam</th>
                            <th>Item/Berkas</th>
                            <th>Tgl Rencana Kembali</th>
                            <th>Tgl Aktual Kembali</th>
                            <th>Status</th>
                            <th style="width: 10%;">Aksi</th>
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
        // Inisialisasi DataTables
        const table = $('#table-data').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('peminjaman/monitoring/list') ?>",
                type: "POST"
            },
            columnDefs: [{
                    "targets": 0,
                    "orderable": false,
                    "searchable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "orderable": false,
                    "targets": -1
                }, // Kolom Aksi
                {
                    "className": "text-center",
                    "targets": [0, 5, 6, 7]
                } // No., Tanggal, Status
            ],
            "drawCallback": function(settings) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('#table-data [data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });

        // Event handler untuk konfirmasi hapus
        $('body').on('submit', '.form-delete', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data peminjaman ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>