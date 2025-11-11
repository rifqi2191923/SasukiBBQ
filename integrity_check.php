<?php
/**
 * Integration Checker & Seamless Setup Helper
 * SASUKI BBQ Payment Flow v2.0
 * 
 * This file ensures all components work together seamlessly
 * Check this to ensure zero errors during payment flow
 */

include 'config/koneksi.php';

// =============================================
// 1. DATABASE INTEGRITY CHECK
// =============================================
function checkDatabaseSchema() {
    global $koneksi;
    
    $issues = [];
    $fixes = [];
    
    // Check if reservasi table exists
    $result = mysqli_query($koneksi, "SHOW TABLES LIKE 'reservasi'");
    if (mysqli_num_rows($result) == 0) {
        $issues[] = "❌ Tabel 'reservasi' tidak ditemukan";
        $fixes[] = "Jalankan database migration di: migrate.php";
        return ['issues' => $issues, 'fixes' => $fixes, 'status' => 'error'];
    }
    
    // Check required columns
    $columns = mysqli_query($koneksi, "DESC reservasi");
    $col_names = [];
    while ($row = mysqli_fetch_assoc($columns)) {
        $col_names[] = $row['Field'];
    }
    
    // Check essential columns
    $required = ['id', 'nama_pelanggan', 'telepon', 'jumlah_orang', 'tanggal', 'jam', 'status'];
    foreach ($required as $col) {
        if (!in_array($col, $col_names)) {
            $issues[] = "❌ Kolom '$col' tidak ditemukan";
        }
    }
    
    // Check v2.0 columns (new features)
    $v2_columns = ['bukti_pembayaran', 'bukti_verified'];
    $missing_v2 = [];
    foreach ($v2_columns as $col) {
        if (!in_array($col, $col_names)) {
            $missing_v2[] = $col;
        }
    }
    
    if (!empty($missing_v2)) {
        $issues[] = "⚠️  Kolom v2.0 belum ditambahkan: " . implode(', ', $missing_v2);
        $fixes[] = "Jalankan migration untuk menambah kolom: go to migrate.php";
    }
    
    if (empty($issues)) {
        return ['issues' => [], 'status' => 'ok'];
    }
    
    return ['issues' => $issues, 'fixes' => $fixes, 'status' => 'warning'];
}

// =============================================
// 2. FILE STRUCTURE CHECK
// =============================================
function checkFileStructure() {
    $issues = [];
    
    $required_files = [
        'reservasi/index.php' => 'Reservation form',
        'reservasi/pembayaran.php' => 'Payment selection page',
        'reservasi/upload_bukti.php' => 'Proof upload page (v2.0)',
        'reservasi/sukses.php' => 'Success page',
        'admin/verifikasi_bukti.php' => 'Admin verification (v2.0)',
        'config/koneksi.php' => 'Database connection',
        'config/wa_config.php' => 'WhatsApp config',
        'config/helpers.php' => 'Helper functions',
        'migrate.php' => 'Database migration'
    ];
    
    foreach ($required_files as $file => $desc) {
        if (!file_exists($file)) {
            $issues[] = "❌ File tidak ditemukan: $file ($desc)";
        }
    }
    
    $required_dirs = [
        'bukti_pembayaran' => 'Proof storage',
        'admin' => 'Admin pages',
        'config' => 'Configuration',
        'reservasi' => 'Reservation pages'
    ];
    
    foreach ($required_dirs as $dir => $desc) {
        if (!is_dir($dir)) {
            $issues[] = "❌ Folder tidak ditemukan: $dir ($desc)";
        }
    }
    
    // Check .htaccess in bukti_pembayaran
    if (is_dir('bukti_pembayaran') && !file_exists('bukti_pembayaran/.htaccess')) {
        $issues[] = "⚠️  File keamanan hilang: bukti_pembayaran/.htaccess";
    }
    
    if (empty($issues)) {
        return ['issues' => [], 'status' => 'ok'];
    }
    
    return ['issues' => $issues, 'status' => 'warning'];
}

// =============================================
// 3. PAYMENT FLOW CONTINUITY CHECK
// =============================================
function checkPaymentFlowContinuity() {
    $issues = [];
    $warnings = [];
    
    // Check pembayaran.php form action
    $pembayaran_content = file_get_contents('reservasi/pembayaran.php');
    if (strpos($pembayaran_content, 'upload_bukti.php') === false) {
        $issues[] = "❌ pembayaran.php tidak redirect ke upload_bukti.php";
    }
    
    // Check upload_bukti.php redirect
    $upload_content = file_get_contents('reservasi/upload_bukti.php');
    if (strpos($upload_content, 'sukses.php') === false) {
        $issues[] = "❌ upload_bukti.php tidak redirect ke sukses.php";
    }
    
    // Check for database update statements
    if (strpos($upload_content, 'UPDATE reservasi') === false) {
        $issues[] = "❌ upload_bukti.php tidak update database";
    }
    
    // Check for bukti_pembayaran column update
    if (strpos($upload_content, 'bukti_pembayaran') === false) {
        $warnings[] = "⚠️  upload_bukti.php tidak save bukti file path";
    }
    
    if (empty($issues) && empty($warnings)) {
        return ['issues' => [], 'status' => 'ok'];
    }
    
    $result = ['status' => 'warning'];
    if (!empty($issues)) {
        $result['issues'] = $issues;
        $result['status'] = 'error';
    }
    if (!empty($warnings)) {
        $result['warnings'] = $warnings;
    }
    
    return $result;
}

