<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= isset($klasifikasi) ? site_url('klasifikasi/update/' . $klasifikasi['id']) : site_url('klasifikasi') ?>" method="post">

                <?php if (isset($klasifikasi)): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="<?= $klasifikasi['id'] ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode" class="form-label">Kode Klasifikasi</label>
                        <input type="text" class="form-control <?= $validation->hasError('kode') ? 'is-invalid' : '' ?>" id="kode" name="kode" value="<?= old('kode', $klasifikasi['kode'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('kode') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nama_klasifikasi" class="form-label">Nama Klasifikasi</label>
                        <input type="text" class="form-control <?= $validation->hasError('nama_klasifikasi') ? 'is-invalid' : '' ?>" id="nama_klasifikasi" name="nama_klasifikasi" value="<?= old('nama_klasifikasi', $klasifikasi['nama_klasifikasi'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('nama_klasifikasi') ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="umur_aktif" class="form-label">Umur Aktif (Tahun)</label>
                        <input type="number" class="form-control <?= $validation->hasError('umur_aktif') ? 'is-invalid' : '' ?>" id="umur_aktif" name="umur_aktif" value="<?= old('umur_aktif', $klasifikasi['umur_aktif'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('umur_aktif') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="umur_inaktif" class="form-label">Umur Inaktif (Tahun)</label>
                        <input type="number" class="form-control <?= $validation->hasError('umur_inaktif') ? 'is-invalid' : '' ?>" id="umur_inaktif" name="umur_inaktif" value="<?= old('umur_inaktif', $klasifikasi['umur_inaktif'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('umur_inaktif') ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="skkad" class="form-label">SKKAD</label>
                        <select class="form-select <?= $validation->hasError('skkad') ? 'is-invalid' : '' ?>" id="skkad" name="skkad">
                            <?php $selectedSkkad = old('skkad', $klasifikasi['skkad'] ?? ''); ?>
                            <option value="biasa" <?= $selectedSkkad == 'biasa' ? 'selected' : '' ?>>Biasa</option>
                            <option value="terbatas" <?= $selectedSkkad == 'terbatas' ? 'selected' : '' ?>>Terbatas</option>
                            <option value="rahasia" <?= $selectedSkkad == 'rahasia' ? 'selected' : '' ?>>Rahasia</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('skkad') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nasib_akhir" class="form-label">Nasib Akhir Arsip</label>
                        <select class="form-select <?= $validation->hasError('nasib_akhir') ? 'is-invalid' : '' ?>" id="nasib_akhir" name="nasib_akhir">
                            <?php $selectedNasib = old('nasib_akhir', $klasifikasi['nasib_akhir'] ?? ''); ?>
                            <option value="musnah" <?= $selectedNasib == 'musnah' ? 'selected' : '' ?>>Musnah</option>
                            <option value="permanen" <?= $selectedNasib == 'permanen' ? 'selected' : '' ?>>Permanen</option>
                            <option value="lainnya" <?= $selectedNasib == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('nasib_akhir') ?></div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= site_url('klasifikasi') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>