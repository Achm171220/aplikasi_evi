<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <a href="<?= site_url('berkas-aktif') ?>" class="btn btn-secondary">
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
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama Berkas</dt>
                        <dd class="col-sm-8">: <?= esc($berkas['nama_berkas']) ?></dd>
                        <dt class="col-sm-4">Nomor Berkas</dt>
                        <dd class="col-sm-8">: <?= esc($berkas['no_berkas'] ?? '-') ?></dd>
                        <dt class="col-sm-4">Nomor Box</dt>
                        <dd class="col-sm-8">: <?= esc($berkas['no_box'] ?? '-') ?></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Jumlah Item Saat Ini</dt>
                        <dd class="col-sm-7">: <span class="badge bg-primary fs-6"><?= $jumlahItemSaatIni ?> Item</span></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Form untuk memilih dan menambahkan item -->
    <form id="form-add-items" action="<?= site_url('berkas-aktif/add-items/' . $berkas['id']) ?>" method="post">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-list-task me-2"></i>Pilih Item yang Akan Ditambahkan</h6>
            </div>
            <div class="card-body">
                <?php if ($berkas['status_tutup'] === 'terbuka' && has_permission('cud_arsip')): ?>
                    <p class="small text-muted mb-3">Pilih dari daftar item di bawah yang belum diberkaskan.</p>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table id="table-unfiled-items" class="table table-hover table-sm" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%;"><input type="checkbox" id="check-all-unfiled" class="form-check-input"></th>
                                    <th>No. Dokumen</th>
                                    <th>Judul Dokumen</th>
                                    <th>Tahun Cipta</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-check-circle me-2"></i> Tambahkan Item Terpilih ke Berkas Ini
                    </button>
                <?php else: ?>
                    <div class="text-center p-4">
                        <i class="bi bi-lock-fill fs-1 text-muted"></i>
                        <p class="mt-2 text-muted">
                            <?php if ($berkas['status_tutup'] === 'tertutup'): ?>
                                Berkas ini telah ditutup. Tidak ada item baru yang bisa ditambahkan.
                            <?php else: ?>
                                Anda tidak memiliki izin untuk menambahkan item ke berkas.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // --- Variabel global untuk menyimpan ID item yang dicentang ---
        let checkedItems = new Set();

        // Tabel item yang belum diberkaskan
        const tableUnfiledItems = $('#table-unfiled-items').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('berkas-aktif/ajaxListUnfiledItems') ?>",
                type: "POST",
                data: function(d) {
                    // Tambahkan ID item yang sudah dicentang ke request
                    d.checked_item_ids = Array.from(checkedItems);
                }
            },
            columnDefs: [{
                    "targets": 0, // Target kolom pertama untuk checkbox
                    "orderable": false,
                    "searchable": false,
                    // --- PERBAIKAN: Fungsi render untuk checkbox ---
                    "render": function(data, type, row, meta) {
                        const itemId = row[0]; // ID item ada di kolom pertama
                        const isChecked = checkedItems.has(itemId);
                        return `<input type="checkbox" class="form-check-input item-checkbox-unfiled" name="item_ids[]" value="${itemId}" ${isChecked ? 'checked' : ''}>`;
                    }
                },
                {
                    "targets": 1, // Target kolom kedua untuk No. Dokumen
                    "orderable": true, // Asumsi bisa diurutkan
                    "searchable": true,
                    "data": 1, // Ambil data dari indeks 1 (No. Dokumen)
                    // --- PERBAIKAN: Custom render jika No. Dokumen kosong ---
                    "render": function(data, type, row, meta) {
                        return data ? data : '-'; // Tampilkan '-' jika kosong
                    }
                },
                {
                    "targets": 2, // Target kolom ketiga untuk Judul Dokumen
                    "data": 2, // Ambil data dari indeks 2 (Judul Dokumen)
                    "render": function(data, type, row, meta) {
                        // Jika ada Judul Dokumen, tampilkan
                        return `<strong>${data}</strong>`;
                    }
                },
                {
                    "targets": 3, // Target kolom keempat untuk Tahun Cipta
                    "data": 3, // Ambil data dari indeks 3 (Tahun Cipta)
                    "className": "text-center" // Posisikan di tengah
                }
            ],
            language: {
                emptyTable: "Semua item arsip yang tersedia sudah diberkaskan."
            },
            // --- PERBAIKAN: Callback untuk mempertahankan checkbox ---
            "drawCallback": function(settings) {
                // Setelah tabel digambar ulang, re-attach event listener untuk checkbox
                // dan pastikan tombol 'check all' diupdate
                $('#check-all-unfiled').prop('checked', false); // Reset master checkbox
                updateCheckAllStatus();
            }
        });

        // --- Logika Check All ---
        $('#check-all-unfiled').on('change', function() {
            const isChecked = this.checked;
            $('.item-checkbox-unfiled').each(function() {
                $(this).prop('checked', isChecked);
                const itemId = $(this).val();
                if (isChecked) {
                    checkedItems.add(itemId);
                } else {
                    checkedItems.delete(itemId);
                }
            });
        });

        // --- Logika per checkbox item ---
        $('body').on('change', '.item-checkbox-unfiled', function() {
            const itemId = $(this).val();
            if (this.checked) {
                checkedItems.add(itemId);
            } else {
                checkedItems.delete(itemId);
            }
            updateCheckAllStatus();
        });

        // Fungsi untuk mengupdate status master checkbox 'check all'
        function updateCheckAllStatus() {
            const totalCheckboxes = $('.item-checkbox-unfiled').length;
            const checkedCheckboxes = $('.item-checkbox-unfiled:checked').length;
            if (totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes) {
                $('#check-all-unfiled').prop('checked', true);
            } else {
                $('#check-all-unfiled').prop('checked', false);
            }
        }


        // Konfirmasi sebelum submit
        $('#form-add-items').on('submit', function(e) {
            e.preventDefault();

            // --- PERBAIKAN: Masukkan ID dari Set ke hidden input ---
            // Kosongkan input lama jika ada
            $(this).find('input[name="item_ids[]"]').remove();

            // Buat hidden input baru untuk setiap ID yang dicentang
            if (checkedItems.size === 0) {
                Swal.fire('Peringatan', 'Pilih minimal satu item untuk ditambahkan.', 'warning');
                return;
            }

            checkedItems.forEach(function(itemId) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'item_ids[]',
                    value: itemId
                }).appendTo('#form-add-items');
            });
            // --- AKHIR PERBAIKAN ---

            Swal.fire({
                title: 'Konfirmasi',
                html: `Anda yakin akan menambahkan <b>${checkedItems.size}</b> item ini ke berkas?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tambahkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Lanjutkan submit form biasa
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>