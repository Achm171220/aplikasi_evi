<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php
            // Tentukan URL action berdasarkan mode (tambah atau edit)
            if (isset($unitKerjaEs1)) {
                $formAction = site_url('unit-kerja-es1/update/' . $unitKerjaEs1['id']);
            } else {
                $formAction = site_url('unit-kerja-es1');
            }
            ?>

            <form action="<?= $formAction ?>" method="post">

                <?php if (isset($unitKerjaEs1)): ?>
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="kode" class="form-label">Kode Eselon 1</label>
                    <!-- PERBAIKAN UTAMA DI SINI -->
                    <input type="text" class="form-control <?= $validation->hasError('kode') ? 'is-invalid' : '' ?>" id="kode" name="kode" value="<?= old('kode', $unitKerjaEs1['kode'] ?? '') ?>" autofocus>
                    <div class="invalid-feedback">
                        <?= $validation->getError('kode') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nama_es1" class="form-label">Nama Eselon 1</label>
                    <!-- PERBAIKAN UTAMA DI SINI -->
                    <input type="text" class="form-control <?= $validation->hasError('nama_es1') ? 'is-invalid' : '' ?>" id="nama_es1" name="nama_es1" value="<?= old('nama_es1', $unitKerjaEs1['nama_es1'] ?? '') ?>">
                    <div class="invalid-feedback">
                        <?= $validation->getError('nama_es1') ?>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= site_url('unit-kerja-es1') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>