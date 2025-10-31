<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <button type="button" class="btn btn-primary" id="btn-tambah">
            <i class="fas fa-plus"></i> Tambah Eselon 1
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <table id="table-data" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th>Kode</th>
                        <th>Nama Unit Eselon 1</th>
                        <th style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="data-modal" tabindex="-1" aria-labelledby="data-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="data-modal-label">Form Eselon 1</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="data-form">
                    <input type="hidden" name="id" id="id">
                    <div class="mb-3">
                        <label for="kode" class="form-label">Kode Eselon 1</label>
                        <input type="text" class="form-control" id="kode" name="kode">
                        <div class="invalid-feedback" data-field="kode"></div>
                    </div>
                    <div class="mb-3">
                        <label for="nama_es1" class="form-label">Nama Eselon 1</label>
                        <input type="text" class="form-control" id="nama_es1" name="nama_es1">
                        <div class="invalid-feedback" data-field="nama_es1"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-save">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Kode Javascript ini hampir identik dengan CRUD sebelumnya
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const table = $('#table-data').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('unit-kerja-es1/list') ?>",
                type: "POST"
            },
            "fnCreatedRow": function(nRow, aData, iDataIndex) {
                $(nRow).find('td:eq(0)').html(iDataIndex + 1);
            },
            columnDefs: [{
                "orderable": false,
                "targets": [0, 3]
            }]
        });

        const dataModal = new bootstrap.Modal(document.getElementById('data-modal'));

        function clearForm() {
            $('#data-form')[0].reset();
            $('#id').val('');
            $('.form-control').removeClass('is-invalid');
        }

        $('#btn-tambah').on('click', function() {
            clearForm();
            $('#data-modal-label').text('Tambah Eselon 1');
            dataModal.show();
        });

        $('#table-data').on('click', '.btn-edit', function() {
            clearForm();
            const id = $(this).data('id');
            $.get(`<?= site_url('unit-kerja-es1/show/') ?>${id}`, function(response) {
                $('#data-modal-label').text('Edit Eselon 1');
                $('#id').val(response.id);
                $('#kode').val(response.kode);
                $('#nama_es1').val(response.nama_es1);
                dataModal.show();
            }, 'json');
        });

        $('#btn-save').on('click', function() {
            $('.form-control').removeClass('is-invalid');
            $.ajax({
                url: "<?= site_url('unit-kerja-es1/save') ?>",
                type: "POST",
                data: new FormData($('#data-form')[0]),
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(response) {
                    dataModal.hide();
                    Swal.fire('Sukses!', response.message, 'success');
                    table.ajax.reload();
                },
                error: function(jqXHR) {
                    if (jqXHR.status === 400) {
                        const errors = jqXHR.responseJSON.errors;
                        for (const field in errors) {
                            $(`[name=${field}]`).addClass('is-invalid');
                            $(`.invalid-feedback[data-field=${field}]`).text(errors[field]);
                        }
                    } else {
                        Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
                    }
                }
            });
        });

        $('#table-data').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Anda yakin ingin menghapus <b>${name}</b>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`<?= site_url('unit-kerja-es1/delete/') ?>${id}`, function(response) {
                        Swal.fire('Dihapus!', response.message, 'success');
                        table.ajax.reload();
                    }, 'json').fail(function(jqXHR) {
                        const msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Gagal menghapus data.';
                        Swal.fire('Error!', msg, 'error');
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>