// =============================================
// 4. WHATSAPP INTEGRATION CHECK
// =============================================
function checkWhatsAppIntegration() {
    $issues = [];
    
    if (!file_exists('config/wa_config.php')) {
        $issues[] = "❌ File WhatsApp config tidak ditemukan";
        return ['issues' => $issues, 'status' => 'error'];
    }
    
    $wa_content = file_get_contents('config/wa_config.php');
    
    // Check for required functions
    $required_functions = ['sendWhatsAppMessage', 'normalizePhoneNumber', 'getMessageTemplate'];
    foreach ($required_functions as $func) {
        if (strpos($wa_content, "function $func") === false) {
            $issues[] = "❌ Function '$func' tidak ditemukan di wa_config.php";
        }
    }
    
    // Check for API token (should be configured by user)
    if (strpos($wa_content, 'FONTRE_TOKEN') === false && 
        strpos($wa_content, 'YOUR_TOKEN') !== false) {
        $issues[] = "⚠️  WhatsApp token belum dikonfigurasi di config/wa_config.php";
    }
    
    if (empty($issues)) {
        return ['issues' => [], 'status' => 'ok'];
    }
    
    return ['issues' => $issues, 'status' => 'warning'];
}

// =============================================
// 5. FOLDER PERMISSIONS CHECK
// =============================================
function checkPermissions() {
    $issues = [];
    
    $writable_dirs = ['bukti_pembayaran', 'logs'];
    foreach ($writable_dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if (!is_writable($dir)) {
            $issues[] = "⚠️  Folder '$dir' tidak writable (chmod 755)";
        }
    }
    
    if (empty($issues)) {
        return ['issues' => [], 'status' => 'ok'];
    }
    
    return ['issues' => $issues, 'status' => 'warning'];
}

// =============================================
// RUN ALL CHECKS
// =============================================
function runAllChecks() {
    $results = [
        'database' => checkDatabaseSchema(),
        'files' => checkFileStructure(),
        'flow' => checkPaymentFlowContinuity(),
        'whatsapp' => checkWhatsAppIntegration(),
        'permissions' => checkPermissions()
    ];
    
    return $results;
}

// =============================================
// AUTO-FIX COMMON ISSUES
// =============================================
function autoFixIssues() {
    $fixes_applied = [];
    
    // Create missing directories
    $dirs = ['bukti_pembayaran', 'logs', 'admin'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $fixes_applied[] = "✅ Created directory: $dir";
        }
    }
    
    // Create .htaccess if missing
    if (!file_exists('bukti_pembayaran/.htaccess')) {
        $htaccess = <<<'HTACCESS'
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>

<FilesMatch "\.(jpg|jpeg|png|gif)$">
    Allow from all
</FilesMatch>

Options -Indexes
HTACCESS;
        file_put_contents('bukti_pembayaran/.htaccess', $htaccess);
        $fixes_applied[] = "✅ Created .htaccess in bukti_pembayaran";
    }
    
    return $fixes_applied;
}

// =============================================
// DISPLAY RESULTS
// =============================================
if (php_sapi_name() === 'cli') {
    // Command line output
    echo "\n=== SASUKI BBQ v2.0 - INTEGRATION CHECK ===\n\n";
    
    $checks = runAllChecks();
    
    foreach ($checks as $name => $result) {
        echo ucfirst($name) . " Check: ";
        echo ($result['status'] === 'ok') ? "✅ OK\n" : "⚠️  " . strtoupper($result['status']) . "\n";
        
        if (!empty($result['issues'])) {
            foreach ($result['issues'] as $issue) {
                echo "  " . $issue . "\n";
            }
        }
    }
    
    echo "\n";
} else {
    // Web interface
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health Check - SASUKI BBQ v2.0</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 40px; }
        .check-group {
            margin: 30px 0;
            padding: 20px;
            border-left: 4px solid #3498db;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .check-title {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .status-ok { color: #27ae60; font-weight: bold; }
        .status-warning { color: #f39c12; font-weight: bold; }
        .status-error { color: #e74c3c; font-weight: bold; }
        .issue-item {
            padding: 8px 0;
            margin: 5px 0;
            padding-left: 15px;
            border-left: 2px solid #e74c3c;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 20px;
        }
        button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 SASUKI BBQ v2.0 - System Health Check</h1>
        
        <?php
        $checks = runAllChecks();
        
        foreach ($checks as $name => $result) {
            $status_class = $result['status'] === 'ok' ? 'status-ok' : ($result['status'] === 'error' ? 'status-error' : 'status-warning');
            $status_text = $result['status'] === 'ok' ? '✅ OK' : ($result['status'] === 'error' ? '❌ ERROR' : '⚠️ WARNING');
            
            echo "<div class='check-group'>";
            echo "<div class='check-title'>" . ucfirst($name) . " Check: <span class='$status_class'>$status_text</span></div>";
            
            if (!empty($result['issues'])) {
                echo "<div>";
                foreach ($result['issues'] as $issue) {
                    echo "<div class='issue-item'>$issue</div>";
                }
                echo "</div>";
            }
            
            if (!empty($result['fixes'])) {
                echo "<div style='margin-top: 10px; padding: 10px; background: #fef5e7; border-left: 3px solid #f39c12;'>";
                echo "<strong>💡 Suggestions:</strong><br>";
                foreach ($result['fixes'] as $fix) {
                    echo "• $fix<br>";
                }
                echo "</div>";
            }
            
            echo "</div>";
        }
        ?>
        
        <form method="POST" style="text-align: center;">
            <button type="submit" name="action" value="autofix">🔧 Auto-Fix Common Issues</button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'autofix') {
            $fixes = autoFixIssues();
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
            echo "<h3>✅ Applied Fixes:</h3>";
            foreach ($fixes as $fix) {
                echo "<p>$fix</p>";
            }
            echo "<p><a href='integrity_check.php' style='color: #0c5460;'>🔄 Refresh Check</a></p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
<?php
}
?>
