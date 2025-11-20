<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <h4>Daftar Proposal Menunggu Eksekusi</h4>
                    <?php if (!empty($proposals_to_execute)) : ?>
                        <?php foreach ($proposals_to_execute as $proposal) : ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <strong>Proposal #<?= $proposal->id ?></strong> oleh <?= $proposal->pengusul->name ?? 'N/A' ?>
                                    <?php if ($proposal->berita_acara) : ?>
                                        <a href="<?= base_url($proposal->berita_acara->file_ba_scan) ?>" target="_blank" class="btn btn-sm btn-info float-end">Lihat Scan BA: <?= $proposal->berita_acara->no_ba ?></a>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if ($proposal->berita_acara) : ?>
                                        <p><strong>Tanggal BA:</strong> <?= date('d-m-Y', strtotime($proposal->berita_acara->tgl_ba)) ?></p>
                                        <p><strong>Pemindah:</strong> <?= $proposal->berita_acara->nama_pemindah ?> (<?= $proposal->berita_acara->jabatan_pemindah ?>)</p>
                                        <p><strong>Penerima:</strong> <?= $proposal->berita_acara->nama_penerima ?> (<?= $proposal->berita_acara->jabatan_penerima ?>)</p>
                                        <p><strong>Catatan BA:</strong> <?= $proposal->berita_acara->catatan ?></p>
                                    <?php else: ?>
                                        <div class="alert alert-warning">Berita Acara belum ditemukan.</div>
                                    <?php endif; ?>

                                    <h5>Item Terkait Proposal Ini (<?= $proposal->item_count ?> item):</h5>
                                    <table class="table table-sm table-bordered" id="proposalItemsTable_<?= $proposal->id ?>" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No Dokumen</th>
                                                <th>Judul Dokumen</th>
                                                <th>Tahun Cipta</th>
                                                <th>Status Item</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded via DataTables AJAX for each BA -->
                                        </tbody>
                                    </table>
                                    <form action="<?= site_url('pemindahan/executeTransfer/' . $proposal->id) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengeksekusi pemindahan untuk proposal ini? Tindakan ini tidak dapat dibatalkan.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger mt-3">Eksekusi Pemindahan</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>Tidak ada proposal yang menunggu eksekusi saat ini.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        <?php if (!empty($proposals_to_execute)) : ?>
            <?php foreach ($proposals_to_execute as $proposal) : ?>
                $('#proposalItemsTable_<?= $proposal->id ?>').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?= site_url('pemindahan/getProposalItemsAjax/' . $proposal->id) ?>",
                        "type": "POST"
                    },
                    "columns": [{
                            "data": "no_dokumen"
                        },
                        {
                            "data": "judul_dokumen"
                        },
                        {
                            "data": "tahun_cipta"
                        },
                        {
                            "data": "status_pindah"
                        }
                    ],
                    "paging": false,
                    "searching": false,
                    "info": false
                });
            <?php endforeach; ?>
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>