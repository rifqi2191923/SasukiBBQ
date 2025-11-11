<?php
include '../config/koneksi.php';
include '../config/wa_config.php';

date_default_timezone_set('Asia/Jakarta');

// Simple authentication (ganti dengan sistem login yang lebih baik)
$admin_password = 'admin123'; // GANTI DENGAN PASSWORD YANG LEBIH KUAT!
session_start();

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
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    margin: 0;
                    padding: 0;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .login-container {
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                    max-width: 400px;
                    width: 90%;
                }
                h1 {
                    color: #2c3e50;
                    text-align: center;
                    margin-top: 0;
                }
                .form-group {
                    margin-bottom: 20px;
                }
                label {
                    display: block;
                    margin-bottom: 8px;
                    color: #495057;
                    font-weight: 600;
                }
                input {
                    width: 100%;
                    padding: 12px 15px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    font-size: 1em;
                    box-sizing: border-box;
                }
                input:focus {
                    outline: none;
                    border-color: #c0392b;
                    box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1);
                }
                button {
                    width: 100%;
                    padding: 12px;
                    background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 1em;
                    cursor: pointer;
                }
                button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3);
                }
                .error {
                    background: #f8d7da;
                    color: #721c24;
                    padding: 12px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    border-left: 4px solid #dc3545;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h1>🔐 Admin Verifikasi</h1>
                <?php if (isset($login_error)): ?>
                    <div class="error"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">Password Admin</label>
                        <input type="password" id="password" name="password" required autofocus>
                    </div>
                    <button type="submit">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle verifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $reservasi_id = intval($_POST['reservasi_id']);
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $update_query = "UPDATE reservasi SET bukti_verified = 1 WHERE id = $reservasi_id";
        if (mysqli_query($koneksi, $update_query)) {
            // Ambil data untuk notifikasi
            $data_query = "SELECT telepon, nama_pelanggan FROM reservasi WHERE id = $reservasi_id";
            $data_result = mysqli_query($koneksi, $data_query);
            if ($data_result && mysqli_num_rows($data_result) > 0) {
                $data = mysqli_fetch_assoc($data_result);
                
                $message = "✅ *Pembayaran Anda Telah Terverifikasi!*\n\n";
                $message .= "Terima kasih " . $data['nama_pelanggan'] . "\n";
                $message .= "Bukti pembayaran Anda telah kami verifikasi.\n\n";
                $message .= "Meja akan kami siapkan sesuai jadwal yang telah ditentukan.\n";
                $message .= "Sampai jumpa di SASUKI BBQ! 🔥";
                
                sendWhatsAppMessage($data['telepon'], $message, ['priority' => 'high']);
            }
            echo "<script>alert('Bukti pembayaran disetujui!'); location.reload();</script>";
        }
    } elseif ($action === 'reject') {
        $reject_reason = isset($_POST['reject_reason']) ? $_POST['reject_reason'] : 'Bukti pembayaran tidak sesuai';
        $update_query = "UPDATE reservasi SET bukti_verified = -1 WHERE id = $reservasi_id";
        if (mysqli_query($koneksi, $update_query)) {
            // Ambil data untuk notifikasi
            $data_query = "SELECT telepon, nama_pelanggan FROM reservasi WHERE id = $reservasi_id";
            $data_result = mysqli_query($koneksi, $data_query);
            if ($data_result && mysqli_num_rows($data_result) > 0) {
                $data = mysqli_fetch_assoc($data_result);
                
                $message = "⚠️ *Bukti Pembayaran Ditolak*\n\n";
                $message .= "Halo " . $data['nama_pelanggan'] . "\n";
                $message .= "Bukti pembayaran Anda belum sesuai.\n\n";
                $message .= "Alasan: " . $reject_reason . "\n\n";
                $message .= "Silakan upload kembali bukti pembayaran yang benar.\n";
                $message .= "Terimakasih 🙏";
                
                sendWhatsAppMessage($data['telepon'], $message, ['priority' => 'high']);
            }
            echo "<script>alert('Bukti pembayaran ditolak!'); location.reload();</script>";
        }
    }
}

