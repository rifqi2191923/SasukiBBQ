<?php
include '../config/koneksi.php';
include '../config/helpers.php';
include '../config/wa_config.php';

date_default_timezone_set('Asia/Jakarta');

session_start();

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    header('Location: dashboard.php');
    exit;
}

// Simple authentication
$admin_password = 'admin123'; // GANTI DENGAN PASSWORD YANG LEBIH KUAT!

if (!isset($_SESSION['admin_verified'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $admin_password) {
            $_SESSION['admin_verified'] = true;
        } else {
            $login_error = 'Password salah!';
        }
    }
    
    if (!isset($_SESSION['admin_verified'])) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login Admin - SASUKI BBQ</title>
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
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }

                .login-container {
                    background: white;
                    padding: 50px 40px;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                    max-width: 400px;
                    width: 90%;
                }

                .login-header {
                    text-align: center;
                    margin-bottom: 30px;
                }

                .logo {
                    font-size: 48px;
                    margin-bottom: 15px;
                }

                h1 {
                    color: #2c3e50;
                    font-size: 24px;
                    margin-bottom: 10px;
                }

                .subtitle {
                    color: #7f8c8d;
                    font-size: 14px;
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
                    transition: all 0.3s;
                }

                input:focus {
                    outline: none;
                    border-color: #667eea;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }

                button {
                    width: 100%;
                    padding: 12px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 14px;
                    cursor: pointer;
                    transition: all 0.3s;
                }

                button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                }

                .error {
                    background: #f8d7da;
                    color: #721c24;
                    padding: 12px 15px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    border-left: 4px solid #dc3545;
                    font-size: 14px;
                }

                .info {
                    background: #e7f3ff;
                    color: #004085;
                    padding: 12px 15px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    border-left: 4px solid #0066cc;
                    font-size: 13px;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="login-header">
                    <div class="logo">🔐</div>
                    <h1>Admin Dashboard</h1>
                    <div class="subtitle">SASUKI BBQ Management System</div>
                </div>

                <?php if (isset($login_error)): ?>
                    <div class="error">❌ <?php echo $login_error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="password">Password Admin</label>
                        <input type="password" id="password" name="password" required autofocus placeholder="Masukkan password">
                    </div>
                    <button type="submit">Masuk</button>
                </form>

                <div class="info">
                    <strong>💡 Tip:</strong> Gunakan password yang aman dan simpan dengan baik.
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Get dashboard statistics
$stats = [
    'total_reservasi' => 0,
    'pending_payment' => 0,
    'verified_payment' => 0,
    'rejected_payment' => 0,
    'today_revenue' => 0,
    'total_revenue' => 0
];

// Count total reservasi
$query = "SELECT COUNT(*) as count FROM reservasi";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_reservasi'] = $row['count'];
}

// Count pending payments
$query = "SELECT COUNT(*) as count FROM reservasi WHERE bukti_verified = 0 AND bukti_pembayaran IS NOT NULL";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['pending_payment'] = $row['count'];
}

// Count verified payments
$query = "SELECT COUNT(*) as count FROM reservasi WHERE bukti_verified = 1";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['verified_payment'] = $row['count'];
}

// Count rejected payments
$query = "SELECT COUNT(*) as count FROM reservasi WHERE bukti_verified = -1";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['rejected_payment'] = $row['count'];
}

// Today's revenue
$today = date('Y-m-d');
$query = "SELECT SUM(jumlah_orang * 50000) as total FROM reservasi WHERE DATE(tanggal) = '$today' AND bukti_verified = 1";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['today_revenue'] = $row['total'] ?? 0;
}

// Total revenue
$query = "SELECT SUM(jumlah_orang * 50000) as total FROM reservasi WHERE bukti_verified = 1";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_revenue'] = $row['total'] ?? 0;
}

// Get recent reservasi
$query = "SELECT id, nama_pelanggan, telepon, jumlah_orang, metode_pembayaran, bukti_verified, 
                  tanggal, jam, (jumlah_orang * 50000) as total
          FROM reservasi 
          ORDER BY id DESC 
          LIMIT 10";
