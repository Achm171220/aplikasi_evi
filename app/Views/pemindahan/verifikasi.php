<?= $this->extend('layout_old/main') ?>

<?= $this->section('title') ?>Sistem Pemindahan Arsip BPKP - Verifikasi Tahap <?= esc($stage) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Specific column widths for verifikasi.php table */
    .table-verifikasi .col-checkbox {
        width: 4%;
    }

    /* New column */
    .table-verifikasi .col-unit-kerja {
        width: 10%;
        white-space: normal;
    }

    .table-verifikasi .col-no-dokumen {
        width: 10%;
    }

    .table-verifikasi .col-judul {
        width: 20%;
        white-space: normal;
    }

    /* Allow wrapping */
    .table-verifikasi .col-tahun {
        width: 7%;
    }

    .table-verifikasi .col-lokasi {
        width: 10%;
    }

    .table-verifikasi .col-verifikator-ditunjuk {
        width: 10%;
    }

    .table-verifikasi .col-status {
        width: 10%;
    }

    .table-verifikasi .col-aksi {
        width: 19%;
    }

    /* Increased width for new buttons */

    .note-view-btn {
        cursor: pointer;
        color: #0d6efd;
    }

    .note-view-btn:hover {
        color: #0a58ca;
    }

    /* Style untuk tombol aksi massal */
    .bulk-actions-container {
        display: flex;
        gap: 10px;
        /* Jarak antar tombol */
        margin-bottom: 1.5rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Verifikasi Arsip Aktif - Tahap <?= esc($stage) ?></h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Daftar item arsip yang menunggu verifikasi pada tahap ini. Gunakan filter untuk menyortir data atau pilih beberapa item untuk aksi massal.</p>

        <!-- Filter Unit Kerja -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="es2_filter" class="form-label">Filter Unit Kerja</label>
                <select class="form-select select2-single" id="es2_filter" name="es2_filter" data-placeholder="Pilih Unit Kerja">
                    <option value=""></option> <!-- Opsi kosong untuk "Semua Unit Kerja" -->
                    <?php foreach ($unitKerjaList as $unit): ?>
                        <option value="<?= esc($unit['id']) ?>" <?= ($selectedEs2Id == $unit['id']) ? 'selected' : '' ?>><?= esc($unit['nama_es2']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (empty($items)): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> Tidak ada arsip yang menunggu verifikasi pada tahap ini.
            </div>
        <?php else: ?>
            <!-- Tombol Aksi Massal -->
            <div class="bulk-actions-container">
                <button type="button" class="btn btn-success btn-sm" id="bulkApproveButton" disabled>
                    <i class="fas fa-check-double me-1"></i> Setujui Terpilih
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="bulkRejectButton" disabled>
                    <i class="fas fa-times me-1"></i> Tolak Terpilih
                </button>
            </div>

            <div class="table-container-scroll">
                <table class="table table-hover table-striped table-bordered align-middle table-verifikasi">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center col-checkbox">
                                <input type="checkbox" id="selectAllItems" class="form-check-input">
                            </th>
                            <th class="col-unit-kerja">Unit Kerja</th>
                            <th class="col-no-dokumen">No. Dokumen</th>
                            <th class="col-judul">Judul Dokumen</th>
                            <th class="col-tahun">Tahun Cipta</th>
                            <th class="col-lokasi">Lokasi Simpan</th>
                            <th class="col-verifikator-ditunjuk">Verifikator Ditunjuk</th>
                            <th class="col-status">Status Proses</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="text-center col-checkbox">
                                    <input type="checkbox" name="selected_items[]" value="<?= esc($item['id']) ?>" class="item-checkbox form-check-input">
                                </td>
                                <td class="col-unit-kerja wrap-text"><?= esc($item['kode_es2'] ?? '-') ?> - <?= esc($item['kode_es3'] ?? '-') ?></td>
                                <td class="col-no-dokumen"><?= esc($item['no_dokumen']) ?></td>
                                <td class="col-judul wrap-text"><?= esc($item['judul_dokumen']) ?></td>
                                <td class="col-tahun"><?= esc($item['tahun_cipta']) ?></td>
                                <td class="col-lokasi"><?= esc($item['lokasi_simpan']) ?></td>
                                <td class="col-verifikator-ditunjuk">
                                    <?php if ($stage == 1): ?>
                                        V1: <?= esc($item['verifikator1_name'] ?? '-') ?>
                                    <?php elseif ($stage == 2): ?>
                                        V2: <?= esc($item['verifikator2_name'] ?? '-') ?>
                                    <?php elseif ($stage == 3): ?>
                                        V3: <?= esc($item['verifikator3_name'] ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                                <td class="col-status">
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if (strpos($item['status_pindah'], 'menunggu') !== false) {
                                        $badgeClass = 'bg-info';
                                    } elseif (strpos($item['status_pindah'], 'disetujui') !== false) {
                                        $badgeClass = 'bg-success';
                                    } elseif (strpos($item['status_pindah'], 'ditolak') !== false) {
                                        $badgeClass = 'bg-danger';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= esc(str_replace('_', ' ', $item['status_pindah'])) ?></span>
                                    <?php if (!empty($item['admin_notes'])): ?>
                                        <br><a href="#" class="note-view-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Catatan" data-note="<?= esc($item['admin_notes']) ?>">
                                            <i class="fas fa-sticky-note small mt-1"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="col-aksi">
                                    <button type="button" class="btn btn-sm btn-success approve-btn" data-id="<?= esc($item['id']) ?>">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger reject-btn mt-1" data-id="<?= esc($item['id']) ?>">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Tombol Kembali di bawah tabel -->
            <div class="d-flex justify-content-start mt-3">
                <a class="btn btn-outline-danger" href="javascript:history.back()"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Select2 untuk filter Unit Kerja
        $('#es2_filter').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: $(this).data('placeholder'),
            allowClear: true
        });

        // Event listener untuk perubahan pada filter Unit Kerja
        $('#es2_filter').on('change', function() {
            const selectedValue = $(this).val();
            const currentUrl = new URL(window.location.href);

            if (selectedValue) {
                currentUrl.searchParams.set('es2_filter', selectedValue);
            } else {
                currentUrl.searchParams.delete('es2_filter');
            }
            window.location.href = currentUrl.toString();
        });


        // Inisialisasi Tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Event listener untuk tombol/ikon lihat catatan
        document.querySelectorAll('.note-view-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const noteContent = this.dataset.note;
                Swal.fire({
                    title: 'Catatan Terakhir',
                    html: '<div style="max-height: 300px; overflow-y: auto; text-align: left; padding: 10px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px;">' + noteContent + '</div>',
                    icon: 'info',
                    confirmButtonText: 'Tutup',
                    width: '500px'
                });
            });
        });

        const selectAllCheckbox = document.getElementById('selectAllItems');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const bulkApproveButton = document.getElementById('bulkApproveButton');
        const bulkRejectButton = document.getElementById('bulkRejectButton');
        const currentStage = <?= json_encode($stage) ?>;
        const csrfToken = '<?= csrf_hash() ?>';

        function toggleBulkActionButtons() {
            const anyChecked = itemCheckboxes.length > 0 && Array.from(itemCheckboxes).some(cb => cb.checked);
            bulkApproveButton.disabled = !anyChecked;
            bulkRejectButton.disabled = !anyChecked;
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleBulkActionButtons();
            });
        }

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', toggleBulkActionButtons);
        });

        // Aksi tunggal Setujui
        document.querySelectorAll('.approve-btn').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.dataset.id;
                Swal.fire({
                    title: 'Konfirmasi Persetujuan',
                    text: "Apakah Anda yakin ingin menyetujui item ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Setujui!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        sendVerificationRequest([itemId], 'approve', null); // Kirim array ID tunggal
                    }
                });
            });
        });

        // Aksi tunggal Tolak
        document.querySelectorAll('.reject-btn').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.dataset.id;
                Swal.fire({
                    title: 'Tolak Item',
                    input: 'textarea',
                    inputLabel: 'Alasan Penolakan:',
                    inputPlaceholder: 'Masukkan alasan penolakan...',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Alasan penolakan tidak boleh kosong!';
                        }
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Tolak',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: (notes) => {
                        return sendVerificationRequest([itemId], 'reject', notes, true); // Kirim array ID tunggal
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
            });
        });

        // Aksi massal Setujui Terpilih
        bulkApproveButton.addEventListener('click', function() {
            const selectedItems = Array.from(itemCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (selectedItems.length === 0) {
                Swal.fire('Peringatan!', 'Tidak ada item yang dipilih.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Persetujuan Massal',
                text: `Apakah Anda yakin ingin menyetujui ${selectedItems.length} item terpilih?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendVerificationRequest(selectedItems, 'approve', null);
                }
            });
        });

        // Aksi massal Tolak Terpilih
        bulkRejectButton.addEventListener('click', function() {
            const selectedItems = Array.from(itemCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (selectedItems.length === 0) {
                Swal.fire('Peringatan!', 'Tidak ada item yang dipilih.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Tolak Item Massal',
                input: 'textarea',
                inputLabel: `Alasan Penolakan untuk ${selectedItems.length} item terpilih:`,
                inputPlaceholder: 'Masukkan alasan penolakan...',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan tidak boleh kosong!';
                    }
                },
                showCancelButton: true,
                confirmButtonText: 'Tolak',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: (notes) => {
                    return sendVerificationRequest(selectedItems, 'reject', notes, true);
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
        });


        function sendVerificationRequest(itemIds, action, notes, isReject = false) {
            const formData = new FormData();
            // Penting: Kirim itemIds sebagai array
            itemIds.forEach(id => formData.append('item_id[]', id)); // Nama field harus item_id[]
            formData.append('action', action);
            formData.append('notes', notes);
            formData.append('<?= csrf_token() ?>', csrfToken);

            fetch(`<?= base_url('pemindahan/verifikasi/process/') ?>${currentStage}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json();
                    } else {
                        window.location.reload(); // Fallback jika respons bukan JSON
                        return Promise.reject('Non-JSON response, likely a redirect.');
                    }
                })
                .then(data => {
                    // Tangani respons JSON dari controller
                    if (data.status === 'success') {
                        Swal.fire('Berhasil!', data.message, 'success')
                            .then(() => window.location.reload());
                    } else if (data.status === 'warning') {
                        Swal.fire('Peringatan!', data.message, 'warning')
                            .then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal!', data.message, 'error')
                            .then(() => window.location.reload());
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (error.message !== 'Non-JSON response, likely a redirect.') {
                        Swal.fire('Error!', 'Terjadi kesalahan saat memproses permintaan.', 'error');
                    }
                    // Pastikan halaman di-reload bahkan jika fetch gagal total, untuk refresh status
                    window.location.reload();
                });
        }

        // Panggil saat DOMContentLoaded untuk inisialisasi status tombol
        toggleBulkActionButtons();
    });
</script>
<?= $this->endSection() ?>