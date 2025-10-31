<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
</head>

<body>
    <h2>Selamat Datang, <?= session()->get('user_name_api') ?></h2>
    <p>Role Akses Anda: <strong><?= session()->get('role_access') ?></strong></p>
    <hr>

    <h3>Data Otorisasi Lokal Anda (Kunci Filter Arsip):</h3>
    <p>ID User Lokal (untuk Filter): <strong><?= session()->get('user_id') ?></strong></p>

    <hr>
    <p>Sekarang Anda dapat mengakses data arsip. Sistem akan memfilter data berdasarkan **ID User Lokal** Anda.</p>

    <a href="<?= base_url('auth/logout') ?>">Logout</a>

    <br><br>
    <a href="<?= base_url('arsip') ?>">Lihat Data Arsip</a>
</body>

</html>