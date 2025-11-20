<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            <?php
            // PERBAIKAN FINAL: Tentukan URL action dengan if-else yang aman
            if (isset($unitKerjaEs3)) {
                $formAction = site_url('unit-kerja-es3/update/' . $unitKerjaEs3['id']);
            } else {
                $formAction = site_url('unit-kerja-es3');
            }
            ?>

            <form action="<?= $formAction ?>" method="post">

                <?php if (isset($unitKerjaEs3)): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" id="current_id" value="<?= $unitKerjaEs3['id'] ?>">
                <?php endif; ?>

                <?php
                $isSuperAdmin = session()->get('role_access') === 'superadmin';
                $prefillEs1 = old('id_es1', $unitKerjaEs3['id_es1'] ?? ($es2_prefill['id_es1'] ?? ''));
                $prefillEs2 = old('id_es2', $unitKerjaEs3['id_es2'] ?? ($es2_prefill['id'] ?? ''));
                ?>

                <!-- --- PERBAIKAN UTAMA DI SINI --- -->
                <?php if (!$isSuperAdmin && !empty($prefillEs2)): ?>
                    <!-- Jika user adalah Admin, sisipkan ID Eselon 2 secara tersembunyi -->
                    <input type="hidden" name="id_es2" value="<?= $prefillEs2 ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="form_id_es1" class="form-label">Induk Unit Eselon 1</label>
                        <select class="form-select select2-form" id="form_id_es1" style="width: 100%;" <?= !$isSuperAdmin ? 'disabled' : '' ?>>
                            <option value="">-- Pilih Unit Eselon 1 --</option>
                            <?php if (!empty($es1_options)): ?>
                                <?php foreach ($es1_options as $opt): ?>
                                    <option value="<?= $opt['id'] ?>" <?= ($prefillEs1 == $opt['id']) ? 'selected' : '' ?>><?= $opt['nama_es1'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_es2" class="form-label">Induk Unit Eselon 2</label>
                        <!-- Jika superadmin, tambahkan name="id_es2". Jika bukan, name tidak perlu karena sudah ada di hidden input -->
                        <select class="form-select select2-form <?= $validation->hasError('id_es2') ? 'is-invalid' : '' ?>"
                            id="id_es2"
                            <?= $isSuperAdmin ? 'name="id_es2"' : '' ?>
                            style="width: 100%;"
                            <?= !$isSuperAdmin ? 'disabled' : '' ?>
                            disabled>
                            <option value="">-- Pilih Eselon 1 Dulu --</option>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_es2') ?></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_suffix" class="form-label">Kode Eselon 3</label>
                        <div class="input-group">
                            <span class="input-group-text" id="kode-prefix">-</span>
                            <!-- PASTIKAN KELAS is-invalid ADA DI SINI -->
                            <input type="text" class="form-control <?= $validation->hasError('kode') ? 'is-invalid' : '' ?>" id="kode_suffix" name="kode_suffix" value="<?= old('kode_suffix', '') ?>" placeholder="Masukkan kode unik...">
                            <input type="hidden" id="kode" name="kode" value="<?= old('kode', $unitKerjaEs3['kode'] ?? '') ?>">
                        </div>
                        <!-- PASTIKAN DIV invalid-feedback ADA DI SINI -->
                        <div class="invalid-feedback d-block" id="kode-feedback"><?= $validation->getError('kode') ?></div>
                        <div id="kode-alert" class="alert alert-danger mt-2 p-2 small d-none">Kode ini sudah digunakan.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nama_es3" class="form-label">Nama Eselon 3</label>
                        <!-- PASTIKAN KELAS is-invalid ADA DI SINI -->
                        <input type="text" class="form-control <?= $validation->hasError('nama_es3') ? 'is-invalid' : '' ?>" id="nama_es3" name="nama_es3" value="<?= old('nama_es3', $unitKerjaEs3['nama_es3'] ?? '') ?>">
                        <!-- PASTIKAN DIV invalid-feedback ADA DI SINI -->
                        <div class="invalid-feedback"><?= $validation->getError('nama_es3') ?></div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" id="btn-save">Simpan</button>
                    <a href="<?= site_url('unit-kerja-es3') ?>" class="btn btn-secondary">Batal</a>
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

        // Deklarasi variabel elemen
        const es1Select = $('#form_id_es1');
        const es2Select = $('#id_es2');
        const kodePrefixSpan = $('#kode-prefix');
        const kodeSuffixInput = $('#kode_suffix');
        const kodeLengkapInput = $('#kode');
        const kodeAlert = $('#kode-alert');
        const btnSave = $('#btn-save');
        let es2DataMap = {};

        // --- LOGIKA KODE OTOMATIS & ALERT UNIK ---
        function updateKodePrefix() {
            const es2Id = es2Select.val();
            const es2Kode = es2DataMap[es2Id] || '';
            kodePrefixSpan.text(es2Kode ? es2Kode + '-' : '-');
            updateKodeLengkap();
        }

        function updateKodeLengkap() {
            const prefix = kodePrefixSpan.text();
            const suffix = kodeSuffixInput.val();
            kodeLengkapInput.val(prefix === '-' ? suffix : prefix + suffix);
            checkKodeUnik();
        }

        let debounceTimer;

        function checkKodeUnik() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const kode = kodeLengkapInput.val();
                const currentId = $('#current_id').val() || null;
                if (kode.length > kodePrefixSpan.text().length || (kode.length > 0 && kodePrefixSpan.text() === '-')) {
                    $.post("<?= site_url('data/check-kode-es3') ?>", {
                        kode: kode,
                        id: currentId
                    }, function(response) {
                        if (response.is_exist) {
                            kodeAlert.removeClass('d-none');
                            kodeSuffixInput.addClass('is-invalid');
                            btnSave.prop('disabled', true);
                        } else {
                            kodeAlert.addClass('d-none');
                            kodeSuffixInput.removeClass('is-invalid');
                            btnSave.prop('disabled', false);
                        }
                    }, 'json');
                } else {
                    kodeAlert.addClass('d-none');
                    kodeSuffixInput.removeClass('is-invalid');
                    btnSave.prop('disabled', false);
                }
            }, 500);
        }

        // --- LOGIKA CHAINED DROPDOWN ---
        function loadEs2(id_es1, selected_es2_id = null) {
            es2Select.prop('disabled', true).html('<option value="">Memuat...</option>').trigger('change');
            if (!id_es1) {
                es2Select.html('<option value="">-- Pilih Eselon 1 Dulu --</option>').trigger('change');
                return;
            }

            $.get(`<?= site_url('data/es2-by-es1/') ?>${id_es1}`, function(response) {
                es2Select.html('<option value="">-- Pilih Unit Eselon 2 --</option>');
                es2DataMap = {};
                if (response && response.length > 0) {
                    es2Select.prop('disabled', false);
                    response.forEach(item => {
                        es2DataMap[item.id] = item.kode;
                        es2Select.append(new Option(item.nama_es2, item.id));
                    });
                } else {
                    es2Select.html('<option value="">Unit Eselon 2 tidak tersedia</option>');
                }
                if (selected_es2_id) {
                    es2Select.val(selected_es2_id).trigger('change');
                }
            }, 'json');
        }

        // Pasang event listener
        es1Select.on('change', function() {
            loadEs2($(this).val());
        });
        es2Select.on('change', updateKodePrefix);
        kodeSuffixInput.on('keyup input', updateKodeLengkap);

        // --- LOGIKA PRE-FILL ---
        const isSuperAdmin = <?= json_encode($isSuperAdmin) ?>;
        const prefillEs1 = '<?= $prefillEs1 ?>';
        const prefillEs2 = '<?= $prefillEs2 ?>';
        const kodeLengkap = '<?= old('kode', $unitKerjaEs3['kode'] ?? '') ?>'; // Kode lengkap dari DB

        function setSuffix(kodeLengkap, prefixText) {
            if (kodeLengkap && prefixText && prefixText !== '-') {
                // Hapus prefix dari kode lengkap untuk mendapatkan suffix
                const suffix = kodeLengkap.replace(prefixText, '');
                kodeSuffixInput.val(suffix);
            }
            kodeLengkapInput.val(kodeLengkap);
            updateKodePrefix(); // Panggil ini terakhir untuk memastikan tampilan kode benar
        }

        // 1. Logika untuk Admin (Terisi Otomatis)
        if (!isSuperAdmin) {
            const es2PrefillData = <?= json_encode($es2_prefill ?? null) ?>;
            if (es2PrefillData) {
                // Langsung set data map dan trigger change
                es2Select.html('');
                es2DataMap[es2PrefillData.id] = es2PrefillData.kode;
                es2Select.append(new Option(es2PrefillData.nama_es2, es2PrefillData.id));
                es2Select.val(es2PrefillData.id).trigger('change');

                // Set suffix secara langsung karena data ES2 sudah tersedia
                const prefixText = es2PrefillData.kode + '-';
                setSuffix(kodeLengkap, prefixText);
            }
        }
        // 2. Logika untuk Superadmin (Membutuhkan AJAX)
        else if (prefillEs1) {
            // Kita butuh AJAX loadEs2 selesai dulu untuk mengetahui kode prefix
            loadEs2(prefillEs1, prefillEs2);

            // Tunggu hingga AJAX selesai memuat ES2 dan kode prefix sudah terisi
            // Kita gunakan .one('ajaxStop') yang lebih aman untuk menunggu AJAX selesai
            $(document).one('ajaxStop', function() {
                const prefixText = kodePrefixSpan.text();
                // Pastikan ES2 sudah terpilih sebelum memanggil setSuffix
                if (es2Select.val() && prefixText !== '-') {
                    setSuffix(kodeLengkap, prefixText);
                }
            });
        }

        // Panggil updateKodeLengkap setelah semua selesai (untuk memastikan validasi kode unik berjalan)
        updateKodeLengkap();
    });
</script>
<?= $this->endSection() ?>