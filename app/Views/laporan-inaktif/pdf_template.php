<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 8px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        /* Beri margin atas */
        .table th,
        .table td {
            border: 1px solid #333;
            padding: 4px;
        }

        .table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        h1,
        h3 {
            text-align: center;
            margin: 0;
        }

        h1 {
            margin-bottom: 5px;
        }

        .signature-table {
            width: 100%;
            margin-top: 40px;
        }

        /* Jarak dari tabel utama */
        .signature-table td {
            border: none;
            text-align: center;
            width: 50%;
        }
    </style>
</head>

<body>
    <h1><?= esc($title) ?></h1>
    <table class="table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Klasifikasi</th>
                <th>Judul Dokumen</th>
                <th>Tahun</th>
                <th>Jumlah</th>
                <th>Lokasi Simpan (Rak/Box)</th>
                <th>Nasib Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($laporanData as $index => $row): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= esc($row['kode_klasifikasi']) ?></td>
                    <td><?= esc($row['judul_dokumen']) ?></td>
                    <td><?= esc($row['tahun_cipta']) ?></td>
                    <td><?= esc($row['jumlah']) ?></td>
                    <td><?= esc($row['lokasi_simpan_aktif'] . ' / ' . $row['no_box']) ?></td>
                    <td><?= esc(ucfirst($row['nasib_akhir'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <table class="signature-table">
        <tr>
            <td>
                <p>Disusun Oleh:</p>
                <br><br><br><br>
                <p>..................................</p>
            </td>
            <td>
                <p>Mengetahui:</p>
                <br><br><br><br>
                <p>..................................</p>
            </td>
        </tr>
    </table>
</body>

</html>