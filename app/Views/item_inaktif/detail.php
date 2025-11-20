<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Detail Item Arsip Aktif</h1>
            <p class="mb-0 text-muted"><?= esc($item['judul_dokumen']) ?></p>
        </div>
        <a href="<?= site_url('item-aktif') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        <!-- Kolom Informasi Utama -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-info-circle-fill me-2"></i>Informasi Detail</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Judul Dokumen</dt>
                        <dd class="col-sm-8"><?= esc($item['judul_dokumen']) ?></dd>
                        <dt class="col-sm-4">Nomor Dokumen</dt>
                        <dd class="col-sm-8"><?= esc($item['no_dokumen'] ?? '-') ?></dd>
                        <dt class="col-sm-4">Tanggal Dokumen</dt>
                        <dd class="col-sm-8"><?= date('d F Y', strtotime($item['tgl_dokumen'])) ?></dd>
                        <dt class="col-sm-4">Tahun Cipta</dt>
                        <dd class="col-sm-8"><?= esc($item['tahun_cipta']) ?></dd>
                    </dl>
                    <hr>
                    <dl class="row">
                        <dt class="col-sm-4">Klasifikasi</dt>
                        <dd class="col-sm-8"><?= esc($item['kode_klasifikasi']) ?> - <?= esc($item['nama_klasifikasi']) ?></dd>
                        <dt class="col-sm-4">Jenis Naskah</dt>
                        <dd class="col-sm-8"><?= esc($item['nama_naskah']) ?></dd>
                    </dl>
                    <hr>
                    <dl class="row">
                        <dt class="col-sm-4">Unit Pencipta (Eselon 3)</dt>
                        <dd class="col-sm-8"><?= esc($item['nama_es3']) ?></dd>
                        <dt class="col-sm-4">Unit Eselon 2</dt>
                        <dd class="col-sm-8"><?= esc($item['nama_es2']) ?></dd>
                        <dt class="col-sm-4">Unit Eselon 1</dt>
                        <dd class="col-sm-8"><?= esc($item['nama_es1']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Kolom Informasi Tambahan -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-box-seam-fill me-2"></i>Status & Fisik</h6>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Status Berkas</dt>
                        <dd>
                            <?php if ($item['id_berkas']): ?>
                                <span class="badge text-bg-success"><i class="bi bi-folder-check me-1"></i> Sudah Diberkaskan</span>
                                <p class="mb-0 small mt-1">Pada Berkas: <a href="<?= site_url('berkas-aktif/detail/' . $item['id_berkas']) ?>"><?= esc($item['nama_berkas']) ?></a></p>
                            <?php else: ?>
                                <span class="badge text-bg-secondary"><i class="bi bi-x-circle me-1"></i> Belum Diberkaskan</span>
                            <?php endif; ?>
                        </dd>
                        <hr>
                        <dt>Jumlah</dt>
                        <dd><?= esc($item['jumlah']) ?></dd>
                        <dt>Media Simpan</dt>
                        <dd><?= esc(ucfirst($item['media_simpan'])) ?></dd>
                        <dt>Tingkat Perkembangan</dt>
                        <dd><?= esc(ucfirst($item['tk_perkembangan'])) ?></dd>
                        <dt>Nomor Box</dt>
                        <dd><?= esc($item['no_box'] ?? '-') ?></dd>
                        <hr>
                        <dt>Dibuat oleh</dt>
                        <dd><?= esc($item['user_creator']) ?> pada <?= date('d M Y H:i', strtotime($item['created_at'])) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>