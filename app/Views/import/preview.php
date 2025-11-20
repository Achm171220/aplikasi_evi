<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
            <p class="mb-0 text-muted">Riwayat ID: <?= $importData['id'] ?>, Diimpor pada: <?= date('d M Y H:i', strtotime($importData['created_at'])) ?></p>
        </div>
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" style="font-size: 0.8rem;">
                    <thead>
                        <tr class="table-light">
                            <?php foreach ($header as $col): ?>
                                <th><?= esc($col) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tableData as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= esc($cell) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>