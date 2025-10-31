<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-3 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="treeview-container">
                <div class="list-group">
                    <?php foreach ($eselon1 as $es1): ?>
                        <a href="#collapse_es1_<?= $es1['id'] ?>" class="list-group-item list-group-item-action treeview-item" data-bs-toggle="collapse">
                            <i class="fas fa-caret-right"></i>
                            <span class="ms-2"><?= $es1['kode'] ?> - <?= $es1['nama_es1'] ?></span>
                        </a>
                        <div class="collapse" id="collapse_es1_<?= $es1['id'] ?>">
                            <div class="list-group ms-4">
                                <?php foreach ($eselon2 as $es2): ?>
                                    <?php if ($es2['id_es1'] == $es1['id']): ?>
                                        <a href="#collapse_es2_<?= $es2['id'] ?>" class="list-group-item list-group-item-action treeview-item" data-bs-toggle="collapse">
                                            <i class="fas fa-caret-right"></i>
                                            <span class="ms-2"><?= $es2['kode'] ?> - <?= $es2['nama_es2'] ?></span>
                                        </a>
                                        <div class="collapse" id="collapse_es2_<?= $es2['id'] ?>">
                                            <div class="list-group ms-4">
                                                <?php foreach ($eselon3 as $es3): ?>
                                                    <?php if ($es3['id_es2'] == $es2['id']): ?>
                                                        <a href="#" class="list-group-item list-group-item-action treeview-item">
                                                            <i class="fas fa-file"></i>
                                                            <span class="ms-2"><?= $es3['kode'] ?> - <?= $es3['nama_es3'] ?></span>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    /* Gaya dasar untuk kontainer treeview */
    .treeview-container {
        padding: 1.5rem;
        background-color: #f8f9fa;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    /* Gaya untuk setiap item dalam treeview */
    .treeview-item {
        border: none;
        border-radius: 8px;
        margin-bottom: 5px;
        transition: background-color 0.3s, box-shadow 0.3s;
        font-weight: 500;
        color: #495057;
    }

    .treeview-item:hover {
        background-color: #e9ecef;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    /* Gaya untuk ikon panah (caret) */
    .treeview-item i.fa-caret-right {
        transition: transform 0.3s ease-in-out;
    }

    /* Rotasi ikon saat dropdown terbuka */
    .treeview-item.collapsed i.fa-caret-right {
        transform: rotate(0deg);
    }

    .treeview-item:not(.collapsed) i.fa-caret-right {
        transform: rotate(90deg);
    }

    /* Gaya untuk dropdown (collapse) */
    .collapse {
        transition: height 0.3s ease;
    }

    /* Gaya untuk garis hierarki vertikal (opsional, untuk memperjelas visual) */
    .list-group {
        border-left: 1px solid #dee2e6;
        padding-left: 10px;
    }

    .list-group:first-child {
        border-left: none;
        /* Hilangkan garis untuk level teratas */
    }
</style>

<script>
    // Dapatkan semua item treeview yang memiliki toggle
    const treeviewItems = document.querySelectorAll('.treeview-item[data-bs-toggle="collapse"]');

    treeviewItems.forEach(item => {
        item.addEventListener('click', function() {
            // Cari ikon di dalam item yang diklik
            const icon = this.querySelector('i.fa-caret-right');

            // Periksa status collapse
            if (this.classList.contains('collapsed')) {
                // Jika tertutup, putar ikon ke 90 derajat
                if (icon) {
                    icon.style.transform = 'rotate(90deg)';
                }
            } else {
                // Jika terbuka, putar ikon kembali ke 0 derajat
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>