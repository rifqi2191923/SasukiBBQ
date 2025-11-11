# 🧪 Testing Guide - SASUKI BBQ Reservation System

Panduan lengkap untuk testing sistem reservasi SASUKI BBQ.

---

## 🚀 Quick Start Testing

### 1. Setup Database
1. Buka: `http://localhost/sasuki_app/setup.php`
2. Klik tombol "Buat Tabel Reservasi"
3. Tunggu hingga selesai
4. Lihat status "OK" untuk tabel

### 2. Configure WhatsApp

**Option A: Test dengan Fonnte (Real WA)**
1. Daftar di https://fonnte.com
2. Get API Token
3. Edit `config/wa_config.php`:
   ```php
   define('FONNTE_TOKEN', 'YOUR_TOKEN_HERE');
   ```
4. Test dengan nomor WhatsApp Anda

**Option B: Test dengan Local Mode**
1. Edit `config/wa_config.php`:
   ```php
   define('WA_GATEWAY', 'local');
   ```
2. Pesan akan disimpan ke `logs/wa_messages.log`
3. Cek file tersebut untuk verify

### 3. Start Testing
Buka: `http://localhost/sasuki_app/reservasi/`

---

## 📝 Test Cases

### Test Case 1: Form Validation

#### Test 1.1: Empty Name
```
Input:
  Nama: [kosong]
  Telepon: 08123456789
  Tanggal: 2025-11-20
  Jam: 19:00
  Jumlah: 2

Expected: ❌ Error - "Data Tidak Lengkap"
```

#### Test 1.2: Invalid Phone Number
```
Input:
  Nama: John Doe
  Telepon: 123 (terlalu pendek)
  Tanggal: 2025-11-20
  Jam: 19:00
  Jumlah: 2

Expected: ❌ Error - "Nomor Telepon Invalid"
```

#### Test 1.3: Past Date
```
Input:
  Nama: John Doe
  Telepon: 08123456789
  Tanggal: 2025-11-01 (tanggal lampau)
  Jam: 19:00
  Jumlah: 2

Expected: ❌ Error - "Tanggal Invalid"
```

#### Test 1.4: Invalid Quantity
```
Input:
  Nama: John Doe
  Telepon: 08123456789
  Tanggal: 2025-11-20
  Jam: 19:00
  Jumlah: 25 (lebih dari 20)

Expected: ❌ Error - "Data Tidak Lengkap"
```

#### Test 1.5: Valid Form
```
Input:
  Nama: John Doe
  Telepon: 08123456789
  Tanggal: 2025-11-20
  Jam: 19:00
  Jumlah: 4
  Catatan: Tidak ada seafood

Expected: ✅ Redirect ke pembayaran.php?id=1
Expected: ✅ Data masuk database dengan status 'pending'
Expected: ✅ WhatsApp #1 terkirim (atau logged)
```

---

### Test Case 2: Database Storage

#### Test 2.1: Data Tersimpan dengan Benar
```
Action: Submit form dengan data valid

Query untuk check:
  SELECT * FROM reservasi WHERE id = 1;

Expected Result:
  ✅ nama_pelanggan = "John Doe"
  ✅ telepon = "08123456789"
  ✅ tanggal = "2025-11-20"
  ✅ jam = "19:00:00"
  ✅ jumlah_orang = 4
  ✅ status = "pending"
  ✅ metode_pembayaran = NULL
  ✅ catatan = "Tidak ada seafood"
  ✅ created_at = [timestamp]
```

---

### Test Case 3: Halaman Pembayaran

#### Test 3.1: Detail Reservasi Tampil
```
Action: Redirect otomatis ke pembayaran.php?id=1

Expected:
  ✅ Halaman pembayaran muncul
  ✅ Detail reservasi ditampilkan:
    - ID: #1
    - Nama: John Doe
    - Telepon: 08123456789
    - Tanggal: 20 Nov 2025
    - Jam: 19:00
    - Jumlah: 4 orang
    - Catatan: Tidak ada seafood
    - Status: Menunggu Pembayaran
```

#### Test 3.2: Payment Method Selection
```
Action: Klik radio button untuk setiap metode

Expected:
  ✅ Tunai - border berubah merah
  ✅ Transfer - border berubah merah
  ✅ QRIS - border berubah merah
  ✅ Total: Rp 200.000 ditampilkan (4 × Rp 50.000)
```

