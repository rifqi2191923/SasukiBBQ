<?php include '../config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reservasi SASUKI BBQ</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            background: url('../img/sasuki.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 50px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        header, footer {
            text-align: center;
            color: white;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 15px 0;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #c0392b;
            color: white;
            border: none;
            margin-top: 20px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #922b21;
        }
    </style>
</head>
<body>
<header>
    <h1>Reservasi Meja SASUKI BBQ</h1>
</header>

<div class="container">
    <h2>Form Pemesanan Meja</h2>
    <form action="proses_tambah.php" method="POST">
        <label>Nama Pemesan:</label>
        <input type="text" name="nama_pelanggan" required>

        <label>No. Handphone:</label>
        <input type="text" name="telepon" required>

        <label>Tanggal Reservasi:</label>
        <input type="date" name="tanggal" required>

        <label>Jam Reservasi:</label>
        <input type="time" name="jam" required>

        <label>Jumlah Orang:</label>
        <input type="number" name="jumlah_orang" min="1" required>

        

        <input type="submit" value="Kirim Reservasi">
    </form>
</div>
</body>
</html>
