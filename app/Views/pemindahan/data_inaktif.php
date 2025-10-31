<?= $this->extend('layout_old/main') ?>

<?= $this->section('title') ?>Sistem Pemindahan Arsip BPKP - Data Arsip Inaktif<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Specific column widths for data_inaktif.php table */
    .table-data-inaktif .col-id {
        width: 6%;
    }

    .table-data-inaktif .col-judul {
        width: 18%;
        white-space: normal;
    }

    /* Allow wrapping */
    .table-data-inaktif .col-tahun {
        width: 6%;
    }

    .table-data-inaktif .col-lokasi-lama {
        width: 9%;
    }

    .table-data-inaktif .col-no-ba {
        width: 8%;
    }

    .table-data-inaktif .col-tgl-ba {
        width: 8%;
    }

    .table-data-inaktif .col-no-berkas-baru {
        width: 10%;
    }

    .table-data-inaktif .col-lokasi-baru {
        width: 13%;
    }

    .table-data-inaktif .col-verif {
        width: 7%;
    }

    /* For V1, V2, V3 */
    .table-data-inaktif .col-status-arsip {
        width: 7%;
    }

    .table-data-inaktif .col-status-pindah {
        width: 8%;
    }

    .table-data-inaktif .col-aksi {
        width: 8%;
    }

    /* New column for action */
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Data Arsip Inaktif</h4>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger" role="alert">
                <ul>
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <p class="text-muted mb-4">Daftar lengkap semua arsip yang telah dipindahkan ke status inaktif.</p>

        <?php if (empty($items)): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> Belum ada arsip inaktif dalam sistem.
            </div>
        <?php else: ?>
            <div class="table-container-scroll">
                <table class="table table-hover table-striped table-bordered align-middle table-data-inaktif">
                    <thead class="table-dark">
                        <tr>
                            <th class="col-id">No. Dokumen</th>
                            <th class="col-judul">Judul Dokumen</th>
                            <th class="col-tahun">Tahun Cipta</th>
                            <th class="col-lokasi-lama">Lokasi Aktif Lama</th>
                            <th class="col-no-ba">No. BA</th>
                            <th class="col-tgl-ba">Tgl BA</th>
                            <th class="col-no-berkas-baru">No. Berkas Baru</th>
                            <th class="col-lokasi-baru">Lokasi Simpan Baru</th>
                            <th class="col-verif">Verif 1</th>
                            <th class="col-verif">Verif 2</th>
                            <th class="col-verif">Verif 3</th>
                            <th class="col-status-arsip">Status Arsip</th>
                            <th class="col-status-pindah">Status Pemindahan</th>
                            <th class="col-aksi">Aksi</th> <!-- NEW COLUMN -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="col-id"><?= esc($item['no_dokumen']) ?></td>
                                <td class="col-judul wrap-text"><?= esc($item['judul_dokumen']) ?></td>
                                <td class="col-tahun"><?= esc($item['tahun_cipta']) ?></td>
                                <td class="col-lokasi-lama"><?= esc($item['lokasi_simpan'] ?? '-') ?></td>
                                <td class="col-no-ba"><?= esc($item['no_ba'] ?? '-') ?></td>
                                <td class="col-tgl-ba"><?= esc($item['tgl_ba'] ? date('d-m-Y', strtotime($item['tgl_ba'])) : '-') ?></td>
                                <td class="col-no-berkas-baru"><?= esc($item['no_new_berkas'] ?? '-') ?></td>
                                <td class="col-lokasi-baru"><?= esc($item['lokasi_simpan_new'] ?? '-') ?></td>
                                <td class="col-verif"><?= esc($item['verifikator1_name'] ?? '-') ?></td>
                                <td class="col-verif"><?= esc($item['verifikator2_name'] ?? '-') ?></td>
                                <td class="col-verif"><?= esc($item['verifikator3_name'] ?? '-') ?></td>
                                <td class="col-status-arsip"><span class="badge bg-secondary"><?= esc($item['status_arsip']) ?></span></td>
                                <td class="col-status-pindah">
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($item['status_pindah'] == 'dipindahkan') {
                                        $badgeClass = 'bg-dark';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= esc(str_replace('_', ' ', $item['status_pindah'])) ?></span>
                                </td>
                                <td class="col-aksi">
                                    <button type="button" class="btn btn-sm btn-info restore-btn" data-id="<?= esc($item['id']) ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Kembalikan ke Arsip Aktif">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Event listener untuk tombol restore
        document.querySelectorAll('.restore-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const itemId = this.dataset.id;
                Swal.fire({
                    title: 'Konfirmasi Pengembalian Arsip',
                    text: "Apakah Anda yakin ingin mengembalikan arsip ini ke status aktif? Ini akan menghapus data dari arsip inaktif.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8', // Info color
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Kembalikan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim permintaan POST menggunakan Fetch API
                        const formData = new FormData();
                        formData.append('id', itemId);
                        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>'); // Sertakan CSRF token

                        fetch('<?= base_url('pemindahan/restore') ?>', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest' // Menandakan request AJAX
                                }
                            })
                            .then(response => response.json()) // Asumsikan controller mengembalikan JSON
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire('Berhasil!', data.message, 'success')
                                        .then(() => {
                                            window.location.reload(); // Reload halaman setelah berhasil
                                        });
                                } else {
                                    Swal.fire('Gagal!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'Terjadi kesalahan saat memproses permintaan.', 'error');
                            });
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>