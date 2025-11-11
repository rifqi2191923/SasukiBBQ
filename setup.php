<?php
/**
 * Database Setup Script
 * Jalankan script ini sekali untuk setup database dan tabel
 * 
 * Akses: http://localhost/sasuki_app/setup.php
 */

// Include koneksi
include 'config/koneksi.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Setup - SASUKI BBQ</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            max-width: 800px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-top: 0;
        }
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .status.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .status.info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        button, a {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #c0392b;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover, a:hover {
            background: #a93226;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f4f4f4;
            font-weight: 600;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class='container'>";

// Check database connection
if (!$koneksi) {
    echo "
        <h1>❌ Setup Gagal</h1>
        <div class='status error'>
            <strong>Koneksi Database Error:</strong><br>
            " . mysqli_connect_error() . "
        </div>
        <div class='status info'>
            Edit file <code>config/koneksi.php</code> dengan parameter database yang benar:
            <pre>
\$host = 'localhost';
\$user = 'root';
\$pass = '';           // Password MySQL
\$db   = 'sasuki_db'; // Nama database</pre>
        </div>
    </div>
</body>
</html>";
    exit;
}

// Check if setup action is requested
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'create_table') {
    // SQL untuk membuat tabel
    $sql = "CREATE TABLE IF NOT EXISTS `reservasi` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nama_pelanggan` VARCHAR(100) NOT NULL,
        `telepon` VARCHAR(20) NOT NULL,
        `tanggal` DATE NOT NULL,
        `jam` TIME NOT NULL,
        `jumlah_orang` INT NOT NULL,
        `status` ENUM('pending', 'dibayar', 'dikonfirmasi', 'selesai', 'batal') DEFAULT 'pending',
        `metode_pembayaran` VARCHAR(50),
        `catatan` TEXT,
        `kode_meja` VARCHAR(10),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_tanggal` (`tanggal`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if (mysqli_query($koneksi, $sql)) {
        echo "
            <h1>✅ Setup Berhasil!</h1>
            <div class='status success'>
                <strong>Tabel <code>reservasi</code> berhasil dibuat!</strong>
            </div>";
    } else {
        echo "
            <h1>⚠️ Tabel Sudah Ada</h1>
            <div class='status info'>
                Tabel <code>reservasi</code> sudah ada di database.
            </div>";
    }

    echo "
        <div class='status success'>
            Database siap digunakan. Silakan mulai gunakan aplikasi!
        </div>
        <div class='action-buttons'>
            <a href='reservasi/'>Mulai Reservasi →</a>
            <a href='setup.php'>Kembali ke Menu Setup</a>
        </div>
    </div>
</body>
</html>";
    exit;
}

// Show setup menu
echo "
    <h1>🍖 SASUKI BBQ - Database Setup</h1>
    
    <div class='status success'>
        <strong>✅ Database Connected!</strong><br>
        Database yang terkoneksi: <code>" . htmlspecialchars(mysqli_get_server_info($koneksi)) . "</code>
    </div>";

// Check if table exists
$tables_result = mysqli_query($koneksi, "SHOW TABLES LIKE 'reservasi'");
$table_exists = mysqli_num_rows($tables_result) > 0;

if ($table_exists) {
    echo "
        <div class='status success'>
            <strong>✅ Tabel <code>reservasi</code> sudah ada!</strong>
        </div>";
    
    // Show table structure
    $columns = mysqli_query($koneksi, "DESCRIBE reservasi");
    echo "
        <h3>Struktur Tabel:</h3>
        <table>
            <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Null</th>
                <th>Key</th>
                <th>Default</th>
            </tr>";
    
    while ($col = mysqli_fetch_assoc($columns)) {
        echo "
            <tr>
                <td>" . htmlspecialchars($col['Field']) . "</td>
                <td><code>" . htmlspecialchars($col['Type']) . "</code></td>
                <td>" . ($col['Null'] === 'YES' ? 'YES' : 'NO') . "</td>
                <td>" . ($col['Key'] ?: '-') . "</td>
                <td>" . htmlspecialchars($col['Default'] ?? '-') . "</td>
            </tr>";
    }
    echo "</table>";
    
    // Show record count
    $count_result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM reservasi");
    $count = mysqli_fetch_assoc($count_result)['total'];
    
    echo "
        <div class='status info'>
            <strong>Total Reservasi:</strong> " . $count . " data
        </div>";

} else {
    echo "
        <div class='status error'>
            <strong>❌ Tabel <code>reservasi</code> belum ada!</strong>
        </div>
        
        <div class='status info'>
            Klik tombol di bawah untuk membuat tabel otomatis.
        </div>";
}

echo "
    <h3>Checklist Setup:</h3>
    <table>
        <tr>
            <td>✅ Database Connection</td>
            <td>OK</td>
        </tr>
        <tr>
            <td>" . ($table_exists ? "✅" : "❌") . " Tabel reservasi</td>
            <td>" . ($table_exists ? "OK" : "PERLU DIBUAT") . "</td>
        </tr>
    </table>
    
    <h3>Next Steps:</h3>
    <ol>
        <li>Pastikan folder <code>logs</code> sudah dibuat dan writable</li>
        <li>Edit file <code>config/wa_config.php</code> dengan token Fonnte Anda</li>
        <li>Test WhatsApp configuration</li>
        <li>Mulai gunakan aplikasi</li>
    </ol>
    
    <div class='action-buttons'>";

if (!$table_exists) {
    echo "<button onclick='location.href=\"?action=create_table\"'>Buat Tabel Reservasi</button>";
}

echo "
        <a href='reservasi/'>Buka Aplikasi →</a>
    </div>
    
    <h3>Informasi Teknis:</h3>
    <ul>
        <li><strong>Server:</strong> " . htmlspecialchars($_SERVER['SERVER_SOFTWARE']) . "</li>
        <li><strong>PHP Version:</strong> " . phpversion() . "</li>
        <li><strong>MySQL Version:</strong> " . htmlspecialchars(mysqli_get_server_info($koneksi)) . "</li>
    </ul>

</div>
</body>
</html>";

?>
