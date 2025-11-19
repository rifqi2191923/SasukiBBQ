<?php
include '../config/koneksi.php';
include '../config/helpers.php';

date_default_timezone_set('Asia/Jakarta');

session_start();

// Check admin access
if (!isset($_SESSION['admin_verified'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$message_type = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($old_password === 'admin123') {
        if ($new_password === $confirm_password && strlen($new_password) >= 6) {
            $message = '✅ Password berhasil diubah! Silakan login kembali dengan password baru.';
            $message_type = 'success';
            // In production, save this to database/config file
        } else {
            $message = '❌ Password baru tidak cocok atau terlalu pendek (minimal 6 karakter)';
            $message_type = 'error';
        }
    } else {
        $message = '❌ Password lama tidak sesuai';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Admin - SASUKI BBQ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        header h1 {
            font-size: 28px;
            margin: 0;
        }

        .header-right {
            display: flex;
            gap: 10px;
        }

        .back-btn, .logout-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid white;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .back-btn:hover, .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        nav {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        nav a {
            color: #495057;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 14px;
        }

        nav a.active {
            background: #667eea;
            color: white;
        }

        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f5f5f5;
        }

        .section-header h2 {
            font-size: 20px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            max-width: 500px;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            min-height: 100px;
            resize: vertical;
            transition: all 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        button:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .button-group button {
            flex: 1;
        }

        .button-group .cancel-btn {
            background: #e9ecef;
            color: #495057;
        }

        .button-group .cancel-btn:hover {
            background: #dee2e6;
        }

        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .setting-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #667eea;
        }

        .setting-card h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 16px;
        }

        .setting-card p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-box {
            background: #e7f3ff;
            color: #004085;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #0066cc;
            font-size: 14px;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }

            input {
                max-width: 100%;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>⚙️ Pengaturan Admin</h1>
            <div class="header-right">
                <a href="dashboard.php" class="back-btn">← Kembali</a>
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="action" value="logout" class="logout-btn" onclick="return confirm('Logout?')">Logout</button>
                </form>
            </div>
        </header>

        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="reservasi.php">📋 Semua Reservasi</a>
            <a href="verifikasi_bukti.php">✅ Verifikasi Pembayaran</a>
            <a href="laporan.php">📈 Laporan</a>
            <a href="settings.php" class="active">⚙️ Pengaturan</a>
        </nav>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Change Password -->
        <div class="content-section">
            <div class="section-header">
                <h2>🔐 Ubah Password Admin</h2>
            </div>

            <div class="info-box">
                💡 Password harus minimal 6 karakter dan kombinasi huruf dan angka untuk keamanan yang lebih baik.
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="old_password">Password Lama</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="button-group">
                    <button type="submit">💾 Simpan Password Baru</button>
                    <button type="reset" class="cancel-btn">Batal</button>
                </div>
            </form>
        </div>

        <!-- System Settings -->
        <div class="content-section">
            <div class="section-header">
                <h2>🔧 Pengaturan Sistem</h2>
            </div>

            <div class="settings-grid">
                <div class="setting-card">
                    <h3>📋 Nama Restoran</h3>
                    <p>SASUKI BBQ - Sistem informasi reservasi dan pembayaran online.</p>
                </div>

                <div class="setting-card">
                    <h3>📍 Lokasi</h3>
                    <p>Jalan Utama No. 123, Jakarta - Update lokasi di config/koneksi.php</p>
                </div>

                <div class="setting-card">
                    <h3>📞 Kontak</h3>
                    <p>Hubungi administrator untuk mengubah nomor kontak dan informasi bisnis lainnya.</p>
                </div>

                <div class="setting-card">
                    <h3>💳 Harga Per Orang</h3>
                    <p>Rp 50.000 - Update di config/helpers.php</p>
                </div>

                <div class="setting-card">
                    <h3>📊 Database</h3>
                    <p>MySQL/MariaDB - Status: Connected ✅</p>
                </div>

                <div class="setting-card">
                    <h3>🔔 WhatsApp Integration</h3>
                    <p>Status: Configured - Update token di config/wa_config.php</p>
                </div>
            </div>
        </div>

        <!-- Database Management -->
        <div class="content-section">
            <div class="section-header">
                <h2>💾 Manajemen Database</h2>
            </div>

            <div class="info-box">
                ⚠️ Operasi database adalah operasi sensitif. Hubungi administrator sebelum melakukan perubahan.
            </div>

            <div class="button-group">
                <a href="../migrate.php" style="display: inline-block; background: #f39c12; color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 600;">📦 Jalankan Migrasi</a>
                <a href="../integrity_check.php" style="display: inline-block; background: #27ae60; color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 600;">✅ Cek Integritas Sistem</a>
            </div>
        </div>

        <!-- System Information -->
        <div class="content-section">
            <div class="section-header">
                <h2>ℹ️ Informasi Sistem</h2>
            </div>

            <table style="width: 100%; font-size: 14px;">
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-weight: 600; color: #495057; width: 200px;">Versi Sistem</td>
                    <td style="padding: 12px;">v2.0 - Payment Flow</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee; background: #f9f9f9;">
                    <td style="padding: 12px; font-weight: 600; color: #495057;">PHP Version</td>
                    <td style="padding: 12px;"><?php echo phpversion(); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-weight: 600; color: #495057;">Server Time</td>
                    <td style="padding: 12px;"><?php echo date('d-m-Y H:i:s'); ?></td>
                </tr>
                <tr style="background: #f9f9f9;">
                    <td style="padding: 12px; font-weight: 600; color: #495057;">Timezone</td>
                    <td style="padding: 12px;"><?php echo date_default_timezone_get(); ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
