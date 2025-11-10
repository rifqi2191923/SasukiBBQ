<?php include '../config/koneksi.php'; ?>
<?php
// Ambil parameter tanggal & jam dari query untuk memfilter ketersediaan
$selectedDate = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$selectedTime = isset($_GET['jam']) ? $_GET['jam'] : '';

function isTableAvailable($koneksi, $kodeMeja, $tanggal, $jam, $durationMinutes = 90) {
    if ($tanggal === '' || $jam === '') return false;
    $tanggalEsc = mysqli_real_escape_string($koneksi, $tanggal);
    $jamEsc = mysqli_real_escape_string($koneksi, $jam);
    $kodeEsc = mysqli_real_escape_string($koneksi, $kodeMeja);

    // Hitung interval waktu
    $query = "SELECT 1 FROM reservasi
              WHERE kode_meja = '$kodeEsc'
                AND tanggal = '$tanggalEsc'
                AND (
                    TIMESTAMP(tanggal, jam) < TIMESTAMP('$tanggalEsc', '$jamEsc') + INTERVAL $durationMinutes MINUTE
                    AND TIMESTAMP('$tanggalEsc', '$jamEsc') < TIMESTAMP(tanggal, jam) + INTERVAL $durationMinutes MINUTE
                )
                AND status NOT IN ('batal','selesai','waiting_ditolak')
              LIMIT 1";

    $res = mysqli_query($koneksi, $query);
    if ($res && mysqli_num_rows($res) > 0) {
        return false; // ada bentrok
    }
    return true; // tidak bentrok
}
?>
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
            max-width: 760px;
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
            box-sizing: border-box;
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
    <?php include 'map_meja.php'; ?>
    <h2>Form Pemesanan Meja</h2>
    <form action="proses_tambah.php" method="POST">
        
        <label>Tanggal Reservasi:</label>
        <input type="date" name="tanggal" value="<?php echo htmlspecialchars($selectedDate); ?>" required>

        <label>Jam Reservasi:</label>
        <input type="time" name="jam" value="<?php echo htmlspecialchars($selectedTime); ?>" required>

        <label>Nama Pemesan:</label>
        <input type="text" name="nama_pelanggan" required>

        <label>No. Telepon:</label>
        <input type="text" name="telepon" required>

        <label>Jumlah Orang:</label>
        <input type="number" name="jumlah_orang" min="1" required>

        <label>Pilih Meja:</label>
        <select name="kode_meja" id="selectMeja" required <?php echo ($selectedDate && $selectedTime) ? '' : 'disabled'; ?>>
            <?php if (!$selectedDate || !$selectedTime) { ?>
                <option value="">Pilih tanggal & jam terlebih dahulu</option>
            <?php } else { ?>
                <?php
                $pref = isset($_GET['pref_meja']) ? $_GET['pref_meja'] : '';
                $hasAny = false;
                for ($i = 1; $i <= 22; $i++) {
                    $kode = str_pad($i, 2, "0", STR_PAD_LEFT);
                    if (isTableAvailable($koneksi, $kode, $selectedDate, $selectedTime)) {
                        $hasAny = true;
                        $selected = ($pref !== '' && $pref == $kode) ? ' selected' : '';
                        echo "<option value='$kode'$selected>Meja $kode</option>";
                    }
                }
                if (!$hasAny) {
                    echo "<option value=''>Tidak ada meja tersedia pada waktu tersebut</option>";
                }
                ?>
            <?php } ?>
        </select>

        <input type="submit" value="Kirim Reservasi">
    </form>
</div>
</body>
<script>
(function() {
    const dateInput = document.querySelector('input[name="tanggal"]');
    const timeInput = document.querySelector('input[name="jam"]');
    function reloadWithParams() {
        const d = dateInput.value; const t = timeInput.value;
        const url = new URL(window.location.href);
        if (d) url.searchParams.set('tanggal', d); else url.searchParams.delete('tanggal');
        if (t) url.searchParams.set('jam', t); else url.searchParams.delete('jam');
        window.location.href = url.toString();
    }
    dateInput && dateInput.addEventListener('change', reloadWithParams);
    timeInput && timeInput.addEventListener('change', reloadWithParams);
})();
</script>
</html>
