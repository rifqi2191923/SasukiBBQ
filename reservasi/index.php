<?php include '../config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Meja - SASUKI BBQ</title>
    <link rel="stylesheet" href="../assets/reservasi.css">
</head>
<body>
<div class="page-wrapper">
    <header>
        <h1>🍖 Reservasi Meja SASUKI BBQ</h1>
        <p>Pesan meja Anda sekarang dan nikmati pengalaman makan yang tak terlupakan</p>
    </header>

    <div class="container">
        <div class="form-section">
            <h2 class="section-title">Form Reservasi</h2>
            
            <div class="alert alert-info">
                <span>ℹ️</span>
                <span>Isi form di bawah ini untuk melakukan reservasi. Meja akan diassign oleh kasir saat Anda datang.</span>
            </div>

            <form action="proses_tambah.php" method="POST" id="reservasiForm">
                <div class="form-grid">
                    <div class="form-group input-icon">
                        <label for="tanggal">Tanggal Reservasi <span class="required">*</span></label>
                        <input type="date" 
                               name="tanggal" 
                               id="tanggal" 
                               required
                               min="<?php echo date('Y-m-d'); ?>">
                        <span class="help-text">Pilih tanggal kunjungan Anda</span>
                    </div>

                    <div class="form-group">
                        <label for="jam">Jam Reservasi <span class="required">*</span></label>
                        <input type="time" 
                               name="jam" 
                               id="jam" 
                               required>
                        <span class="help-text">Pilih jam kedatangan (contoh: 18:00)</span>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_pelanggan">Nama Pemesan <span class="required">*</span></label>
                        <input type="text" 
                               name="nama_pelanggan" 
                               id="nama_pelanggan" 
                               placeholder="Masukkan nama lengkap"
                               required
                               pattern="[A-Za-z\s]{3,50}"
                               title="Nama harus 3-50 karakter dan hanya huruf">
                        <span class="help-text">Minimal 3 karakter, hanya huruf</span>
                    </div>

                    <div class="form-group">
                        <label for="telepon">No. Telepon <span class="required">*</span></label>
                        <input type="tel" 
                               name="telepon" 
                               id="telepon" 
                               placeholder="08xxxxxxxxxx"
                               required
                               pattern="[0-9]{10,13}"
                               title="Nomor telepon 10-13 digit">
                        <span class="help-text">Contoh: 081234567890</span>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="jumlah_orang">Jumlah Orang <span class="required">*</span></label>
                        <input type="number" 
                               name="jumlah_orang" 
                               id="jumlah_orang" 
                               min="1" 
                               max="20"
                               required
                               placeholder="1">
                        <span class="help-text">Maksimal 20 orang per reservasi</span>
                    </div>

                    <div class="form-group">
                        <label for="catatan">Catatan (Opsional)</label>
                        <textarea name="catatan" 
                                  id="catatan" 
                                  rows="3"
                                  placeholder="Catatan khusus, alergi, dll..."></textarea>
                        <span class="help-text">Tambahkan catatan jika diperlukan</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span id="submitText">🚀 Lanjut ke Pembayaran</span>
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p>Memproses reservasi...</p>
                    </div>
                </button>
            </form>
        </div>
    </div>
</div>

<script defer>
(function() {
    'use strict';
    
    // Cache DOM elements
    const dateInput = document.getElementById('tanggal');
    const form = document.getElementById('reservasiForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loading = document.getElementById('loading');
    const teleponInput = document.getElementById('telepon');
    const namaInput = document.getElementById('nama_pelanggan');
    
    // Set minimum date to today (only once on load)
    if (dateInput && !dateInput.value) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        dateInput.min = today.toISOString().split('T')[0];
    }

    // Form submission - simplified
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            submitText.style.display = 'none';
            loading.style.display = 'block';
            submitBtn.disabled = true;
        }, { passive: true });
    }

    // Phone number formatting - debounced
    if (teleponInput) {
        teleponInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        }, { passive: true });
    }

    // Name input formatting - debounced
    if (namaInput) {
        namaInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        }, { passive: true });
    }
})();
</script>
</body>
</html>
