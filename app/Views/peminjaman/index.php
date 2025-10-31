<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <!-- Notifikasi sudah ditangani secara global di template.php -->

    <!-- Form Peminjaman (dibungkus form utama) -->
    <form action="<?= site_url('peminjaman/pinjam') ?>" method="post" id="form-peminjaman-arsip">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-box-arrow-in-right me-2"></i>Form Peminjaman Arsip</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="item_berkas_display" class="form-label">Item/Berkas yang Dipinjam</label>
                        <!-- Input ini hanya untuk display, nilai sebenarnya dari checkbox tersembunyi -->
                        <input type="text" class="form-control" id="item_berkas_display" value="Belum Ada Yang Dipilih" readonly required>
                        <!-- Hidden input untuk validasi minimum pilihan -->
                        <input type="hidden" name="pinjam_id" id="pinjam_id" value="">
                        <input type="hidden" name="pinjam_type" id="pinjam_type" value="">
                        <div class="invalid-feedback d-block" id="pinjam-id-feedback"><?= $validation->getError('pinjam_id') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="peminjam_nama" class="form-label">Nama Peminjam</label>
                        <input type="text" class="form-control <?= $validation->hasError('peminjam_nama') ? 'is-invalid' : '' ?>" id="peminjam_nama" name="peminjam_nama" value="<?= old('peminjam_nama', '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('peminjam_nama') ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="peminjam_unit" class="form-label">Unit/Instansi Peminjam</label>
                        <input type="text" class="form-control <?= $validation->hasError('peminjam_unit') ? 'is-invalid' : '' ?>" id="peminjam_unit" name="peminjam_unit" value="<?= old('peminjam_unit', '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('peminjam_unit') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tgl_pinjam" class="form-label">Tanggal Pinjam</label>
                        <input type="date" class="form-control <?= $validation->hasError('tgl_pinjam') ? 'is-invalid' : '' ?>" id="tgl_pinjam" name="tgl_pinjam" value="<?= old('tgl_pinjam', $currentDate) ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('tgl_pinjam') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tgl_kembali_rencana" class="form-label">Tanggal Kembali Rencana</label>
                        <input type="date" class="form-control <?= $validation->hasError('tgl_kembali_rencana') ? 'is-invalid' : '' ?>" id="tgl_kembali_rencana" name="tgl_kembali_rencana" value="<?= old('tgl_kembali_rencana', date('Y-m-d', strtotime('+7 days'))) ?>" required>
                        <div class="invalid-feedback"></div> <!-- Ini akan diisi oleh JS -->
                    </div>
                </div>
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= old('keterangan', '') ?></textarea>
                </div>
                <a href="<?= base_url('peminjaman/monitoring'); ?>" class="btn btn-danger mt-3"><i class="bi bi-eye me-3"></i> Monitoring</a>
                <button type="submit" class="btn btn-primary mt-3">Proses Peminjaman</button>
            </div>
        </div>

        <div class="row">
            <!-- Kolom Kiri: Tabel Item Aktif -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Item Arsip Aktif (Tersedia)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-items" class="table table-bordered table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;"><input type="checkbox" id="check-all-items" class="form-check-input"></th>
                                        <th>No. Dokumen</th>
                                        <th>Judul Dokumen</th>
                                        <th>Klasifikasi</th>
                                        <th>Tahun</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Tabel Berkas Aktif -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Berkas Arsip Aktif (Tersedia)</h6>
                    </div>
                    <div class="card-body">
                        <table id="table-berkas" class="table table-bordered table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%;"><input type="checkbox" id="check-all-berkas" class="form-check-input"></th>
                                    <th>No. Berkas</th>
                                    <th>Nama Berkas</th>
                                    <th>Jumlah Item</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form> <!-- Penutup form utama -->
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables
        const tableItems = $('#table-items').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('peminjaman/list-items') ?>",
                type: "POST"
            },
            columnDefs: [{
                    "targets": 0,
                    "orderable": false,
                    "searchable": false
                }, // Checkbox
                {
                    "orderable": false,
                    "targets": -1
                } // Kolom terakhir (kosong)
            ]
        });

        const tableBerkas = $('#table-berkas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('peminjaman/list-berkas') ?>",
                type: "POST"
            },
            columnDefs: [{
                    "targets": 0,
                    "orderable": false,
                    "searchable": false
                }, // Checkbox
                {
                    "orderable": false,
                    "targets": -1
                } // Kolom terakhir (kosong)
            ]
        });

        // --- Logika Checkbox Peminjaman ---
        function updateSelectedDisplay() {
            const selectedItems = $('.select-item-checkbox:checked');
            const selectedBerkas = $('.select-berkas-checkbox:checked');

            let displayText = "Belum Ada Yang Dipilih";
            let pinjamType = "";
            let pinjamId = "";

            // Reset display errors
            $('#item_berkas_display').removeClass('is-invalid');
            $('#pinjam-id-feedback').text('');

            if (selectedItems.length > 0) {
                displayText = `${selectedItems.length} Item Terpilih`;
                pinjamType = "item";
                // Untuk validasi minimum, set pinjam_id dengan ID pertama
                pinjamId = selectedItems.first().val();

                // Nonaktifkan checkbox berkas
                $('.select-berkas-checkbox').prop('disabled', true);
                $('#check-all-berkas').prop('disabled', true);
            } else if (selectedBerkas.length > 0) {
                displayText = `${selectedBerkas.length} Berkas Terpilih`;
                pinjamType = "berkas";
                pinjamId = selectedBerkas.first().val();

                // Nonaktifkan checkbox item
                $('.select-item-checkbox').prop('disabled', true);
                $('#check-all-items').prop('disabled', true);
            } else {
                // Aktifkan kembali semua checkbox jika tidak ada yang terpilih
                $('.select-berkas-checkbox, .select-item-checkbox').prop('disabled', false);
                $('#check-all-berkas, #check-all-items').prop('disabled', false);
            }

            $('#item_berkas_display').val(displayText);
            $('#pinjam_type').val(pinjamType);
            $('#pinjam_id').val(pinjamId); // Ini akan digunakan untuk validasi di backend
        }

        // Event listener untuk checkbox item
        $('body').on('change', '.select-item-checkbox', updateSelectedDisplay);
        $('#check-all-items').on('change', function() {
            $('.select-item-checkbox').prop('checked', this.checked);
            updateSelectedDisplay();
        });

        // Event listener untuk checkbox berkas
        $('body').on('change', '.select-berkas-checkbox', updateSelectedDisplay);
        $('#check-all-berkas').on('change', function() {
            $('.select-berkas-checkbox').prop('checked', this.checked);
            updateSelectedDisplay();
        });

        // --- Logika Validasi Tanggal Kembali Rencana (Frontend) ---
        const tglPinjamInput = $('#tgl_pinjam');
        const tglKembaliRencanaInput = $('#tgl_kembali_rencana');
        const prosesPeminjamanButton = $('#form-peminjaman-arsip button[type="submit"]');

        function validateTanggalKembaliRencana() {
            const tglPinjam = tglPinjamInput.val();
            const tglKembaliRencana = tglKembaliRencanaInput.val();

            let isValid = true;
            let errorMessage = '';

            if (!tglPinjam || !tglKembaliRencana) {
                // Biarkan validasi 'required' dari HTML5 yang bekerja
            } else if (new Date(tglKembaliRencana) < new Date(tglPinjam)) {
                isValid = false;
                errorMessage = 'Tanggal kembali rencana harus setelah tanggal pinjam.';
            }

            if (!isValid) {
                tglKembaliRencanaInput.addClass('is-invalid');
                tglKembaliRencanaInput.next('.invalid-feedback').text(errorMessage);
                prosesPeminjamanButton.prop('disabled', true);
            } else {
                tglKembaliRencanaInput.removeClass('is-invalid');
                tglKembaliRencanaInput.next('.invalid-feedback').text('');
                prosesPeminjamanButton.prop('disabled', false);
            }
            return isValid;
        }

        tglPinjamInput.on('change', validateTanggalKembaliRencana);
        tglKembaliRencanaInput.on('change', validateTanggalKembaliRencana);

        // --- Proses submit form peminjaman ---
        $('#form-peminjaman-arsip').on('submit', function(e) {
            // Panggil updateSelectedDisplay() sekali lagi untuk memastikan status terakhir
            updateSelectedDisplay();

            // Cek validasi tanggal terlebih dahulu
            if (!validateTanggalKembaliRencana()) {
                e.preventDefault();
                Swal.fire('Validasi Gagal!', 'Tanggal kembali rencana tidak valid.', 'error');
                return;
            }

            // Cek manual jika pinjam_id kosong (berarti tidak ada item/berkas dipilih)
            if (!$('#pinjam_id').val()) {
                $('#item_berkas_display').addClass('is-invalid');
                $('#pinjam-id-feedback').text('Pilih minimal satu item atau satu berkas untuk dipinjam.');
                Swal.fire('Peringatan!', 'Pilih minimal satu item atau satu berkas untuk dipinjam.', 'warning');
                e.preventDefault();
                return;
            }

            // Optional: Konfirmasi dengan SweetAlert sebelum submit
            Swal.fire({
                title: 'Konfirmasi Peminjaman',
                html: 'Anda akan memproses peminjaman ini.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Lanjutkan submit form, PHP akan validasi lebih lanjut
                    this.submit();
                } else {
                    e.preventDefault(); // Batalkan submit jika tidak dikonfirmasi
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>