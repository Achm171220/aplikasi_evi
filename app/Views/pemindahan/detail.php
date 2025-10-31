<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h5>Detail Usulan Pemindahan Arsip #<?= esc($proposal['id']) ?></h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Pengusul:</strong> <?= esc($proposal['pengusul_name']) ?></p>
                <p><strong>Status Proposal:</strong>
                    <?php
                    $statusClass = '';
                    switch ($proposal['status_proposal']) {
                        case 'submitted':
                            $statusClass = 'badge bg-secondary';
                            break;
                        case 'verified_1':
                            $statusClass = 'badge bg-info';
                            break;
                        case 'verified_2':
                            $statusClass = 'badge bg-primary';
                            break;
                        case 'verified_3':
                            $statusClass = 'badge bg-success';
                            break;
                        case 'completed':
                            $statusClass = 'badge bg-success';
                            break;
                        case 'rejected_1':
                        case 'rejected_2':
                        case 'rejected_3':
                            $statusClass = 'badge bg-danger';
                            break;
                        default:
                            $statusClass = 'badge bg-light text-dark';
                            break;
                    }
                    ?>
                    <span class="<?= $statusClass ?>"><?= esc(ucwords(str_replace('_', ' ', $proposal['status_proposal']))) ?></span>
                </p>
                <p><strong>Dibuat Pada:</strong> <?= esc(date('d-m-Y H:i', strtotime($proposal['created_at']))) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Verifikator 1 (Arsiparis):</strong> <?= esc($proposal['v1_name']) ?></p>
                <p><strong>Verifikator 2 (Pemangku):</strong> <?= esc($proposal['v2_name']) ?></p>
                <p><strong>Verifikator 3 (Verifikator):</strong> <?= esc($proposal['v3_name']) ?></p>
            </div>
        </div>

        <h6 class="mt-4">Catatan Verifikasi:</h6>
        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>V1 (Arsiparis):</strong> <?= esc($proposal['notes_v1'] ?? 'Belum ada catatan') ?></li>
            <li class="list-group-item"><strong>V2 (Pemangku):</strong> <?= esc($proposal['notes_v2'] ?? 'Belum ada catatan') ?></li>
            <li class="list-group-item"><strong>V3 (Verifikator):</strong> <?= esc($proposal['notes_v3'] ?? 'Belum ada catatan') ?></li>
        </ul>

        <h6 class="mt-4">Daftar Item Arsip dalam Usulan:</h6>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="itemsInProposalTable">
                <thead>
                    <tr>
                        <th>ID Item</th>
                        <th>No. Dokumen</th>
                        <th>Judul Dokumen</th>
                        <th>Tahun Cipta</th>
                        <th>Klasifikasi</th>
                        <th>Status Arsip</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($itemsInProposal)): ?>
                        <?php foreach ($itemsInProposal as $item): ?>
                            <tr>
                                <td><?= esc($item['id']) ?></td>
                                <td><?= esc($item['no_dokumen']) ?></td>
                                <td><?= esc($item['judul_dokumen']) ?></td>
                                <td><?= esc($item['tahun_cipta']) ?></td>
                                <td><?= esc($item['nama_klasifikasi']) ?></td>
                                <td><span class="badge bg-secondary"><?= esc(ucwords(str_replace('_', ' ', $item['status_arsip']))) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada item arsip yang terlampir dalam usulan ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $showVerificationForm = false;
        $allowedToVerify = false;
        $formTitle = '';

        if ($currentUserRoleAccess === 'admin') { // Menggunakan 'currentUserRoleAccess'
            if ($currentUserRoleJabatan === 'arsiparis' && ($proposal['status_proposal'] === 'submitted' || $proposal['status_proposal'] === 'rejected_1')) {
                if ($proposal['id_user_v1'] == $currentUserId) {
                    $showVerificationForm = true;
                    $allowedToVerify = true;
                    $formTitle = 'Verifikasi oleh Arsiparis';
                }
            } elseif ($currentUserRoleJabatan === 'pengampu' && ($proposal['status_proposal'] === 'verified_1' || $proposal['status_proposal'] === 'rejected_2')) {
                if ($proposal['id_user_v2'] == $currentUserId) {
                    $showVerificationForm = true;
                    $allowedToVerify = true;
                    $formTitle = 'Verifikasi oleh Pemangku';
                }
            } elseif ($currentUserRoleJabatan === 'verifikator' && ($proposal['status_proposal'] === 'verified_2' || $proposal['status_proposal'] === 'rejected_3')) {
                if ($proposal['id_user_v3'] == $currentUserId) {
                    $showVerificationForm = true;
                    $allowedToVerify = true;
                    $formTitle = 'Verifikasi oleh Verifikator';
                }
            }
        }
        ?>

        <?php if ($showVerificationForm && $allowedToVerify && $proposal['status_proposal'] !== 'completed'): ?>
            <hr class="my-4">
            <div class="card border-warning mb-3">
                <div class="card-header bg-warning text-white">
                    <h6><?= $formTitle ?></h6>
                </div>
                <div class="card-body">
                    <?= form_open(base_url('pemindahan/process-verification/' . $proposal['id'])) ?>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Verifikasi:</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="action" value="approve" class="btn btn-success me-2">Setujui</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">Tolak</button>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        <?php elseif ($proposal['status_proposal'] === 'completed'): ?>
            <hr class="my-4">
            <div class="alert alert-success mt-4" role="alert">
                <h4 class="alert-heading">Proposal Selesai!</h4>
                <p>Usulan ini telah selesai diverifikasi dan arsip telah dipindahkan.</p>
                <?php if ($proposal['id_ba']): ?>
                    <p>Nomor Berita Acara: <strong><?= esc($proposal['id_ba']) ?></strong></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('pemindahan') ?>" class="btn btn-secondary mt-4">Kembali</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#itemsInProposalTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });
    });
</script>
<?= $this->endSection() ?>