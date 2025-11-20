<?= $this->extend('layout/template') // Sesuaikan dengan path layout Anda 
?>

<?= $this->section('styles') // Section khusus untuk CSS 
?>
<style>
    /* === TIMELINE STYLE === */
    .timeline {
        list-style: none;
        padding: 0;
        position: relative;
    }

    .timeline:before {
        content: '';
        position: absolute;
        top: 10px;
        bottom: 10px;
        left: 15px;
        width: 3px;
        background-color: #e9ecef;
        border-radius: 3px;
    }

    .timeline-item {
        margin-bottom: 1.5rem;
        position: relative;
        padding-left: 45px;
    }

    .timeline-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #fff;
        transition: all 0.3s ease;
    }

    .timeline-item.success .timeline-icon {
        background-color: #198754;
        color: #fff;
    }

    .timeline-item.process .timeline-icon {
        background-color: #0d6efd;
        color: #fff;
        animation: pulse 2s infinite;
    }

    .timeline-content {
        padding-top: 5px;
    }

    .timeline-content .fw-bold {
        color: #343a40;
    }

    .timeline-content .text-muted {
        font-size: 0.85rem;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.5);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(13, 110, 253, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
        }
    }

    /* === STYLING KARTU PETUGAS === */
    .petugas-list .list-group-item {
        border-left: 0;
        border-right: 0;
        padding-left: 0;
        padding-right: 0;
    }

    .petugas-list .list-group-item:first-child {
        border-top: 0;
    }

    .petugas-list .list-group-item:last-child {
        border-bottom: 0;
    }

    .petugas-role {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .petugas-name {
        font-weight: 500;
        color: #212529;
    }

    /* === STYLING TABEL ITEM === */
    .table-detail thead th {
        background-color: #f8f9fa;
        font-weight: 500;
    }

    .table-detail tbody tr:hover {
        background-color: #f1f3f5;
    }

    .rejection-note {
        font-size: 0.8rem;
        color: #dc3545;
        font-style: italic;
        display: block;
        margin-top: 4px;
    }
</style>
<?= $this->endSection() ?>


<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Detail Usulan Pemindahan</h1>
            <p class="mb-0 text-muted">No. Usulan: <strong><?= esc($usulan['no_usulan']) ?></strong> | Tanggal: <strong><?= date('d F Y', strtotime($usulan['tgl_usulan'])) ?></strong></p>
        </div>
        <a href="<?= site_url('pemindahan/pantau') ?>" class="btn btn-secondary btn-sm shadow-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali ke Daftar Pantau</a>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Status & Petugas -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Proses</h6>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <?php
                        $statuses = [
                            'diajukan' => ['icon' => 'fas fa-paper-plane', 'text' => 'Usulan Diajukan'],
                            'proses_v1' => ['icon' => 'fas fa-user-check', 'text' => 'Proses Verifikasi 1'],
                            'proses_v2' => ['icon' => 'fas fa-user-check', 'text' => 'Proses Verifikasi 2'],
                            'proses_f1' => ['icon' => 'fas fa-user-shield', 'text' => 'Proses Finalisasi 1'],
                            'pembuatan_ba' => ['icon' => 'fas fa-file-signature', 'text' => 'Menunggu Berita Acara'],
                            'proses_f2' => ['icon' => 'fas fa-gavel', 'text' => 'Proses Eksekusi'],
                            'selesai' => ['icon' => 'fas fa-check-circle', 'text' => 'Selesai'],
                            'ditolak' => ['icon' => 'fas fa-times-circle', 'text' => 'Ditolak']
                        ];
                        $current_status_index = array_search($usulan['status'], array_keys($statuses));
                        $i = 0;
                        ?>
                        <?php foreach ($statuses as $key => $status): ?>
                            <?php if ($key === 'ditolak' && $usulan['status'] !== 'ditolak') continue; ?>
                            <?php $item_class = ($i < $current_status_index) ? 'success' : (($i == $current_status_index) ? 'process' : ''); ?>
                            <li class="timeline-item <?= $item_class ?>">
                                <div class="timeline-icon"><i class="<?= $status['icon'] ?>"></i></div>
                                <div class="timeline-content">
                                    <h6 class="fw-bold mb-0"><?= $status['text'] ?></h6>
                                    <?php if ($i == $current_status_index): ?><p class="text-primary small mb-0">Tahap Saat Ini</p><?php endif; ?>
                                </div>
                            </li>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Daftar Arsip -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Arsip yang Diusulkan</h6>
                    <span class="badge bg-light text-primary rounded-pill"><?= count($items) ?> Item</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-detail">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Judul Dokumen</th>
                                    <th>No. Dokumen</th>
                                    <th class="text-center">Tahun</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <strong><?= esc($item['judul_dokumen']) ?></strong>
                                            <?php if ($item['status_verifikasi'] === 'ditolak' && !empty($item['catatan_item'])): ?>
                                                <span class="rejection-note"><i class="fas fa-comment-dots fa-sm"></i> Catatan: <?= esc($item['catatan_item']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($item['no_dokumen']) ?></td>
                                        <td class="text-center"><?= esc($item['tahun_cipta']) ?></td>
                                        <td class="text-center">
                                            <?php $item_status_info = $status_item_map[$item['status_verifikasi']] ?? ['class' => 'dark', 'text' => $item['status_verifikasi']]; ?>
                                            <span class="badge bg-<?= $item_status_info['class'] ?>"><?= $item_status_info['text'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>