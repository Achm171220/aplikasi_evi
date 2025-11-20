<?= $this->extend('trial/main') ?>

<?= $this->section('content') ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><?= $title ?>: <?= esc($user['name']) ?></h6>
        <a href="/trial" class="btn btn-secondary btn-sm">Kembali ke Daftar User</a>
    </div>
    <div class="card-body">
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Nama:</strong> <?= esc($user['name']) ?></li>
            <li class="list-group-item"><strong>Email:</strong> <?= esc($user['email']) ?></li>
            <li class="list-group-item"><strong>Role Akses:</strong> <?= esc(ucfirst($user['role_access'])) ?></li>
            <li class="list-group-item"><strong>Role Jabatan:</strong> <?= esc(ucwords(str_replace('_', ' ', $user['role_jabatan']))) ?></li>
            <li class="list-group-item"><strong>Status:</strong>
                <span class="badge rounded-pill bg-<?= $user['status'] == 'aktif' ? 'success' : 'danger' ?>">
                    <?= esc(ucfirst($user['status'])) ?>
                </span>
            </li>
            <li class="list-group-item"><strong>API Token:</strong> <small class="text-muted"><?= esc($user['api_token'] ?? 'N/A') ?></small></li>
            <li class="list-group-item"><strong>Dibuat Pada:</strong> <?= esc($user['created_at']) ?></li>
            <li class="list-group-item"><strong>Diperbarui Pada:</strong> <?= esc($user['updated_at']) ?></li>
            <?php if (isset($user['deleted_at'])): ?>
                <li class="list-group-item"><strong>Dihapus Pada:</strong> <?= esc($user['deleted_at']) ?></li>
            <?php endif; ?>
        </ul>
        <div class="mt-3">
            <a href="/trial/edit/<?= esc($user['id']) ?>" class="btn btn-warning">Edit</a>
            <form action="/trial/delete/<?= esc($user['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>