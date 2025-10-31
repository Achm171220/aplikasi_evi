<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?><?= (isset($pageTitle) ? ' | ' . $pageTitle : '') ?></title>

    <!-- Google Fonts: Ubuntu -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style2.css') ?>">

    <!-- Extra CSS Section for Page Specific Styles (e.g., DataTables CSS) -->
    <?= $this->renderSection('extra_css') ?>
</head>

<body>

    <div class="d-flex" id="wrapper">

        <!-- Sidebar -->
        <?= $this->include('layout_new/sidebar') ?>
        <!-- /Sidebar -->

        <!-- Page Content -->
        <div id="page-content-wrapper" class="p-3 p-md-4">
            <!-- Navbar -->
            <?= $this->include('layout_new/navbar') ?>
            <!-- /Navbar -->

            <div class="container-fluid px-0">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
        <!-- /Page Content -->

    </div>

    <!-- Footer & Scripts -->
    <?= $this->include('layout_new/footer') ?>

    <!-- Extra JS Section for Page Specific Scripts (e.g., DataTables JS and initialization) -->
    <?= $this->renderSection('extra_js') ?>
</body>

</html>