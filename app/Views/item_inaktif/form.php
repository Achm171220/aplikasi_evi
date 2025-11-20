<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <!-- ... (Notifikasi error validasi dan flashdata) ... -->
            <form action="<?= isset($itemInaktif) ? site_url('item-inaktif/update/' . $itemInaktif['id']) : site_url('item-inaktif') ?>" method="post">

                <?php if (isset($itemInaktif)): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="<?= $itemInaktif['id'] ?>">
                <?php endif; ?>

                <h5 class="mb-3 border-bottom pb-2">Informasi Dasar Arsip</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="no_dokumen" class="form-label">No. Dokumen</label>
                        <input type="text" class="form-control <?= $validation->hasError('no_dokumen') ? 'is-invalid' : '' ?>" id="no_dokumen" name="no_dokumen" value="<?= old('no_dokumen', $itemInaktif['no_dokumen'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('no_dokumen') ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tgl_dokumen" class="form-label">Tanggal Dokumen</label>
                        <input type="date" class="form-control <?= $validation->hasError('tgl_dokumen') ? 'is-invalid' : '' ?>" id="tgl_dokumen" name="tgl_dokumen" value="<?= old('tgl_dokumen', $itemInaktif['tgl_dokumen'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('tgl_dokumen') ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tahun_cipta" class="form-label">Tahun Cipta</label>
                        <input type="text" class="form-control <?= $validation->hasError('tahun_cipta') ? 'is-invalid' : '' ?>" id="tahun_cipta" name="tahun_cipta" value="<?= old('tahun_cipta', $itemInaktif['tahun_cipta'] ?? '') ?>" readonly>
                        <div class="invalid-feedback"><?= $validation->getError('tahun_cipta') ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="judul_dokumen" class="form-label">Judul Dokumen</label>
                        <input type="text" class="form-control <?= $validation->hasError('judul_dokumen') ? 'is-invalid' : '' ?>" id="judul_dokumen" name="judul_dokumen" value="<?= old('judul_dokumen', $itemInaktif['judul_dokumen'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('judul_dokumen') ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="dasar_catat" class="form-label">Dasar Pencatatan</label>
                        <select class="form-select select2-form <?= $validation->hasError('dasar_catat') ? 'is-invalid' : '' ?>" id="dasar_catat" name="dasar_catat">
                            <?php
                            $options = ['srikandi', 'sima', 'map', 'bisma', 'sadewa', 'pos', 'lainnya'];
                            $selectedValue = old('dasar_catat', $itemInaktif['dasar_catat'] ?? '');
                            ?>
                            <option value="">-- Pilih Dasar Pencatatan --</option>
                            <?php foreach ($options as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($selectedValue == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('dasar_catat') ?></div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3 border-bottom pb-2">Detail Klasifikasi & Unit Pencipta</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_klasifikasi" class="form-label">Kode Klasifikasi</label>
                        <select class="form-select select2-form <?= $validation->hasError('id_klasifikasi') ? 'is-invalid' : '' ?>" id="id_klasifikasi" name="id_klasifikasi" style="width: 100%;">
                            <option value="">-- Pilih Klasifikasi --</option>
                            <?php foreach ($klasifikasi_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= (old('id_klasifikasi', $itemInaktif['id_klasifikasi'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= $opt['kode'] ?> - <?= $opt['nama_klasifikasi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_klasifikasi') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_jenis_naskah" class="form-label">Jenis Naskah</label>
                        <select class="form-select select2-form <?= $validation->hasError('id_jenis_naskah') ? 'is-invalid' : '' ?>" id="id_jenis_naskah" name="id_jenis_naskah" style="width: 100%;">
                            <option value="">-- Pilih Jenis Naskah --</option>
                            <?php foreach ($jenis_naskah_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= (old('id_jenis_naskah', $itemInaktif['id_jenis_naskah'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= $opt['nama_naskah'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_jenis_naskah') ?></div>
                    </div>
                </div>
                <div class="row">
                    <?php
                    $isSuperAdmin = session()->get('user_role') === 'superadmin';
                    $prefillEs2 = old('id_es2_for_form', $itemInaktif['id_es2_for_form'] ?? ($id_es2_prefill ?? ''));
                    $prefillEs3 = old('id_es3', $itemInaktif['id_es3'] ?? ($id_es3_prefill ?? ''));
                    ?>
                    <div class="col-md-6 mb-3">
                        <label for="form_id_es2" class="form-label">Unit Eselon 2</label>
                        <select class="form-select select2-form" id="form_id_es2" style="width: 100%;" <?= !$isSuperAdmin ? 'disabled' : '' ?>>
                            <option value="">-- Pilih Unit Eselon 2 --</option>
                            <?php foreach ($es2_options as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= ($prefillEs2 == $opt['id']) ? 'selected' : '' ?>><?= esc($opt['nama_es2']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_es3" class="form-label">Unit Eselon 3 (Pencipta)</label>
                        <select class="form-select select2-form <?= $validation->hasError('id_es3') ? 'is-invalid' : '' ?>" id="id_es3" name="id_es3" style="width: 100%;" disabled>
                            <option value="">-- Pilih Eselon 2 Dulu --</option>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_es3') ?></div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3 border-bottom pb-2">Informasi Fisik & Lokasi Arsip</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control <?= $validation->hasError('jumlah') ? 'is-invalid' : '' ?>" id="jumlah" name="jumlah" value="<?= old('jumlah', $itemInaktif['jumlah'] ?? 1) ?>" min="1">
                        <div class="invalid-feedback"><?= $validation->getError('jumlah') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tk_perkembangan" class="form-label">Tingkat Perkembangan</label>
                        <select class="form-select <?= $validation->hasError('tk_perkembangan') ? 'is-invalid' : '' ?>" name="tk_perkembangan">
                            <?php $selectedTk = old('tk_perkembangan', $itemInaktif['tk_perkembangan'] ?? 'asli'); ?>
                            <option value="asli" <?= $selectedTk == 'asli' ? 'selected' : '' ?>>Asli</option>
                            <option value="copy" <?= $selectedTk == 'copy' ? 'selected' : '' ?>>Copy</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('tk_perkembangan') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="media_simpan" class="form-label">Media Simpan</label>
                        <select class="form-select <?= $validation->hasError('media_simpan') ? 'is-invalid' : '' ?>" id="media_simpan" name="media_simpan">
                            <?php $selectedMedia = old('media_simpan', $itemInaktif['media_simpan'] ?? 'kertas'); ?>
                            <option value="kertas">Kertas</option>
                            <option value="elektronik">Elektronik</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('media_simpan') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="no_box" class="form-label">Nomor Box</label>
                        <input type="text" class="form-control" id="no_box" name="no_box" value="<?= old('no_box', $itemInaktif['no_box'] ?? '') ?>">
                    </div>
                </div>

                <div id="elektronik-fields" class="row" style="display: none;">
                    <div class="col-md-4 mb-3">
                        <label for="nama_file" class="form-label">Nama File</label>
                        <input type="text" class="form-control" id="nama_file" name="nama_file" value="<?= old('nama_file', $itemInaktif['nama_file'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="nama_folder" class="form-label">Nama Folder</label>
                        <input type="text" class="form-control" id="nama_folder" name="nama_folder" value="<?= old('nama_folder', $itemInaktif['nama_folder'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="nama_link" class="form-label">Link</label>
                        <input type="text" class="form-control" id="nama_link" name="nama_link" value="<?= old('nama_link', $itemInaktif['nama_link'] ?? '') ?>">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Item Inaktif</button>
                    <a href="<?= site_url('item-inaktif') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // 1. Inisialisasi awal
        $('.select2-form').select2({
            theme: 'bootstrap-5'
        });

        $('#tgl_dokumen').on('change', function() {
            const tgl = $(this).val();
            $('#tahun_cipta').val(tgl ? new Date(tgl).getFullYear() : '');
        }).trigger('change');

        const mediaSimpanSelect = $('#media_simpan');
        const noBoxInput = $('#no_box');
        const elektronikFields = $('#elektronik-fields');

        function toggleMediaFields() {
            const selectedMedia = mediaSimpanSelect.val();
            if (selectedMedia === 'elektronik') {
                noBoxInput.prop('disabled', true).val('');
                elektronikFields.slideDown();
            } else {
                noBoxInput.prop('disabled', false);
                elektronikFields.slideUp();
                $('#nama_file, #nama_folder, #nama_link').val('');
            }
        }
        toggleMediaFields();
        mediaSimpanSelect.on('change', toggleMediaFields);

        // 2. Logika Chained Dropdown
        const es2Select = $('#form_id_es2');
        const es3Select = $('#id_es3');

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
                    es3Select.html('<option value="">Unit Eselon 3 tidak tersedia</option>');
                }
                if (selected_es3_id) {
                    es3Select.val(selected_es3_id).trigger('change');
                }
            }, 'json');
        }

        es2Select.on('change', function() {
            loadEs3($(this).val());
        });

        // 3. Logika Pre-fill untuk Edit Mode atau Hak Akses
        const isSuperAdmin = <?= json_encode(session()->get('user_role') === 'superadmin') ?>;
        const prefillEs2 = '<?= old('id_es2_for_form', $itemInaktif['id_es2_for_form'] ?? ($id_es2_prefill ?? '')) ?>';
        const prefillEs3 = '<?= old('id_es3', $itemInaktif['id_es3'] ?? ($id_es3_prefill ?? '')) ?>';

        if (prefillEs2) {
            if (isSuperAdmin) {
                es2Select.val(prefillEs2).trigger('change');
                $(document).one('ajaxStop', function() {
                    if (prefillEs3) es3Select.val(prefillEs3).trigger('change');
                });
            } else {
                const es2OptionsForAdmin = <?= json_encode($es2_options ?? []) ?>;
                es2Select.html('');
                if (es2OptionsForAdmin && es2OptionsForAdmin.length > 0) {
                    es2OptionsForAdmin.forEach(opt => es2Select.append(new Option(opt.nama_es2, opt.id)));
                    es2Select.val(prefillEs2).trigger('change');
                    es2Select.prop('disabled', true);
                } else {
                    es2Select.html('<option value="">Hak akses unit tidak ditemukan</option>').prop('disabled', true);
                }
                loadEs3(prefillEs2, prefillEs3);
            }
        }
    });
</script>
<?= $this->endSection() ?>