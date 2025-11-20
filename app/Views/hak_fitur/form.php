<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php
            // Tentukan mode dan URL action
            $isEditMode = isset($hakFitur);
            // Kunci perbaikan: Sesuaikan URL sesuai routing PUT Anda
            $formAction = $isEditMode
                ? site_url('hak-fitur/update/' . $hakFitur['id']) // <--- URL yang BENAR
                : site_url('hak-fitur');
            ?>

            <form action="<?= $formAction ?>" method="post">
                <?= csrf_field() ?>
                <?php if ($isEditMode): ?>
                    <!-- Ini memberitahu CI4 bahwa ini adalah permintaan PUT -->
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <!-- Notifikasi Error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger" role="alert"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>
                <?php if ($validation->getErrors()): ?>
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Terjadi Kesalahan Validasi!</h4>
                        <hr>
                        <p class="mb-0"><?= $validation->listErrors() ?></p>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="id_user" class="form-label">Pengguna</label>
                    <select class="form-select select2-form" id="id_user" name="id_user" style="width: 100%;">
                        <option value="">-- Pilih Pengguna --</option>
                        <?php foreach ($user_options as $opt): ?>
                            <option value="<?= $opt['id'] ?>" <?= (old('id_user', $hakFitur['id_user'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= $opt['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <hr>
                <p class="fw-bold">Atur Hak Akses Berdasarkan Unit Kerja</p>
                <p class="text-muted small">Pilih hingga level terdalam yang diizinkan. Jika hanya Eselon 1 yang dipilih, pengguna dapat mengakses semua di bawahnya dengan izin baca. Jika Eselon 2 atau 3 dipilih, pengguna mendapat izin tulis.</p>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="id_es1" class="form-label">Unit Eselon 1</label>
                        <select class="form-select select2-form" id="id_es1" name="id_es1" style="width: 100%;">
                            <option value="">-- Hapus Pilihan Eselon 1 --</option>
                            <?php foreach ($es1_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= (old('id_es1', $hakFitur['id_es1'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= $opt['nama_es1'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="id_es2" class="form-label">Unit Eselon 2</label>
                        <select class="form-select select2-form" id="id_es2" name="id_es2" style="width: 100%;" disabled>
                            <option value="">-- Pilih Eselon 1 Dulu --</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="id_es3" class="form-label">Unit Eselon 3</label>
                        <select class="form-select select2-form" id="id_es3" name="id_es3" style="width: 100%;" disabled>
                            <option value="">-- Pilih Eselon 2 Dulu --</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= site_url('hak-fitur') ?>" class="btn btn-secondary">Batal</a>
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

        // --- LOGIKA CHAINED DROPDOWN ---
        const es1Select = $('#id_es1');
        const es2Select = $('#id_es2');
        const es3Select = $('#id_es3');

        function loadEs2(id_es1, selected_es2_id = null) {
            es2Select.prop('disabled', true).html('<option value="">Memuat...</option>').trigger('change');
            if (!id_es1) {
                es2Select.html('<option value="">-- Pilih Eselon 1 Dulu --</option>').trigger('change');
                return;
            }
            $.get(`<?= site_url('data/es2-by-es1/') ?>${id_es1}`, function(response) {
                es2Select.html('<option value="">-- Hapus Pilihan Eselon 2 --</option>');
                if (response && response.length > 0) {
                    es2Select.prop('disabled', false);
                    response.forEach(item => es2Select.append(new Option(item.nama_es2, item.id)));
                } else {
                    es2Select.html('<option value="">Unit Eselon 2 tidak tersedia</option>');
                }
                if (selected_es2_id) {
                    es2Select.val(selected_es2_id).trigger('change');
                }
            }, 'json');
        }

        function loadEs3(id_es2, selected_es3_id = null) {
            es3Select.prop('disabled', true).html('<option value="">Memuat...</option>').trigger('change');
            if (!id_es2) {
                es3Select.html('<option value="">-- Pilih Eselon 2 Dulu --</option>').trigger('change');
                return;
            }
            $.get(`<?= site_url('data/es3-by-es2/') ?>${id_es2}`, function(response) {
                es3Select.html('<option value="">-- Hapus Pilihan Eselon 3 --</option>');
                if (response && response.length > 0) {
                    es3Select.prop('disabled', false);
                    response.forEach(item => es3Select.append(new Option(item.nama_es3, item.id)));
                } else {
                    es3Select.html('<option value="">Unit Eselon 3 tidak tersedia</option>');
                }
                if (selected_es3_id) {
                    es3Select.val(selected_es3_id).trigger('change');
                }
            }, 'json');
        }

        // Pasang event listener
        es1Select.on('change', function() {
            loadEs2($(this).val());
        });
        es2Select.on('change', function() {
            loadEs3($(this).val());
        });

        // --- LOGIKA PRE-FILL UNTUK MODE EDIT ---
        const prefillEs1 = '<?= old('id_es1', $hakFitur['id_es1'] ?? '') ?>';
        const prefillEs2 = '<?= old('id_es2', $hakFitur['id_es2'] ?? '') ?>';
        const prefillEs3 = '<?= old('id_es3', $hakFitur['id_es3'] ?? '') ?>';

        if (prefillEs1) {
            // Gunakan timeout untuk memberi waktu Select2 inisialisasi
            setTimeout(function() {
                es1Select.trigger('change');

                // Gunakan .one() agar listener hanya berjalan sekali
                $(document).one('ajaxStop', function() {
                    if (prefillEs2) {
                        es2Select.val(prefillEs2).trigger('change');
                        $(document).one('ajaxStop', function() {
                            if (prefillEs3) {
                                es3Select.val(prefillEs3).trigger('change');
                            }
                        });
                    }
                });
            }, 100);
        }
    });
</script>
<?= $this->endSection() ?>