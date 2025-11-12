<?= $this->extend('layout/template') ?>

<?= $this->section('styles') ?>
<style>
    /* --- CLEAN FLUID DETAIL PAGE STYLE --- */
    body {
        background-color: #f8f9fb;
    }

    .page-wrapper {
        padding: 1rem 2rem;
    }

    @media (min-width: 992px) {
        .page-wrapper {
            padding: 1.5rem 4rem;
        }
    }

    .card-modern {
        border: none;
        border-radius: 1rem;
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease-in-out;
    }

    .card-modern:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .card-header-min {
        background: transparent !important;
        border-bottom: none;
        padding: 0.75rem 1.25rem 0;
    }

    .card-header-min h6 {
        font-weight: 600;
        font-size: 0.95rem;
        color: #0d6efd;
        display: flex;
        align-items: center;
    }

    .card-header-min h6 i {
        font-size: 0.9rem;
        margin-right: 0.5rem;
    }

    .card-body {
        padding: 0.75rem 1.25rem 1rem;
    }

    .list-group-clean .list-group-item {
        border: none;
        border-bottom: 1px dashed #f0f0f0;
        font-size: 0.9rem;
        padding: 0.4rem 0;
    }

    .list-group-clean .list-group-item:last-child {
        border-bottom: none;
    }

    .detail-section-title {
        font-weight: 600;
        color: #0d6efd;
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid #eaeaea;
        padding-bottom: 4px;
    }

    .text-label {
        color: #6c757d;
        font-size: 0.88rem;
    }

    .badge-soft {
        background-color: #eef5ff;
        color: #0d6efd;
        font-weight: 500;
    }

    .small-muted {
        color: #adb5bd;
        font-size: 0.8rem;
    }

    .card-title-doc {
        font-size: 1.05rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .shadow-thin {
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid page-wrapper">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
        <div>
            <h5 class="fw-semibold text-dark mb-1"><?= $title ?></h5>
            <p class="small text-muted mb-0">Detail informasi arsip aktif</p>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="<?= site_url('item-aktif') ?>" class="btn btn-outline-secondary btn-sm me-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <?php if (has_permission('cud_arsip')): ?>
                <a href="<?= site_url('item-aktif/edit/' . $item['id']) ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Judul Dokumen -->
    <div class="card card-modern mb-4 p-4 shadow-thin">
        <div class="card-title-doc mb-1 text-dark"><?= esc($item['judul_dokumen']) ?></div>
        <p class="small text-muted mb-0">
            <i class="fas fa-file-alt me-1"></i> Nomor Dokumen:
            <span class="fw-semibold text-dark"><?= esc($item['no_dokumen'] ?? '-') ?></span>
        </p>
    </div>

    <!-- GRID -->
    <div class="row g-4">

        <!-- KOLOM KIRI -->
        <div class="col-lg-8">

            <!-- Metadata -->
            <div class="card card-modern mb-4">
                <div class="card-header card-header-min">
                    <h6><i class="fas fa-cogs"></i> Metadata Dasar Arsip</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <p class="detail-section-title">Waktu & Dasar</p>
                            <ul class="list-group list-group-clean">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="fas fa-calendar-alt me-2 text-secondary"></i>Tanggal Dokumen</span>
                                    <span class="fw-semibold"><?= date('d F Y', strtotime($item['tgl_dokumen'])) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="fas fa-clock me-2 text-secondary"></i>Tahun Cipta</span>
                                    <span><?= esc($item['tahun_cipta']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="fas fa-keyboard me-2 text-secondary"></i>Dasar Pencatatan</span>
                                    <span class="badge badge-soft"><?= esc($item['dasar_catat'] ?? 'Manual') ?></span>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <p class="detail-section-title">Klasifikasi & Jenis</p>
                            <ul class="list-group list-group-clean">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Kode Klasifikasi</span>
                                    <span class="fw-semibold"><?= esc($item['kode_klasifikasi']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Nama Klasifikasi</span>
                                    <span><?= esc($item['nama_klasifikasi']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Jenis Naskah</span>
                                    <span><?= esc($item['nama_naskah']) ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unit Pencipta -->
            <div class="card card-modern">
                <div class="card-header card-header-min">
                    <h6><i class="fas fa-building"></i> Unit Pencipta</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-clean">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-label">Eselon 3 (Pencipta)</span>
                            <span class="fw-semibold"><?= esc($item['nama_es3']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-label">Eselon 2</span>
                            <span><?= esc($item['nama_es2']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-label">Eselon 1</span>
                            <span><?= esc($item['nama_es1']) ?></span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN -->
        <div class="col-lg-4">

            <!-- Lokasi & Fisik -->
            <div class="card card-modern mb-4">
                <div class="card-header card-header-min">
                    <h6><i class="fas fa-map-marker-alt"></i> Lokasi & Fisik</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-clean">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Media Simpan</span>
                            <span class="badge badge-soft"><?= esc(ucfirst($item['media_simpan'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tingkat Perkembangan</span>
                            <span><?= esc(ucfirst($item['tk_perkembangan'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Jumlah</span>
                            <span class="fw-semibold"><?= esc($item['jumlah']) ?></span>
                        </li>

                        <?php if ($item['media_simpan'] == 'kertas'): ?>
                            <li class="list-group-item">
                                <p class="fw-semibold mb-1">Lokasi Fisik</p>
                                <small class="text-muted d-block">Nomor Box: <?= esc($item['no_box'] ?? '-') ?></small>
                                <small class="text-muted d-block">Lokasi: <?= esc($item['lokasi_simpan'] ?? '-') ?></small>
                            </li>
                        <?php else: ?>
                            <li class="list-group-item">
                                <p class="fw-semibold mb-1">Akses Digital</p>
                                <?php if (!empty($item['nama_link'])): ?>
                                    <a href="<?= esc($item['nama_link']) ?>" target="_blank" class="small text-decoration-none text-success">
                                        <i class="fas fa-link me-1"></i> Klik untuk mengunduh/melihat
                                    </a>
                                <?php else: ?>
                                    <small class="text-danger">Link digital tidak tersedia.</small>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Administrasi -->
            <div class="card card-modern">
                <div class="card-header card-header-min">
                    <h6><i class="fas fa-user-shield"></i> Administrasi</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-clean">
                        <li class="list-group-item">
                            <p class="fw-semibold mb-1">Status Berkas</p>
                            <?php if ($item['id_berkas']): ?>
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-folder-open me-1"></i> Sudah Diberkaskan
                                </span>
                                <p class="small mt-1 mb-0">
                                    Berkas: <a href="<?= site_url('berkas-aktif/detail/' . $item['id_berkas']) ?>" class="text-decoration-none"><?= esc($item['nama_berkas']) ?></a>
                                </p>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-dark">
                                    <i class="fas fa-folder-minus me-1"></i> Belum Diberkaskan
                                </span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item mt-2">
                            <p class="fw-semibold mb-1">Pencatat</p>
                            <small class="d-block"><?= esc($item['user_creator']) ?></small>
                            <small class="text-muted">Dicatat pada: <?= date('d M Y H:i', strtotime($item['created_at'])) ?></small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>