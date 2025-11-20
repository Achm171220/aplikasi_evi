<div class="modal fade" id="tema-modal" tabindex="-1" aria-labelledby="temaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="tema-form" action="<?= esc($form_action) ?>" method="post">
                <?= csrf_field() ?>
                <?php if (isset($tema)): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="<?= esc($tema['id']) ?>">
                <?php endif; ?>

                <div class="modal-header">
                    <h5 class="modal-title" id="temaModalLabel"><?= esc($title) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_tema" class="form-label">Nama Tema</label>
                        <input type="text" class="form-control" id="nama_tema" name="nama_tema" value="<?= old('nama_tema', $tema['nama_tema'] ?? '') ?>" required>
                        <!-- Placeholder untuk pesan error validasi -->
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= old('deskripsi', $tema['deskripsi'] ?? '') ?></textarea>
                        <!-- Placeholder untuk pesan error validasi -->
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>