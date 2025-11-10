<?php include '../config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Reservasi</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header>
    <h1>Data Reservasi SASUKI BBQ</h1>
</header>

<div class="container">
    <a href="index.php" class="btn">+ Tambah Reservasi</a>
    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <tr style="background-color:#c0392b; color:white;">
            <th>No</th>
            <th>Nama</th>
            <th>Telepon</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Jumlah Orang</th>
            <th>Kode Meja</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php
        $no = 1;
        $data = mysqli_query($koneksi, "SELECT * FROM reservasi ORDER BY tanggal DESC, jam DESC");
        while ($row = mysqli_fetch_assoc($data)) {
            echo "
            <tr>
                <td>$no</td>
                <td>{$row['nama_pelanggan']}</td>
                <td>{$row['telepon']}</td>
                <td>{$row['tanggal']}</td>
                <td>{$row['jam']}</td>
                <td>{$row['jumlah_orang']}</td>
                <td>{$row['kode_meja']}</td>
                <td>{$row['status']}</td>
                <td>
                    <a href='edit_reservasi.php?id={$row['id']}'>Edit</a> |
                    <a href='hapus_reservasi.php?id={$row['id']}' onclick='return confirm(\"Yakin?\")'>Hapus</a>
                </td>
            </tr>";
            $no++;
        }
        ?>
    </table>
</div>
</body>
</html>
