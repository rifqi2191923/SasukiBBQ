<?php
include '../config/koneksi.php';
include '../config/wa_config.php';

date_default_timezone_set('Asia/Jakarta');

function showError($title, $message) {
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error - Reservasi</title>
        <style>
            body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
            .error-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; max-width: 500px; }
            .error-icon { font-size: 64px; margin-bottom: 20px; }
            h1 { color: #e74c3c; margin: 0 0 15px 0; }
            p { color: #666; margin-bottom: 25px; line-height: 1.5; }
            .btn { display: inline-block; padding: 12px 24px; background: #c0392b; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; }
            .btn:hover { background: #a93226; }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='error-icon'>❌</div>
            <h1>$title</h1>
            <p>$message</p>
            <a href='index.php' class='btn'>Kembali ke Form Reservasi</a>
        </div>
    </body>
    </html>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Sanitize input
$nama       = isset($_POST['nama_pelanggan']) ? trim($_POST['nama_pelanggan']) : '';
$telepon    = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
$tanggal    = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
$jam        = isset($_POST['jam']) ? $_POST['jam'] : '';
$jumlah     = isset($_POST['jumlah_orang']) ? intval($_POST['jumlah_orang']) : 0;
$catatan    = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';

// Validasi input
if (empty($nama) || empty($telepon) || empty($tanggal) || empty($jam) || $jumlah <= 0 || $jumlah > 20) {
    showError('Data Tidak Lengkap', 'Mohon lengkapi semua field dengan benar. Jumlah orang maksimal 20 orang.');
}

// Validasi format tanggal
$date = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$date || $date->format('Y-m-d') !== $tanggal) {
    showError('Format Tanggal Invalid', 'Silakan pilih tanggal yang valid.');
}

// Validasi tanggal tidak boleh di masa lalu
$today = new DateTime('today');
if ($date < $today) {
    showError('Tanggal Invalid', 'Silakan pilih tanggal hari ini atau di masa depan.');
}

// Validasi format jam
if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $jam)) {
    showError('Format Jam Invalid', 'Silakan masukkan jam dengan format HH:MM.');
}

// Validasi telepon (minimal 10 digit, maksimal 13 digit)
if (!preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $telepon)) {
    showError('Nomor Telepon Invalid', 'Nomor telepon harus 10-13 digit. Contoh: 081234567890');
}

// Escape untuk keamanan
$tanggalEsc = mysqli_real_escape_string($koneksi, $tanggal);
$jamEsc = mysqli_real_escape_string($koneksi, $jam);
$namaEsc = mysqli_real_escape_string($koneksi, $nama);
$teleponEsc = mysqli_real_escape_string($koneksi, $telepon);
$catatanEsc = mysqli_real_escape_string($koneksi, $catatan);

// Simpan reservasi
mysqli_begin_transaction($koneksi);
try {
    // Siapkan query dengan atau tanpa catatan
    if (!empty($catatanEsc)) {
        // Jika catatan ada, gunakan query dengan catatan
        $query = "INSERT INTO reservasi (nama_pelanggan, telepon, tanggal, jam, jumlah_orang, status, catatan) 
                  VALUES ('$namaEsc', '$teleponEsc', '$tanggalEsc', '$jamEsc', '$jumlah', 'pending', '$catatanEsc')";
    } else {
        // Jika catatan kosong, gunakan query tanpa catatan
        $query = "INSERT INTO reservasi (nama_pelanggan, telepon, tanggal, jam, jumlah_orang, status) 
                  VALUES ('$namaEsc', '$teleponEsc', '$tanggalEsc', '$jamEsc', '$jumlah', 'pending')";
    }
    
    if (!mysqli_query($koneksi, $query)) {
        throw new Exception('Gagal menyimpan data reservasi: ' . mysqli_error($koneksi));
    }
    
    $reservasi_id = mysqli_insert_id($koneksi);
    mysqli_commit($koneksi);
    
    // Kirim notifikasi WhatsApp
    $message = getMessageTemplate('reservasi_pending', [
        'id' => $reservasi_id,
        'nama' => $nama,
        'tanggal' => $tanggal,
        'jam' => $jam,
        'jumlah' => $jumlah,
        'telepon' => $telepon
    ]);
    
    $wa_result = sendWhatsAppMessage($telepon, $message);
    
    // Log hasil pengiriman WA
    if (is_writable(dirname(__FILE__) . '/../logs')) {
        $log_file = dirname(__FILE__) . '/../logs/reservasi.log';
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Reservasi #$reservasi_id - WA: " . 
                         ($wa_result['success'] ? 'SUCCESS' : 'FAILED - ' . $wa_result['message']) . "\n", FILE_APPEND);
    }
    
    // Redirect ke halaman pembayaran
    header("Location: pembayaran.php?id=" . $reservasi_id);
    exit;
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    error_log('Reservasi Error: ' . $e->getMessage());
    showError('Gagal Menyimpan Reservasi', $e->getMessage());
}
?>
