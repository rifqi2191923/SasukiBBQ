<?php
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama       = $_POST['nama_pelanggan'];
    $telepon    = $_POST['telepon'];
    $tanggal    = $_POST['tanggal'];
    $jam        = $_POST['jam'];
    $jumlah     = $_POST['jumlah_orang'];
    $kode_meja  = $_POST['kode_meja'];

    // Simpan data ke tabel reservasi
    $query = "INSERT INTO reservasi (nama_pelanggan, telepon, tanggal, jam, jumlah_orang, kode_meja, status)
              VALUES ('$nama', '$telepon', '$tanggal', '$jam', '$jumlah', '$kode_meja', 'menunggu')";
    $update_meja = "UPDATE meja SET status='dipesan' WHERE kode_meja='$kode_meja'";

    if (mysqli_query($koneksi, $query) && mysqli_query($koneksi, $update_meja)) {
        echo "<script>alert('Reservasi berhasil dikirim!');window.location.href='data_reservasi.php';</script>";
    } else {
        echo "Gagal menyimpan reservasi: " . mysqli_error($koneksi);
    }
}
?>
