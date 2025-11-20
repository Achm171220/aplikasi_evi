<?= $this->extend('layout_old/main') ?>

<?= $this->section('title') ?>Sistem Pemindahan Arsip BPKP - Monitoring<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Specific column widths for monitoring.php table */
    .table-monitoring .col-id {
        width: 8%;
    }

    .table-monitoring .col-judul {
        width: 25%;
        white-space: normal;
    }

    /* Allow wrapping */
    .table-monitoring .col-tahun {
        width: 7%;
    }

    .table-monitoring .col-lokasi {
        width: 12%;
    }

    .table-monitoring .col-verif {
        width: 9%;
    }

    .table-monitoring .col-bano {
        width: 9%;
    }

    .table-monitoring .col-batgl {
        width: 8%;
    }

    .table-monitoring .col-status {
        width: 10%;
    }

    .table-monitoring .col-catatan {
        width: 6%;
    }

    .note-view-btn {
        cursor: pointer;
        color: #0d6efd;
    }

    .note-view-btn:hover {
        color: #0a58ca;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Monitoring Usulan Pemindahan Arsip</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Lihat status terkini dari semua usulan pemindahan arsip.</p>

        <?php if (empty($items)): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> Belum ada usulan pemindahan arsip yang aktif.
            </div>
        <?php else: ?>
            <div class="table-container-scroll">
                <table class="table table-hover table-striped table-bordered align-middle table-monitoring">
                    <thead class="table-dark">
                        <tr>
                            <th class="col-status">Status Proses</th>
                            <th class="col-id">No. Dokumen</th>
                            <th class="col-judul">Judul Dokumen</th>
                            <th class="col-tahun">Tahun Cipta</th>
                            <th class="col-lokasi">Lokasi Simpan Aktif</th>
                            <th class="col-bano">No. BA</th>
                            <th class="col-batgl">Tgl BA</th>
                            <th class="col-catatan">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="col-status">
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($item['status_pindah'] == 'menunggu_verif1' || $item['status_pindah'] == 'menunggu_eksekusi') {
                                        $badgeClass = 'bg-info';
                                    } elseif (strpos($item['status_pindah'], 'disetujui') !== false || $item['status_pindah'] == 'dipindahkan') {
                                        $badgeClass = 'bg-success';
                                    } elseif (strpos($item['status_pindah'], 'ditolak') !== false) {
                                        $badgeClass = 'bg-danger';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= esc(str_replace('_', ' ', $item['status_pindah'])) ?></span>
                                </td>
                                <td class="col-id"><?= esc($item['no_dokumen']) ?></td>
                                <td class="col-judul wrap-text"><?= esc($item['judul_dokumen']) ?></td>
                                <td class="col-tahun"><?= esc($item['tahun_cipta']) ?></td>
                                <td class="col-lokasi"><?= esc($item['lokasi_simpan']) ?></td>
                                <td class="col-bano"><?= esc($item['no_ba'] ?? '-') ?></td>
                                <td class="col-batgl"><?= esc($item['tgl_ba'] ? date('d-m-Y', strtotime($item['tgl_ba'])) : '-') ?></td>
                                <td class="col-catatan">
                                    <?php if (!empty($item['admin_notes'])): ?>
                                        <a href="#" class="note-view-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Catatan" data-note="<?= esc($item['admin_notes']) ?>">
                                            <i class="fas fa-sticky-note"></i>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        document.querySelectorAll('.note-view-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const noteContent = this.dataset.note;
                Swal.fire({
                    title: 'Catatan Terakhir',
                    html: '<div style="max-height: 300px; overflow-y: auto; text-align: left; padding: 10px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px;">' + noteContent + '</div>',
                    icon: 'info',
                    confirmButtonText: 'Tutup',
                    width: '500px'
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>