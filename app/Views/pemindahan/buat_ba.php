<?= $this->extend('layout_old/main') ?>

<?= $this->section('title') ?>Sistem Pemindahan Arsip BPKP - Buat Berita Acara<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Specific column widths for buat_ba.php table */
    .table-buat-ba .col-checkbox {
        width: 5%;
    }

    .table-buat-ba .col-no-dokumen {
        width: 15%;
    }

    .table-buat-ba .col-judul {
        width: 30%;
        white-space: normal;
    }

    .table-buat-ba .col-tahun {
        width: 15%;
    }

    .table-buat-ba .col-lokasi {
        width: 20%;
    }

    .table-buat-ba .col-status {
        width: 15%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Buat Berita Acara Pemindahan Arsip</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Pilih item arsip yang telah disetujui Verifikator 3, lalu masukkan detail Berita Acara.</p>

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
                <i class="fas fa-info-circle me-2"></i> Tidak ada arsip yang siap dibuat Berita Acara saat ini.
            </div>
        <?php else: ?>
            <form action="<?= base_url('pemindahan/process_buat_ba') ?>" method="post" id="formBuatBA" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- BA Details Input -->
                <div class="card bg-light p-4 mb-4">
                    <h5 class="card-title text-primary mb-3">Detail Berita Acara</h5>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="no_ba" class="form-label">Nomor Berita Acara <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="no_ba" name="no_ba" value="<?= old('no_ba') ?>" required placeholder="Misal: BA/BPKP/001/2023">
                        </div>
                        <div class="col-md-6">
                            <label for="tgl_ba" class="form-label">Tanggal Berita Acara <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tgl_ba" name="tgl_ba" value="<?= old('tgl_ba', date('Y-m-d')) ?>" required>
                            <div class="form-text">Format: YYYY-MM-DD</div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nama_pemindah" class="form-label">Nama Pihak Memindahkan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_pemindah" name="nama_pemindah" value="<?= old('nama_pemindah', $default_pemindah_name ?? '') ?>" required placeholder="Nama Lengkap">
                        </div>
                        <div class="col-md-6">
                            <label for="jabatan_pemindah" class="form-label">Jabatan Pihak Memindahkan</label>
                            <input type="text" class="form-control" id="jabatan_pemindah" name="jabatan_pemindah" value="<?= old('jabatan_pemindah', $default_pemindah_jabatan ?? '') ?>" placeholder="Misal: Arsiparis Ahli Muda">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nama_penerima" class="form-label">Nama Pihak Menerima <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" value="<?= old('nama_penerima') ?>" required placeholder="Nama Lengkap">
                        </div>
                        <div class="col-md-6">
                            <label for="jabatan_penerima" class="form-label">Jabatan Pihak Menerima</label>
                            <input type="text" class="form-control" id="jabatan_penerima" name="jabatan_penerima" value="<?= old('jabatan_penerima') ?>" placeholder="Misal: Kepala Subbagian Umum">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="catatan_ba" class="form-label">Catatan Tambahan Berita Acara</label>
                        <textarea class="form-control" id="catatan_ba" name="catatan_ba" rows="3" placeholder="Tambahkan catatan terkait BA ini..."><?= old('catatan_ba') ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label for="file_ba_scan" class="form-label">Scan Berita Acara (File)</label>
                        <input type="text" class="form-control" id="file_ba_scan" name="file_ba_scan" value="<?= old('file_ba_scan') ?>" placeholder="Nama file scan BA, cth: scan_ba_001.pdf">
                        <div class="form-text">Untuk sementara hanya nama file. Fitur upload file yang sebenarnya memerlukan validasi dan penyimpanan fisik.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBAButton" disabled>
                        <i class="fas fa-file-alt me-2"></i> Buat Berita Acara & Teruskan untuk Eksekusi
                    </button>
                </div>

                <h5 class="mb-3">Daftar Item Arsip Siap Berita Acara</h5>
                <div class="table-container-scroll">
                    <table class="table table-hover table-striped table-bordered align-middle table-buat-ba">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center col-checkbox">
                                    <input type="checkbox" id="selectAllItems" class="form-check-input">
                                </th>
                                <th class="col-no-dokumen">No. Dokumen</th>
                                <th class="col-judul">Judul Dokumen</th>
                                <th class="col-tahun">Tahun Cipta</th>
                                <th class="col-lokasi">Lokasi Simpan Aktif</th>
                                <th class="col-status">Status Proses</th>
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
                                    <td class="col-lokasi"><?= esc($item['lokasi_simpan']) ?></td>
                                    <td class="col-status"><span class="badge bg-success"><?= esc(str_replace('_', ' ', $item['status_pindah'])) ?></span></td>
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
        const submitBAButton = document.getElementById('submitBAButton');
        const formBuatBA = document.getElementById('formBuatBA');

        const noBaInput = document.getElementById('no_ba');
        const tglBaInput = document.getElementById('tgl_ba');
        const namaPemindahInput = document.getElementById('nama_pemindah');
        const namaPenerimaInput = document.getElementById('nama_penerima');

        function checkRequiredFields() {
            return noBaInput.value.trim() !== '' &&
                tglBaInput.value.trim() !== '' &&
                namaPemindahInput.value.trim() !== '' &&
                namaPenerimaInput.value.trim() !== '';
        }

        function toggleSubmitBAButton() {
            let anyItemSelected = false;
            itemCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    anyItemSelected = true;
                }
            });
            submitBAButton.disabled = !(anyItemSelected && checkRequiredFields());
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleSubmitBAButton();
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
                toggleSubmitBAButton();
            });
        });

        noBaInput.addEventListener('input', toggleSubmitBAButton);
        tglBaInput.addEventListener('input', toggleSubmitBAButton);
        namaPemindahInput.addEventListener('input', toggleSubmitBAButton);
        namaPenerimaInput.addEventListener('input', toggleSubmitBAButton);

        toggleSubmitBAButton();
    });
</script>
<?= $this->endSection() ?>