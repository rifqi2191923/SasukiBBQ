<?php
include '../config/koneksi.php';
include '../config/helpers.php';
include '../config/wa_config.php';

date_default_timezone_set('Asia/Jakarta');

session_start();

// Check admin access
if (!isset($_SESSION['admin_verified'])) {
    header('Location: dashboard.php');
    exit;
}

// Get all reservasi
$query = "SELECT id, nama_pelanggan, telepon, jumlah_orang, metode_pembayaran, bukti_verified, 
                  tanggal, jam, (jumlah_orang * 50000) as total
          FROM reservasi 
          ORDER BY tanggal DESC, jam DESC";
$result = mysqli_query($koneksi, $query);
$reservasi_list = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reservasi_list[] = $row;
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['reservasi_id']);
    $delete_query = "DELETE FROM reservasi WHERE id = $id";
    if (mysqli_query($koneksi, $delete_query)) {
        echo "<script>alert('Reservasi deleted!'); location.reload();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Reservasi - Admin SASUKI BBQ</title>
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

        .header-left h1 {
            font-size: 28px;
            margin-bottom: 5px;
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
            transform: translateY(-2px);
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

        nav a:hover {
            background: #f5f5f5;
            color: #667eea;
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

        .filter-group {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-group select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
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
            margin-right: 5px;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #764ba2;
        }

        .action-btn.danger {
            background: #e74c3c;
        }

        .action-btn.danger:hover {
            background: #c0392b;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #666;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-decoration: none;
            color: #667eea;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
        }

        .pagination span.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
            }

            .header-right {
                width: 100%;
                justify-content: space-between;
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
                margin-right: 3px;
            }

            .filter-group {
                flex-direction: column;
            }

            .filter-group select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-left">
                <h1>📋 Semua Reservasi</h1>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="back-btn">← Kembali</a>
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="action" value="logout" class="logout-btn" onclick="return confirm('Logout?')">Logout</button>
                </form>
            </div>
        </header>

        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="reservasi.php" class="active">📋 Semua Reservasi</a>
            <a href="verifikasi_bukti.php">✅ Verifikasi Pembayaran</a>
            <a href="laporan.php">📈 Laporan</a>
            <a href="settings.php">⚙️ Pengaturan</a>
        </nav>

        <div class="content-section">
            <div class="section-header">
                <h2>Total: <?php echo count($reservasi_list); ?> Reservasi</h2>
            </div>

            <div class="filter-group">
                <select onchange="filterTable(this.value)" id="statusFilter">
                    <option value="">📊 Semua Status</option>
                    <option value="verified">✅ Terverifikasi</option>
                    <option value="pending">⏳ Pending</option>
                    <option value="rejected">❌ Ditolak</option>
                    <option value="no-payment">📋 Belum Bayar</option>
                </select>
            </div>

            <?php if (!empty($reservasi_list)): ?>
            <table id="reservasiTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Tanggal & Jam</th>
                        <th>Jumlah Orang</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservasi_list as $res):
                        if ($res['bukti_verified'] == 0 && is_null($res['bukti_pembayaran'])) {
                            $status_class = 'status-no-payment';
                            $status_text = '📋 Belum Bayar';
                            $status_filter = 'no-payment';
                        } elseif ($res['bukti_verified'] == 0) {
                            $status_class = 'status-pending';
                            $status_text = '⏳ Pending';
                            $status_filter = 'pending';
                        } elseif ($res['bukti_verified'] == 1) {
                            $status_class = 'status-verified';
                            $status_text = '✅ Verified';
                            $status_filter = 'verified';
                        } else {
                            $status_class = 'status-rejected';
                            $status_text = '❌ Rejected';
                            $status_filter = 'rejected';
                        }
                    ?>
                    <tr data-status="<?php echo $status_filter; ?>">
                        <td>#<?php echo $res['id']; ?></td>
                        <td><?php echo htmlspecialchars($res['nama_pelanggan']); ?></td>
                        <td><?php echo $res['telepon']; ?></td>
                        <td><?php echo formatTanggal($res['tanggal']) . ' - ' . $res['jam']; ?></td>
                        <td><?php echo $res['jumlah_orang']; ?> orang</td>
                        <td>Rp <?php echo number_format($res['total'], 0, ',', '.'); ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <button class="action-btn" onclick="alert('Detail reservasi #<?php echo $res['id']; ?>\\nNama: <?php echo htmlspecialchars($res['nama_pelanggan']); ?>\\nTotal: Rp <?php echo number_format($res['total'], 0, ',', '.'); ?>')">Detail</button>
                            <form method="POST" style="display: inline; margin: 0;">
                                <input type="hidden" name="reservasi_id" value="<?php echo $res['id']; ?>">
                                <button type="submit" name="action" value="delete" class="action-btn danger" onclick="return confirm('Yakin hapus?')">Hapus</button>
                            </form>
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
    </div>

    <script>
        function filterTable(status) {
            const table = document.getElementById('reservasiTable');
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                if (status === '' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
