# 🚀 Quick Start Guide - SASUKI BBQ Reservasi System

Panduan cepat untuk setup dan menjalankan sistem reservasi SASUKI BBQ.

## ⚡ Quick Setup (5 Menit)

### Step 1: Database Setup

1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Buat database baru: `sasuki_db`
3. Import atau buat tabel `reservasi` dengan struktur:

```sql
CREATE TABLE IF NOT EXISTS `reservasi` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 2: Configure Database Connection

Edit file: `config/koneksi.php`

```php
<?php
$host = "localhost";
$user = "root";
$pass = "";          // Password MySQL Anda (kosong jika default)
$db   = "sasuki_db"; // Nama database yang dibuat

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
```

### Step 3: Setup Logs Folder

```bash
# Di folder sasuki_app, buat folder logs
mkdir logs
chmod 777 logs
```

Atau buat folder `logs` secara manual:
- Lokasi: `C:\xampp\htdocs\sasuki_app\logs`

### Step 4: WhatsApp Gateway Configuration

Edit file: `config/wa_config.php`

#### Option A: Gunakan Fonnte (Production)

```php
define('WA_GATEWAY', 'fonnte');
define('FONNTE_TOKEN', 'YOUR_FONNTE_API_TOKEN_HERE');
```

**Cara mendapat Fonnte Token:**

1. Daftar di https://fonnte.com
2. Dashboard → Settings → API Key
3. Copy API Token
4. Paste ke `YOUR_FONNTE_API_TOKEN_HERE`

Contoh:
```php
define('FONNTE_TOKEN', 'n5Hk8mP3xQw9zJ7lMoNpRsT4uVwXyZ2aB3cD4eFgH5');
```

#### Option B: Testing Mode (Development)

Untuk development/testing tanpa API:

```php
define('WA_GATEWAY', 'local');
```

Pesan akan disimpan ke `logs/wa_messages.log`

### Step 5: Test Aplikasi

1. Buka browser: `http://localhost/sasuki_app/reservasi/`
2. Isi form dengan data test
3. Klik submit
4. Jika Fonnte dikonfigurasi, pesan akan terkirim ke nomor
5. Jika local mode, cek file `logs/wa_messages.log`

---

## 📝 Struktur Alur Aplikasi

```
┌─────────────────────────────────────────┐
│  User Buka: /reservasi/index.php        │
│  (Form Reservasi)                       │
└──────────────┬──────────────────────────┘
               │ Submit Form
               ↓
┌──────────────────────────────────────────┐
│  /reservasi/proses_tambah.php            │
│  - Validasi data                         │
│  - Simpan ke database (status: pending)  │
│  - Kirim WA #1 (Konfirmasi Reservasi)    │
│  - Redirect ke pembayaran                │
└──────────────┬───────────────────────────┘
               │
               ↓
┌──────────────────────────────────────────┐
│  /reservasi/pembayaran.php?id=123        │
│  - Tampilkan detail reservasi            │
│  - Pilih metode pembayaran               │
│  - Form untuk confirm                    │
└──────────────┬───────────────────────────┘
               │ Submit Pembayaran
               ↓
┌──────────────────────────────────────────┐
│  /reservasi/proses_pembayaran.php        │
│  - Update status (dibayar)               │
│  - Simpan metode pembayaran              │
│  - Kirim WA #2 (Pembayaran Berhasil)     │
│  - Redirect ke sukses                    │
└──────────────┬───────────────────────────┘
               │
               ↓
┌──────────────────────────────────────────┐
│  /reservasi/sukses.php?id=123            │
│  - Tampilkan detail final                │
│  - Button: Cetak, Kembali                │
└──────────────────────────────────────────┘
```

---

## 🧪 Testing Checklist

- [ ] Database `sasuki_db` sudah dibuat
- [ ] Tabel `reservasi` sudah ada dengan struktur benar
- [ ] Folder `logs` sudah dibuat dan writable
- [ ] File `koneksi.php` sudah dikonfigurasi
- [ ] File `wa_config.php` sudah dikonfigurasi
- [ ] Bisa akses `http://localhost/sasuki_app/reservasi/`
- [ ] Form bisa diisi dan disubmit
- [ ] Data masuk ke database
- [ ] Pesan WhatsApp terkirim (atau masuk log)
- [ ] Bisa lanjut ke pembayaran
- [ ] Pembayaran bisa dikonfirmasi
- [ ] Halaman sukses muncul

---

## 🔍 Debugging

### Cek Koneksi Database

Buat file test: `config/test_koneksi.php`

