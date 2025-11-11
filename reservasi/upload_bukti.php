<?php
include '../config/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

$reservasi_id = isset($_POST['reservasi_id']) ? intval($_POST['reservasi_id']) : 0;
$metode_pembayaran = isset($_POST['metode_pembayaran']) ? $_POST['metode_pembayaran'] : '';

if ($reservasi_id <= 0 || empty($metode_pembayaran)) {
    header('Location: index.php');
    exit;
}

// Ambil data reservasi
$query = "SELECT * FROM reservasi WHERE id = $reservasi_id";
$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit;
}

$reservasi = mysqli_fetch_assoc($result);

// Hitung total pembayaran
$harga_per_orang = 50000;
$total_pembayaran = $reservasi['jumlah_orang'] * $harga_per_orang;

// Format metode pembayaran
$metode_display = ucfirst($metode_pembayaran);

$error_message = '';
$success_message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $file = $_FILES['bukti_pembayaran'];
    
    // Validasi file
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Cek tipe file
    if (!in_array($file['type'], $allowed_types)) {
        $error_message = 'Format file tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.';
    }
    // Cek ukuran file
    elseif ($file['size'] > $max_size) {
        $error_message = 'Ukuran file terlalu besar. Maksimal 5MB.';
    }
    // Cek ada error saat upload
    elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Terjadi error saat upload file. Silakan coba lagi.';
    }
    else {
        // Buat folder bukti jika belum ada
        $bukti_dir = '../bukti_pembayaran';
        if (!is_dir($bukti_dir)) {
            mkdir($bukti_dir, 0755, true);
        }
        
        // Generate nama file unik
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = 'bukti_' . $reservasi_id . '_' . time() . '.' . $file_ext;
        $file_path = $bukti_dir . '/' . $file_name;
        
        // Pindahkan file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Update database dengan bukti pembayaran
            $file_path_db = 'bukti_pembayaran/' . $file_name;
            
            $update_query = "UPDATE reservasi SET 
                            status = 'dibayar',
                            metode_pembayaran = '$metode_pembayaran',
                            bukti_pembayaran = '$file_path_db',
                            bukti_verified = 0,
                            updated_at = NOW()
                            WHERE id = $reservasi_id";
            
            if (mysqli_query($koneksi, $update_query)) {
                // Kirim WhatsApp notification
                include '../config/wa_config.php';
                
                $message = "📱 *Bukti Pembayaran Diterima*\n\n";
                $message .= "Reservasi ID: #" . $reservasi_id . "\n";
                $message .= "Atas Nama: " . $reservasi['nama_pelanggan'] . "\n";
                $message .= "Total: Rp " . number_format($total_pembayaran, 0, ',', '.') . "\n";
                $message .= "Metode: " . $metode_display . "\n\n";
                $message .= "✅ Bukti pembayaran Anda telah diterima.\n";
                $message .= "Kami akan verifikasi dalam waktu 5-10 menit.\n\n";
                $message .= "Terimakasih! 🙏";
                
                sendWhatsAppMessage($reservasi['telepon'], $message, ['priority' => 'high']);
                
                // Redirect ke halaman sukses
                header('Location: sukses.php?id=' . $reservasi_id);
                exit;
            } else {
                // Hapus file jika gagal update database
                unlink($file_path);
                $error_message = 'Gagal menyimpan data pembayaran. Silakan coba lagi.';
            }
        } else {
            $error_message = 'Gagal mengunggah file. Silakan coba lagi.';
        }
    }
}

