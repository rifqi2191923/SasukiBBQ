# 🍖 SASUKI BBQ - Sistem Reservasi dengan WhatsApp Integration

Dokumentasi lengkap untuk sistem reservasi dengan integrasi WhatsApp otomatis.

## 📋 Daftar Isi
1. [Instalasi & Setup](#instalasi--setup)
2. [Konfigurasi WhatsApp Gateway](#konfigurasi-whatsapp-gateway)
3. [Flow Sistem](#flow-sistem)
4. [API Reference](#api-reference)
5. [Troubleshooting](#troubleshooting)

---

## Instalasi & Setup

### 1. Persiapan Database

Pastikan tabel `reservasi` memiliki struktur berikut:

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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Setup File Logging

Buat folder `logs` di root aplikasi:

```bash
mkdir logs
chmod 777 logs
```

### 3. Konfigurasi Awal

File konfigurasi sudah tersedia di `config/wa_config.php`. Sesuaikan sesuai kebutuhan Anda.

---

## Konfigurasi WhatsApp Gateway

### Opsi 1: Menggunakan Fonnte API (Recommended)

**Langkah 1: Daftar di Fonnte**

1. Buka https://fonnte.com
2. Daftar dan lakukan verifikasi
3. Connect WhatsApp Business Account Anda (atau personal)
4. Dapatkan API Token dari dashboard

**Langkah 2: Setup di Aplikasi**

Edit file `config/wa_config.php`:

```php
define('WA_GATEWAY', 'fonnte');
define('FONNTE_TOKEN', 'YOUR_FONNTE_API_TOKEN_HERE');
```

Ganti `YOUR_FONNTE_API_TOKEN_HERE` dengan token Anda.

**Harga & Limit:**
- Fonnte menyediakan paket gratis untuk testing (terbatas pesan)
- Paket berbayar mulai dari Rp 50.000/bulan untuk 1000 pesan
- Pesan teks unlimited tersedia dengan paket premium

**Format Nomor:**
- Accepted: `081234567890`, `+6281234567890`, `6281234567890`
- Akan otomatis konversi ke format: `6281234567890`

### Opsi 2: Mode Development (Testing Tanpa API)

Jika ingin testing tanpa API key, gunakan mode `local`:

```php
define('WA_GATEWAY', 'local');
```

Pesan akan disimpan ke file `logs/wa_messages.log` untuk review.

### Opsi 3: Twilio (Opsional - Belum Diimplementasi)

Untuk di-implementasikan di masa depan jika diperlukan.

---

## Flow Sistem

### 1️⃣ **Proses Reservasi (Form)**
- User mengisi form di `reservasi/index.php`
- Data: Nama, Telepon, Tanggal, Jam, Jumlah Orang, Catatan
- Validasi dilakukan di client dan server
- Submit → `proses_tambah.php`

### 2️⃣ **Penyimpanan Data & Notifikasi 1**
- Data disimpan ke database dengan status `pending`
- **WhatsApp 1**: Konfirmasi Reservasi dikirim
  ```
  🍖 KONFIRMASI RESERVASI SASUKI BBQ
  ID: #123
  Nama: John Doe
  Tanggal: 15/11/2025
  Jam: 19:00
  Jumlah Orang: 4
  Status: Menunggu Pembayaran
  ```
- Redirect ke halaman pembayaran

### 3️⃣ **Halaman Pembayaran**
- User melihat detail reservasi
- Pilih metode pembayaran: Tunai / Transfer / QRIS
- Submit → `proses_pembayaran.php`

### 4️⃣ **Konfirmasi Pembayaran & Notifikasi 2**
- Status berubah menjadi `dibayar`
- Metode pembayaran disimpan
- **WhatsApp 2**: Notifikasi Pembayaran Berhasil
  ```
  ✅ PEMBAYARAN BERHASIL
  ID: #123
  Nama: John Doe
  Status: Pembayaran Dikonfirmasi
  Metode: Tunai
  ```
- Redirect ke halaman sukses

### 5️⃣ **Halaman Sukses**
- Tampilkan detail lengkap reservasi
- Instruksi next steps
- Button: Cetak Bukti, Kembali ke Beranda

---

## API Reference

### Function: `sendWhatsAppMessage()`

**Signature:**
```php
function sendWhatsAppMessage($phone_number, $message, $options = [])
```

**Parameter:**
- `$phone_number` (string): Nomor HP dengan format 62xxxx
- `$message` (string): Isi pesan WhatsApp
- `$options` (array): Opsi tambahan (image, dll)

**Return:**
```php
[
    'success' => true/false,
    'message' => 'Deskripsi status',
    'data' => [...] // Response dari API
]
```

**Contoh Penggunaan:**
```php
$result = sendWhatsAppMessage(
    '6281234567890',
    'Halo! Ini adalah pesan test.',
    []
);

if ($result['success']) {
    echo "Pesan berhasil dikirim!";
} else {
    echo "Error: " . $result['message'];
}
```

### Function: `normalizePhoneNumber()`

**Signature:**
```php
function normalizePhoneNumber($phone)
```

**Parameter:**
- `$phone` (string): Nomor dalam berbagai format

**Return:**
- `string`: Nomor terstandardisasi (62xxxx) atau `null` jika invalid

**Contoh:**
```php
normalizePhoneNumber('081234567890');      // → 6281234567890
normalizePhoneNumber('+6281234567890');    // → 6281234567890
normalizePhoneNumber('6281234567890');     // → 6281234567890
```

### Function: `getMessageTemplate()`

**Signature:**
```php
function getMessageTemplate($type, $data = [])
```

**Parameter:**
- `$type` (string): Tipe template
  - `reservasi_pending`: Konfirmasi reservasi awal
  - `pembayaran_sukses`: Notifikasi pembayaran berhasil
  - `pembayaran_pending`: Reminder pembayaran
  - `reminder_pembayaran`: Pengingat pembayaran

- `$data` (array): Data untuk template
  ```php
  [
      'id' => 123,
      'nama' => 'John Doe',
      'tanggal' => '2025-11-15',
      'jam' => '19:00',
      'jumlah' => 4,
      'telepon' => '081234567890',
      'metode' => 'tunai',
      'total' => 200000
  ]
  ```

**Return:**
- `string`: Pesan siap dikirim

---

## Struktur File

```
sasuki_app/
├── config/
│   ├── koneksi.php           # Database connection
│   └── wa_config.php         # WhatsApp configuration
├── assets/
│   ├── style.css
│   └── reservasi.css
├── reservasi/
│   ├── index.php             # Form reservasi
│   ├── pembayaran.php        # Halaman pembayaran
│   ├── proses_tambah.php     # Process form (save + send WA)
│   ├── proses_pembayaran.php # Process payment (update + send WA)
│   ├── sukses.php            # Success page
│   ├── data_reservasi.php    # View all reservations
│   └── map_meja.php          # Table map visualization
├── logs/
│   ├── reservasi.log         # Reservation logs
│   ├── pembayaran.log        # Payment logs
│   └── wa_messages.log       # WhatsApp message logs (development)
├── img/                      # Images folder
└── README.md                 # This file
```

---

## Fitur & Validasi

### ✅ Validasi Input

1. **Nama Pelanggan**
   - Minimal 3 karakter
   - Hanya huruf dan spasi
   - Required

2. **Nomor Telepon**
   - Format: 10-13 digit
   - Accepts: 0xxxxxxxxxx, +62xxxxxxxxxx, 62xxxxxxxxxx
   - Required

3. **Tanggal**
   - Tidak boleh di masa lalu
   - Format: YYYY-MM-DD
   - Required

4. **Jam**
   - Format: HH:MM
   - Required

5. **Jumlah Orang**
   - Minimal: 1 orang
   - Maksimal: 20 orang
   - Required

6. **Catatan** (Opsional)
   - Dapat berisi alergi, preferensi, dll

### 📱 WhatsApp Messages

**Message 1: Konfirmasi Reservasi**
- Dikirim setelah form disubmit
- Berisi detail reservasi
- Instruksi untuk lanjut pembayaran

**Message 2: Konfirmasi Pembayaran**
- Dikirim setelah pembayaran dikonfirmasi
- Berisi detail lengkap
- Instruksi tentang durasi makan (90 menit)

### 💾 Data Logging

Sistem mencatat semua aktivitas di folder `logs/`:
- `reservasi.log`: Setiap reservasi baru + status WA
- `pembayaran.log`: Setiap pembayaran + status WA
- `wa_messages.log`: (Development mode) Raw messages

---

## Troubleshooting

### ❌ Pesan WhatsApp Tidak Terkirim

**1. Check Fonnte Token**
```php
// Di wa_config.php, pastikan token sudah diganti
if (FONNTE_TOKEN === 'YOUR_FONNTE_API_TOKEN') {
    // ERROR: Token belum dikonfigurasi
}
```

**2. Check Nomor Telepon**
```
✅ Valid: 081234567890, +6281234567890, 6281234567890
❌ Invalid: 1234567890 (tanpa kode negara)
```

**3. Check Log File**
```
Lihat file: logs/pembayaran.log
Cari entry terbaru untuk melihat error message
```

**4. Test Mode (Development)**
```php
// Ubah ke local mode untuk test tanpa API
define('WA_GATEWAY', 'local');

// Kemudian check file logs/wa_messages.log
```

### ❌ Database Error

**Error: Table `reservasi` tidak ada**
```bash
# Run SQL yang ada di folder /migration atau
# Jalankan query di atas untuk membuat tabel
```

**Error: Kolom tidak ditemukan**
- Periksa struktur tabel di database Anda
- Pastikan semua kolom yang digunakan ada

### ⚠️ Redirect Loop

**Problem:** Halaman terus redirect ke halaman awal
- Check: Session/cookie sudah di-set dengan benar?
- Check: Database connection aktif?
- Check: ID reservasi valid di URL?

### 🐛 Debug Mode

Tambahkan di file manapun untuk debugging:
```php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log ke file
error_log("Debug message: " . print_r($data, true));
```

---

## Security Notes

1. **SQL Injection Prevention**
   - Semua input di-escape menggunakan `mysqli_real_escape_string()`
   - Gunakan prepared statements untuk production

2. **Validation**
   - Input divalidasi di client (JavaScript)
   - Input divalidasi ulang di server (PHP)

3. **Sensitive Data**
   - Nomor telepon tidak ditampilkan full (disembunyikan di success page)
   - Token API harus disimpan aman (environment variable untuk production)

4. **HTTPS**
   - Untuk production, pastikan menggunakan HTTPS
   - WhatsApp API memerlukan HTTPS

---

## Contact & Support

Untuk bantuan lebih lanjut:
- Dokumentasi Fonnte: https://docs.fonnte.com
- Developer: [Nomor WhatsApp Anda]

---

**Version:** 1.0.0  
**Last Updated:** November 11, 2025  
**License:** MIT