#### Test 3.3: Submit Pembayaran
```
Action: 
  1. Pilih metode: "Tunai"
  2. Klik "Konfirmasi Pembayaran"

Expected:
  ✅ Button disabled & text "Memproses..."
  ✅ Redirect ke sukses.php?id=1
```

---

### Test Case 4: Pembayaran Processing

#### Test 4.1: Status Update ke Database
```
Query:
  SELECT status, metode_pembayaran FROM reservasi WHERE id = 1;

Expected:
  ✅ status = "dibayar"
  ✅ metode_pembayaran = "tunai"
  ✅ updated_at = [current timestamp]
```

#### Test 4.2: WhatsApp Notification #2
```
Expected:
  ✅ WhatsApp terkirim ke nomor 08123456789
  
  Message Content:
  ```
  ✅ PEMBAYARAN BERHASIL
  Reservasi Anda telah dikonfirmasi!
  
  *Detail Reservasi:*
  📋 ID: #1
  👤 Nama: John Doe
  📅 Tanggal: 20 Nov 2025
  🕐 Jam: 19:00
  👥 Jumlah Orang: 4
  💳 Metode: Tunai
  
  Kami sudah menerima pembayaran Anda.
  Meja akan dipersiapkan untuk Anda.
  
  Sampai jumpa di SASUKI BBQ! 🎉
  ```
```

---

### Test Case 5: Success Page

#### Test 5.1: Detail Ditampilkan
```
Expected:
  ✅ Success icon muncul (✅)
  ✅ "Pembayaran Berhasil!" heading
  ✅ Detail lengkap tersedia:
    - ID: #1
    - Nama: John Doe
    - Telepon: 08...6789 (partially hidden)
    - Tanggal: 20 Nov 2025
    - Jam: 19:00
    - Jumlah: 4 orang
    - Catatan: Tidak ada seafood
  ✅ Status: ✅ Pembayaran Dikonfirmasi
  ✅ Total: Rp 200.000
  ✅ Info box: Konfirmasi dikirim ke WA
```

#### Test 5.2: Print Function
```
Action: Klik "🖨️ Cetak Bukti"

Expected:
  ✅ Print dialog muncul
  ✅ Halaman siap untuk print
  ✅ Action buttons tidak tercetak
  ✅ Info box tidak tercetak
```

#### Test 5.3: Back to Home
```
Action: Klik "🏠 Kembali ke Beranda"

Expected:
  ✅ Redirect ke reservasi/index.php
  ✅ Form kosong siap untuk reservasi baru
```

---

### Test Case 6: Phone Number Normalization

#### Test 6.1: Nomor Lokal
```
Input: 081234567890
Expected Output: 6281234567890
WA Target: ✅ Terkirim
```

#### Test 6.2: Nomor dengan +62
```
Input: +6281234567890
Expected Output: 6281234567890
WA Target: ✅ Terkirim
```

#### Test 6.3: Nomor dengan 62
```
Input: 6281234567890
Expected Output: 6281234567890
WA Target: ✅ Terkirim
```

#### Test 6.4: Invalid Format
```
Input: 123456789 (kurang dari 10 digit)
Expected: ❌ Validation error
```

---

### Test Case 7: Logging & Monitoring

#### Test 7.1: Reservasi Log
```
Check: logs/reservasi.log

Expected Content:
  [2025-11-11 14:30:45] - Reservasi #1 - WA: SUCCESS
  [2025-11-11 14:31:20] - Reservasi #2 - WA: FAILED - Invalid token
```

#### Test 7.2: Pembayaran Log
```
Check: logs/pembayaran.log

Expected Content:
  [2025-11-11 14:31:00] - Reservasi #1 - WA: SUCCESS
  [2025-11-11 14:32:15] - Reservasi #2 - WA: SUCCESS
```

#### Test 7.3: WhatsApp Log (Local Mode)
```
Check: logs/wa_messages.log

Expected Content:
  {"timestamp":"2025-11-11 14:30:45","phone":"6281234567890","message":"🍖 KONFIRMASI RESERVASI..."}
  {"timestamp":"2025-11-11 14:31:00","phone":"6281234567890","message":"✅ PEMBAYARAN BERHASIL..."}
```

---

## 🔍 Debug Scenarios

### Scenario 1: WhatsApp Tidak Terkirim

**Checklist:**
1. ✅ Token Fonnte benar? 
   ```php
   // Di config/wa_config.php
   echo FONNTE_TOKEN; // Harus bukan "YOUR_FONNTE_API_TOKEN"
   ```

