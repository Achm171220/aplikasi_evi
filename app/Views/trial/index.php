<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>

    <!-- Bootstrap 5 CSS CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables CSS CDN -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.css" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            padding: 2rem;
        }

        h1,
        h4,
        h6 {
            color: #32325d;
            font-weight: 600;
        }

        .card {
            border: none;
            border-radius: .5rem;
            box-shadow: 0 0 2rem 0 rgba(136, 152, 170, .15);
            background-color: #fff;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        /* Styling DataTables (dari style.css Anda) */
        .dataTables_wrapper .row {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .form-control,
        .dataTables_wrapper .form-select {
            background-color: #fff;
            border-color: #dee2e6;
            color: #212529;
            border-radius: .375rem;
        }

        .dataTables_wrapper .form-control:focus,
        .dataTables_wrapper .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .table thead th {
            font-weight: 600;
            color: #32325d;
            border-bottom-width: 1px;
            background-color: #f8f9fa;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 0.85rem 1rem;
            color: #212529;
            border-color: #eff2f5;
        }

        .pagination .page-item .page-link {
            border-radius: .375rem;
            margin: 0 2px;
            border: none;
            color: #0d6efd;
            background-color: #f8f9fa;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pagination .page-item:not(.active) .page-link:hover {
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Koneksi API Sukses!</h4>
            <p>Data pegawai aktif berhasil diambil dari API Stara BPKP secara dinamis.</p>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Pegawai Aktif</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-stara-pegawai" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No.</th>
                                <th>NIP</th>
                                <th>Nama Pegawai</th>
                                <th>Jabatan</th>
                                <th>Unit Eselon 2</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan dimuat oleh DataTables AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS CDN -->
    <script type="text/javascript" src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.js"></script>

    <script>
        $(document).ready(function() {
            $('#table-stara-pegawai').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "<?= site_url('trial/list-data') ?>", // URL ke controller kita
                    type: "POST",
                    error: function(jqXHR, textStatus, errorThrown) {
                        let errorMessage = 'Terjadi kesalahan saat memuat data. ';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                            errorMessage += jqXHR.responseJSON.error;
                        } else {
                            errorMessage += 'Periksa koneksi atau log server.';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memuat Data!',
                            html: errorMessage
                        });
                        console.error("DataTables AJAX error:", textStatus, errorThrown, jqXHR);
                    }
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
                        "orderable": true,
                        "targets": [1, 2, 3, 4, 5]
                    }, // Kolom yang bisa diurutkan
                    {
                        "searchable": false,
                        "targets": [0, 4, 5]
                    } // Kolom yang tidak bisa dicari
                ],
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Semua"]
                ],
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                    "infoFiltered": "(difilter dari _MAX_ total entri)",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "initComplete": function(settings, json) {
                    // Opsional: tampilkan SweetAlert sukses hanya jika data benar-benar berhasil dimuat
                    if (json && json.recordsTotal > 0 && !json.error) {
                        // Swal.fire({ ... })
                    }
                }
            });
        });
    </script>
</body>

</html>