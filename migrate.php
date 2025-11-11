<?php
/**
 * Database Migration/Update Script
 * Jalankan script ini untuk update struktur tabel database
 * 
 * Akses: http://localhost/sasuki_app/migrate.php
 */

include 'config/koneksi.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Migration - SASUKI BBQ</title>
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
            max-width: 700px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-top: 0;
        }
        .status {
            padding: 15px;
            margin: 15px 0;
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
        .status.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
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
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        button {
            padding: 12px 24px;
            background: #c0392b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            margin: 5px;
        }
        button:hover {
            background: #a93226;
        }
        .table-struct {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #e9ecef;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class='container'>";

if (!$koneksi) {
    echo "
        <h1>❌ Database Error</h1>
        <div class='status error'>
            " . mysqli_connect_error() . "
        </div>
    </div>
</body>
</html>";
    exit;
}

// Check action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// List of migrations
$migrations = [
    'add_metode_pembayaran' => [
        'name' => 'Tambah kolom metode_pembayaran',
        'description' => 'Menambahkan kolom untuk menyimpan metode pembayaran',
        'sql' => "ALTER TABLE reservasi ADD COLUMN metode_pembayaran VARCHAR(50) AFTER status"
    ],
    'add_bukti_pembayaran' => [
        'name' => 'Tambah kolom bukti pembayaran',
        'description' => 'Menambahkan kolom untuk menyimpan path bukti dan status verifikasi',
        'sql' => [
            "ALTER TABLE reservasi ADD COLUMN bukti_pembayaran VARCHAR(255) AFTER metode_pembayaran",
            "ALTER TABLE reservasi ADD COLUMN bukti_verified INT DEFAULT 0 AFTER bukti_pembayaran"
        ]
    ],
    'add_timestamps' => [
        'name' => 'Tambah kolom timestamps',
        'description' => 'Menambahkan created_at dan updated_at',
        'sql' => [
            "ALTER TABLE reservasi ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "ALTER TABLE reservasi ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ]
    ]
];

// Execute migration if requested
if ($action && isset($migrations[$action])) {
    $migration = $migrations[$action];
    
    echo "<h1>⚙️ Menjalankan Migration</h1>";
    echo "<div class='status info'><strong>" . htmlspecialchars($migration['name']) . "</strong></div>";
    
    $sqls = is_array($migration['sql']) ? $migration['sql'] : [$migration['sql']];
    $success_count = 0;
    $error_count = 0;
    
    foreach ($sqls as $sql) {
        if (mysqli_query($koneksi, $sql)) {
            echo "<div class='status success'>✅ Query berhasil: <code>" . htmlspecialchars(substr($sql, 0, 50)) . "...</code></div>";
            $success_count++;
        } else {
            $error = mysqli_error($koneksi);
            // Cek apakah error karena kolom sudah ada (bukan error serius)
            if (strpos($error, 'Duplicate column') !== false || strpos($error, 'already exists') !== false) {
                echo "<div class='status warning'>⚠️ Kolom sudah ada: <code>" . htmlspecialchars(substr($sql, 0, 50)) . "...</code></div>";
                $success_count++;
            } else {
                echo "<div class='status error'>❌ Error: " . htmlspecialchars($error) . "</div>";
                $error_count++;
            }
        }
    }
    
    echo "<div class='actions'>";
    if ($error_count === 0) {
        echo "<div class='status success'><strong>✅ Migration berhasil dijalankan!</strong></div>";
    }
    echo "<a href='migrate.php' style='display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: 600;'>← Kembali ke List Migration</a>";
    echo "</div>";
    
    echo "</div></body></html>";
    exit;
}

// Show list of available migrations
echo "
    <h1>🔧 Database Migration</h1>
    
    <div class='status info'>
        Gunakan tool ini untuk update struktur database Anda.
    </div>";

// Check current table structure
$columnsResult = mysqli_query($koneksi, "DESCRIBE reservasi");
$existingColumns = [];
while ($col = mysqli_fetch_assoc($columnsResult)) {
    $existingColumns[] = $col['Field'];
}

echo "
    <h2>📊 Struktur Tabel Saat Ini</h2>
    <div class='table-struct'>
        <table>
            <tr>
                <th>Kolom</th>
                <th>Type</th>
                <th>Null</th>
                <th>Key</th>
            </tr>";

$columnsResult = mysqli_query($koneksi, "DESCRIBE reservasi");
while ($col = mysqli_fetch_assoc($columnsResult)) {
    echo "
            <tr>
                <td><code>" . htmlspecialchars($col['Field']) . "</code></td>
                <td>" . htmlspecialchars($col['Type']) . "</td>
                <td>" . ($col['Null'] === 'YES' ? 'YES' : 'NO') . "</td>
                <td>" . ($col['Key'] ?: '-') . "</td>
            </tr>";
}

echo "
        </table>
    </div>";

// Show available migrations
echo "
    <h2>📋 Available Migrations</h2>";

foreach ($migrations as $key => $migration) {
    $isApplied = false;
    
    // Check if migration is already applied
    if ($key === 'add_metode_pembayaran') {
        $isApplied = in_array('metode_pembayaran', $existingColumns);
    } elseif ($key === 'add_timestamps') {
        $isApplied = in_array('created_at', $existingColumns);
    }
    
    $status_class = $isApplied ? 'success' : 'warning';
    $status_text = $isApplied ? '✅ Applied' : '⚠️ Pending';
    
    echo "
    <div class='status " . $status_class . "'>
        <strong>" . htmlspecialchars($migration['name']) . "</strong>
        <p>" . htmlspecialchars($migration['description']) . "</p>
        <p style='margin: 0; font-size: 0.9em;'><code>" . htmlspecialchars(is_array($migration['sql']) ? $migration['sql'][0] : $migration['sql']) . "</code></p>
        <p style='margin: 5px 0 0 0;'>" . $status_text . "</p>";
    
    if (!$isApplied) {
        echo "
        <a href='?action=" . htmlspecialchars($key) . "' style='display: inline-block; padding: 8px 16px; background: #c0392b; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; margin-top: 10px;'>Jalankan Migration</a>";
    }
    
    echo "
    </div>";
}

echo "
    <div class='actions'>
        <a href='index.php' style='display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: 600;'>← Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>";
?>
