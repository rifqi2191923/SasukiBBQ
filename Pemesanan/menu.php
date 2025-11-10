<?php
$table = isset($_GET['table']) ? $_GET['table'] : 'Unknown';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menu Pemesanan - Meja <?php echo htmlspecialchars($table); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff9f4;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        h2 {
            color: #e74c3c;
        }
        .menu-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
        }
        .menu-item {
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            width: 200px;
            padding: 15px;
        }
        .menu-item img {
            width: 100%;
            border-radius: 10px;
        }
        .menu-item h4 {
            margin: 10px 0 5px;
        }
        .menu-item p {
            color: #555;
        }
        .btn {
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #219150;
        }
    </style>
</head>
<body>

<h2>🍖 Menu Pemesanan - Meja <?php echo htmlspecialchars($table); ?></h2>
<p>Silakan pilih menu dan lakukan pemesanan langsung dari meja Anda.</p>

<div class="menu-container">
    <div class="menu-item">
    <img src="../img/sasuki.jpg" alt="Beef BBQ">
        <h4>Beef BBQ</h4>
        <p>Rp 45.000</p>
        <a href="#" class="btn">Tambah</a>
    </div>

    <div class="menu-item">
    <img src="../img/sasuki.jpg" alt="Chicken Sukiyaki">
        <h4>Chicken Sukiyaki</h4>
        <p>Rp 38.000</p>
        <a href="#" class="btn">Tambah</a>
    </div>

    <div class="menu-item">
    <img src="../img/sasuki.jpg" alt="Matcha Ice Cream">
        <h4>Matcha Ice Cream</h4>
        <p>Rp 22.000</p>
        <a href="#" class="btn">Tambah</a>
    </div>
</div>

</body>
</html>
