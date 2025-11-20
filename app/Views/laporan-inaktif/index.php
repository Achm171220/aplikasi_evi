<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
            <p class="mb-0 text-muted">Unit Pengolah: <strong><?= esc($nama_es2) ?></strong></p>
        </div>
        <div class="btn-group">
            <a href="<?= site_url('laporan-inaktif/excel?' . http_build_query(request()->getGet())) ?>" class="btn btn-success"><i class="bi bi-file-earmark-excel-fill me-2"></i> Ekspor ke Excel</a>
            <a href="<?= site_url('laporan-inaktif/pdf?' . http_build_query(request()->getGet())) ?>" class="btn btn-danger"><i class="bi bi-file-earmark-pdf-fill me-2"></i> Ekspor ke PDF</a>
        </div>
    </div>

    <!-- === FORM FILTER TANGGAL DAN ES2 === -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="<?= site_url('laporan-inaktif') ?>" method="get" class="row align-items-end">
                <?php $isSuperAdmin = session()->get('user_role') === 'superadmin'; ?>

                <?php if ($isSuperAdmin): ?>
                    <div class="col-md-4 mb-3">
                        <label for="filter_es2_id" class="form-label">Filter Unit Eselon 2</label>
                        <select class="form-select select2-filter" id="filter_es2_id" name="es2_id" style="width: 100%;">
                            <option value="">-- Semua Unit Eselon 2 --</option>
                            <?php foreach ($es2_filter_options as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (old('es2_id', $current_es2_filter_id ?? '') == $opt['id']) ? 'selected' : '' ?>><?= esc($opt['nama_es2']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="<?= $isSuperAdmin ? 'col-md-3' : 'col-md-5' ?> mb-3">
                    <label for="tanggal_mulai" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" name="tanggal_mulai" id="tanggal_mulai" value="<?= esc($tanggal_mulai) ?>">
                </div>
                <div class="<?= $isSuperAdmin ? 'col-md-3' : 'col-md-5' ?> mb-3">
                    <label for="tanggal_akhir" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="tanggal_akhir" id="tanggal_akhir" value="<?= esc($tanggal_akhir) ?>">
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-2"></i>Terapkan</button>
                    <a href="<?= site_url('laporan-inaktif') ?>" class="btn btn-secondary w-100 mt-2">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <!-- AKHIR FORM FILTER -->

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                <table class="table table-bordered table-striped table-sm" id="laporan-table" style="width:100%; font-size: 0.8rem;">
                    <thead class="table-primary" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th>Kode Klasifikasi</th>
                            <th>Judul Dokumen</th>
                            <th>No. Dokumen</th>
                            <th>Tahun</th>
                            <th>Jumlah</th>
                            <th>Media</th>
                            <th>Lokasi Simpan (Rak/Box)</th>
                            <th>Nasib Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporanData)): ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data arsip inaktif.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($laporanData as $index => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><?= esc($row['kode_klasifikasi']) ?></td>
                                    <td><?= esc($row['judul_dokumen']) ?></td>
                                    <td><?= esc($row['no_dokumen']) ?></td>
                                    <td class="text-center"><?= esc($row['tahun_cipta']) ?></td>
                                    <td class="text-center"><?= esc($row['jumlah']) ?></td>
                                    <td><?= esc(ucfirst($row['media_simpan'])) ?></td>
                                    <td><?= esc($row['lokasi_simpan_new'] . ' / ' . $row['no_box']) ?></td>
                                    <td><?= esc(ucfirst($row['nasib_akhir'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 untuk filter
        $('.select2-filter').select2({
            theme: 'bootstrap-5'
        });

        // Tambahkan script ini agar Select2 di filter Es2 untuk Superadmin bisa bekerja
        // Jika ada elemen filter_es2_id (hanya untuk Superadmin)
        <?php if ($isSuperAdmin): ?>
            const filterEs2Select = $('#filter_es2_id');
            // Pemicu untuk mempertahankan pilihan jika halaman dimuat ulang dengan filter
            const currentEs2FilterId = '<?= $current_es2_filter_id ?? '' ?>';
            if (currentEs2FilterId) {
                filterEs2Select.val(currentEs2FilterId).trigger('change');
            }
        <?php endif; ?>

        // Inisialisasi DataTables untuk fitur search dan sort di sisi klien (jika ada data)
        <?php if (!empty($laporanData)): ?>
            $('#laporan-table').DataTable({
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
                    },
                }
            });
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>