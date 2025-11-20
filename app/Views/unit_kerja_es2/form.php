<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= isset($es2) ? site_url('unit-kerja-es2/update/' . $es2['id']) : site_url('unit-kerja-es2') ?>" method="post">

                <?php if (isset($es2)): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="<?= $es2['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="id_es1" class="form-label">Induk Unit Eselon 1</label>
                    <select class="form-select <?= $validation->hasError('id_es1') ? 'is-invalid' : '' ?>" id="id_es1" name="id_es1">
                        <option value="">-- Pilih Unit Eselon 1 --</option>
                        <?php $selectedEs1 = old('id_es1', $es2['id_es1'] ?? ''); ?>
                        <?php foreach ($es1_options as $option): ?>
                            <option value="<?= $option['id'] ?>" <?= $selectedEs1 == $option['id'] ? 'selected' : '' ?>>
                                <?= $option['nama_es1'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        <?= $validation->getError('id_es1') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="kode" class="form-label">Kode Eselon 2</label>
                    <input type="text" class="form-control <?= $validation->hasError('kode') ? 'is-invalid' : '' ?>" id="kode" name="kode" value="<?= old('kode', $es2['kode'] ?? '') ?>">
                    <div class="invalid-feedback">
                        <?= $validation->getError('kode') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nama_es2" class="form-label">Nama Eselon 2</label>
                    <input type="text" class="form-control <?= $validation->hasError('nama_es2') ? 'is-invalid' : '' ?>" id="nama_es2" name="nama_es2" value="<?= old('nama_es2', $es2['nama_es2'] ?? '') ?>">
                    <div class="invalid-feedback">
                        <?= $validation->getError('nama_es2') ?>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= site_url('unit-kerja-es2') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 pada dropdown Eselon 1
        $('#id_es1').select2({
            theme: 'bootstrap-5'
        });
    });
</script>
<?= $this->endSection() ?>