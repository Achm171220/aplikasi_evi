<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= isset($user) ? site_url('users/update/' . $user['id']) : site_url('users') ?>" method="post" autocomplete="off">

                <?php if (isset($user)): ?>
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <h5 class="mb-3 border-bottom pb-2">Integrasi Data Pegawai</h5>
                <div class="mb-3">
                    <label for="search_nip" class="form-label">Cari Pegawai (berdasarkan NIP/Nama)</label>
                    <select class="form-select select2-form" id="search_nip" name="search_nip" style="width: 100%;">
                        <?php if (isset($user['nip']) && isset($user['name'])): ?>
                            <option value="<?= $user['nip'] ?>" selected><?= $user['name'] . ' - ' . ($user['nama_jabatan_api'] ?? 'Jabatan Tidak Ditemukan') ?></option>
                        <?php else: ?>
                            <option value="">-- Cari Pegawai --</option>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">Pilih pegawai dari API untuk mengisi form secara otomatis.</small>
                </div>

                <!-- Hidden Fields untuk Data API -->
                <?php
                // Tentukan apakah NIP sudah ada (dari old() atau data $user)
                $isNipFilled = !empty(old('nip', $user['nip'] ?? ''));
                // Logika readonly/disabled
                $readonlyAttribute = $isNipFilled ? 'readonly' : '';
                ?>
                <input type="hidden" name="nip" id="nip" value="<?= old('nip', $user['nip'] ?? '') ?>">
                <input type="hidden" name="nama_jabatan_api" id="nama_jabatan_api" value="<?= old('nama_jabatan_api', $user['nama_jabatan_api'] ?? '') ?>">
                <input type="hidden" name="namaunit" id="namaunit" value="<?= old('namaunit', $user['namaunit'] ?? '') ?>">

                <hr>

                <!-- TAMPILAN INFORMASI UNIT KERJA (jika sudah terisi) -->
                <?php if ($isNipFilled): ?>
                    <div class="mb-3" id="unit_display_box">
                        <label for="display_namaunit" class="form-label">Unit Kerja</label>
                        <!-- Tampilkan sebagai input read-only untuk informasi visual -->
                        <input type="text" class="form-control" id="display_namaunit" value="<?= old('namaunit', $user['namaunit'] ?? 'Unit Tidak Diketahui') ?>" readonly>
                    </div>
                    <hr id="unit_hr">
                <?php endif; ?>

                <h5 class="mb-3 border-bottom pb-2">Informasi Akun</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <!-- Tambahkan logic readonly -->
                        <input type="text" class="form-control <?= $validation->hasError('name') ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name', $user['name'] ?? '') ?>" autofocus <?= $readonlyAttribute ?>>
                        <div class="invalid-feedback"><?= $validation->getError('name') ?></div>
                        <?php if ($isNipFilled): ?><small class="form-text text-muted">Field terisi otomatis dan dikunci (Read-only).</small><?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <!-- Tambahkan logic readonly -->
                        <input type="email" class="form-control <?= $validation->hasError('email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>" <?= $readonlyAttribute ?>>
                        <div class="invalid-feedback"><?= $validation->getError('email') ?></div>
                        <?php if ($isNipFilled): ?><small class="form-text text-muted">Field terisi otomatis dan dikunci (Read-only).</small><?php endif; ?>
                    </div>
                </div>


                <hr>
                <h5 class="mb-3 border-bottom pb-2">Pengaturan Peran & Status</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="role_access" class="form-label">Role Access</label>
                        <select class="form-select <?= $validation->hasError('role_access') ? 'is-invalid' : '' ?>" name="role_access" <?= (session()->get('role_access') !== 'superadmin') ? 'disabled' : '' ?>>
                            <?php $selectedRole = old('role_access', $user['role_access'] ?? 'user'); ?>
                            <?php if (session()->get('role_access') === 'superadmin'): ?>
                                <option value="user" <?= $selectedRole == 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $selectedRole == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="superadmin" <?= $selectedRole == 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                            <?php else: ?>
                                <option value="user" selected>User</option>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('role_access') ?></div>
                    </div>

                    <!-- Field Role Jabatan, HANYA untuk Superadmin atau Admin -->
                    <?php if (session()->get('role_access') === 'superadmin' || session()->get('role_access') === 'admin'): ?>
                        <div class="col-md-4 mb-3">
                            <label for="role_jabatan" class="form-label">Role Jabatan</label>
                            <select class="form-select select2-form <?= $validation->hasError('role_jabatan') ? 'is-invalid' : '' ?>" name="role_jabatan" id="role_jabatan" style="width: 100%;">
                                <?php
                                // Jabatan dari API akan di-lowercase dan disesuaikan
                                $jabatanOptions = ['sekretaris', 'pengelola_arsip', 'arsiparis', 'pengampu', 'verifikator', 'pimpinan'];
                                $selectedJabatan = old('role_jabatan', $user['role_jabatan'] ?? '');
                                ?>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php foreach ($jabatanOptions as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $selectedJabatan == $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback d-block"><?= $validation->getError('role_jabatan') ?></div>
                            <!-- Hidden field untuk menyimpan nama jabatan lengkap dari API sudah dipindah ke atas -->
                            <!-- <input type="hidden" name="nama_jabatan_api" id="nama_jabatan_api" value="<?= old('nama_jabatan_api', $user['nama_jabatan_api'] ?? '') ?>"> -->
                        </div>
                    <?php endif; ?>

                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select <?= $validation->hasError('status') ? 'is-invalid' : '' ?>" name="status">
                            <?php $selectedStatus = old('status', $user['status'] ?? 'aktif'); ?>
                            <option value="aktif" <?= $selectedStatus == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="non-aktif" <?= $selectedStatus == 'non-aktif' ? 'selected' : '' ?>>Non-Aktif</option>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('status') ?></div>
                    </div>
                </div>

                <!-- Section Hak Akses, HANYA untuk Admin -->
                <?php if (session()->get('role_access') === 'admin' && isset($es3_options)): ?>
                    <hr>
                    <h5 class="mb-3 border-bottom pb-2">Hak Akses Unit Kerja</h5>
                    <div class="mb-3">
                        <label for="id_es3" class="form-label">Tugaskan ke Unit Eselon 3</label>
                        <select class="form-select select2-form <?= $validation->hasError('id_es3') ? 'is-invalid' : '' ?>" id="id_es3" name="id_es3" style="width: 100%;">
                            <option value="">-- Pilih Unit Eselon 3 --</option>
                            <?php foreach ($es3_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= (old('id_es3', $hak_fitur['id_es3'] ?? '') == $opt['id']) ? 'selected' : '' ?>><?= $opt['nama_es3'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback d-block"><?= $validation->getError('id_es3') ?></div>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= site_url('users') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 pada semua dropdown yang memiliki kelas .select2-form
        $('.select2-form').select2({
            theme: 'bootstrap-5'
        });

        // Inisialisasi Select2 untuk pencarian pegawai dari API
        $('#search_nip').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari NIP atau Nama Pegawai...',
            allowClear: true,
            ajax: {
                url: '<?= site_url('users/getPegawaiFromApiForSelect2') ?>',
                dataType: 'json',
                delay: 250, // wait for 250 milliseconds before triggering the request
                data: function(params) {
                    return {
                        term: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 3 // Minimum karakter untuk mulai mencari
        });

        // Event listener saat memilih item dari Select2 pegawai
        $('#search_nip').on('select2:select', function(e) {
            var data = e.params.data;

            // Setel nilai ke input form hidden
            $('#nip').val(data.id); // NIP
            $('#nama_jabatan_api').val(data.jabatan_text); // Jabatan lengkap dari API
            $('#namaunit').val(data.namaunit); // **TAMBAH INI: Nama Unit**

            // Setel nilai ke input form terlihat
            $('#name').val(data.text.split(' - ')[0]); // Hanya nama lengkap
            $('#email').val(data.email);

            // **TAMBAH INI: Tampilkan Nama Unit dan buat Name & Email Readonly**
            // Hapus tampilan unit yang mungkin ada sebelumnya
            $('#unit_display_box').remove();
            $('#unit_hr').remove();

            // Tampilkan nama unit di form
            var displayUnit = `<div class="mb-3" id="unit_display_box"><label for="display_namaunit" class="form-label">Unit Kerja</label><input type="text" class="form-control" id="display_namaunit" value="${data.namaunit}" readonly></div><hr id="unit_hr">`;
            // Masukkan elemen baru setelah hidden field NIP
            $('#nip').after(displayUnit);

            // Buat Name dan Email menjadi Readonly
            $('#name').prop('readonly', true).parent().find('small').remove().end().append('<small class="form-text text-muted">Field terisi otomatis dan dikunci (Read-only).</small>');
            $('#email').prop('readonly', true).parent().find('small').remove().end().append('<small class="form-text text-muted">Field terisi otomatis dan dikunci (Read-only).</small>');

            // Sesuaikan role_jabatan
            var apiJabatan = data.role_jabatan; // Ambil nilai role_jabatan dari API
            var found = false;
            $('#role_jabatan option').each(function() {
                // Konversi nilai option ke lowercase untuk perbandingan yang konsisten
                if ($(this).val().toLowerCase() === apiJabatan.toLowerCase()) {
                    $(this).prop('selected', true);
                    found = true;
                    return false; // Berhenti iterasi
                }
            });
            // Jika tidak ditemukan di option yang tersedia, bisa di setel ke default atau dibiarkan kosong
            if (!found) {
                $('#role_jabatan').val('').trigger('change'); // Pilih opsi kosong jika tidak match
            }
            $('#role_jabatan').trigger('change'); // Perbarui tampilan Select2

            // Password dikosongkan untuk diisi manual atau dibiarkan default
        });

        // Event listener saat menghapus pilihan dari Select2 pegawai (clear)
        $('#search_nip').on('select2:unselect', function(e) {
            // Kosongkan field terkait
            $('#nip').val('');
            $('#name').val('');
            $('#email').val('');
            $('#role_jabatan').val('').trigger('change');
            $('#nama_jabatan_api').val('');
            $('#namaunit').val(''); // **TAMBAH INI**

            // Hapus status Readonly
            $('#name').prop('readonly', false).parent().find('small').remove(); // Hapus pesan small
            $('#email').prop('readonly', false).parent().find('small').remove(); // Hapus pesan small

            // Hapus tampilan nama unit
            $('#unit_display_box').remove();
            $('#unit_hr').remove();
        });
    });
</script>
<?= $this->endSection() ?>