<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-activity-logs" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Role Access</th> <!-- <-- KOLOM BARU -->
                            <th>Aksi</th>
                            <th>Target</th>
                            <th>IP Address</th>
                            <th style="width: 10%;">Detail</th>
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
        const table = $('#table-activity-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('activity-logs/list') ?>",
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
                }, // Kolom Detail
                {
                    "className": "text-center",
                    "targets": [3, 4, 5, 6]
                } // Role Access, Aksi, Target, IP
            ],
            "order": [
                [1, "desc"]
            ], // Urutkan berdasarkan waktu terbaru
            // Inisialisasi Tooltip setelah tabel digambar ulang
            "drawCallback": function(settings) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('#table-activity-logs [data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });

        // Tambahkan event listener untuk tombol detail log (akan menampilkan modal/popup)
        // Example:
        // $('body').on('click', '.btn-view-detail', function() {
        //     const logId = $(this).data('id');
        //     // Fetch detail log via AJAX and display in a modal
        //     $.get('<?= site_url('activity-logs/detail/') ?>' + logId, function(response) {
        //         // Display response in a SweetAlert or Bootstrap Modal
        //         Swal.fire({
        //             title: 'Detail Log',
        //             html: `<pre class="text-start">${JSON.stringify(response, null, 2)}</pre>`,
        //             width: 800,
        //             heightAuto: false,
        //             confirmButtonText: 'Tutup'
        //         });
        //     }, 'json');
        // });
    });
</script>
<?= $this->endSection() ?>