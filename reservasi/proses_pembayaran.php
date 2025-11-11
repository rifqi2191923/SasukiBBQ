<?php
include '../config/koneksi.php';
include '../config/wa_config.php';

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$reservasi_id = isset($_POST['reservasi_id']) ? intval($_POST['reservasi_id']) : 0;
$metode_pembayaran = isset($_POST['metode_pembayaran']) ? $_POST['metode_pembayaran'] : '';

if ($reservasi_id <= 0 || empty($metode_pembayaran)) {
    header('Location: index.php');
    exit;
}

// Ambil data reservasi terlebih dahulu
$query = "SELECT * FROM reservasi WHERE id = $reservasi_id";
$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit;
}

$reservasi = mysqli_fetch_assoc($result);

// Cek apakah sudah dibayar
if ($reservasi['status'] === 'dibayar' || $reservasi['status'] === 'dikonfirmasi') {
    header('Location: sukses.php?id=' . $reservasi_id);
    exit;
}

// Update status reservasi menjadi 'dibayar'
$metodeEsc = mysqli_real_escape_string($koneksi, $metode_pembayaran);

// Pertama, check apakah kolom metode_pembayaran ada
$checkQuery = "SHOW COLUMNS FROM reservasi LIKE 'metode_pembayaran'";
$checkResult = mysqli_query($koneksi, $checkQuery);
$columnExists = mysqli_num_rows($checkResult) > 0;

// Update query - tergantung apakah kolom ada atau tidak
if ($columnExists) {
    // Kolom ada, update dengan metode_pembayaran
    $query = "UPDATE reservasi SET status = 'dibayar', metode_pembayaran = '$metodeEsc' WHERE id = $reservasi_id";
} else {
    // Kolom tidak ada, update tanpa metode_pembayaran
    $query = "UPDATE reservasi SET status = 'dibayar' WHERE id = $reservasi_id";
}

if (!mysqli_query($koneksi, $query)) {
    error_log('Update Error: ' . mysqli_error($koneksi));
    header('Location: index.php?error=payment_update_failed');
    exit;
}

// Kirim notifikasi WhatsApp pembayaran berhasil
$harga_per_orang = 50000;
$total_pembayaran = $reservasi['jumlah_orang'] * $harga_per_orang;

$message = getMessageTemplate('pembayaran_sukses', [
    'id' => $reservasi['id'],
    'nama' => $reservasi['nama_pelanggan'],
    'tanggal' => $reservasi['tanggal'],
    'jam' => $reservasi['jam'],
    'jumlah' => $reservasi['jumlah_orang'],
    'metode' => $metode_pembayaran,
    'total' => $total_pembayaran
]);

$wa_result = sendWhatsAppMessage($reservasi['telepon'], $message);

// Log hasil pengiriman WA
if (is_writable(dirname(__FILE__) . '/../logs')) {
    @mkdir(dirname(__FILE__) . '/../logs', 0777, true);
    $log_file = dirname(__FILE__) . '/../logs/pembayaran.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Reservasi #$reservasi_id - WA: " . 
                     ($wa_result['success'] ? 'SUCCESS' : 'FAILED - ' . $wa_result['message']) . "\n", FILE_APPEND);
}

// ========================================
// PAYMENT FLOW v2.0 - REDIRECT TO UPLOAD
// ========================================
// For v2.0: After payment method selection, redirect to upload_bukti.php
// This is no longer the final step - user must upload proof first

// DO NOT redirect directly to sukses.php
// Instead, send response back to pembayaran.php for upload modal
// The form submission in pembayaran.php will handle this

// This file is now only called in old flow compatibility mode
// New flow: pembayaran.php → upload_bukti.php → sukses.php

// For backward compatibility, still update database
// but don't redirect - let pembayaran.php handle next step

// Redirect to upload bukti page (NEW FLOW v2.0)
header("Location: upload_bukti.php?id=" . $reservasi_id . "&metode=" . urlencode($metode_pembayaran));
exit;
?>