// Ambil data bukti yang belum diverifikasi
$query = "SELECT id, nama_pelanggan, telepon, jumlah_orang, metode_pembayaran, bukti_pembayaran, bukti_verified, 
                  tanggal, jam, (jumlah_orang * 50000) as total
          FROM reservasi 
          WHERE bukti_pembayaran IS NOT NULL
          ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Bukti Pembayaran - SASUKI BBQ Admin</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
        }

        .logout-btn {
            background: rgba(255,255,255,0.3);
            border: 1px solid white;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.4);
        }

        .data-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9em;
        }

        .btn-view {
            background: #2196F3;
            color: white;
        }

        .btn-view:hover {
            background: #1976D2;
        }

        .btn-approve {
            background: #27ae60;
            color: white;
        }

        .btn-approve:hover {
            background: #229954;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn-reject:hover {
            background: #c0392b;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal.show {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .bukti-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state h2 {
            margin: 0;
            color: #666;
        }

        @media (max-width: 768px) {
            table {
                font-size: 0.8em;
            }

            th, td {
                padding: 10px;
            }

            .actions {
                flex-direction: column;
                gap: 5px;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 Verifikasi Bukti Pembayaran</h1>
            <form action="" method="POST" style="margin: 0;">
                <button type="submit" name="action" value="logout" class="logout-btn" onclick="return confirm('Logout?')">Logout</button>
            </form>
        </header>

        <div class="data-table">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $status_class = 'status-pending';
                        $status_text = '⏳ Pending';
                        
                        if ($row['bukti_verified'] == 1) {
                            $status_class = 'status-approved';
                            $status_text = '✅ Approved';
                        } elseif ($row['bukti_verified'] == -1) {
                            $status_class = 'status-rejected';
                            $status_text = '❌ Rejected';
                        }
                    ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                        <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                        <td><?php echo ucfirst($row['metode_pembayaran']); ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <div class="actions">
                                <button class="btn-view" onclick="viewBukti('<?php echo $row['bukti_pembayaran']; ?>', '<?php echo htmlspecialchars($row['nama_pelanggan']); ?>')">Lihat</button>
                                <?php if ($row['bukti_verified'] == 0): ?>
                                <form method="POST" style="display: inline; margin: 0;">
                                    <input type="hidden" name="reservasi_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-approve">Approve</button>
                                </form>
                                <button class="btn-reject" onclick="rejectBukti(<?php echo $row['id']; ?>)">Reject</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <h2>📭 Tidak ada bukti pembayaran</h2>
                <p>Semua pembayaran sudah terverifikasi atau belum ada yang diupload.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Lihat Bukti -->
    <div class="modal" id="buktiModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📸 Bukti Pembayaran</h2>
                <button class="close-btn" onclick="closeBuktiModal()">&times;</button>
            </div>
            <div id="buktiContent"></div>
        </div>
    </div>

    <!-- Modal Reject -->
    <div class="modal" id="rejectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tolak Bukti Pembayaran</h2>
                <button class="close-btn" onclick="closeRejectModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="reservasi_id" id="rejectReservasiId">
                <input type="hidden" name="action" value="reject">
                
                <div style="margin-bottom: 20px;">
                    <label for="reject_reason">Alasan Penolakan</label>
                    <textarea name="reject_reason" id="reject_reason" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; min-height: 100px;"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeRejectModal()" style="flex: 1; background: #e9ecef; color: #495057;">Batal</button>
                    <button type="submit" style="flex: 1; background: #e74c3c; color: white;">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewBukti(buktiPath, nama) {
            const modal = document.getElementById('buktiModal');
            const content = document.getElementById('buktiContent');
            content.innerHTML = '<p><strong>Atas Nama:</strong> ' + nama + '</p><img src="../' + buktiPath + '" alt="Bukti" class="bukti-image">';
            modal.classList.add('show');
        }

        function closeBuktiModal() {
            document.getElementById('buktiModal').classList.remove('show');
        }

        function rejectBukti(reservasiId) {
            document.getElementById('rejectReservasiId').value = reservasiId;
            document.getElementById('rejectModal').classList.add('show');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('show');
        }

        window.addEventListener('click', function(event) {
            const buktiModal = document.getElementById('buktiModal');
            const rejectModal = document.getElementById('rejectModal');
            if (event.target === buktiModal) {
                closeBuktiModal();
            }
            if (event.target === rejectModal) {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
