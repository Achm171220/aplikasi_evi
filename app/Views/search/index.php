<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Judul Halaman -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Overview</div>
                <h1 class="page-title">Pencarian Arsip</h1>
            </div>
        </div>
    </div>

    <!-- Form Pencarian (mirip dengan modal) -->
    <div class="mb-4">
        <form action="<?= site_url('search') ?>" method="get">
            <div class="input-group input-group-lg shadow-sm rounded-3">
                <input type="search" name="q" class="form-control border-0" placeholder="Masukkan kata kunci: judul, nomor dokumen, nomor berkas..." value="<?= esc($keyword, 'attr') ?>" required>
                <button class="btn btn-primary" type="submit" style="border-radius: 0 0.375rem 0.375rem 0;">
                    <i class="bi bi-search me-2"></i> Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Tampilkan hasil jika ada kata kunci -->
    <?php if (!empty($keyword)): ?>
        <p class="text-muted">Menampilkan hasil pencarian untuk: <strong>"<?= esc($keyword) ?>"</strong></p>

        <!-- Hasil dari Berkas Aktif -->
        <?php if (!empty($results['berkas_aktif'])): ?>
            <h5 class="mb-3 mt-4 text-gray-600">Ditemukan di Berkas Aktif</h5>
            <div class="list-group">
                <?php foreach ($results['berkas_aktif'] as $berkas): ?>
                    <a href="<?= site_url('berkas-aktif/detail/' . $berkas['id']) ?>" class="list-group-item list-group-item-action mb-2 border-start-3 border-start-success rounded-3 shadow-sm">
                        <div class="d-flex w-100 align-items-center">
                            <i class="bi bi-folder-fill fs-3 text-success me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold"><?= esc($berkas['nama_berkas']) ?></h6>
                                <small class="text-muted">No. Berkas: <?= esc($berkas['no_berkas'] ?? '-') ?></small>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Hasil dari Item Aktif -->
        <?php if (!empty($results['item_aktif'])): ?>
            <h5 class="mb-3 mt-4 text-gray-600">Ditemukan di Item Aktif</h5>
            <div class="list-group">
                <?php foreach ($results['item_aktif'] as $item): ?>
                    <a href="<?= site_url('item-aktif/edit/' . $item['id']) ?>" class="list-group-item list-group-item-action mb-2 border-start-3 border-start-primary rounded-3 shadow-sm">
                        <div class="d-flex w-100 align-items-center">
                            <i class="bi bi-file-earmark-text-fill fs-3 text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold"><?= esc($item['judul_dokumen']) ?></h6>
                                <small class="text-muted">No. Dokumen: <?= esc($item['no_dokumen'] ?? 'Tidak ada') ?> | Tahun: <?= esc($item['tahun_cipta']) ?></small>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Pesan jika tidak ada hasil sama sekali -->
        <?php if (empty($results['berkas_aktif']) && empty($results['item_aktif'])): ?>
            <div class="text-center py-5">
                <div class="display-1 text-muted"><i class="bi bi-journal-x"></i></div>
                <h4 class="mt-3">Hasil Tidak Ditemukan</h4>
                <p class="text-muted">Kami tidak dapat menemukan arsip yang cocok dengan kata kunci Anda. Coba gunakan kata kunci lain.</p>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Tampilan awal sebelum pencarian -->
        <div class="text-center py-5">
            <div class="display-1 text-primary"><i class="bi bi-search"></i></div>
            <h4 class="mt-3">Mulai Mencari Arsip</h4>
            <p class="text-muted">Gunakan kotak pencarian di atas untuk menemukan item atau berkas arsip secara cepat di seluruh sistem.</p>
        </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Style tambahan untuk border berwarna di hasil pencarian */
    .border-start-3 {
        border-left-width: .25rem !important;
    }
</style>
<?= $this->endSection() ?>