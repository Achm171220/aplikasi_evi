<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <!-- Form Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-8 mb-3">
                    <label for="tema_id" class="form-label">Pilih Tema Arsip</label>
                    <div class="input-group">
                        <select class="form-select select2-filter" id="tema_id" name="tema_id" style="width: 80%;" required>
                            <option value="">-- Pilih Tema --</option>
                            <?php foreach ($themes as $theme): ?>
                                <option value="<?= $theme['id'] ?>" <?= ($selected_theme_id == $theme['id']) ? 'selected' : '' ?>>
                                    <?= esc($theme['nama_tema']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <a href="<?= site_url('tema') ?>" class="btn btn-outline-secondary" target="_blank" title="Kelola Tema"><i class="fas fa-cog"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-3 d-flex">
                    <button type="button" id="btn-tampilkan" class="btn btn-primary w-50 me-2"><i class="fas fa-search me-2"></i> Tampilkan</button>
                    <a href="#" id="btn-export-excel" class="btn btn-success w-50 disabled" target="_blank">
                        <i class="bi bi-file-earmark-excel-fill me-2"></i> Ekspor
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Hasil Pencarian -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Arsip Berdasarkan Tema</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="table-arsip-tematik" style="width:100%">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Judul</th>
                            <th>Pencipta Arsip</th>
                            <th>Kode Klasifikasi</th>
                            <th>Jenis/Uraian</th>
                            <th>Nomor Laporan</th>
                            <th>Tingkat Perkembangan</th>
                            <th>Tanggal</th>
                            <th>Kurun Waktu (Tahun)</th>
                            <th>Jumlah</th>
                            <th>Lokasi Simpan</th>
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
        $('.select2-filter').select2({
            theme: 'bootstrap-5'
        });

        // --- KUNCI PERBAIKAN: Inisialisasi DataTables tanpa properti 'columns' ---
        const dataTable = $('#table-arsip-tematik').DataTable({
            // DataTables akan secara otomatis membaca header dari <thead> HTML Anda.
            // Tidak perlu mendefinisikan kolom di sini saat menggunakan mode client-side manual.
            language: {
                emptyTable: "Pilih tema dan klik 'Tampilkan' untuk memuat data."
            }
        });

        // Event handler untuk tombol "Tampilkan Arsip"
        $('#btn-tampilkan').on('click', function() {
            const themeId = $('#tema_id').val();
            const themeName = $('#tema_id').find('option:selected').text().trim();
            const button = $(this);
            const exportButton = $('#btn-export-excel');

            if (!themeId) {
                Swal.fire('Peringatan', 'Silakan pilih tema terlebih dahulu.', 'warning');
                exportButton.addClass('disabled').attr('href', '#');
                return;
            }

            // Aktifkan tombol ekspor
            const exportUrl = `<?= site_url('arsip-tematik/export') ?>?tema_id=${themeId}`;
            exportButton.attr('href', exportUrl).removeClass('disabled');

            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memuat...');
            dataTable.clear().draw();

            $.ajax({
                url: "<?= site_url('arsip-tematik/list') ?>",
                type: "POST",
                data: {
                    tema_id: themeId
                },
                dataType: "json",
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        dataTable.rows.add(response.data).draw();
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Data Tidak Ditemukan',
                            text: `Tidak ada arsip yang ditautkan ke tema "${themeName}".`,
                        });
                    }
                },
                error: function() {
                    Swal.fire('Gagal!', 'Terjadi kesalahan pada server. Cek Network Tab di console untuk detail.', 'error');
                },
                complete: function() {
                    button.prop('disabled', false).html('<i class="fas fa-search me-2"></i> Tampilkan');
                }
            });
        });

        $('#tema_id').on('change', function() {
            if (!$(this).val()) {
                $('#btn-export-excel').addClass('disabled').attr('href', '#');
            }
        });

        if ($('#tema_id').val()) {
            $('#btn-tampilkan').trigger('click');
        }
    });
</script>
<?= $this->endSection() ?>