2. ✅ Nomor telepon valid?
   ```
   ✅ 08123456789
   ✅ +6281234567890
   ❌ 123456789 (tidak cukup digit)
   ```

3. ✅ Internet connection aktif?
   ```bash
   curl -I https://api.fonnte.com
   ```

4. ✅ Check logs:
   ```
   Lihat: logs/pembayaran.log
   Cari error message
   ```

5. ✅ Test dengan local mode:
   ```php
   define('WA_GATEWAY', 'local');
   // Check logs/wa_messages.log
   ```

---

### Scenario 2: Database Connection Error

**Checklist:**
1. ✅ MySQL running?
   ```bash
   # Check di XAMPP Control Panel
   ```

2. ✅ Config parameters benar?
   ```php
   $host = "localhost";
   $user = "root";
   $pass = ""; // atau password Anda
   $db   = "sasuki_db";
   ```

3. ✅ Database & tabel ada?
   ```sql
   SHOW DATABASES LIKE 'sasuki_db';
   SHOW TABLES LIKE 'reservasi';
   ```

---

### Scenario 3: Form Tidak Submit

**Checklist:**
1. ✅ Browser console ada error?
   - Open: F12 → Console tab
   - Lihat red errors

2. ✅ Form elements benar?
   ```html
   <form action="proses_tambah.php" method="POST">
   ```

3. ✅ JavaScript enabled?
   - Check browser settings

4. ✅ File proses_tambah.php ada?
   ```bash
   ls -la reservasi/proses_tambah.php
   ```

---

## 📊 Performance Testing

### Load Testing

**Test 1: Multiple Submissions**
```
Action: Submit 10 reservasi cepat

Expected:
  ✅ Semua data tersimpan
  ✅ Tidak ada data duplicate
  ✅ WA terkirim untuk setiap reservasi
  ✅ Performance masih baik
```

**Test 2: Large Payload**
```
Input: Catatan sangat panjang (1000 karakter)

Expected:
  ✅ Data tersimpan dengan benar
  ✅ Tidak ada truncation
  ✅ WA message tetap terkirim
```

---

## ✅ Final Checklist

- [ ] Database setup berhasil
- [ ] Tabel reservasi ada dengan struktur benar
- [ ] Folder logs dibuat & writable
- [ ] WhatsApp gateway dikonfigurasi
- [ ] Form validation working (client & server)
- [ ] Data tersimpan ke database
- [ ] WA notifikasi terkirim
- [ ] Halaman pembayaran muncul
- [ ] Pembayaran bisa dikonfirmasi
- [ ] Halaman sukses muncul
- [ ] WA notifikasi #2 terkirim
- [ ] Cetak bukti working
- [ ] Log files terisi dengan benar
- [ ] Responsive di mobile
- [ ] Tidak ada security issues

---

## 🎯 Testing Results Template

Gunakan template ini untuk dokumentasi testing:

```
DATE: [DD/MM/YYYY]
TESTER: [Nama]

TEST CASE 1: Form Validation
- Input Valid: ✅ PASS / ❌ FAIL
- Input Invalid: ✅ PASS / ❌ FAIL
- Error Message: ✅ PASS / ❌ FAIL

TEST CASE 2: Database
- Data Stored: ✅ PASS / ❌ FAIL
- Status Correct: ✅ PASS / ❌ FAIL

TEST CASE 3: Payment
- Halaman Muncul: ✅ PASS / ❌ FAIL
- Detail Akurat: ✅ PASS / ❌ FAIL
- Submit Working: ✅ PASS / ❌ FAIL

TEST CASE 4: Success
- Halaman Muncul: ✅ PASS / ❌ FAIL
- WA Terkirim: ✅ PASS / ❌ FAIL
- Print Working: ✅ PASS / ❌ FAIL

OVERALL: ✅ PASS / ❌ FAIL

NOTES:
[Catatan tambahan]

SIGNED: [Tanda tangan/Nama Tester]
```

---

## 📞 Troubleshooting Reference

| Issue | Cause | Solution |
|-------|-------|----------|
| Tabel tidak ada | SQL error | Run setup.php |
| WA tidak terkirim | Token salah | Check config/wa_config.php |
| Form tidak submit | JS error | Check browser console |
| Data tidak tersimpan | DB error | Check mysqli error log |
| Halaman blank | PHP error | Enable display_errors |
| Nomor tidak dikenali | Format salah | Gunakan format +62 |

---

**Happy Testing!** 🚀

Last Update: November 11, 2025
