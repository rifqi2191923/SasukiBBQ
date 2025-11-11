<?php
include '../config/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

$reservasi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reservasi_id <= 0) {
    header('Location: index.php');
    exit;
}

$query = "SELECT * FROM reservasi WHERE id = $reservasi_id";
$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit;
}

$reservasi = mysqli_fetch_assoc($result);

// Hitung informasi pembayaran
$harga_per_orang = 50000;
$total_pembayaran = $reservasi['jumlah_orang'] * $harga_per_orang;

// Format metode pembayaran
$metode_display = isset($reservasi['metode_pembayaran']) ? ucfirst($reservasi['metode_pembayaran']) : 'Tidak diketahui';

// Status verifikasi bukti pembayaran
$is_verified = isset($reservasi['bukti_verified']) && $reservasi['bukti_verified'] == 1;
$status_text = $is_verified ? 'Terverifikasi' : 'Menunggu Verifikasi';

// Fungsi helper untuk format tanggal Indonesia
function formatTanggalIndonesia($tanggal) {
    $bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return date('d', strtotime($tanggal)) . ' ' . 
           $bulan[(int)date('m', strtotime($tanggal)) - 1] . ' ' . 
           date('Y', strtotime($tanggal));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - SASUKI BBQ</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            max-width: 1000px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-section {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 15px;
            animation: bounce 1s ease-in-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .header-section h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            font-weight: 700;
        }

        .header-section p {
            margin: 0;
            font-size: 1.1em;
            opacity: 0.95;
        }

        .content-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .left-section {
            background: #e8f5e9;
            padding: 40px;
            border-right: 2px solid #c8e6c9;
        }

        .right-section {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .section-title {
            color: #1b5e20;
            font-size: 1.4em;
            font-weight: 700;
            margin: 0 0 25px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-item {
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
        }

        .info-label {
            font-size: 0.85em;
            color: #558b2f;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value {
            color: #212529;
            font-size: 1.05em;
            font-weight: 500;
        }

        .payment-highlight {
            background: white;
            border: 2px solid #27ae60;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .payment-status {
            color: #27ae60;
            font-size: 0.9em;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-total {
            color: #1b5e20;
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .payment-method {
            color: #666;
            font-size: 0.95em;
        }

        .info-note {
            background: linear-gradient(135deg, #fff9c4 0%, #fffde7 100%);
            border-left: 4px solid #fbc02d;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            font-size: 0.95em;
            line-height: 1.6;
            color: #856404;
        }

        .info-note strong {
            color: #654321;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            min-width: 150px;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(149, 165, 166, 0.4);
        }

        .divider {
            width: 100%;
            height: 1px;
            background: #ddd;
            margin: 20px 0;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .success-container {
                box-shadow: none;
            }

            .action-buttons,
            .info-note {
                display: none;
            }

            .content-section {
                grid-template-columns: 1fr;
            }

            .left-section {
                border-right: none;
                border-bottom: 2px solid #c8e6c9;
            }
        }

        @media (max-width: 768px) {
            .header-section {
                padding: 30px 20px;
            }

            .header-section h1 {
                font-size: 2em;
            }

            .success-icon {
                font-size: 60px;
            }

            .content-section {
                grid-template-columns: 1fr;
            }

            .left-section {
                border-right: none;
                border-bottom: 2px solid #c8e6c9;
                padding: 30px 20px;
            }

            .right-section {
                padding: 30px 20px;
            }

            .payment-total {
                font-size: 1.8em;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="success-container print-section">
        <!-- HEADER -->
        <div class="header-section">
            <div class="success-icon">✅</div>
            <h1>Pembayaran Berhasil!</h1>
            <p>Reservasi Anda telah dikonfirmasi dan disimpan di sistem kami</p>
        </div>

        <!-- CONTENT -->
        <div class="content-section">
            <!-- LEFT: Detail Reservasi -->
            <div class="left-section">
                <div class="section-title">📋 Detail Reservasi</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ID Reservasi</div>
                        <div class="info-value">#<?php echo htmlspecialchars($reservasi['id']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nama Pemesan</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservasi['nama_pelanggan']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">No. Telepon</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservasi['telepon']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tanggal Kunjungan</div>
                        <div class="info-value"><?php echo formatTanggalIndonesia($reservasi['tanggal']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jam Kunjungan</div>
                        <div class="info-value"><?php echo date('H:i', strtotime($reservasi['jam'])); ?> WIB</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jumlah Tamu</div>
                        <div class="info-value"><?php echo $reservasi['jumlah_orang']; ?> orang</div>
                    </div>
                    <?php if (!empty($reservasi['catatan'])): ?>
                    <div class="info-item">
                        <div class="info-label">Catatan Khusus</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservasi['catatan']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT: Payment Info & Actions -->
            <div class="right-section">
                <div class="section-title">💰 Pembayaran</div>
                
                <div class="payment-highlight">
                    <div class="payment-status"><?php echo $is_verified ? '✅ Pembayaran Terverifikasi' : '⏳ Menunggu Verifikasi'; ?></div>
                    <div class="payment-total">Rp <?php echo number_format($total_pembayaran, 0, ',', '.'); ?></div>
                    <div class="payment-method">
                        Metode: <strong><?php echo htmlspecialchars($metode_display); ?></strong>
                    </div>
                    <div style="color: #666; font-size: 0.85em; margin-top: 10px;">
                        <?php echo $reservasi['jumlah_orang']; ?> orang × Rp <?php echo number_format($harga_per_orang, 0, ',', '.'); ?>
                    </div>
                </div>

                <?php if (!$is_verified): ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 8px; margin: 15px 0; color: #856404; font-size: 0.95em; line-height: 1.5;">
                    <strong>⏳ Proses Verifikasi:</strong><br>
                    Bukti pembayaran Anda sedang kami verifikasi. Biasanya memakan waktu 5-10 menit. Anda akan menerima notifikasi WhatsApp segera setelah verifikasi selesai.
                </div>
                <?php endif; ?>

                <div class="divider"></div>

                <div class="info-note">
                    <strong>📱 Status Pembayaran</strong><br>
                    <?php if ($is_verified): ?>
                    ✅ Pembayaran Anda telah terverifikasi. Meja akan disiapkan sesuai jadwal yang ditentukan.
                    <?php else: ?>
                    ⏳ Kami sedang memverifikasi bukti pembayaran Anda. Notifikasi akan dikirim ke WhatsApp Anda.
                    <?php endif; ?>
                    <br><br>
                    <strong>⏱️ Informasi Penting:</strong><br>
                    • Meja akan disiapkan sesuai jam yang ditentukan<br>
                    • Durasi makan maksimal 90 menit (AYCE unlimited)<br>
                    • Hubungi kami di WhatsApp jika ada pertanyaan
                </div>

                <div class="action-buttons">
                    <a href="index.php" class="btn btn-primary">🏠 Kembali ke Beranda</a>
                    <button onclick="window.print()" class="btn btn-secondary">🖨️ Cetak Bukti</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

