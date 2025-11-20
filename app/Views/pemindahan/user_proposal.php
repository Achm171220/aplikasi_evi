<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Usulan Pemindahan Arsip Aktif</h4>
    </div>
    <div class="card-body">
        <p class="text-muted">Pilih item arsip aktif yang ingin Anda usulkan untuk pemindahan.</p>

        <?php if (empty($items)): ?>
            <div class="alert alert-info text-center" role="alert">
                Tidak ada arsip aktif yang tersedia untuk diusulkan pemindahan saat ini.
            </div>
        <?php else: ?>
            <form action="<?= base_url('pemindahan/propose') ?>" method="post" id="formPemindahan">
                <?= csrf_field() ?> <!-- Penting untuk keamanan CSRF -->

                <div class="mb-3">
                    <button type="submit" class="btn btn-success" id="proposeButton" disabled>
                        <i class="fas fa-paper-plane"></i> Usulkan Pemindahan Item Terpilih
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="selectAllItems">
                                </th>
                                <th style="width: 15%;">No. Dokumen</th>
                                <th style="width: 30%;">Judul Dokumen</th>
                                <th style="width: 15%;">Tahun Cipta</th>
                                <th style="width: 20%;">Lokasi Simpan</th>
                                <th style="width: 15%;">Status Arsip</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" class="item-checkbox">
                                    </td>
                                    <td><?= esc($item['no_dokumen']) ?></td>
                                    <td><?= esc($item['judul_dokumen']) ?></td>
                                    <td><?= esc($item['tahun_cipta']) ?></td>
                                    <td><?= esc($item['lokasi_simpan']) ?></td>
                                    <td><span class="badge bg-primary"><?= esc($item['status_arsip']) ?></span></td>
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
    $(document).ready(function() {
        const selectAllCheckbox = $('#selectAllItems');
        const itemCheckboxes = $('.item-checkbox');
        const proposeButton = $('#proposeButton');

        // Fungsi untuk mengaktifkan/menonaktifkan tombol proposal
        function toggleProposeButton() {
            if (itemCheckboxes.is(':checked')) {
                proposeButton.prop('disabled', false);
            } else {
                proposeButton.prop('disabled', true);
            }
        }

        // Event listener untuk "Select All"
        selectAllCheckbox.on('change', function() {
            itemCheckboxes.prop('checked', this.checked);
            toggleProposeButton();
        });

        // Event listener untuk checkbox item individual
        itemCheckboxes.on('change', function() {
            if (!this.checked) {
                selectAllCheckbox.prop('checked', false);
            } else if (itemCheckboxes.length === itemCheckboxes.filter(':checked').length) {
                selectAllCheckbox.prop('checked', true);
            }
            toggleProposeButton();
        });

        // Event listener untuk tombol usulkan
        $('#formPemindahan').on('submit', function(e) {
            e.preventDefault(); // Mencegah submit form default

            Swal.fire({
                title: 'Konfirmasi Usulan',
                text: "Apakah Anda yakin ingin mengusulkan item terpilih untuk pemindahan?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Usulkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Submit form jika dikonfirmasi
                }
            });
        });

        // Initial check on page load
        toggleProposeButton();
    });
</script>
<?= $this->endSection() ?>