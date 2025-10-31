<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
            <p class="mb-0 text-muted">Unit Pengolah: <strong><?= esc($nama_es2) ?></strong></p>
        </div>
        <div class="btn-group">
            <a href="<?= site_url('laporan-aktif/excel?' . http_build_query(request()->getGet())) ?>" class="btn btn-success"><i class="bi bi-file-earmark-excel-fill me-2"></i> Ekspor ke Excel</a>
            <a href="<?= site_url('laporan-aktif/pdf?' . http_build_query(request()->getGet())) ?>" class="btn btn-danger"><i class="bi bi-file-earmark-pdf-fill me-2"></i> Ekspor ke PDF</a>
        </div>
    </div>

    <!-- === FORM FILTER TANGGAL DAN ES2 (DIPERBARUI) === -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="<?= site_url('laporan-aktif') ?>" method="get" class="row align-items-end">
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
                    <a href="<?= site_url('laporan-aktif') ?>" class="btn btn-secondary w-100 mt-2">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <!-- AKHIR FORM FILTER -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                <table class="table table-bordered table-striped table-sm" style="width:100%; font-size: 0.8rem;">
                    <thead class="table-primary" style="position: sticky; top: 0; z-index: 1;">
                        <tr class="table-primary text-center">
                            <th>No Berkas</th>
                            <th>Kode Klasifikasi</th>
                            <th>Judul Berkas</th>
                            <th>Kurun Waktu</th>
                            <th>No. Item</th>
                            <th>Uraian Informasi</th>
                            <th>Tanggal</th>
                            <th>Tingkat Perkembangan</th>
                            <th>Media</th>
                            <!-- KOLOM KONDISI DIHAPUS -->
                            <th>Jumlah</th>
                            <th>Jangka Simpan & Nasib Akhir</th>
                            <th>Keamanan & Akses</th>
                            <th>Kategori</th>
                            <th>Lokasi Simpan</th>
                            <th>No. Boks</th>
                            <th>Ket.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporanData)): ?>
                            <tr>
                                <td colspan="15" class="text-center">Tidak ada data untuk ditampilkan.</td>
                                <!-- colspan diubah dari 16 menjadi 15 -->
                            </tr>
                        <?php else: ?>
                            <?php foreach ($laporanData as $row): ?>
                                <tr>
                                    <td><?= esc($row['no_berkas_lengkap']) ?></td>
                                    <td><?= esc($row['kode_klasifikasi']) ?></td>
                                    <td><?= esc($row['judul_berkas']) ?></td>
                                    <td><?= esc($row['kurun_waktu']) ?></td>
                                    <td class="text-center"><?= esc($row['no_item']) ?></td>
                                    <td><?= esc($row['uraian_informasi']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                    <td><?= esc($row['tingkat_perkembangan']) ?></td>
                                    <td><?= esc($row['media_arsip']) ?></td>
                                    <!-- KOLOM KONDISI DIHAPUS -->
                                    <td class="text-center"><?= esc($row['jumlah']) ?></td>
                                    <td><?= esc($row['jangka_simpan_nasib']) ?></td>
                                    <td><?= esc($row['klasifikasi_keamanan']) ?></td>
                                    <td><?= esc($row['kategori_arsip']) ?></td>
                                    <td><?= esc($row['lokasi_simpan']) ?></td>
                                    <td><?= esc($row['no_box']) ?></td>
                                    <td><?= esc($row['keterangan']) ?></td>
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
    });
</script>
<?= $this->endSection() ?>