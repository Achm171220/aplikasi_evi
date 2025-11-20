<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <button type="button" class="btn btn-primary" id="btn-tambah-tema"><i class="fas fa-plus me-2"></i> Tambah Tema</button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-data" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 30%;">Nama Tema</th>
                            <th>Deskripsi</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Container (Akan diisi oleh AJAX) -->
<div id="modal-container"></div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        const dataTable = $('#table-data').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('tema/list') ?>",
                type: "POST"
            },
            columnDefs: [{
                    "targets": 0,
                    "orderable": false,
                    "searchable": false,
                    "render": (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                },
                {
                    "orderable": false,
                    "targets": [3]
                },
                {
                    "className": "text-center",
                    "targets": [0, 3]
                }
            ],
        });

        function loadModalForm(url) {
            $.get(url, (response) => {
                if (response.success) {
                    $('#modal-container').html(response.html);
                    new bootstrap.Modal(document.getElementById('tema-modal')).show();
                }
            }, 'json').fail(() => Swal.fire('Error!', 'Gagal memuat form.', 'error'));
        }

        $('#btn-tambah-tema').on('click', () => loadModalForm("<?= site_url('tema/new') ?>"));
        $('body').on('click', '.btn-edit', (e) => loadModalForm(`<?= site_url('tema/edit/') ?>${$(e.currentTarget).data('id')}`));

        $('body').on('submit', '#tema-form', function(e) {
            e.preventDefault();
            const form = $(this);

            // --- PERBAIKAN UTAMA: Error handling yang lebih baik ---
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#tema-modal').modal('hide');
                    dataTable.ajax.reload(null, false);
                    Swal.fire('Berhasil!', response.message, 'success');
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    // Hapus pesan error lama
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');

                    if (xhr.status === 400 && response.errors) {
                        // Jika ini error validasi, tampilkan pesan di bawah setiap input
                        $.each(response.errors, function(key, value) {
                            $(`[name="${key}"]`).addClass('is-invalid').next('.invalid-feedback').text(value);
                        });
                    } else {
                        // Jika error lain (500, 404, dll)
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan pada server.', 'error');
                    }
                }
            });
        });

        $('body').on('submit', '.form-delete', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Konfirmasi',
                text: "Anda yakin ingin menghapus tema ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });

        $('body').on('hidden.bs.modal', '#tema-modal', function() {
            $(this).remove();
        });
    });
</script>
<?= $this->endSection() ?>