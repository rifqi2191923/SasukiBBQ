<?php
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama       = $_POST['nama_pelanggan'];
    $telepon    = $_POST['telepon'];
    $tanggal    = $_POST['tanggal'];
    $jam        = $_POST['jam'];
    $jumlah     = $_POST['jumlah_orang'];
    $kode_meja  = $_POST['kode_meja'];

    // Cek bentrok 90 menit
    $tanggalEsc = mysqli_real_escape_string($koneksi, $tanggal);
    $jamEsc = mysqli_real_escape_string($koneksi, $jam);
    $kodeEsc = mysqli_real_escape_string($koneksi, $kode_meja);
    $check = "SELECT 1 FROM reservasi
              WHERE kode_meja = '$kodeEsc'
                AND tanggal = '$tanggalEsc'
                AND (
                    TIMESTAMP(tanggal, jam) < TIMESTAMP('$tanggalEsc', '$jamEsc') + INTERVAL 90 MINUTE
                    AND TIMESTAMP('$tanggalEsc', '$jamEsc') < TIMESTAMP(tanggal, jam) + INTERVAL 90 MINUTE
                )
                AND status NOT IN ('batal','selesai','waiting_ditolak')
              LIMIT 1";
    $conflict = mysqli_query($koneksi, $check);

    if ($conflict && mysqli_num_rows($conflict) > 0) {
        // Bentrok: masukkan waiting list
        $query = "INSERT INTO reservasi (nama_pelanggan, telepon, tanggal, jam, jumlah_orang, kode_meja, status)
                  VALUES ('$nama', '$telepon', '$tanggalEsc', '$jamEsc', '$jumlah', '$kodeEsc', 'waiting')";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Slot penuh pada waktu tersebut. Anda masuk ke waiting list.');window.location.href='index.php?tanggal=" . $tanggalEsc . "&jam=" . $jamEsc . "';</script>";
        } else {
            echo "Gagal menyimpan waiting list: " . mysqli_error($koneksi);
        }
    } else {
        // Tidak bentrok: simpan sebagai menunggu dan tandai meja dipesan
        mysqli_begin_transaction($koneksi);
        try {
            $query = "INSERT INTO reservasi (nama_pelanggan, telepon, tanggal, jam, jumlah_orang, kode_meja, status)
                      VALUES ('$nama', '$telepon', '$tanggalEsc', '$jamEsc', '$jumlah', '$kodeEsc', 'menunggu')";
            if (!mysqli_query($koneksi, $query)) { throw new Exception(mysqli_error($koneksi)); }
            if (!mysqli_query($koneksi, "UPDATE meja SET status='dipesan' WHERE kode_meja='$kodeEsc'")) { throw new Exception(mysqli_error($koneksi)); }
            mysqli_commit($koneksi);
            echo "<script>alert('Reservasi berhasil dikirim!');window.location.href='index.php?tanggal=" . $tanggalEsc . "&jam=" . $jamEsc . "';</script>";
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            echo "Gagal menyimpan reservasi: " . $e->getMessage();
        }
    }
}
?>
