<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<!-- Memastikan link CSS yang diperlukan -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
    /* Agar Select2 tampil penuh di dalam Bootstrap */
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    /* Style untuk Modal DataTables */
    .modal-dialog {
        max-width: 90%;
    }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">

            <?php // Tampilkan pesan sukses/error/validasi
            $is_edit = isset($item);

            if (isset($validation) && $validation->getErrors()): ?>
                <div class="alert alert-danger">Validasi gagal. Mohon periksa input Anda.</div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <form action="<?= $is_edit ? site_url('item-aktif/update/' . $item['id']) : site_url('item-aktif') ?>" method="post">
                <?= csrf_field() ?>
                <?php if ($is_edit): ?>
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <!-- ======================================================= -->
                <!-- SUMBER DATA ARSIP (API/MANUAL) -->
                <!-- ======================================================= -->
                <?php if (!$is_edit): ?>
                    <h5 class="mb-3 border-bottom pb-2">Sumber Data Arsip</h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sumber_data" class="form-label fw-bold">Sumber Data</label>
                            <select id="sumber_data" class="form-select">
                                <option value="manual">Isi Manual</option>
                                <option value="api_sima">SIMA BPKP (Laporan PKAU)</option>
                                <option value="api_sadewa">SADEWA BPKP (Surat Masuk)</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3" id="filter_tahun_section">
                            <label for="filter_tahun" class="form-label fw-bold">Tahun Dokumen (Filter API)</label>
                            <select class="form-select" id="filter_tahun" disabled>
                                <option value="">-- Pilih Tahun --</option>
                                <?php
                                $target_years = range(2020, 2025);
                                rsort($target_years);
                                foreach ($target_years as $year): ?>
                                    <option value="<?= $year ?>"><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-danger" id="tahun_warning" style="display: none;">
                                Harap pilih tahun.
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Cari Data</label>
                            <!-- Tombol yang memicu modal -->
                            <button type="button" id="btn_search_api" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#apiSearchModal" disabled>
                                Buka Pencarian API
                            </button>
                        </div>
                    </div>
                    <!-- 
                    <div class="alert alert-info mt-2" id="search_api_section">
                        Silahkan cari data dari sumber yang dipilih.
                    </div> -->
                    <hr>
                <?php endif; ?>

                <!-- ======================================================= -->
                <!-- INFORMASI FISIK & DIGITAL -->
                <!-- ======================================================= -->
                <h5 class="mb-3 border-bottom pb-2">Informasi Fisik & Digital</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="media_simpan" class="form-label fw-bold">Media Simpan</label>
                        <select class="form-select <?= $validation->hasError('media_simpan') ? 'is-invalid' : '' ?>" id="media_simpan" name="media_simpan">
                            <?php $selectedMedia = old('media_simpan', $item['media_simpan'] ?? 'elektronik'); ?>
                            <option value="kertas" <?= $selectedMedia == 'kertas' ? 'selected' : '' ?>>Kertas (Fisik)</option>
                            <option value="elektronik" <?= $selectedMedia == 'elektronik' ? 'selected' : '' ?>>Elektronik (Digital)</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('media_simpan') ?></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control <?= $validation->hasError('jumlah') ? 'is-invalid' : '' ?>" id="jumlah" name="jumlah" value="<?= old('jumlah', $item['jumlah'] ?? 1) ?>" min="1">
                        <div class="invalid-feedback"><?= $validation->getError('jumlah') ?></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="tk_perkembangan" class="form-label">Tingkat Perkembangan</label>
                        <select class="form-select <?= $validation->hasError('tk_perkembangan') ? 'is-invalid' : '' ?>" name="tk_perkembangan">
                            <?php $selectedTk = old('tk_perkembangan', $item['tk_perkembangan'] ?? 'asli'); ?>
                            <option value="asli" <?= $selectedTk == 'asli' ? 'selected' : '' ?>>Asli</option>
                            <option value="copy" <?= $selectedTk == 'copy' ? 'selected' : '' ?>>Copy</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('tk_perkembangan') ?></div>
                    </div>
                </div>

                <!-- Fields Khusus Media -->
                <div id="media-fields-container">
                    <!-- Kertas Fields (Nomor Box & Lokasi Simpan) -->
                    <div id="kertas-fields" class="row">
                        <div class="col-md-6 mb-3">
                            <label for="no_box" class="form-label">Nomor Box</label>
                            <input type="text" class="form-control" id="no_box" name="no_box" value="<?= old('no_box', $item['no_box'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lokasi_simpan" class="form-label">Lokasi Simpan</label>
                            <input type="text" class="form-control" id="lokasi_simpan" name="lokasi_simpan" value="<?= old('lokasi_simpan', $item['lokasi_simpan'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Elektronik Fields (Link) -->
                    <div id="elektronik-fields" class="row" style="display: none;">
                        <div class="col-md-12 mb-3">
                            <label for="nama_link" class="form-label fw-bold">Link File Digital</label>
                            <input type="text" class="form-control" id="nama_link" name="nama_link" value="<?= old('nama_link', $item['nama_link'] ?? '') ?>">
                            <small class="form-text text-muted">Akan terisi otomatis dari parameter "file_download" (SIMA) jika tersedia.</small>
                        </div>
                        <input type="hidden" name="nama_file" value="<?= old('nama_file', $item['nama_file'] ?? 'auto') ?>">
                        <input type="hidden" name="nama_folder" value="<?= old('nama_folder', $item['nama_folder'] ?? 'auto') ?>">
                    </div>
                </div>

                <hr>

                <!-- ======================================================= -->
                <!-- INFORMASI DASAR DOKUMEN -->
                <!-- ======================================================= -->

                <h5 class="mb-3 border-bottom pb-2">Informasi Dasar Dokumen</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="no_dokumen" class="form-label">No. Dokumen</label>
                        <input type="text" class="form-control <?= $validation->hasError('no_dokumen') ? 'is-invalid' : '' ?>" id="no_dokumen" name="no_dokumen" value="<?= old('no_dokumen', $item['no_dokumen'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('no_dokumen') ?></div>
                        <?php if (!$is_edit): ?>
                            <small class="form-text text-muted">Diisi otomatis dari API jika dipilih.</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tgl_dokumen" class="form-label">Tanggal Dokumen</label>
                        <input type="date" class="form-control <?= $validation->hasError('tgl_dokumen') ? 'is-invalid' : '' ?>" id="tgl_dokumen" name="tgl_dokumen" value="<?= old('tgl_dokumen', $item['tgl_dokumen'] ?? '') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('tgl_dokumen') ?></div>
                        <?php if (!$is_edit): ?>
                            <small class="form-text text-muted">Diisi otomatis dari API jika dipilih.</small>
                        <?php endif; ?>
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
                        <?php if (!$is_edit): ?>
                            <small class="form-text text-muted">Diisi otomatis dari API jika dipilih.</small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="dasar_catat" class="form-label">Dasar Pencatatan</label>
                        <select class="form-select select2-form <?= $validation->hasError('dasar_catat') ? 'is-invalid' : '' ?>" id="dasar_catat" name="dasar_catat" style="width: 100%;">
                            <?php
                            $options = ['Srikandi', 'SIMA', 'MAP', 'BISMA', 'SADEWA', 'POS', 'GWS', 'Lainnya'];
                            $selectedValue = old('dasar_catat', $item['dasar_catat'] ?? '');
                            ?>
                            <option value="">-- Pilih Dasar Pencatatan --</option>
                            <?php foreach ($options as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($selectedValue == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('dasar_catat') ?></div>
                        <small class="form-text text-muted">Terisi otomatis dari Sumber Data.</small>
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
                    $prefillEs2 = old('id_es2_for_form', $id_es2_prefill ?? ($item['id_es2'] ?? ''));
                    $prefillEs3 = old('id_es3', $item['id_es3'] ?? '');
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
                            <option value="<?= $prefillEs3 ?>" selected><?= $is_edit && $prefillEs3 ? 'Memuat data lama...' : '-- Pilih Eselon 2 Dulu --' ?></option>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_es3') ?></div>
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

<!-- Memanggil Modal yang sudah dipisahkan -->
<?= $this->include('item_aktif/modal') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        const isEdit = <?= $is_edit ? 'true' : 'false' ?>;

        // --- 1. SETUP STANDAR FORM & UTILITY ---

        $('.select2-form').select2({
            theme: 'bootstrap-5'
        });

        // Auto-fill Tahun Cipta
        $('#tgl_dokumen').on('change', function() {
            const tgl = $(this).val();
            $('#tahun_cipta').val(tgl ? new Date(tgl).getFullYear() : '');
        }).trigger('change');

        // Toggle field media (Kertas vs Elektronik)
        const mediaSimpanSelect = $('#media_simpan');
        const kertasFields = $('#kertas-fields');
        const elektronikFields = $('#elektronik-fields');
        const namaLinkInput = $('#nama_link');

        function toggleMediaFields() {
            const selectedMedia = mediaSimpanSelect.val();

            if (selectedMedia === 'elektronik') {
                kertasFields.slideUp();
                elektronikFields.slideDown();
                // Reset field kertas saat beralih ke elektronik
                $('#no_box').val('');
                $('#lokasi_simpan').val('');
            } else {
                elektronikFields.slideUp();
                kertasFields.slideDown();
                // Reset field elektronik saat beralih ke kertas
                namaLinkInput.val('');
            }
        }
        toggleMediaFields();
        mediaSimpanSelect.on('change', toggleMediaFields);

        // --- 2. LOGIKA API LOOKUP (HANYA UNTUK CREATE) ---

        if (!isEdit) {

            // --- Variabel Global API Lookup ---
            let apiDataTable;
            let currentApiUrl = '';

            function resetFormInputs(resetApiFields = true) {
                if (resetApiFields) {
                    $('#no_dokumen').val('');
                    $('#tgl_dokumen').val('').trigger('change');
                    $('#judul_dokumen').val('');
                    namaLinkInput.val('');
                }
            }

            // Aksi ketika sumber data diubah
            $('#sumber_data').on('change', function() {
                const selectedSource = $(this).val();
                resetFormInputs(true);

                // 1. Autofill Dasar Pencatatan
                if (selectedSource === 'api_sima') {
                    $('#dasar_catat').val('SIMA').trigger('change');
                    $('#filter_tahun').prop('disabled', false);
                    $('#btn_search_api').prop('disabled', true);
                } else if (selectedSource === 'api_sadewa') {
                    $('#dasar_catat').val('SADEWA').trigger('change');
                    $('#filter_tahun').prop('disabled', false);
                    $('#btn_search_api').prop('disabled', true);
                } else {
                    $('#dasar_catat').val('').trigger('change');
                    $('#filter_tahun').prop('disabled', true).val('');
                    $('#btn_search_api').prop('disabled', true);
                }

                // 2. Atur visibilitas
                if (selectedSource !== 'manual') {
                    $('#filter_tahun_section').show();
                    $('#search_api_section').slideDown();
                } else {
                    $('#filter_tahun_section').hide();
                    $('#search_api_section').slideUp();
                }

                // Panggil event change pada filter tahun untuk mengatur status tombol search
                $('#filter_tahun').trigger('change');
            });

            // Aksi ketika Tahun filter diubah
            $('#filter_tahun').on('change', function() {
                const tahun = $(this).val();
                const selectedSource = $('#sumber_data').val();

                if (tahun) {
                    $('#tahun_warning').hide();
                    $('#btn_search_api').prop('disabled', false);

                    if (selectedSource === 'api_sima') {
                        currentApiUrl = '<?= site_url('item-aktif/load-sima-data') ?>';
                    } else if (selectedSource === 'api_sadewa') {
                        currentApiUrl = '<?= site_url('item-aktif/load-sadewa-data') ?>';
                    }
                } else {
                    $('#tahun_warning').show();
                    $('#btn_search_api').prop('disabled', true);
                    currentApiUrl = '';
                }
            });

            // LOGIKA DATATABLES MODAL

            $('#apiSearchModal').on('show.bs.modal', function(e) {
                const selectedSource = $('#sumber_data').val();
                const tahun = $('#filter_tahun').val();

                if (!tahun || !currentApiUrl) {
                    alert('Harap pilih Sumber Data dan Tahun Filter yang valid.');
                    return e.preventDefault();
                }

                // Update judul modal
                $('#modal_api_source').text(selectedSource === 'api_sima' ? 'SIMA BPKP (PKAU)' : 'SADEWA BPKP (Surat Masuk)');
                $('#modal_api_tahun').text(tahun);

                // Konfigurasi kolom DataTables
                let headers;
                let columns;

                if (selectedSource === 'api_sima') {
                    headers = ['Tahun', 'No. Laporan', 'Keterangan Penugasan', 'Tgl. Laporan', 'Aksi'];
                    columns = [{
                            data: 'thang',
                            name: 'thang'
                        },
                        {
                            data: 'nomor_laporan',
                            name: 'nomor_laporan'
                        },
                        {
                            data: 'keterangan_penugasan',
                            name: 'keterangan_penugasan'
                        },
                        {
                            data: 'tanggal_laporan',
                            name: 'tanggal_laporan'
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false
                        }
                    ];
                } else { // SADEWA
                    headers = ['Tahun Terima', 'No. Surat', 'Perihal', 'Tgl. Surat', 'Aksi'];
                    columns = [{
                            data: 'tahun_terima_surat',
                            name: 'tahun_terima_surat'
                        },
                        {
                            data: 'nomor_surat',
                            name: 'nomor_surat'
                        },
                        {
                            data: 'perihal',
                            name: 'perihal'
                        },
                        {
                            data: 'tgl_surat',
                            name: 'tgl_surat'
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false
                        }
                    ];
                }

                // Masukkan header ke tabel
                $('#apiTableHeader').empty().html(headers.map(h => `<th>${h}</th>`).join(''));

                // Hancurkan DataTables lama (jika ada)
                if ($.fn.DataTable.isDataTable('#apiDataTable')) {
                    $('#apiDataTable').DataTable().destroy();
                }

                // Inisialisasi DataTables baru
                apiDataTable = $('#apiDataTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: currentApiUrl,
                        type: "POST",
                        data: function(d) {
                            d.tahun_filter = tahun; // Kirim filter tahun ke Controller
                        }
                    },
                    columns: columns,
                    columnDefs: [{
                            targets: [3], // kolom Tanggal
                            className: 'text-nowrap'
                        },
                        {
                            targets: '_all',
                            // Custom rendering untuk memastikan tombol aksi berfungsi
                            createdCell: function(td, cellData, rowData, row, col) {
                                if (col === columns.length - 1) { // Kolom Aksi
                                    // Pastikan data item di encode untuk mencegah error JSON
                                    const rawItem = JSON.stringify(rowData.raw_data);

                                    // Rebuild tombol agar data-item selalu diperbarui
                                    let btnClass = selectedSource === 'api_sima' ? 'btn-select-sima' : 'btn-select-sadewa';
                                    $(td).html(`<button type="button" class="btn btn-sm btn-success ${btnClass}" data-item='${encodeURIComponent(rawItem)}'>Pilih</button>`);
                                }
                            }
                        }
                    ],
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                });
            });

            // EVENT KLIK TOMBOL PILIH (SIMA)
            $(document).on('click', '.btn-select-sima', function() {
                // Decode data yang di-encodeURIComponent dan kemudian di-JSON.parse
                const rawItem = JSON.parse(decodeURIComponent($(this).data('item')));

                // Mapping Data SIMA
                $('#no_dokumen').val(rawItem.nomor_laporan);
                $('#judul_dokumen').val(rawItem.keterangan_penugasan);

                const rawDate = rawItem.tanggal_laporan;
                let formattedDate = rawDate ? rawDate.substring(0, 10) : '';
                $('#tgl_dokumen').val(formattedDate).trigger('change');

                // Link File Digital (file_download)
                if ($('#media_simpan').val() === 'elektronik') {
                    const fileLink = rawItem.file_download || '';
                    $('#nama_link').val(fileLink);
                }

                $('#apiSearchModal').modal('hide');
            });

            // EVENT KLIK TOMBOL PILIH (SADEWA)
            $(document).on('click', '.btn-select-sadewa', function() {
                const rawItem = JSON.parse(decodeURIComponent($(this).data('item')));

                // Mapping Data SADEWA
                $('#no_dokumen').val(rawItem.nomor_surat);
                $('#judul_dokumen').val(rawItem.perihal);

                const rawDate = rawItem.tgl_surat;
                let formattedDate = rawDate ? rawDate.substring(0, 10) : '';
                $('#tgl_dokumen').val(formattedDate).trigger('change');

                // Link File Digital (SADEWA tidak menyediakan file_download)
                if ($('#media_simpan').val() === 'elektronik') {
                    $('#nama_link').val('');
                }

                $('#apiSearchModal').modal('hide');
            });


            $('#sumber_data').trigger('change');
        } // End if (!isEdit)

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
        const prefillEs3 = '<?= $prefillEs3 ?>';

        if (prefillEs2) {
            loadEs3(prefillEs2, prefillEs3);
        }
    });
</script>
<?= $this->endSection() ?>