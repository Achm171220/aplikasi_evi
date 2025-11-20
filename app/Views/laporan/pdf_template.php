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
    <h1>DAFTAR ARSIP AKTIF</h1>
    <h3>UNIT PENGOLAH: <?= esc(strtoupper($nama_es2)) ?></h3>
    <table class="table">
        <thead>
            <tr>
                <th>No Berkas</th>
                <th>Kode Klasifikasi</th>
                <th>Judul Berkas</th>
                <th>Kurun Waktu</th>
                <th>No. Item</th>
                <th>Uraian Informasi</th>
                <th>Tanggal</th>
                <th>Tk. Perkemb.</th>
                <th>Media</th>
                <th>Kondisi</th>
                <th>Jumlah</th>
                <th>Jangka Simpan & Nasib Akhir</th>
                <th>Keamanan & Akses</th>
                <th>Kategori</th>
                <th>Lokasi Simpan</th>
                <th>No. Boks</th>
                <th>Ket.</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($laporanData)): ?>
                <tr>
                    <td colspan="16" style="text-align:center;">Tidak ada data.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($laporanData as $row): ?>
                    <tr>
                        <td><?= esc($row['no_berkas_lengkap']) ?></td>
                        <td><?= esc($row['kode_klasifikasi']) ?></td>
                        <td><?= esc($row['judul_berkas']) ?></td>
                        <td><?= esc($row['kurun_waktu']) ?></td>
                        <td style="text-align:center;"><?= esc($row['no_item']) ?></td>
                        <td><?= esc($row['uraian_informasi']) ?></td>
                        <td><?= esc($row['tanggal']) ?></td>
                        <td><?= esc($row['tingkat_perkembangan']) ?></td>
                        <td><?= esc($row['media_arsip']) ?></td>
                        <td><?= esc($row['kondisi_arsip']) ?></td>
                        <td style="text-align:center;"><?= esc($row['jumlah']) ?></td>
                        <td><?= esc($row['jangka_simpan_nasib']) ?></td>
                        <td><?= esc($row['klasifikasi_keamanan']) ?></td>
                        <td><?= esc($row['kategori_arsip']) ?></td>
                        <td><?= esc($row['lokasi_simpan']) ?></td>
                        <td><?= esc($row['no_box']) ?></td>
                        <td><?= esc($row['keterangan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
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