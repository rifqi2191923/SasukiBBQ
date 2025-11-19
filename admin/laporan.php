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

// Get filter parameters
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$filter_month = isset($_GET['filter_month']) ? $_GET['filter_month'] : date('Y-m');
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'daily';

// Get daily data
$daily_stats = [];
$query = "SELECT DATE(tanggal) as date, 
                 COUNT(*) as total_reservasi,
                 SUM(CASE WHEN bukti_verified = 1 THEN 1 ELSE 0 END) as verified_count,
                 SUM(CASE WHEN bukti_verified = 1 THEN jumlah_orang * 50000 ELSE 0 END) as daily_revenue
          FROM reservasi 
          WHERE bukti_verified = 1
          GROUP BY DATE(tanggal)
          ORDER BY date DESC";
$result = mysqli_query($koneksi, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $daily_stats[] = $row;
    }
}

// Get monthly data
$monthly_stats = [];
$query = "SELECT DATE_FORMAT(tanggal, '%Y-%m') as month,
                 COUNT(*) as total_reservasi,
                 SUM(CASE WHEN bukti_verified = 1 THEN 1 ELSE 0 END) as verified_count,
                 SUM(CASE WHEN bukti_verified = 1 THEN jumlah_orang * 50000 ELSE 0 END) as monthly_revenue
          FROM reservasi 
          WHERE bukti_verified = 1
          GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
          ORDER BY month DESC";
$result = mysqli_query($koneksi, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $monthly_stats[] = $row;
    }
}

// Get top customers
$top_customers = [];
$query = "SELECT nama_pelanggan, COUNT(*) as booking_count, 
                 SUM(jumlah_orang * 50000) as total_spent
          FROM reservasi 
          WHERE bukti_verified = 1
          GROUP BY nama_pelanggan
          ORDER BY total_spent DESC
          LIMIT 10";
$result = mysqli_query($koneksi, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $top_customers[] = $row;
    }
}

// Overall statistics
$overall_stats = [
    'total_revenue' => 0,
    'total_reservasi' => 0,
    'avg_per_reservasi' => 0,
    'total_customers' => 0
];

$query = "SELECT 
          SUM(CASE WHEN bukti_verified = 1 THEN jumlah_orang * 50000 ELSE 0 END) as total_revenue,
          COUNT(*) as total_reservasi,
          COUNT(DISTINCT nama_pelanggan) as total_customers
          FROM reservasi";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $overall_stats['total_revenue'] = $row['total_revenue'] ?? 0;
    $overall_stats['total_reservasi'] = $row['total_reservasi'] ?? 0;
    $overall_stats['total_customers'] = $row['total_customers'] ?? 0;
    if ($row['total_reservasi'] > 0) {
        $overall_stats['avg_per_reservasi'] = $row['total_revenue'] / $row['total_reservasi'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Admin SASUKI BBQ</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            border-left: 4px solid;
        }

        .stat-card.blue { border-left-color: #2196F3; }
        .stat-card.green { border-left-color: #27ae60; }
        .stat-card.orange { border-left-color: #f39c12; }
        .stat-card.purple { border-left-color: #667eea; }

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

        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }

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

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📈 Laporan Penjualan</h1>
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
            <a href="laporan.php" class="active">📈 Laporan</a>
            <a href="settings.php">⚙️ Pengaturan</a>
        </nav>

        <!-- Overall Statistics -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">💰 Total Pendapatan</div>
                <div class="stat-value">Rp <?php echo number_format($overall_stats['total_revenue'], 0, ',', '.'); ?></div>
            </div>

            <div class="stat-card green">
                <div class="stat-label">📋 Total Reservasi</div>
                <div class="stat-value"><?php echo $overall_stats['total_reservasi']; ?></div>
            </div>

            <div class="stat-card orange">
                <div class="stat-label">👥 Total Pelanggan</div>
                <div class="stat-value"><?php echo $overall_stats['total_customers']; ?></div>
            </div>

            <div class="stat-card purple">
                <div class="stat-label">💵 Rata-rata per Reservasi</div>
                <div class="stat-value">Rp <?php echo number_format($overall_stats['avg_per_reservasi'], 0, ',', '.'); ?></div>
            </div>
        </div>

        <!-- Daily Revenue Chart -->
        <div class="content-section">
            <div class="section-header">
                <h2>📊 Pendapatan Harian (7 Hari Terakhir)</h2>
            </div>
            <div class="chart-container">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="content-section">
            <div class="section-header">
                <h2>📅 Pendapatan Bulanan</h2>
            </div>

            <?php if (!empty($monthly_stats)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Total Reservasi</th>
                        <th>Terverifikasi</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_stats as $month): ?>
                    <tr>
                        <td><?php echo formatBulanTahun($month['month']); ?></td>
                        <td><?php echo $month['total_reservasi']; ?></td>
                        <td><?php echo $month['verified_count']; ?></td>
                        <td><strong>Rp <?php echo number_format($month['monthly_revenue'], 0, ',', '.'); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <p>Belum ada data untuk ditampilkan</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Top Customers -->
        <div class="content-section">
            <div class="section-header">
                <h2>⭐ 10 Pelanggan Terbaik</h2>
            </div>

            <?php if (!empty($top_customers)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Jumlah Booking</th>
                        <th>Total Belanja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['nama_pelanggan']); ?></td>
                        <td><?php echo $customer['booking_count']; ?>x</td>
                        <td><strong>Rp <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <p>Belum ada data untuk ditampilkan</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Daily Chart
        const dailyData = <?php echo json_encode($daily_stats); ?>;
        const dates = dailyData.slice(0, 7).reverse().map(d => new Date(d.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' }));
        const revenues = dailyData.slice(0, 7).reverse().map(d => d.daily_revenue || 0);

        const ctx = document.getElementById('dailyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Pendapatan Harian',
                    data: revenues,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
function formatBulanTahun($bulan) {
    $parts = explode('-', $bulan);
    $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $bulanIndo[$parts[1]] . ' ' . $parts[0];
}
?>
