<div class="table-responsive">
    <table class="table table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th>Nama Tema</th>
                <th>Deskripsi</th>
                <th style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($themes)): ?>
                <tr>
                    <td colspan="4" class="text-center fst-italic text-muted">Belum ada data tema yang ditambahkan.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1;
                foreach ($themes as $theme): ?>
                    <tr id="theme-row-<?= $theme['id'] ?>">
                        <td><?= $no++ ?></td>
                        <td><?= esc($theme['nama_tema']) ?></td>
                        <td><?= esc($theme['deskripsi']) ?></td>
                        <td class="text-center">
                            <!-- Tombol Edit dengan pola yang sama -->
                            <button class="btn btn-sm btn-warning me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#formModal"
                                hx-get="<?= site_url('tema/edit/' . $theme['id']) ?>"
                                hx-target="#modal-form-content"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- Tombol Hapus (tetap sama) -->
                            <button class="btn btn-sm btn-danger"
                                hx-delete="<?= site_url('tema/delete/' . $theme['id']) ?>"
                                hx-target="#theme-row-<?= $theme['id'] ?>"
                                hx-swap="outerHTML"
                                hx-confirm="Apakah Anda yakin ingin menghapus tema ini?"
                                title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>