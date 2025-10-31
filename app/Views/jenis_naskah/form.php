<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= isset($jenis_naskah) ? site_url('jenis-naskah/update/' . $jenis_naskah['id']) : site_url('jenis-naskah') ?>" method="post">

                <?php if (isset($jenis_naskah)): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="<?= $jenis_naskah['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="kode_naskah" class="form-label">Kode Naskah</label>
                    <input type="text" class="form-control <?= $validation->hasError('kode_naskah') ? 'is-invalid' : '' ?>" id="kode_naskah" name="kode_naskah" value="<?= old('kode_naskah', $jenis_naskah['kode_naskah'] ?? '') ?>">
                    <div class="invalid-feedback">
                        <?= $validation->getError('kode_naskah') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nama_naskah" class="form-label">Nama Naskah</label>
                    <input type="text" class="form-control <?= $validation->hasError('nama_naskah') ? 'is-invalid' : '' ?>" id="nama_naskah" name="nama_naskah" value="<?= old('nama_naskah', $jenis_naskah['nama_naskah'] ?? '') ?>">
                    <div class="invalid-feedback">
                        <?= $validation->getError('nama_naskah') ?>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= site_url('jenis-naskah') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>