```php
<?php
include 'koneksi.php';

echo "<h2>Test Koneksi Database</h2>";

if ($koneksi) {
    echo "✅ Koneksi berhasil!<br>";
    
    // Test query
    $result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM reservasi");
    $row = mysqli_fetch_assoc($result);
    echo "Total reservasi: " . $row['total'] . "<br>";
} else {
    echo "❌ Koneksi gagal: " . mysqli_connect_error();
}
?>
```

Akses: `http://localhost/sasuki_app/config/test_koneksi.php`

### Cek WhatsApp Configuration

Buat file test: `config/test_wa.php`

```php
<?php
include 'wa_config.php';

echo "<h2>WhatsApp Configuration Test</h2>";
echo "Gateway: " . WA_GATEWAY . "<br>";
echo "Token: " . (FONNTE_TOKEN === 'YOUR_FONNTE_API_TOKEN' ? '❌ NOT SET' : '✅ SET') . "<br>";

// Test send
$result = sendWhatsAppMessage(
    '62812345678901', // Ganti dengan nomor Anda
    'Ini adalah pesan test dari SASUKI BBQ'
);

echo "<pre>";
print_r($result);
echo "</pre>";
?>
```

Akses: `http://localhost/sasuki_app/config/test_wa.php`

---

## 📱 WhatsApp Message Format

### Message 1: Konfirmasi Reservasi

```
🍖 KONFIRMASI RESERVASI SASUKI BBQ

Terima kasih telah melakukan reservasi!

*Detail Reservasi:*
📋 ID: #1234
👤 Nama: John Doe
📅 Tanggal: 15/11/2025
🕐 Jam: 19:00
👥 Jumlah Orang: 4
📞 No. Telepon: 081234567890

Status: *Menunggu Pembayaran*

Silakan lanjutkan ke halaman pembayaran untuk mengkonfirmasi reservasi Anda.

Terima kasih! 🙏
```

### Message 2: Pembayaran Berhasil

```
✅ *PEMBAYARAN BERHASIL*

Reservasi Anda telah dikonfirmasi!

*Detail Reservasi:*
📋 ID: #1234
👤 Nama: John Doe
📅 Tanggal: 15/11/2025
🕐 Jam: 19:00
👥 Jumlah Orang: 4
💳 Metode: Tunai

Kami sudah menerima pembayaran Anda.
Meja akan dipersiapkan untuk Anda.

Sampai jumpa di SASUKI BBQ! 🎉
```

---

## 💾 Database Queries Útiles

### Lihat Semua Reservasi

```sql
SELECT * FROM reservasi ORDER BY tanggal DESC, jam DESC;
```

### Lihat Reservasi Hari Ini

```sql
SELECT * FROM reservasi WHERE DATE(tanggal) = CURDATE();
```

### Lihat Reservasi yang Belum Dibayar

```sql
SELECT * FROM reservasi WHERE status = 'pending';
```

### Update Status Reservasi

```sql
UPDATE reservasi SET status = 'selesai' WHERE id = 123;
```

### Hapus Reservasi

```sql
DELETE FROM reservasi WHERE id = 123;
```

---

## 📊 Monitoring & Analytics

### Total Reservasi Perbulan

```sql
SELECT 
    MONTH(tanggal) as bulan,
    COUNT(*) as total,
    SUM(jumlah_orang) as total_orang,
    COUNT(CASE WHEN status='dibayar' THEN 1 END) as terbayar
FROM reservasi 
WHERE YEAR(tanggal) = YEAR(NOW())
GROUP BY MONTH(tanggal);
```

### Metode Pembayaran

```sql
SELECT 
    metode_pembayaran,
    COUNT(*) as jumlah,
    COUNT(*) * 50000 as revenue
FROM reservasi 
WHERE status = 'dibayar'
GROUP BY metode_pembayaran;
```

---

## 🆘 Common Issues & Solutions

| Issue | Solusi |
|-------|--------|
| "Koneksi gagal" | Check `koneksi.php`, pastikan host/user/pass benar |
| "Tabel tidak ditemukan" | Jalankan SQL untuk membuat tabel |
| "Folder logs tidak writable" | Run: `chmod 777 logs` (Linux) atau buat manual (Windows) |
| "Pesan WA tidak terkirim" | Check Fonnte token, cek nomor telepon format |
| "Form tidak bisa submit" | Check browser console untuk error, run developer tools |
| "Halaman blank/error" | Check error_log di XAMPP, enable display_errors |

---

## 📞 Support

Untuk pertanyaan lebih lanjut:
1. Baca dokumentasi lengkap: `README.md`
2. Check log files di folder `logs/`
3. Test dengan mode development (local)

---

**Version:** 1.0.0  
**Last Updated:** November 11, 2025