// Fungsi helper format tanggal
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
    <title>Upload Bukti Pembayaran - SASUKI BBQ</title>
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
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
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

        .header {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0 0 10px 0;
            font-size: 2.2em;
            font-weight: 700;
        }

        .header p {
            margin: 0;
            font-size: 1.05em;
            opacity: 0.95;
        }

        .content {
            padding: 40px 30px;
        }

        .info-section {
            background: #f8f9fa;
            border-left: 4px solid #c0392b;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.85em;
            color: #666;
            font-weight: 600;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.1em;
            color: #212529;
            font-weight: 500;
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .alert-error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-warning {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        .alert-info {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .upload-section {
            background: #f9f9f9;
            border: 2px dashed #c0392b;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 25px;
            position: relative;
            transition: all 0.3s ease;
        }

        .upload-section:hover {
            border-color: #e74c3c;
            background: #fff5f5;
        }

        .upload-section.dragover {
            border-color: #c0392b;
            background: #fff5f5;
            box-shadow: 0 4px 12px rgba(192, 57, 43, 0.2);
        }

        .upload-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .upload-text {
            color: #666;
            margin-bottom: 15px;
        }

        .upload-text strong {
            color: #c0392b;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
        }

        .btn-upload {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3);
        }

        #bukti_pembayaran {
            display: none;
        }

        .file-info {
            margin-top: 15px;
            padding: 15px;
            background: #e8f5e9;
            border-left: 4px solid #27ae60;
            border-radius: 8px;
            display: none;
        }

        .file-info.show {
            display: block;
        }

        .file-name {
            color: #27ae60;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .file-size {
            color: #558b2f;
            font-size: 0.9em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #c0392b;
            box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1);
        }

        .form-text {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(192, 57, 43, 0.4);
        }

        .btn-primary:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: #e9ecef;
            color: #495057;
        }

        .btn-secondary:hover {
            background: #dee2e6;
        }

        .note-section {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            color: #1565c0;
            line-height: 1.6;
        }

        .note-section strong {
            color: #1565c0;
        }

        .note-section ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .note-section li {
            margin: 8px 0;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 1.8em;
            }

            .content {
                padding: 25px 20px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 Upload Bukti Pembayaran</h1>
            <p>Mohon upload screenshot atau foto bukti pembayaran Anda</p>
        </div>

        <div class="content">
            <!-- Info Reservasi -->
            <div class="info-section">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">ID Reservasi</span>
                        <span class="info-value">#<?php echo htmlspecialchars($reservasi['id']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nama Pemesan</span>
                        <span class="info-value"><?php echo htmlspecialchars($reservasi['nama_pelanggan']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Pembayaran</span>
                        <span class="info-value">Rp <?php echo number_format($total_pembayaran, 0, ',', '.'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Metode Pembayaran</span>
                        <span class="info-value"><?php echo $metode_display; ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                ❌ <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <div class="alert alert-warning">
                ⚠️ <strong>Pastikan bukti pembayaran Anda jelas dan memuat:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Nama penerima (PT Sasuki BBQ)</li>
                    <li>Jumlah transfer (Rp <?php echo number_format($total_pembayaran, 0, ',', '.'); ?>)</li>
                    <li>Tanggal dan jam transaksi</li>
                    <li>Status "Berhasil" atau "Sukses"</li>
                </ul>
            </div>

            <!-- Form Upload -->
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-section" id="dropZone">
                    <div class="upload-icon">📤</div>
                    <div class="upload-text">
                        Klik di sini atau <strong>drag & drop</strong> file bukti pembayaran Anda<br>
                        <span style="font-size: 0.9em; color: #999;">Format: JPG, JPEG, PNG | Ukuran max: 5MB</span>
                    </div>
                    <button type="button" class="btn-upload" onclick="document.getElementById('bukti_pembayaran').click()">
                        Pilih File
                    </button>
                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/jpeg,image/png,image/jpg">
                    
                    <div class="file-info" id="fileInfo">
                        <div class="file-name" id="fileName">-</div>
                        <div class="file-size" id="fileSize">-</div>
                    </div>
                </div>

                <div class="note-section">
                    <strong>ℹ️ Informasi Penting:</strong>
                    <ul>
                        <li>Upload bukti pembayaran dalam format foto/screenshot yang jelas</li>
                        <li>Tim kami akan verifikasi dalam waktu 5-10 menit</li>
                        <li>Anda akan menerima notifikasi WhatsApp setelah verifikasi</li>
                        <li>Jangan close halaman ini sebelum upload selesai</li>
                    </ul>
                </div>

                <div class="action-buttons">
                    <a href="pembayaran.php?id=<?php echo $reservasi_id; ?>" class="btn btn-secondary">← Kembali</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        ✅ Upload & Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('bukti_pembayaran');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const submitBtn = document.getElementById('submitBtn');
        const uploadForm = document.getElementById('uploadForm');

        // Click to upload
        dropZone.addEventListener('click', () => fileInput.click());

        // Drag & drop events
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            handleFileSelect();
        });

        // File input change
        fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            const file = fileInput.files[0];
            if (file) {
                // Validasi file
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.');
                    fileInput.value = '';
                    fileInfo.classList.remove('show');
                    return;
                }

                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    fileInput.value = '';
                    fileInfo.classList.remove('show');
                    return;
                }

                // Tampilkan info file
                fileName.textContent = '📄 ' + file.name;
                fileSize.textContent = 'Ukuran: ' + (file.size / 1024).toFixed(2) + ' KB';
                fileInfo.classList.add('show');
                submitBtn.disabled = false;
            } else {
                fileInfo.classList.remove('show');
                submitBtn.disabled = true;
            }
        }

        // Disable submit jika belum ada file
        submitBtn.disabled = true;

        // Handle form submit
        uploadForm.addEventListener('submit', function(e) {
            if (!fileInput.files[0]) {
                e.preventDefault();
                alert('Pilih file bukti pembayaran terlebih dahulu!');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Sedang Upload...';
        });
    </script>
</body>
</html>
