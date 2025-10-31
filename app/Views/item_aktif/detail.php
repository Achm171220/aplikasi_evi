<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header dan Tombol Kembali -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800"><?= $title ?></h1>
            <p class="mb-0 text-muted">Detail lengkap data arsip aktif.</p>
        </div>
        <div>
            <a href="<?= site_url('item-aktif') ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <!-- Tambahkan tombol Edit jika perlu -->
            <?php if (has_permission('cud_arsip')): ?>
                <a href="<?= site_url('item-aktif/edit/' . $item['id']) ?>" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit Data
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Judul Dokumen Utama (Lebih menonjol) -->
    <div class="card shadow mb-4 p-3 bg-white border-bottom-primary">
        <h4 class="mb-0 text-dark fw-bold">
            <?= esc($item['judul_dokumen']) ?>
        </h4>
        <small class="text-muted mt-1">
            <i class="fas fa-file-alt me-1"></i> Nomor Dokumen: <?= esc($item['no_dokumen'] ?? '-') ?>
        </small>
    </div>

    <div class="row">

        <!-- Kolom Kiri: Detail Dokumen & Unit Pencipta -->
        <div class="col-lg-8">

            <!-- Card: INFORMASI DASAR -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light py-2">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-clipboard-list me-2"></i> Detail Dokumen & Pencipta</h6>
                </div>
                <div class="card-body">

                    <div class="row row-cols-md-2 row-cols-1 g-3">

                        <!-- Group 1: Informasi Waktu & Catatan -->
                        <div>
                            <p class="fw-bold mb-1 text-primary">Waktu & Dasar</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                    <span>Tanggal Dokumen</span>
                                    <span class="fw-bold"><?= date('d F Y', strtotime($item['tgl_dokumen'])) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                    <span>Tahun Cipta</span>
                                    <span class="fw-bold"><?= esc($item['tahun_cipta']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                    <span>Dasar Pencatatan</span>
                                    <span class="badge bg-info text-dark fw-bold"><?= esc($item['dasar_catat'] ?? 'Manual') ?></span>
                                </li>
                            </ul>
                        </div>

                        <!-- Group 2: Klasifikasi -->
                        <div>
                            <p class="fw-bold mb-1 text-primary">Klasifikasi & Jenis</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                    <span>Kode Klasifikasi</span>
                                    <span class="text-end"><?= esc($item['kode_klasifikasi']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                    <span>Nama Klasifikasi</span>
                                    <span class="text-end"><?= esc($item['nama_klasifikasi']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                    <span>Jenis Naskah</span>
                                    <span class="text-end"><?= esc($item['nama_naskah']) ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <h6 class="mt-4 pt-3 border-top fw-bold text-primary">Unit Pencipta</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                            <span class="col-sm-4 text-muted">Eselon 3 (Pencipta)</span>
                            <span class="col-sm-8 text-end"><?= esc($item['nama_es3']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                            <span class="col-sm-4 text-muted">Eselon 2</span>
                            <span class="col-sm-8 text-end"><?= esc($item['nama_es2']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                            <span class="col-sm-4 text-muted">Eselon 1</span>
                            <span class="col-sm-8 text-end"><?= esc($item['nama_es1']) ?></span>
                        </li>
                    </ul>

                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Fisik, Lokasi & Administrasi -->
        <div class="col-lg-4">

            <!-- Card: LOKASI FISIK & DIGITAL -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light py-2">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-boxes me-2"></i> Lokasi & Fisik</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Media Simpan
                            <span class="badge bg-secondary"><?= esc(ucfirst($item['media_simpan'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tingkat Perkembangan
                            <span><?= esc(ucfirst($item['tk_perkembangan'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Jumlah
                            <span class="fw-bold"><?= esc($item['jumlah']) ?></span>
                        </li>

                        <?php if ($item['media_simpan'] == 'kertas'): ?>
                            <li class="list-group-item">
                                <p class="mb-0 fw-bold">Lokasi Fisik</p>
                                <span class="small text-muted d-block">Nomor Box: <?= esc($item['no_box'] ?? '-') ?></span>
                                <span class="small text-muted d-block">Lokasi: <?= esc($item['lokasi_simpan'] ?? '-') ?></span>
                            </li>
                        <?php else: // Media Elektronik 
                        ?>
                            <li class="list-group-item">
                                <p class="mb-0 fw-bold">Akses Digital</p>
                                <?php if (!empty($item['nama_link'])): ?>
                                    <a href="<?= esc($item['nama_link']) ?>" target="_blank" class="small text-decoration-none">
                                        <i class="fas fa-link me-1"></i> Klik untuk mengunduh/melihat
                                    </a>
                                <?php else: ?>
                                    <span class="small text-danger">Link digital tidak tersedia.</span>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Card: STATUS & ADMINISTRASI -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light py-2">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-user-shield me-2"></i> Administrasi</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <p class="mb-0 fw-bold">Status Berkas</p>
                            <?php if ($item['id_berkas']): ?>
                                <span class="badge bg-success mt-1"><i class="fas fa-folder-open me-1"></i> Sudah Diberkaskan</span>
                                <p class="mb-0 small mt-1">Berkas Induk: <a href="<?= site_url('berkas-aktif/detail/' . $item['id_berkas']) ?>"><?= esc($item['nama_berkas']) ?></a></p>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark mt-1"><i class="fas fa-folder-minus me-1"></i> Belum Diberkaskan</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item px-0 mt-2">
                            <p class="mb-0 fw-bold">Pencatat</p>
                            <span class="small d-block"><?= esc($item['user_creator']) ?></span>
                            <span class="small text-muted d-block">Dicatat pada: <?= date('d M Y H:i', strtotime($item['created_at'])) ?></span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>