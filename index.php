<?php
/**
 * SASUKI BBQ - Landing Page / Status Check
 * Halaman untuk checking status aplikasi dan quick links
 */
date_default_timezone_set('Asia/Jakarta');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SASUKI BBQ - Sistem Reservasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 1000px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 40px 20px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            color: #2c3e50;
            font-size: 1.5em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .status-card {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid;
        }

        .status-card.ok {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .status-card.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .status-card.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .status-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .status-label {
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        .status-value {
            font-size: 0.9em;
            opacity: 0.8;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .btn {
            padding: 20px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: white;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, #0093E9 0%, #80D0C9 100%);
        }

        .btn-warning {
            background: linear-gradient(135deg, #FA709A 0%, #FEE140 100%);
        }

        .btn-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .btn-label {
            font-size: 0.95em;
            margin-bottom: 5px;
        }

        .btn-desc {
            font-size: 0.8em;
            opacity: 0.9;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            color: #1565c0;
            font-size: 0.95em;
        }

        .checklist {
            list-style: none;
            margin-top: 15px;
        }

        .checklist li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checklist li:last-child {
            border-bottom: none;
        }

        .checklist-icon {
            font-size: 1.2em;
            min-width: 25px;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
            border-top: 1px solid #e9ecef;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }

            .content {
                padding: 20px;
            }

            .button-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍖 SASUKI BBQ</h1>
            <p>Sistem Reservasi Online</p>
        </div>

        <div class="content">
            <!-- Status Check Section -->
            <div class="section">
                <h2>📊 Status Sistem</h2>

                <div class="status-grid">
                    <?php
                    // Check database connection
                    include 'config/koneksi.php';
                    $db_status = $koneksi ? 'ok' : 'error';
                    $db_icon = $koneksi ? '✅' : '❌';
                    $db_label = $koneksi ? 'Database OK' : 'Database Error';
                    ?>
                    <div class="status-card <?php echo $db_status; ?>">
                        <div class="status-icon"><?php echo $db_icon; ?></div>
                        <div class="status-label">Database</div>
                        <div class="status-value"><?php echo $db_label; ?></div>
                    </div>

                    <?php
                    // Check logs folder
                    $logs_dir = __DIR__ . '/logs';
                    $logs_status = is_dir($logs_dir) && is_writable($logs_dir) ? 'ok' : 'warning';
                    $logs_icon = is_dir($logs_dir) && is_writable($logs_dir) ? '✅' : '⚠️';
                    $logs_label = is_dir($logs_dir) ? 'Logs OK' : 'Logs Missing';
                    ?>
                    <div class="status-card <?php echo $logs_status; ?>">
                        <div class="status-icon"><?php echo $logs_icon; ?></div>
                        <div class="status-label">Logs Folder</div>
                        <div class="status-value"><?php echo $logs_label; ?></div>
                    </div>

                    <?php
                    // Check PHP version
                    $php_version = phpversion();
                    $php_ok = version_compare($php_version, '7.0.0', '>=');
                    $php_status = $php_ok ? 'ok' : 'error';
                    $php_icon = $php_ok ? '✅' : '❌';
                    ?>
                    <div class="status-card <?php echo $php_status; ?>">
                        <div class="status-icon"><?php echo $php_icon; ?></div>
                        <div class="status-label">PHP Version</div>
                        <div class="status-value"><?php echo $php_version; ?></div>
                    </div>

                    <?php
                    // Check WhatsApp config
                    include 'config/wa_config.php';
                    $wa_configured = defined('FONNTE_TOKEN') && FONNTE_TOKEN !== 'YOUR_FONNTE_API_TOKEN';
                    $wa_status = $wa_configured ? 'ok' : 'warning';
                    $wa_icon = $wa_configured ? '✅' : '⚠️';
                    $wa_label = $wa_configured ? 'Configured' : 'Not Set';
                    ?>
                    <div class="status-card <?php echo $wa_status; ?>">
                        <div class="status-icon"><?php echo $wa_icon; ?></div>
                        <div class="status-label">WhatsApp</div>
                        <div class="status-value"><?php echo $wa_label; ?></div>
                    </div>

                    <?php
                    // Check tabel reservasi
                    $table_exists = false;
                    if ($koneksi) {
                        $result = mysqli_query($koneksi, "SHOW TABLES LIKE 'reservasi'");
                        $table_exists = mysqli_num_rows($result) > 0;
                    }
                    $table_status = $table_exists ? 'ok' : 'error';
                    $table_icon = $table_exists ? '✅' : '❌';
                    $table_label = $table_exists ? 'Table OK' : 'Table Missing';
                    ?>
                    <div class="status-card <?php echo $table_status; ?>">
                        <div class="status-icon"><?php echo $table_icon; ?></div>
                        <div class="status-label">Tabel Reservasi</div>
                        <div class="status-value"><?php echo $table_label; ?></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="section">
                <h2>🚀 Quick Actions</h2>

                <div class="info-box">
                    ℹ️ Klik tombol di bawah untuk mengakses fitur utama aplikasi
                </div>

                <div class="button-grid">
                    <a href="reservasi/" class="btn btn-success">
                        <div class="btn-icon">📋</div>
                        <div class="btn-label">Mulai Reservasi</div>
                        <div class="btn-desc">Buat reservasi baru sekarang</div>
                    </a>

                    <a href="reservasi/data_reservasi.php" class="btn btn-primary">
                        <div class="btn-icon">📊</div>
                        <div class="btn-label">Lihat Data Reservasi</div>
                        <div class="btn-desc">Kelola semua reservasi</div>
                    </a>

                    <a href="setup.php" class="btn btn-info">
                        <div class="btn-icon">⚙️</div>
                        <div class="btn-label">Database Setup</div>
                        <div class="btn-desc">Setup tabel database</div>
                    </a>

                    <a href="README.md" class="btn btn-warning">
                        <div class="btn-icon">📚</div>
                        <div class="btn-label">Dokumentasi</div>
                        <div class="btn-desc">Baca dokumentasi lengkap</div>
                    </a>
                </div>
            </div>

            <!-- Documentation Section -->
            <div class="section">
                <h2>📖 Panduan & Resources</h2>

                <div class="info-box">
                    <strong>📌 Untuk Memulai:</strong><br>
                    1. Baca file SETUP_GUIDE.md<br>
                    2. Jalankan setup.php untuk database setup<br>
                    3. Configure WhatsApp di config/wa_config.php<br>
                    4. Mulai reservasi!
                </div>

                <ul class="checklist">
                    <li>
                        <span class="checklist-icon">📄</span>
                        <span><strong>README.md</strong> - Dokumentasi lengkap sistem</span>
                    </li>
                    <li>
                        <span class="checklist-icon">🚀</span>
                        <span><strong>SETUP_GUIDE.md</strong> - Panduan setup cepat (5 menit)</span>
                    </li>
                    <li>
                        <span class="checklist-icon">🧪</span>
                        <span><strong>TESTING_GUIDE.md</strong> - Panduan testing & debugging</span>
                    </li>
                    <li>
                        <span class="checklist-icon">✅</span>
                        <span><strong>IMPLEMENTATION_SUMMARY.md</strong> - Ringkasan implementasi</span>
                    </li>
                </ul>
            </div>

            <!-- System Requirements -->
            <div class="section">
                <h2>✅ Persyaratan Sistem</h2>

                <ul class="checklist">
                    <li>
                        <span class="checklist-icon">✅</span>
                        <span>PHP >= 7.0 (Current: <?php echo phpversion(); ?>)</span>
                    </li>
                    <li>
                        <span class="checklist-icon">✅</span>
                        <span>MySQL / MariaDB</span>
                    </li>
                    <li>
                        <span class="checklist-icon">✅</span>
                        <span>Folder writable: logs/</span>
                    </li>
                    <li>
                        <span class="checklist-icon">✅</span>
                        <span>curl extension (untuk WhatsApp API)</span>
                    </li>
                </ul>
            </div>

            <!-- Features Section -->
            <div class="section">
                <h2>⭐ Fitur Utama</h2>

                <ul class="checklist">
                    <li>
                        <span class="checklist-icon">📋</span>
                        <span>Form reservasi dengan validasi lengkap</span>
                    </li>
                    <li>
                        <span class="checklist-icon">💳</span>
                        <span>Integrasi pembayaran (Tunai, Transfer, QRIS)</span>
                    </li>
                    <li>
                        <span class="checklist-icon">📱</span>
                        <span>Notifikasi WhatsApp otomatis via Fonnte</span>
                    </li>
                    <li>
                        <span class="checklist-icon">💾</span>
                        <span>Penyimpanan data ke database</span>
                    </li>
                    <li>
                        <span class="checklist-icon">📊</span>
                        <span>Manajemen & viewing semua reservasi</span>
                    </li>
                    <li>
                        <span class="checklist-icon">🔒</span>
                        <span>Validasi & error handling</span>
                    </li>
                    <li>
                        <span class="checklist-icon">📱</span>
                        <span>Responsive design (mobile-friendly)</span>
                    </li>
                </ul>
            </div>

            <!-- Version Info -->
            <div class="section" style="background: #f8f9fa; padding: 15px; border-radius: 10px;">
                <h2 style="margin-top: 0; border-bottom: none;">ℹ️ Informasi Sistem</h2>
                <ul class="checklist" style="margin-top: 10px;">
                    <li>
                        <span class="checklist-icon">📌</span>
                        <span><strong>Versi:</strong> 1.0.0</span>
                    </li>
                    <li>
                        <span class="checklist-icon">📅</span>
                        <span><strong>Tanggal Update:</strong> November 11, 2025</span>
                    </li>
                    <li>
                        <span class="checklist-icon">🕐</span>
                        <span><strong>Server Time:</strong> <?php echo date('d/m/Y H:i:s'); ?></span>
                    </li>
                    <li>
                        <span class="checklist-icon">🗄️</span>
                        <span><strong>Database:</strong> <?php echo $koneksi ? 'Connected' : 'Disconnected'; ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>🍖 SASUKI BBQ - Sistem Reservasi Online v1.0.0</p>
            <p style="margin-top: 10px; font-size: 0.85em;">
                For support, check documentation files or contact administrator
            </p>
        </div>
    </div>
</body>
</html>
