<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<!-- Memastikan link Select2 CSS dimuat di layout atau di header ini -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Agar Select2 tampil penuh di dalam Bootstrap */
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">

            <?php // Tampilkan pesan sukses/error
            if (isset($validation) && $validation->getErrors()): ?>
                <div class="alert alert-danger">
                    Validasi gagal. Mohon periksa input Anda.
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <form action="<?= isset($item) ? site_url('item-aktif/update/' . $item['id']) : site_url('itemaktif/store') ?>" method="post">
                <?= csrf_field() ?>
                <?php if (isset($item)): ?>
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <!-- ======================================================= -->
                <!-- BARU: Pilihan Sumber Data dan Filter API -->
                <!-- ======================================================= -->
                <h5 class="mb-3 border-bottom pb-2">Sumber Data Arsip</h5>

                <!-- Pilih Sumber Data -->
                <div class="mb-3">
                    <label for="sumber_data" class="form-label fw-bold">Sumber Data</label>
                    <select id="sumber_data" class="form-select">
                        <option value="manual">Isi Manual</option>
                        <option value="api_sima">API SIMA BPKP (Laporan PKAU)</option>
                        <option value="api_sadewa">API SADEWA BPKP (Surat Masuk)</option>
                    </select>
                </div>

                <!-- Filter Tahun (Wajib untuk API) -->
                <div id="filter_tahun_section" class="mb-4" style="display: none;">
                    <div class="mb-3">
                        <label for="filter_tahun" class="form-label fw-bold">Tahun Dokumen (Wajib Filter API)</label>
                        <input type="number" class="form-control" id="filter_tahun" placeholder="Contoh: 2024">
                        <div class="form-text text-danger" id="tahun_warning" style="display: none;">
                            Harap isi tahun (4 digit) terlebih dahulu sebelum melakukan pencarian API.
                        </div>
                    </div>
                </div>

                <!-- Area Pencarian API -->
                <div id="search_api_section" class="mb-4" style="display: none;">
                    <div class="alert alert-info">
                        Silahkan cari data dari sumber yang dipilih:
                    </div>

                    <div class="mb-3 api-search-container" id="sima_search_container" style="display: none;">
                        <label for="search_sima" class="form-label">Cari Data Laporan SIMA</label>
                        <select class="form-control" id="search_sima" style="width: 100%;"></select>
                    </div>

                    <div class="mb-3 api-search-container" id="sadewa_search_container" style="display: none;">
                        <label for="search_sadewa" class="form-label">Cari Data Surat Masuk SADEWA</label>
                        <select class="form-control" id="search_sadewa" style="width: 100%;"></select>
                    </div>
                </div>

                <hr>

                <!-- ======================================================= -->
                <!-- INFORMASI DASAR ARSIP (Diisi manual atau otomatis) -->
                <!-- ======================================================= -->

                <h5 class="mb-3 border-bottom pb-2">Informasi Dasar Arsip</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="no_dokumen" class="form-label">No. Dokumen</label>
                        <input type="text" class="form-control <?= $validation->hasError('no_dokumen') ? 'is-invalid' : '' ?>" id="no_dokumen" name="no_dokumen" value="<?= old('no_dokumen', $item['no_dokumen'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('no_dokumen') ?></div>
                        <small class="form-text text-muted">Diisi otomatis dari API.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tgl_dokumen" class="form-label">Tanggal Dokumen</label>
                        <input type="date" class="form-control <?= $validation->hasError('tgl_dokumen') ? 'is-invalid' : '' ?>" id="tgl_dokumen" name="tgl_dokumen" value="<?= old('tgl_dokumen', $item['tgl_dokumen'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('tgl_dokumen') ?></div>
                        <small class="form-text text-muted">Diisi otomatis dari API.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tahun_cipta" class="form-label">Tahun Cipta</label>
                        <input type="text" class="form-control" id="tahun_cipta" name="tahun_cipta" value="<?= old('tahun_cipta', $item['tahun_cipta'] ?? '') ?>" readonly>
                        <small class="form-text text-muted">Terisi otomatis dari Tanggal Dokumen.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="judul_dokumen" class="form-label">Judul Dokumen / Perihal</label>
                        <input type="text" class="form-control <?= $validation->hasError('judul_dokumen') ? 'is-invalid' : '' ?>" id="judul_dokumen" name="judul_dokumen" value="<?= old('judul_dokumen', $item['judul_dokumen'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('judul_dokumen') ?></div>
                        <small class="form-text text-muted">Diisi otomatis dari API.</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="dasar_catat" class="form-label">Dasar Pencatatan</label>
                        <select class="form-select select2-form <?= $validation->hasError('dasar_catat') ? 'is-invalid' : '' ?>" id="dasar_catat" name="dasar_catat" style="width: 100%;">
                            <?php
                            $options = ['Srikandi', 'SIMA', 'MAP', 'BISMA', 'SADEWA', 'POS', 'Lainnya'];
                            $selectedValue = old('dasar_catat', $item['dasar_catat'] ?? '');
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
                        <select class="form-select select2-form" id="id_klasifikasi" name="id_klasifikasi" style="width: 100%;">
                            <option value="">-- Pilih Klasifikasi --</option>
                            <?php foreach ($klasifikasi_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= (old('id_klasifikasi', $item['id_klasifikasi'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= $opt['kode'] ?> - <?= $opt['nama_klasifikasi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_klasifikasi') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_jenis_naskah" class="form-label">Jenis Naskah</label>
                        <select class="form-select select2-form" id="id_jenis_naskah" name="id_jenis_naskah" style="width: 100%;">
                            <option value="">-- Pilih Jenis Naskah --</option>
                            <?php foreach ($jenis_naskah_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= (old('id_jenis_naskah', $item['id_jenis_naskah'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= $opt['nama_naskah'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_jenis_naskah') ?></div>
                    </div>
                </div>
                <div class="row">
                    <?php
                    $isSuperAdmin = session()->get('user_role') === 'superadmin';
                    $prefillEs2 = old('id_es2_for_form', $item['id_es2_for_form'] ?? ($id_es2_prefill ?? ''));
                    ?>
                    <input type="hidden" name="id_es2" id="hidden_id_es2" value="<?= $prefillEs2 ?>">

                    <div class="col-md-6 mb-3">
                        <label for="form_id_es2" class="form-label">Unit Eselon 2</label>
                        <select class="form-select select2-form" id="form_id_es2" style="width: 100%;" <?= !$isSuperAdmin ? 'disabled' : '' ?>>
                            <option value="">-- Pilih Unit Eselon 2 --</option>
                            <?php foreach ($es2_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= ($prefillEs2 == $opt['id']) ? 'selected' : '' ?>><?= $opt['nama_es2'] ?></option>
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
                        <input type="number" class="form-control <?= $validation->hasError('jumlah') ? 'is-invalid' : '' ?>" id="jumlah" name="jumlah" value="<?= old('jumlah', $item['jumlah'] ?? 1) ?>" min="1">
                        <div class="invalid-feedback"><?= $validation->getError('jumlah') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tk_perkembangan" class="form-label">Tingkat Perkembangan</label>
                        <select class="form-select <?= $validation->hasError('tk_perkembangan') ? 'is-invalid' : '' ?>" name="tk_perkembangan">
                            <?php $selectedTk = old('tk_perkembangan', $item['tk_perkembangan'] ?? 'asli'); ?>
                            <option value="asli" <?= $selectedTk == 'asli' ? 'selected' : '' ?>>Asli</option>
                            <option value="copy" <?= $selectedTk == 'copy' ? 'selected' : '' ?>>Copy</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('tk_perkembangan') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="media_simpan" class="form-label">Media Simpan</label>
                        <select class="form-select <?= $validation->hasError('media_simpan') ? 'is-invalid' : '' ?>" id="media_simpan" name="media_simpan">
                            <?php $selectedMedia = old('media_simpan', $item['media_simpan'] ?? 'kertas'); ?>
                            <option value="kertas">Kertas</option>
                            <option value="elektronik">Elektronik</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('media_simpan') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="no_box" class="form-label">Nomor Box</label>
                        <input type="text" class="form-control" id="no_box" name="no_box" value="<?= old('no_box', $item['no_box'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jumlah" class="form-label">Lokasi Simpan</label>
                        <input type="text" class="form-control" id="lokasi_simpan" name="lokasi_simpan" value="<?= old('lokasi_simpan', $item['lokasi_simpan'] ?? '') ?>">

                    </div>
                </div>

                <div id="elektronik-fields" class="row" style="display: none;">
                    <div class="col-md-4 mb-3">
                        <label for="nama_file" class="form-label">Nama File</label>
                        <input type="text" class="form-control" id="nama_file" name="nama_file" value="<?= old('nama_file', $item['nama_file'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="nama_folder" class="form-label">Nama Folder</label>
                        <input type="text" class="form-control" id="nama_folder" name="nama_folder" value="<?= old('nama_folder', $item['nama_folder'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="nama_link" class="form-label">Link</label>
                        <input type="text" class="form-control" id="nama_link" name="nama_link" value="<?= old('nama_link', $item['nama_link'] ?? '') ?>">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Item Arsip</button>
                    <a href="<?= site_url('item-aktif') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Memastikan Select2 JS dimuat -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {

        // --- 1. SETUP STANDAR FORM & UTILITY ---

        // 1.a. Inisialisasi Select2 standar (non-API)
        $('.select2-form').select2({
            theme: 'bootstrap-5'
        });

        // 1.b. Auto-fill Tahun Cipta
        $('#tgl_dokumen').on('change', function() {
            const tgl = $(this).val();
            $('#tahun_cipta').val(tgl ? new Date(tgl).getFullYear() : '');
        }).trigger('change');

        // 1.c. Toggle field elektronik
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

        // --- 2. LOGIKA API LOOKUP (BARU) ---

        function resetFormInputs(resetApiFields = true) {
            // Hanya mereset field yang diisi otomatis oleh API
            if (resetApiFields) {
                $('#no_dokumen').val('');
                $('#tgl_dokumen').val('');
                $('#judul_dokumen').val('');
            }
            // Reset Select2 pencarian
            $('#search_sima').val(null).trigger('change');
            $('#search_sadewa').val(null).trigger('change');
        }

        function destroySelect2(selector) {
            if ($(selector).data('select2')) {
                $(selector).select2('destroy');
                $(selector).html('<option></option>');
            }
        }

        function initializeSelect2(selector, url, placeholder) {
            destroySelect2(selector);

            $(selector).select2({
                theme: "bootstrap-5",
                placeholder: placeholder,
                minimumInputLength: 3,
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        const tahun = $('#filter_tahun').val();

                        if (!tahun || tahun.length !== 4) {
                            $('#tahun_warning').show();
                            return null; // Membatalkan request AJAX
                        }
                        $('#tahun_warning').hide();

                        return {
                            term: params.term,
                            page: params.page,
                            tahun_filter: tahun
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                }
            });
        }

        // Aksi ketika sumber data diubah
        $('#sumber_data').on('change', function() {
            const selectedSource = $(this).val();
            resetFormInputs(true); // Reset input form yang terisi API

            // Auto-set Dasar Pencatatan sesuai pilihan API
            if (selectedSource === 'api_sima') {
                $('#dasar_catat').val('SIMA').trigger('change');
            } else if (selectedSource === 'api_sadewa') {
                $('#dasar_catat').val('SADEWA').trigger('change');
            } else {
                // Anda bisa reset ke nilai default jika manual
                // $('#dasar_catat').val('').trigger('change'); 
            }

            // Atur visibilitas filter tahun dan section pencarian
            if (selectedSource !== 'manual') {
                $('#filter_tahun_section').slideDown();
                $('#search_api_section').slideDown();
            } else {
                $('#filter_tahun_section').slideUp();
                $('#search_api_section').slideUp();
                $('#tahun_warning').hide();
            }

            $('#sima_search_container').hide();
            $('#sadewa_search_container').hide();

            if (selectedSource === 'api_sima') {
                $('#sima_search_container').show();
            } else if (selectedSource === 'api_sadewa') {
                $('#sadewa_search_container').show();
            }

            // Trigger perubahan tahun untuk inisialisasi Select2
            $('#filter_tahun').trigger('change');
        });

        // Aksi ketika Tahun filter diubah
        $('#filter_tahun').on('change keyup', function() {
            const tahun = $(this).val();
            const selectedSource = $('#sumber_data').val();

            if (tahun && tahun.length === 4) {
                $('#tahun_warning').hide();

                if (selectedSource === 'api_sima') {
                    initializeSelect2('#search_sima', "<?= url_to('itemaktif.searchSimaApi') ?>", "Ketik kata kunci untuk mencari Laporan PKAU...");
                } else if (selectedSource === 'api_sadewa') {
                    initializeSelect2('#search_sadewa', "<?= url_to('itemaktif.searchSadewaApi') ?>", "Ketik kata kunci untuk mencari Surat Masuk...");
                }
            } else {
                destroySelect2('#search_sima');
                destroySelect2('#search_sadewa');
            }
        });

        // Handler Pemilihan Data SIMA (Laporan PKAU)
        $('#search_sima').on('select2:select', function(e) {
            const apiData = e.params.data.data_full;
            if (apiData) {
                $('#no_dokumen').val(apiData.nomor_laporan).trigger('change');
                $('#judul_dokumen').val(apiData.keterangan_penugasan);

                const rawDate = apiData.tanggal_laporan;
                let formattedDate = rawDate ? rawDate.substring(0, 10) : '';
                $('#tgl_dokumen').val(formattedDate).trigger('change'); // Trigger change untuk Tahun Cipta
            }
        });

        // Handler Pemilihan Data SADEWA (Surat Masuk)
        $('#search_sadewa').on('select2:select', function(e) {
            const apiData = e.params.data.data_full;
            if (apiData) {
                $('#no_dokumen').val(apiData.nomor_surat).trigger('change');
                $('#judul_dokumen').val(apiData.perihal);

                const rawDate = apiData.tgl_surat;
                let formattedDate = rawDate ? rawDate.substring(0, 10) : '';
                $('#tgl_dokumen').val(formattedDate).trigger('change'); // Trigger change untuk Tahun Cipta
            }
        });

        // --- 3. LOGIKA CHAINED DROPDOWN (LAMA) ---

        const es2Select = $('#form_id_es2');
        const es3Select = $('#id_es3');
        const hiddenEs2Input = $('#hidden_id_es2');

        function loadEs3(id_es2, selected_es3_id = null) {
            es3Select.prop('disabled', true).html('<option value="">Memuat...</option>').trigger('change');
            if (!id_es2) {
                es3Select.html('<option value="">-- Pilih Eselon 2 Dulu --</option>').trigger('change');
                return;
            }

            $.ajax({
                url: `<?= site_url('data/es3-by-es2/') ?>${id_es2}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    es3Select.html('<option value="">-- Pilih Unit Eselon 3 --</option>');
                    if (response && response.length > 0) {
                        es3Select.prop('disabled', false);
                        response.forEach(item => es3Select.append(new Option(item.nama_es3, item.id)));
                    } else {
                        es3Select.prop('disabled', true);
                        es3Select.html('<option value="">Unit Eselon 3 tidak tersedia</option>');
                    }
                    if (selected_es3_id) {
                        es3Select.val(selected_es3_id);
                    }
                    es3Select.trigger('change');
                },
                error: function() {
                    es3Select.html('<option value="">Gagal memuat data</option>').trigger('change');
                }
            });
        }

        es2Select.on('change', function() {
            const selectedEs2Id = $(this).val();
            hiddenEs2Input.val(selectedEs2Id);
            loadEs3(selectedEs2Id);
        });

        // Logika Pre-fill saat halaman dimuat
        const prefillEs2 = '<?= $prefillEs2 ?>';
        const prefillEs3 = '<?= old('id_es3', $item['id_es3'] ?? null) ?>';

        if (prefillEs2) {
            loadEs3(prefillEs2, prefillEs3);
        }

        // Trigger awal untuk API Lookup
        $('#sumber_data').trigger('change');
    });
</script>
<?= $this->endSection() ?>