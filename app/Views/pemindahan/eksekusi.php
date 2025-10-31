<?= $this->extend('layout_old/main') ?>

<?= $this->section('title') ?>Sistem Pemindahan Arsip BPKP - Eksekusi Pemindahan<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Specific column widths for eksekusi.php table */
    .table-eksekusi .col-checkbox {
        width: 4%;
    }

    .table-eksekusi .col-no-dokumen {
        width: 10%;
    }

    .table-eksekusi .col-judul {
        width: 18%;
        white-space: normal;
    }

    /* Allow wrapping */
    .table-eksekusi .col-tahun {
        width: 6%;
    }

    .table-eksekusi .col-lokasi-aktif {
        width: 10%;
    }

    .table-eksekusi .col-no-ba {
        width: 8%;
    }

    .table-eksekusi .col-tgl-ba {
        width: 8%;
    }

    .table-eksekusi .col-no-berkas-baru {
        width: 12%;
    }

    .table-eksekusi .col-lokasi-simpan-baru {
        width: 16%;
    }

    .table-eksekusi .col-status-proses {
        width: 8%;
    }

    /* Style for inputs within table cells */
    .table-container-scroll td input.form-control {
        font-size: 0.85rem;
        padding: 0.3rem 0.5rem;
        height: auto;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Eksekusi Pemindahan Arsip</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Pilih item arsip yang telah dibuatkan Berita Acara dan siap untuk dieksekusi pemindahannya ke arsip inaktif. Masukkan detail lokasi baru per item.</p>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger" role="alert">
                <ul>
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> Tidak ada arsip yang siap dieksekusi pemindahannya saat ini.
            </div>
        <?php else: ?>
            <form action="<?= base_url('pemindahan/process_eksekusi') ?>" method="post" id="formEksekusi">
                <?= csrf_field() ?>

                <!-- Detail Informasi Berita Acara -->
                <?php if (!empty($uniqueBas)): ?>
                    <div class="card bg-light p-4 mb-4">
                        <h5 class="card-title text-primary mb-3">Berita Acara Terkait (<small class="text-muted">Item di bawah terkait dengan BA berikut</small>)</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($uniqueBas as $baId => $ba): ?>
                                <li class="list-group-item bg-transparent px-0">
                                    <strong>No. BA:</strong> <?= esc($ba['no_ba']) ?> (Tgl: <?= esc($ba['tgl_ba']) ?>)<br>
                                    <small class="text-muted">
                                        Pemindah: <?= esc($ba['nama_pemindah'] ?? '-') ?>, Penerima: <?= esc($ba['nama_penerima'] ?? '-') ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <button type="submit" class="btn btn-warning btn-lg" id="executeButton" disabled>
                        <i class="fas fa-arrow-right-arrow-left me-2"></i> Eksekusi Pemindahan Item Terpilih
                    </button>
                </div>

                <h5 class="mb-3">Daftar Item Arsip Siap Eksekusi</h5>
                <div class="table-container-scroll">
                    <table class="table table-hover table-striped table-bordered align-middle table-eksekusi">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center col-checkbox">
                                    <input type="checkbox" id="selectAllItems" class="form-check-input">
                                </th>
                                <th class="col-no-dokumen">No. Dokumen</th>
                                <th class="col-judul">Judul Dokumen</th>
                                <th class="col-tahun">Tahun Cipta</th>
                                <th class="col-lokasi-aktif">Lokasi Aktif</th>
                                <th class="col-no-ba">No. BA</th>
                                <th class="col-tgl-ba">Tgl BA</th>
                                <th class="col-no-berkas-baru">Nomor Berkas Baru <span class="text-danger">*</span></th>
                                <th class="col-lokasi-simpan-baru">Lokasi Simpan Baru <span class="text-danger">*</span></th>
                                <th class="col-status-proses">Status Proses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="text-center col-checkbox">
                                        <input type="checkbox" name="selected_items[]" value="<?= esc($item['id']) ?>" class="item-checkbox form-check-input">
                                    </td>
                                    <td class="col-no-dokumen"><?= esc($item['no_dokumen']) ?></td>
                                    <td class="col-judul wrap-text"><?= esc($item['judul_dokumen']) ?></td>
                                    <td class="col-tahun"><?= esc($item['tahun_cipta']) ?></td>
                                    <td class="col-lokasi-aktif"><?= esc($item['lokasi_simpan']) ?></td>
                                    <td class="col-no-ba"><?= esc($item['no_ba'] ?? '-') ?></td>
                                    <td class="col-tgl-ba"><?= esc($item['tgl_ba'] ? date('d-m-Y', strtotime($item['tgl_ba'])) : '-') ?></td>
                                    <td class="col-no-berkas-baru">
                                        <input type="text" class="form-control form-control-sm" name="item_data[<?= esc($item['id']) ?>][no_berkas_baru]" value="<?= old('item_data.' . $item['id'] . '.no_berkas_baru') ?>" placeholder="No. Berkas">
                                        <?php
                                        // Cek apakah ada error spesifik untuk input ini dari flashdata
                                        if (session()->getFlashdata('errors')):
                                            $errorMessages = session()->getFlashdata('errors');
                                            foreach ($errorMessages as $msg) {
                                                if (strpos($msg, "Item ID " . $item['id'] . ": Kolom 'no_berkas_baru'") !== false || strpos($msg, "Nomor Berkas Baru wajib diisi untuk item ID: " . $item['id']) !== false) {
                                                    echo '<div class="text-danger small mt-1">Wajib diisi atau format tidak valid</div>';
                                                    break;
                                                }
                                            }
                                        endif;
                                        ?>
                                    </td>
                                    <td class="col-lokasi-simpan-baru">
                                        <input type="text" class="form-control form-control-sm" name="item_data[<?= esc($item['id']) ?>][lokasi_simpan_new]" value="<?= old('item_data.' . $item['id'] . '.lokasi_simpan_new') ?>" placeholder="Lokasi Baru">
                                        <?php
                                        if (session()->getFlashdata('errors')):
                                            $errorMessages = session()->getFlashdata('errors');
                                            foreach ($errorMessages as $msg) {
                                                if (strpos($msg, "Item ID " . $item['id'] . ": Kolom 'lokasi_simpan_new'") !== false || strpos($msg, "Lokasi Simpan Baru wajib diisi untuk item ID: " . $item['id']) !== false) {
                                                    echo '<div class="text-danger small mt-1">Wajib diisi atau format tidak valid</div>';
                                                    break;
                                                }
                                            }
                                        endif;
                                        ?>
                                    </td>
                                    <td class="col-status-proses"><span class="badge bg-info"><?= esc(str_replace('_', ' ', $item['status_pindah'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAllItems');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const executeButton = document.getElementById('executeButton');
        const formEksekusi = document.getElementById('formEksekusi');

        function toggleExecuteButton() {
            let anyItemSelected = false;
            let allSelectedItemsHaveRequiredInputsFilled = true;

            itemCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    anyItemSelected = true;
                    const row = checkbox.closest('tr');
                    const noNewBerkasInput = row.querySelector(`input[name="item_data[${checkbox.value}][no_berkas_baru]"]`);
                    const lokasiSimpanNewInput = row.querySelector(`input[name="item_data[${checkbox.value}][lokasi_simpan_new]"]`);

                    if (!noNewBerkasInput || noNewBerkasInput.value.trim() === '' ||
                        !lokasiSimpanNewInput || lokasiSimpanNewInput.value.trim() === '') {
                        allSelectedItemsHaveRequiredInputsFilled = false;
                    }
                }
            });
            executeButton.disabled = !(anyItemSelected && allSelectedItemsHaveRequiredInputsFilled);
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleExecuteButton();
            });
        }

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                let allChecked = true;
                itemCheckboxes.forEach(cb => {
                    if (!cb.checked) {
                        allChecked = false;
                    }
                });
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                }
                toggleExecuteButton();
            });

            const row = checkbox.closest('tr');
            const noNewBerkasInput = row.querySelector(`input[name="item_data[${checkbox.value}][no_berkas_baru]"]`);
            const lokasiSimpanNewInput = row.querySelector(`input[name="item_data[${checkbox.value}][lokasi_simpan_new]"]`);

            if (noNewBerkasInput) {
                noNewBerkasInput.addEventListener('input', toggleExecuteButton);
            }
            if (lokasiSimpanNewInput) {
                lokasiSimpanNewInput.addEventListener('input', toggleExecuteButton);
            }
        });

        if (formEksekusi) {
            formEksekusi.addEventListener('submit', function(e) {
                e.preventDefault();

                let anyItemSelected = false;
                let validationMessages = []; // Ini untuk pesan validasi klien-side

                itemCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        anyItemSelected = true;
                        const row = checkbox.closest('tr');
                        const noNewBerkasInput = row.querySelector(`input[name="item_data[${checkbox.value}][no_berkas_baru]"]`);
                        const lokasiSimpanNewInput = row.querySelector(`input[name="item_data[${checkbox.value}][lokasi_simpan_new]"]`);

                        if (!noNewBerkasInput || noNewBerkasInput.value.trim() === '') {
                            validationMessages.push(`Nomor Berkas Baru wajib diisi untuk item ID: ${checkbox.value}`);
                        }
                        if (!lokasiSimpanNewInput || lokasiSimpanNewInput.value.trim() === '') {
                            validationMessages.push(`Lokasi Simpan Baru wajib diisi untuk item ID: ${checkbox.value}`);
                        }
                    }
                });

                if (!anyItemSelected) {
                    Swal.fire('Peringatan!', 'Tidak ada item yang dipilih untuk dieksekusi.', 'warning');
                    return;
                }

                if (validationMessages.length > 0) {
                    Swal.fire('Peringatan!', 'Harap lengkapi semua field yang wajib diisi untuk item yang terpilih:<br>' + validationMessages.join('<br>'), 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Eksekusi Pemindahan',
                    text: "Apakah Anda yakin ingin mengeksekusi pemindahan item terpilih? Item akan dipindahkan ke arsip inaktif dan dihapus dari arsip aktif.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Eksekusi!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formEksekusi.submit();
                    }
                });
            });
        }
        toggleExecuteButton();
    });
</script>
<?= $this->endSection() ?>