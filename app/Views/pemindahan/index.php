<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pemindahan Arsip BPKP</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.min.css">
    <!-- Optional: Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 100%;
        }

        .card-header {
            background-color: #0d6efd !important;
        }

        .table thead th {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">Sistem Arsip BPKP</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- Menu User -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            User
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownUser">
                            <li><a class="dropdown-item" href="<?= base_url('pemindahan') ?>">Usulan Pemindahan</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('pemindahan/monitoring') ?>">Monitoring Usulan</a></li>
                            <li><a class="dropdown-item" href="#">Buat Berita Acara</a></li>
                        </ul>
                    </li>
                    <!-- Menu Admin -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                            <li><a class="dropdown-item" href="<?= base_url('pemindahan/verifikasi/1') ?>">Verifikasi 1</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('pemindahan/verifikasi/2') ?>">Verifikasi 2</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('pemindahan/verifikasi/3') ?>">Verifikasi 3</a></li>
                            <li><a class="dropdown-item" href="#">Eksekusi Pemindahan</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <!-- Konten Halaman Usulan Pemindahan Arsip Aktif (yang sudah ada) -->
        <div class="card shadow-sm mb-4">
            <div class="card-header text-white">
                <h4 class="mb-0">Usulan Pemindahan Arsip Aktif</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">Pilih item arsip aktif yang ingin Anda usulkan untuk pemindahan.</p>

                <?php if (empty($items)): ?>
                    <div class="alert alert-info text-center" role="alert">
                        Tidak ada arsip aktif yang tersedia untuk diusulkan pemindahan saat ini.
                    </div>
                <?php else: ?>
                    <form action="<?= base_url('pemindahan/propose') ?>" method="post" id="formPemindahan">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-success" id="proposeButton" disabled>
                                Usulkan Pemindahan Item Terpilih
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width: 5%;">
                                            <input type="checkbox" id="selectAllItems" class="form-check-input">
                                        </th>
                                        <th style="width: 15%;">No. Dokumen</th>
                                        <th style="width: 30%;">Judul Dokumen</th>
                                        <th style="width: 15%;">Tahun Cipta</th>
                                        <th style="width: 20%;">Lokasi Simpan</th>
                                        <th style="width: 15%;">Status Arsip</th>
                                        <th style="width: 10%;">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="selected_items[]" value="<?= esc($item['id']) ?>" class="item-checkbox form-check-input">
                                            </td>
                                            <td><?= esc($item['no_dokumen']) ?></td>
                                            <td><?= esc($item['judul_dokumen']) ?></td>
                                            <td><?= esc($item['tahun_cipta']) ?></td>
                                            <td><?= esc($item['lokasi_simpan']) ?></td>
                                            <td><span class="badge bg-primary"><?= esc($item['status_arsip']) ?></span></td>
                                            <td><?= !empty($item['admin_notes']) ? '<span class="text-danger fst-italic">' . esc($item['admin_notes']) . '</span>' : '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SCRIPT BAWAH (SAMA SEPERTI SEBELUMNYA) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= session()->getFlashdata('success') ?>',
                showConfirmButton: false,
                timer: 3000
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= session()->getFlashdata('error') ?>',
                showConfirmButton: false,
                timer: 3000
            });
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAllItems');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const proposeButton = document.getElementById('proposeButton');
            const formPemindahan = document.getElementById('formPemindahan');

            function toggleProposeButton() {
                let anyChecked = false;
                itemCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        anyChecked = true;
                    }
                });
                proposeButton.disabled = !anyChecked;
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    itemCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    toggleProposeButton();
                });
            }

            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    let allChecked = true;
                    itemCheckboxes.forEach(cb => {
                        if (!cb.checked) {
                            allChecked = false;
                        }
                    });
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                    }
                    toggleProposeButton();
                });
            });

            if (formPemindahan) {
                formPemindahan.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Konfirmasi Usulan',
                        text: "Apakah Anda yakin ingin mengusulkan item terpilih untuk pemindahan?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Usulkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            formPemindahan.submit();
                        }
                    });
                });
            }
            toggleProposeButton();
        });
    </script>
</body>

</html>