<?php
include '../config/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

// Ambil ID reservasi
$reservasi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reservasi_id <= 0) {
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

// Cek apakah sudah dibayar
if ($reservasi['status'] === 'dibayar' || $reservasi['status'] === 'dikonfirmasi') {
    header('Location: sukses.php?id=' . $reservasi_id);
    exit;
}

// Hitung total pembayaran (contoh: per orang Rp 50.000)
$harga_per_orang = 50000;
$total_pembayaran = $reservasi['jumlah_orang'] * $harga_per_orang;

// Fungsi helper untuk format tanggal Indonesia
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
    <title>Pembayaran Reservasi - SASUKI BBQ</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, rgba(192, 57, 43, 0.9) 0%, rgba(146, 43, 33, 0.9) 100%), url('../img/sasuki.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .page-wrapper {
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            text-align: center;
            color: white;
            padding: 30px 20px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            letter-spacing: 1px;
        }

        header p {
            margin: 10px 0 0 0;
            font-size: 1.1em;
            opacity: 0.95;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.98);
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            flex: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }

        /* LEFT SIDE - DETAIL RESERVASI */
        .detail-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .section-title {
            color: #2c3e50;
            font-size: 1.5em;
            margin: 0;
            padding-bottom: 15px;
            border-bottom: 3px solid #c0392b;
            font-weight: 700;
        }

        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border-left: 5px solid #c0392b;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
        }

        .info-item:not(:last-child) {
            border-bottom: 1px solid #e9ecef;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.95em;
        }

        .info-value {
            color: #212529;
            font-weight: 500;
            text-align: right;
        }

        .info-value.highlight {
            color: #c0392b;
            font-weight: 700;
        }

        /* RIGHT SIDE - PAYMENT */
        .payment-section {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .payment-title {
            color: #2c3e50;
            font-size: 1.5em;
            margin: 0;
            padding-bottom: 15px;
            border-bottom: 3px solid #c0392b;
            font-weight: 700;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .payment-method {
            position: relative;
            cursor: pointer;
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .method-card {
            border: 2px solid #ddd;
            border-radius: 12px;
            padding: 25px 15px;
            text-align: center;
            transition: all 0.3s ease;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .payment-method input[type="radio"]:checked ~ .method-card {
            border-color: #c0392b;
            background: #fff5f5;
            box-shadow: 0 4px 12px rgba(192, 57, 43, 0.2);
        }

        .method-card:hover {
            border-color: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(192, 57, 43, 0.15);
        }

        .method-icon {
            font-size: 2.8em;
        }

        .method-info {
            flex: 1;
        }

        .method-name {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 3px;
            font-size: 1.1em;
        }

        .method-desc {
            font-size: 0.85em;
            color: #6c757d;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .modal-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.8em;
        }

        .close-btn {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            background: none;
            border: none;
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #000;
        }

        .bank-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .bank-option {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        .bank-option:hover {
            border-color: #c0392b;
            background: #fff5f5;
        }

        .bank-option.active {
            border-color: #c0392b;
            background: #fff5f5;
            box-shadow: 0 4px 12px rgba(192, 57, 43, 0.2);
        }

        .bank-icon {
            font-size: 2em;
            margin-bottom: 8px;
        }

        .bank-name {
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.95em;
        }

        .account-info {
            background: #e8f5e9;
            border-left: 4px solid #27ae60;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            display: none;
        }

        .account-info.active {
            display: block;
            animation: slideDown 0.3s ease;
        }

        .account-label {
            color: #558b2f;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .account-number {
            background: white;
            border: 1px solid #c8e6c9;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #1b5e20;
            font-size: 1.2em;
            text-align: center;
            margin-bottom: 10px;
            word-break: break-all;
        }

        .copy-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9em;
            transition: all 0.3s ease;
            width: 100%;
        }

        .copy-btn:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        .qris-display {
            text-align: center;
            margin: 20px 0;
        }

        .qris-code {
            max-width: 300px;
            margin: 0 auto;
            border: 2px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            background: #f9f9f9;
        }

        .qris-code img {
            width: 100%;
            height: auto;
        }

        .qris-info {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.9em;
            color: #1565c0;
            line-height: 1.5;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }

        .btn-modal {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-modal-primary {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            flex: 1;
        }

        .btn-modal-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3);
        }

        .btn-modal-secondary {
            background: #e9ecef;
            color: #495057;
        }

        .btn-modal-secondary:hover {
            background: #dee2e6;
        }

        .total-card {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(192, 57, 43, 0.3);
        }

        .total-label {
            font-size: 0.95em;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .total-amount {
            font-size: 2.3em;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .total-detail {
            font-size: 0.9em;
            opacity: 0.85;
        }

        .btn-submit {
            width: 100%;
            padding: 15px 30px;
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            border: none;
            font-size: 1.05em;
            font-weight: 700;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-submit:disabled {
            background: #95a5a6;
            color: white;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .container {
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 1.8em;
            }

            header p {
                font-size: 0.95em;
            }

            .container {
                padding: 20px;
                margin: 10px;
            }

            .section-title,
            .payment-title {
                font-size: 1.2em;
            }

            .total-amount {
                font-size: 2em;
            }

            .method-card {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <header>
        <h1>💳 Konfirmasi Pembayaran</h1>
        <p>Selesaikan pembayaran untuk mengkonfirmasi reservasi Anda</p>
    </header>

    <div class="container">
        <div class="content-wrapper">
            <!-- LEFT: Detail Reservasi -->
            <div class="detail-section">
                <h2 class="section-title">📋 Detail Reservasi</h2>
                
                <div class="info-card">
                    <div class="info-item">
                        <span class="info-label">ID Reservasi</span>
                        <span class="info-value highlight">#<?php echo htmlspecialchars($reservasi['id']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nama Pemesan</span>
                        <span class="info-value"><?php echo htmlspecialchars($reservasi['nama_pelanggan']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">No. Telepon</span>
                        <span class="info-value"><?php echo htmlspecialchars($reservasi['telepon']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal</span>
                        <span class="info-value"><?php echo formatTanggalIndonesia($reservasi['tanggal']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jam</span>
                        <span class="info-value"><?php echo date('H:i', strtotime($reservasi['jam'])); ?> WIB</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jumlah Tamu</span>
                        <span class="info-value highlight"><?php echo $reservasi['jumlah_orang']; ?> orang</span>
                    </div>
                    <?php if (!empty($reservasi['catatan'])): ?>
                    <div class="info-item">
                        <span class="info-label">Catatan</span>
                        <span class="info-value"><?php echo htmlspecialchars($reservasi['catatan']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT: Payment Section -->
            <div class="payment-section">
                <h2 class="payment-title">💰 Metode Pembayaran</h2>

                <form action="upload_bukti.php" method="POST" id="paymentForm">
                    <input type="hidden" name="reservasi_id" value="<?php echo htmlspecialchars($reservasi['id']); ?>">
                    <input type="hidden" name="metode_pembayaran" id="hiddenMetode" value="">
                    
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="metode_pembayaran_display" value="transfer" required>
                            <div class="method-card">
                                <div class="method-icon">🏦</div>
                                <div class="method-info">
                                    <div class="method-name">Transfer Bank</div>
                                    <div class="method-desc">BCA / Mandiri / BRI / CIMB</div>
                                </div>
                            </div>
                        </label>

                        <label class="payment-method">
                            <input type="radio" name="metode_pembayaran_display" value="qris" required>
                            <div class="method-card">
                                <div class="method-icon">📱</div>
                                <div class="method-info">
                                    <div class="method-name">QRIS</div>
                                    <div class="method-desc">Scan dengan semua e-wallet</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="total-card">
                        <div class="total-label">Total Pembayaran</div>
                        <div class="total-amount">Rp <?php echo number_format($total_pembayaran, 0, ',', '.'); ?></div>
                        <div class="total-detail"><?php echo $reservasi['jumlah_orang']; ?> orang × Rp <?php echo number_format($harga_per_orang, 0, ',', '.'); ?></div>
                    </div>

                    <button type="button" class="btn-submit" id="nextBtn" style="display: none;">
                        ✅ Lanjutkan ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Transfer Bank -->
    <div id="bankModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🏦 Pilih Bank & Rekening</h2>
                <button class="close-btn" onclick="closeBankModal()">&times;</button>
            </div>
            <div class="bank-list" id="bankList">
                <div class="bank-option" onclick="selectBank(this, 'BCA', '1234567890', 'PT Sasuki BBQ')">
                    <div class="bank-icon">🏦</div>
                    <div class="bank-name">BCA</div>
                </div>
                <div class="bank-option" onclick="selectBank(this, 'Mandiri', '1110022333', 'PT Sasuki BBQ')">
                    <div class="bank-icon">🏦</div>
                    <div class="bank-name">Mandiri</div>
                </div>
                <div class="bank-option" onclick="selectBank(this, 'BRI', '0123456789', 'PT Sasuki BBQ')">
                    <div class="bank-icon">🏦</div>
                    <div class="bank-name">BRI</div>
                </div>
                <div class="bank-option" onclick="selectBank(this, 'CIMB', '7001234567', 'PT Sasuki BBQ')">
                    <div class="bank-icon">🏦</div>
                    <div class="bank-name">CIMB</div>
                </div>
                <div class="bank-option" onclick="selectBank(this, 'OVO', '081234567890', 'PT Sasuki BBQ')">
                    <div class="bank-icon">📱</div>
                    <div class="bank-name">OVO</div>
                </div>
                <div class="bank-option" onclick="selectBank(this, 'Dana', '081234567890', 'PT Sasuki BBQ')">
                    <div class="bank-icon">📱</div>
                    <div class="bank-name">Dana</div>
                </div>
            </div>
            <div class="account-info" id="accountInfo">
                <div class="account-label">Nomor Rekening</div>
                <div class="account-number" id="accountNumber">-</div>
                <button type="button" class="copy-btn" onclick="copyAccount()">📋 Salin Nomor Rekening</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeBankModal()">Batal</button>
                <button type="button" class="btn-modal btn-modal-primary" onclick="confirmBankPayment()">Lanjut Upload Bukti</button>
            </div>
        </div>
    </div>

    <!-- Modal QRIS -->
    <div id="qrisModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📱 Pembayaran QRIS</h2>
                <button class="close-btn" onclick="closeQrisModal()">&times;</button>
            </div>
            <div class="qris-display">
                <div style="color: #2c3e50; margin-bottom: 15px; font-weight: 600;">Scan QR Code dengan e-wallet Anda</div>
                <div class="qris-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=SASUKI-BBQ-PAYMENT-<?php echo $reservasi['id']; ?>" alt="QR Code QRIS">
                </div>
                <div class="qris-info">
                    ℹ️ <strong>Cara Pembayaran:</strong><br>
                    1. Buka aplikasi e-wallet (OVO, Dana, LinkAja, GOPAY, dsb)<br>
                    2. Pilih "Scan QR Code" atau "Bayar"<br>
                    3. Arahkan kamera ke QR code di atas<br>
                    4. Konfirmasi dan selesaikan pembayaran<br>
                    5. Simpan bukti pembayaran Anda
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeQrisModal()">Batal</button>
                <button type="button" class="btn-modal btn-modal-primary" onclick="confirmQrisPayment()">Lanjut Upload Bukti</button>
            </div>
        </div>
    </div>
</div>

<script>
// Data untuk setiap bank
const bankData = {
    'BCA': { number: '1234567890', name: 'PT Sasuki BBQ' },
    'Mandiri': { number: '1110022333', name: 'PT Sasuki BBQ' },
    'BRI': { number: '0123456789', name: 'PT Sasuki BBQ' },
    'CIMB': { number: '7001234567', name: 'PT Sasuki BBQ' },
    'OVO': { number: '081234567890', name: 'PT Sasuki BBQ' },
    'Dana': { number: '081234567890', name: 'PT Sasuki BBQ' }
};

let selectedBank = null;
let selectedMetode = null;

// Handle metode pembayaran selection
document.querySelectorAll('input[name="metode_pembayaran_display"]').forEach(radio => {
    radio.addEventListener('change', function() {
        selectedMetode = this.value;
        document.getElementById('nextBtn').style.display = 'block';
    });
});

document.getElementById('nextBtn').addEventListener('click', function() {
    if (selectedMetode === 'transfer') {
        openBankModal();
    } else if (selectedMetode === 'qris') {
        openQrisModal();
    }
});

// Bank Modal Functions
function openBankModal() {
    document.getElementById('bankModal').style.display = 'block';
}

function closeBankModal() {
    document.getElementById('bankModal').style.display = 'none';
    selectedBank = null;
    document.querySelectorAll('.bank-option').forEach(el => el.classList.remove('active'));
    document.getElementById('accountInfo').classList.remove('active');
}

function selectBank(element, bankName, accountNumber, accountHolder) {
    document.querySelectorAll('.bank-option').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    
    selectedBank = {
        name: bankName,
        number: accountNumber,
        holder: accountHolder
    };
    
    document.getElementById('accountNumber').textContent = accountNumber;
    document.getElementById('accountInfo').classList.add('active');
}

function copyAccount() {
    const accountNumber = document.getElementById('accountNumber').textContent;
    navigator.clipboard.writeText(accountNumber).then(() => {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = '✅ Tersalin!';
        setTimeout(() => {
            btn.textContent = originalText;
        }, 2000);
    });
}

function confirmBankPayment() {
    if (!selectedBank) {
        alert('Pilih bank terlebih dahulu!');
        return;
    }
    
    document.getElementById('hiddenMetode').value = 'transfer';
    closeBankModal();
    
    // Redirect ke halaman upload bukti
    const form = document.getElementById('paymentForm');
    form.action = 'upload_bukti.php';
    form.method = 'POST';
    
    const metodeInput = document.createElement('input');
    metodeInput.type = 'hidden';
    metodeInput.name = 'metode_pembayaran';
    metodeInput.value = 'transfer';
    form.appendChild(metodeInput);
    
    form.submit();
}

// QRIS Modal Functions
function openQrisModal() {
    document.getElementById('qrisModal').style.display = 'block';
}

function closeQrisModal() {
    document.getElementById('qrisModal').style.display = 'none';
}

function confirmQrisPayment() {
    document.getElementById('hiddenMetode').value = 'qris';
    closeQrisModal();
    
    // Redirect ke halaman upload bukti
    const form = document.getElementById('paymentForm');
    form.action = 'upload_bukti.php';
    form.method = 'POST';
    
    const metodeInput = document.createElement('input');
    metodeInput.type = 'hidden';
    metodeInput.name = 'metode_pembayaran';
    metodeInput.value = 'qris';
    form.appendChild(metodeInput);
    
    form.submit();
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const bankModal = document.getElementById('bankModal');
    const qrisModal = document.getElementById('qrisModal');
    
    if (event.target === bankModal) {
        closeBankModal();
    }
    if (event.target === qrisModal) {
        closeQrisModal();
    }
});
</script>
</body>
</html>