$recent_result = mysqli_query($koneksi, $query);
$recent_reservasi = [];
if ($recent_result) {
    while ($row = mysqli_fetch_assoc($recent_result)) {
        $recent_reservasi[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SASUKI BBQ</title>
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

        /* Header */
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

        .header-left h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header-left p {
            font-size: 14px;
            opacity: 0.9;
        }

        .header-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .time {
            font-size: 14px;
            opacity: 0.9;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid white;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        /* Navigation */
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

        nav a:hover {
            background: #f5f5f5;
            color: #667eea;
        }

        nav a.active {
            background: #667eea;
            color: white;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .stat-card.purple {
            border-left-color: #667eea;
        }

        .stat-card.yellow {
            border-left-color: #f39c12;
        }

        .stat-card.green {
            border-left-color: #27ae60;
        }

        .stat-card.red {
            border-left-color: #e74c3c;
        }

        .stat-card.blue {
            border-left-color: #2196F3;
        }

        .stat-card.pink {
            border-left-color: #e91e63;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .stat-change {
            font-size: 12px;
            color: #7f8c8d;
        }

        .stat-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }

        /* Content Section */
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

        .section-header a {
            background: #667eea;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .section-header a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-verified {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-no-payment {
            background: #e2e3e5;
            color: #383d41;
        }

        .action-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #666;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
            }

            .header-left, .header-right {
                width: 100%;
            }

            .header-right {
                justify-content: space-between;
            }

            nav {
                flex-direction: column;
                gap: 0;
            }

            nav a {
                width: 100%;
                padding: 12px 15px;
                border-radius: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
            }

            .action-btn {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <div class="header-left">
                <h1>🍖 SASUKI BBQ Admin Dashboard</h1>
                <p>Kelola reservasi dan verifikasi pembayaran</p>
            </div>
            <div class="header-right">
                <div class="time">
                    <span id="current-time"></span>
                </div>
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="action" value="logout" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">Logout</button>
                </form>
            </div>
        </header>

        <!-- Navigation -->
        <nav>
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <a href="reservasi.php">📋 Semua Reservasi</a>
            <a href="verifikasi_bukti.php">✅ Verifikasi Pembayaran</a>
            <a href="laporan.php">📈 Laporan</a>
            <a href="settings.php">⚙️ Pengaturan</a>
        </nav>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-icon">📊</div>
                <div class="stat-label">Total Reservasi</div>
                <div class="stat-value"><?php echo $stats['total_reservasi']; ?></div>
            </div>

            <div class="stat-card yellow">
                <div class="stat-icon">⏳</div>
                <div class="stat-label">Menunggu Verifikasi</div>
                <div class="stat-value"><?php echo $stats['pending_payment']; ?></div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">✅</div>
                <div class="stat-label">Terverifikasi</div>
                <div class="stat-value"><?php echo $stats['verified_payment']; ?></div>
            </div>

            <div class="stat-card red">
                <div class="stat-icon">❌</div>
                <div class="stat-label">Ditolak</div>
                <div class="stat-value"><?php echo $stats['rejected_payment']; ?></div>
            </div>

            <div class="stat-card blue">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Pendapatan Hari Ini</div>
                <div class="stat-value">Rp <?php echo number_format($stats['today_revenue'], 0, ',', '.'); ?></div>
            </div>

            <div class="stat-card pink">
                <div class="stat-icon">💵</div>
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value">Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></div>
            </div>
        </div>

        <!-- Recent Reservasi -->
        <div class="content-section">
            <div class="section-header">
                <h2>📋 Reservasi Terbaru</h2>
                <a href="reservasi.php">Lihat Semua →</a>
            </div>

            <?php if (!empty($recent_reservasi)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Tanggal & Jam</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_reservasi as $res): 
                        if ($res['bukti_verified'] == 0 && is_null($res['bukti_pembayaran'])) {
                            $status_class = 'status-no-payment';
                            $status_text = '📋 Belum Bayar';
                        } elseif ($res['bukti_verified'] == 0) {
                            $status_class = 'status-pending';
                            $status_text = '⏳ Pending';
                        } elseif ($res['bukti_verified'] == 1) {
                            $status_class = 'status-verified';
                            $status_text = '✅ Verified';
                        } else {
                            $status_class = 'status-rejected';
                            $status_text = '❌ Rejected';
                        }
                    ?>
                    <tr>
                        <td>#<?php echo $res['id']; ?></td>
                        <td><?php echo htmlspecialchars($res['nama_pelanggan']); ?></td>
                        <td><?php echo formatTanggal($res['tanggal']) . ' - ' . $res['jam']; ?></td>
                        <td><?php echo $res['jumlah_orang']; ?> orang</td>
                        <td>Rp <?php echo number_format($res['total'], 0, ',', '.'); ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <button class="action-btn" onclick="alert('Detail untuk reservasi #<?php echo $res['id']; ?>')">Detail</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <h3>📭 Belum ada reservasi</h3>
                <p>Tidak ada data reservasi untuk ditampilkan.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="content-section">
            <div class="section-header">
                <h2>⚡ Aksi Cepat</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="verifikasi_bukti.php" style="background: #27ae60; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: all 0.3s;">
                    ✅ Verifikasi Pembayaran
                </a>
                <a href="reservasi.php" style="background: #2196F3; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: all 0.3s;">
                    📋 Lihat Semua Reservasi
                </a>
                <a href="laporan.php" style="background: #f39c12; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: all 0.3s;">
                    📈 Laporan Penjualan
                </a>
                <a href="settings.php" style="background: #667eea; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: all 0.3s;">
                    ⚙️ Pengaturan Admin
                </a>
            </div>
        </div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('current-time').textContent = now.toLocaleDateString('id-ID', options);
        }

        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
