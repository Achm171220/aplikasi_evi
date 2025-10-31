<?= $this->extend('layout_old/main') ?>

<?= $this->section('title') ?>Sistem Pemindahan Arsip BPKP - Dashboard<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Info Cards (Global Summary) */
    .info-card {
        text-align: center;
        padding: 1.5rem;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        cursor: pointer;
        border-radius: 0.75rem;
        /* Consistent with other cards */
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .info-card .icon {
        font-size: 3rem;
        margin-bottom: 0.5rem;
        /* Warna ikon akan disesuaikan dengan latar belakang card */
    }

    .info-card .number {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
        /* Warna angka akan disesuaikan */
    }

    .info-card .label {
        font-size: 1rem;
        /* Warna label akan disesuaikan */
    }

    /* Warna Kustom untuk Info Cards */
    .info-card.bg-custom-primary {
        background-color: var(--bs-primary) !important;
        color: white;
    }

    .info-card.bg-custom-dark {
        background-color: var(--bs-dark) !important;
        color: white;
    }

    .info-card.bg-custom-info {
        background-color: var(--bs-info) !important;
        color: white;
    }

    /* Workflow Status & Ditolak Cards */
    .workflow-status-card {
        border-radius: 0.75rem;
        /* Consistent with other cards */
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    }

    .workflow-status-card .card-header {
        background-color: var(--bs-primary) !important;
        /* Biru Primary untuk header */
        color: white;
        font-weight: 600;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .workflow-status-card .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        border-color: #eee;
        /* Border halus antar item */
    }

    .workflow-status-card .list-group-item:first-child {
        border-top-left-radius: 0;
        /* Remove top radius if header has it */
        border-top-right-radius: 0;
    }

    .workflow-status-card .list-group-item:last-child {
        border-bottom-left-radius: var(--bs-border-radius-lg);
        border-bottom-right-radius: var(--bs-border-radius-lg);
    }

    /* Kustomisasi badge */
    .badge.bg-info-soft {
        background-color: rgba(var(--bs-info-rgb), 0.15) !important;
        /* Info lebih lembut */
        color: var(--bs-info) !important;
    }

    .badge.bg-danger-soft {
        background-color: rgba(var(--bs-danger-rgb), 0.15) !important;
        /* Danger lebih lembut */
        color: var(--bs-danger) !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Dashboard Pemindahan Arsip</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Ringkasan status proses pemindahan arsip saat ini.</p>

        <!-- Status Alur Kerja Detil -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card workflow-status-card">
                    <div class="card-header">
                        <i class="fas fa-tasks me-2"></i> Status Proses Pemindahan
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            Usulan Baru (Menunggu V1)
                            <span class="badge bg-info-soft rounded-pill"><?= esc($counts['verifikasi1_menunggu']) ?></span>
                        </li>
                        <li class="list-group-item">
                            Menunggu Verifikasi 2
                            <span class="badge bg-info-soft rounded-pill"><?= esc($counts['verifikasi2_menunggu']) ?></span>
                        </li>
                        <li class="list-group-item">
                            Menunggu Verifikasi 3
                            <span class="badge bg-info-soft rounded-pill"><?= esc($counts['verifikasi3_menunggu']) ?></span>
                        </li>
                        <li class="list-group-item">
                            Menunggu Pembuatan Berita Acara
                            <span class="badge bg-info-soft rounded-pill"><?= esc($counts['buatba_menunggu']) ?></span>
                        </li>
                        <li class="list-group-item">
                            Menunggu Eksekusi Final
                            <span class="badge bg-info-soft rounded-pill"><?= esc($counts['eksekusi_menunggu']) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card workflow-status-card">
                    <div class="card-header">
                        <i class="fas fa-exclamation-triangle me-2"></i> Status Item Ditolak
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            Total Item Ditolak
                            <span class="badge bg-danger-soft rounded-pill"><?= esc($counts['total_ditolak']) ?></span>
                        </li>
                        <li class="list-group-item">
                            Ditolak Verifikasi 1
                            <span class="badge bg-danger-soft rounded-pill"><?= esc($counts['ditolak_verif1']) ?></span>
                        </li>
                        <li class="list-group-item">
                            Ditolak Verifikasi 2
                            <span class="badge bg-danger-soft rounded-pill"><?= esc($counts['ditolak_verif2']) ?></span>
                        </li>
                        <li class="list-group-item">
                            Ditolak Verifikasi 3
                            <span class="badge bg-danger-soft rounded-pill"><?= esc($counts['ditolak_verif3']) ?></span>
                        </li>
                        <li class="list-group-item text-muted small py-3">
                            <small>Item yang ditolak akan dikembalikan ke status 'belum' dan dapat diusulkan kembali oleh user.</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Statistik Berita Acara & Per ES2 Unit Kerja -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card workflow-status-card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice me-2"></i> Statistik Berita Acara
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            Total Berita Acara Terdaftar
                            <span class="badge bg-primary rounded-pill"><?= esc($counts['total_berita_acara']) ?></span>
                        </li>
                        <li class="list-group-item text-muted small py-3">
                            <small>Jumlah Berita Acara yang telah dibuat untuk proses pemindahan.</small>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card workflow-status-card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie me-2"></i> Pemindahan per Unit Kerja ES2
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($counts['pemindahan_per_es2'])): ?>
                            <li class="list-group-item text-center text-muted">
                                Tidak ada data pemindahan per unit kerja ES2.
                            </li>
                        <?php else: ?>
                            <?php foreach ($counts['pemindahan_per_es2'] as $es2Data): ?>
                                <li class="list-group-item">
                                    <?= esc($es2Data['nama_es2'] ?? 'Tidak Diketahui') ?>
                                    <span class="badge bg-secondary rounded-pill"><?= esc($es2Data['total_items_moved']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <li class="list-group-item text-muted small py-3">
                            <small>Jumlah item arsip yang telah dipindahkan per unit kerja Eselon 2.</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // No specific JS needed for dashboard functionality itself.
    });
</script>
<?= $this->endSection() ?>