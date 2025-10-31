<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <!-- Tampilkan notifikasi error manual dari controller (jika ada) -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger" role="alert"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= isset($berkas) ? site_url('berkas-aktif/update/' . $berkas['id']) : site_url('berkas-aktif') ?>" method="post">
                <?= csrf_field() ?>
                <?php if (isset($berkas)): ?>
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nama_berkas" class="form-label">Nama Berkas</label>
                    <input type="text" class="form-control <?= $validation->hasError('nama_berkas') ? 'is-invalid' : '' ?>" id="nama_berkas" name="nama_berkas" value="<?= old('nama_berkas', $berkas['nama_berkas'] ?? '') ?>" autofocus>
                    <div class="invalid-feedback"><?= $validation->getError('nama_berkas') ?></div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_klasifikasi" class="form-label">Kode Klasifikasi</label>
                        <select class="form-select select2-form <?= $validation->hasError('id_klasifikasi') ? 'is-invalid' : '' ?>" id="id_klasifikasi" name="id_klasifikasi" style="width: 100%;">
                            <option value="">-- Pilih Klasifikasi --</option>
                            <?php foreach ($klasifikasi_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= (old('id_klasifikasi', $berkas['id_klasifikasi'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= $opt['kode'] ?> - <?= $opt['nama_klasifikasi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_klasifikasi') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="no_box" class="form-label">Nomor Box</label>
                        <input type="text" class="form-control" id="no_box" name="no_box" value="<?= old('no_box', $berkas['no_box'] ?? '') ?>">
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Detail Unit Kerja & Nomor Berkas</h5>
                <div class="row">
                    <?php
                    $isSuperAdmin = session()->get('role_access') === 'superadmin';
                    // Prioritas: old data > data dari controller (mode edit/hak akses)
                    $prefillEs2 = old('id_es2', $berkas['id_es2'] ?? ($id_es2_prefill ?? ''));
                    $prefillEs3 = old('id_es3', $berkas['id_es3'] ?? null);

                    // Ekstrak nomor berkas input untuk mode edit
                    $noBerkasInputValue = '';
                    if (isset($berkas['no_berkas'])) {
                        $parts = explode('-', $berkas['no_berkas']);
                        $noBerkasInputValue = end($parts);
                    }
                    $noBerkasInputValue = old('no_berkas_input', $noBerkasInputValue);
                    ?>
                    <div class="col-md-4 mb-3">
                        <label for="form_id_es2" class="form-label">Unit Eselon 2</label>
                        <select class="form-select select2-form" id="form_id_es2" name="id_es2" style="width: 100%;" <?= !$isSuperAdmin ? 'disabled' : '' ?>>
                            <option value="">-- Pilih Unit Eselon 2 --</option>
                            <?php foreach ($es2_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= ($prefillEs2 == $opt['id']) ? 'selected' : '' ?>><?= $opt['nama_es2'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <!-- Input tersembunyi untuk mengirim nilai jika disabled -->
                        <?php if (!$isSuperAdmin): ?>
                            <input type="hidden" name="id_es2" value="<?= $prefillEs2 ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="id_es3" class="form-label">Unit Eselon 3</label>
                        <select class="form-select select2-form <?= $validation->hasError('id_es3') ? 'is-invalid' : '' ?>" id="id_es3" name="id_es3" style="width: 100%;" disabled>
                            <option value="">-- Pilih Eselon 2 Dulu --</option>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_es3') ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="no_berkas_input" class="form-label">Nomor Berkas (Input Angka)</label>
                        <input type="number" class="form-control <?= $validation->hasError('no_berkas_input') ? 'is-invalid' : '' ?>" id="no_berkas_input" name="no_berkas_input" value="<?= $noBerkasInputValue ?>" min="1">
                        <div class="invalid-feedback"><?= $validation->getError('no_berkas_input') ?></div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= site_url('berkas-aktif') ?>" class="btn btn-secondary">Batal</a>
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

        const es2Select = $('#form_id_es2');
        const es3Select = $('#id_es3');
        // Superadmin punya dropdown Es2 yang bisa dipilih, jadi kita butuh hidden input
        // untuk memastikan nilainya selalu terkirim
        <?php if ($isSuperAdmin): ?>
            $('<input>').attr({
                type: 'hidden',
                id: 'hidden_id_es2',
                name: 'id_es2',
                value: es2Select.val()
            }).insertAfter(es2Select);
        <?php endif; ?>

        function loadEs3(id_es2, selected_es3_id = null) {
            es3Select.prop('disabled', true).html('<option value="">Memuat...</option>').trigger('change');
            if (!id_es2) {
                es3Select.html('<option value="">-- Pilih Eselon 2 Dulu --</option>').trigger('change');
                return;
            }

            $.get(`<?= site_url('data/es3-by-es2/') ?>${id_es2}`, function(response) {
                es3Select.html('<option value="">-- Pilih Unit Eselon 3 --</option>');
                if (response && response.length > 0) {
                    es3Select.prop('disabled', false);
                    response.forEach(item => es3Select.append(new Option(item.nama_es3, item.id)));
                } else {
                    es3Select.prop('disabled', true);
                    es3Select.html('<option value="">Eselon 3 tidak tersedia</option>');
                }
                if (selected_es3_id) {
                    es3Select.val(selected_es3_id);
                }
                es3Select.trigger('change');
            }, 'json');
        }

        es2Select.on('change', function() {
            const selectedId = $(this).val();
            // Update hidden input jika ada (untuk superadmin)
            $('#hidden_id_es2').val(selectedId);
            loadEs3(selectedId);
        });

        const prefillEs2 = '<?= $prefillEs2 ?>';
        const prefillEs3 = '<?= $prefillEs3 ?>';

        if (prefillEs2) {
            // Panggil fungsi loadEs3 untuk memuat data Eselon 3
            loadEs3(prefillEs2, prefillEs3);
        }
    });
</script>
<?= $this->endSection() ?>