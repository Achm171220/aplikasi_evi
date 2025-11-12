<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php
            $isEditMode = isset($np);
            $formAction = $isEditMode ? site_url('nilai-pengawasan/' . $np['id']) : site_url('nilai-pengawasan');
            ?>

            <form action="<?= $formAction ?>" method="post">
                <?= csrf_field() ?>
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger" role="alert"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>
                <?php if ($validation->getErrors()): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $validation->listErrors() ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="id_es2" class="form-label">Unit Eselon 2</label>
                    <select class="form-select select2-form" id="id_es2" name="id_es2" style="width: 100%;" required>
                        <option value="">-- Pilih Unit Eselon 2 --</option>
                        <?php foreach ($es2_options as $opt): ?>
                            <option value="<?= $opt['id'] ?>" <?= (old('id_es2', $np['id_es2'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= esc($opt['nama_es2']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tahun" class="form-label">Tahun Pengawasan</label>
                        <input type="number" class="form-control" id="tahun" name="tahun" value="<?= old('tahun', $np['tahun'] ?? date('Y')) ?>" min="2000" max="<?= date('Y') + 1 ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="skor" class="form-label">Skor Nilai (0-100)</label>
                        <input type="number" step="0.01" class="form-control" id="skor" name="skor" value="<?= old('skor', $np['skor'] ?? '') ?>" min="0" max="100" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kategori Otomatis</label>
                        <input type="text" class="form-control" id="kategori_display" value="<?= $isEditMode ? esc($np['kategori']) : 'Belum Terhitung' ?>" readonly>
                        <small class="text-muted">Kategori akan ditentukan otomatis setelah Skor diinput.</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                    <a href="<?= site_url('nilai-pengawasan') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.select2-form').select2({
            theme: 'bootstrap-5'
        });

        const skorInput = $('#skor');
        const kategoriDisplay = $('#kategori_display');

        function updateKategoriDisplay() {
            const skor = parseFloat(skorInput.val());
            let kategori = 'Tidak Valid';

            if (isNaN(skor) || skor < 0 || skor > 100) {
                kategori = 'Skor tidak valid';
            } else if (skor >= 90) {
                kategori = 'AA (Sangat Memuaskan)';
            } else if (skor >= 80) {
                kategori = 'A (Memuaskan)';
            } else if (skor >= 70) {
                kategori = 'BB (Sangat Baik)';
            } else if (skor >= 60) {
                kategori = 'B (Baik)';
            } else if (skor >= 50) {
                kategori = 'CC (Sangat Cukup)';
            } else {
                kategori = 'C (Cukup)';
            }
            kategoriDisplay.val(kategori);
        }

        skorInput.on('input', updateKategoriDisplay);
        updateKategoriDisplay();
    });
</script>
<?= $this->endSection() ?>