# ✅ Testing Checklist - Payment Flow v2.0

## 🎯 Testing Overview

Dokumen ini berisi testing checklist lengkap untuk payment flow baru dengan bukti pembayaran.

**Total Test Cases**: 35
**Estimated Time**: 30-45 menit

---

## 1️⃣ DATABASE & SETUP

### Test 1.1 - Database Migration
- [ ] Buka: `http://localhost/sasuki_app/migrate.php`
- [ ] Login migration (jika ada)
- [ ] Klik "Tambah kolom bukti pembayaran"
- [ ] Verifikasi: Message "Berhasil" tampil

**Expected:**
- ✅ Kolom `bukti_pembayaran` ditambah
- ✅ Kolom `bukti_verified` ditambah

### Test 1.2 - Database Verification
```sql
DESCRIBE reservasi;
```
- [ ] Kolom `bukti_pembayaran` ada tipe VARCHAR(255)
- [ ] Kolom `bukti_verified` ada tipe INT DEFAULT 0

---

## 2️⃣ FORM RESERVASI

### Test 2.1 - Form Validasi Lengkap
- [ ] Buka: `http://localhost/sasuki_app/reservasi/index.php`
- [ ] Isi semua field dengan data valid:
  - Nama: Budi Santoso
  - Telepon: 081234567890
  - Tanggal: [Hari ini atau masa depan]
  - Jam: 19:00
  - Jumlah: 4
  - Catatan: (Optional)
- [ ] Click Submit

**Expected:**
- ✅ Tidak ada error
- ✅ Redirect ke `pembayaran.php?id=[reservasi_id]`

### Test 2.2 - Form Validasi Error
- [ ] Isi form dengan data invalid:
  - Nama: (kosong)
  - Telepon: 123 (terlalu pendek)
  - Tanggal: [Tanggal kemarin]
- [ ] Click Submit

**Expected:**
- ✅ Tampil error message
- ✅ Tidak redirect

---

## 3️⃣ HALAMAN PEMBAYARAN (pembayaran.php)

### Test 3.1 - Layout & Display
- [ ] Buka halaman pembayaran (dari Test 2.1)
- [ ] Verifikasi layout 2 kolom:
  - Kolom kiri: Detail Reservasi (hijau)
  - Kolom kanan: Pembayaran (putih)

**Expected:**
- ✅ Detail reservasi tampil lengkap (nama, telepon, tanggal, jam, jumlah, catatan jika ada)
- ✅ Total pembayaran benar: jumlah × Rp 50.000

### Test 3.2 - Payment Methods Display
- [ ] Verifikasi hanya 2 metode tampil:
  - 🏦 Transfer Bank
  - 📱 QRIS
- [ ] ❌ Metode "Tunai" TIDAK ada

**Expected:**
- ✅ Transfer Bank dan QRIS terlihat jelas
- ✅ Tombol "Lanjutkan ke Pembayaran" tersedia

### Test 3.3 - Transfer Bank Selection
- [ ] Click pada kartu "Transfer Bank"
- [ ] Verifikasi kartu ter-highlight (background berubah)
- [ ] Tombol "Lanjutkan ke Pembayaran" muncul

**Expected:**
- ✅ Kartu Transfer Bank aktif
- ✅ Tombol Lanjutkan muncul

### Test 3.4 - QRIS Selection
- [ ] Click pada kartu "QRIS"
- [ ] Verifikasi kartu ter-highlight

**Expected:**
- ✅ Kartu QRIS aktif
- ✅ Tombol Lanjutkan muncul

---

## 4️⃣ MODAL TRANSFER BANK

### Test 4.1 - Transfer Bank Modal Display
- [ ] Pilih Transfer Bank
- [ ] Click "Lanjutkan ke Pembayaran"
- [ ] Verifikasi modal terbuka dengan:
  - Title: "🏦 Pilih Bank & Rekening"
  - Grid 6 pilihan bank

**Expected:**
- ✅ Modal muncul smooth (animasi)
- ✅ Bank options: BCA, Mandiri, BRI, CIMB, OVO, Dana

### Test 4.2 - Select Bank BCA
- [ ] Klik kartu bank "BCA"
- [ ] Verifikasi:
  - Kartu BCA ter-highlight
  - Nomor rekening tampil di box bawah
  - Field account info muncul

**Expected:**
- ✅ BCA highlighted
- ✅ Nomor rekening tampil benar
- ✅ Tombol "Salin Nomor Rekening" aktif

### Test 4.3 - Copy Account Number
- [ ] Click "Salin Nomor Rekening"
- [ ] Paste di notepad (Ctrl+V)
- [ ] Verifikasi nomor tercopas benar

**Expected:**
- ✅ Nomor tersalin ke clipboard
- ✅ Button text berubah ke "✅ Tersalin!" (2 detik)

### Test 4.4 - Select Different Banks
- [ ] Ulangi Test 4.2 untuk:
  - [ ] Mandiri
  - [ ] BRI
  - [ ] CIMB
  - [ ] OVO
  - [ ] Dana

**Expected:**
- ✅ Setiap bank punya nomor rekening unik
- ✅ Nomor selalu benar

### Test 4.5 - Continue to Upload
- [ ] Select bank
- [ ] Click "Lanjut Upload Bukti"
- [ ] Verifikasi redirect ke upload_bukti.php

**Expected:**
- ✅ Modal close
- ✅ Redirect ke halaman upload bukti

---

## 5️⃣ MODAL QRIS

### Test 5.1 - QRIS Modal Display
- [ ] Pilih QRIS (dari halaman pembayaran fresh)
- [ ] Click "Lanjutkan ke Pembayaran"
- [ ] Verifikasi modal terbuka dengan:
  - Title: "📱 Pembayaran QRIS"
  - QR Code tampil
  - Instruksi cara scan

**Expected:**
- ✅ Modal muncul smooth
- ✅ QR code besar dan jelas
- ✅ Instruksi readable

### Test 5.2 - QR Code Validity
- [ ] Scan QR code dengan smartphone
- [ ] Verifikasi QR code scannable

**Expected:**
- ✅ QR code valid dan scannable
- ✅ Berisi data reservasi

### Test 5.3 - QRIS Continue to Upload
- [ ] Click "Lanjut Upload Bukti"
- [ ] Verifikasi redirect ke upload_bukti.php

**Expected:**
- ✅ Modal close
- ✅ Redirect ke upload bukti

---

## 6️⃣ HALAMAN UPLOAD BUKTI (upload_bukti.php) ⭐

### Test 6.1 - Page Display
- [ ] Buka halaman upload (dari flow pembayaran)
- [ ] Verifikasi tampilan:
  - Header merah dengan title
  - Info section: ID, Nama, Total, Metode
  - Warning checklist
  - Upload section drag & drop
  - Note section
  - Tombol Kembali & Upload

**Expected:**
- ✅ Semua elemen tampil
- ✅ Layout responsive

### Test 6.2 - Upload Area Click
- [ ] Click area upload
- [ ] Verifikasi file picker muncul

**Expected:**
- ✅ File dialog terbuka

### Test 6.3 - Upload Valid File
- [ ] Pilih file JPG valid (< 5MB, resolusi normal)
- [ ] Verifikasi:
  - File info tampil (nama & ukuran)
  - Tombol Upload aktif

**Expected:**
- ✅ File preview tampil
- ✅ Ukuran file terlihat
- ✅ Submit button enabled

### Test 6.4 - Upload Invalid Format
- [ ] Upload file PDF / DOC
- [ ] Verifikasi error message

**Expected:**
- ✅ Error: "Format file tidak valid"
- ✅ File tidak disimpan

### Test 6.5 - Upload Oversized File
- [ ] Upload file > 5MB
- [ ] Verifikasi error message

**Expected:**
- ✅ Error: "Ukuran file terlalu besar"
- ✅ File tidak disimpan

### Test 6.6 - Drag & Drop
- [ ] Drag file JPG valid ke area
- [ ] Drop file

**Expected:**
- ✅ File terbaca
- ✅ File info tampil
- ✅ Submit button enabled

### Test 6.7 - Submit Upload
- [ ] Click "✅ Upload & Lanjutkan"
- [ ] Tunggu loading

**Expected:**
- ✅ File tersimpan ke folder `bukti_pembayaran/`
- ✅ Database terupdate dengan path file
- ✅ Redirect ke `sukses.php?id=[id]`
- ✅ WhatsApp notification terkirim

---

## 7️⃣ HALAMAN SUKSES (sukses.php) - UPDATED

### Test 7.1 - Sukses Page Display
- [ ] Dari upload bukti redirect ke sukses
- [ ] Verifikasi tampilan:
  - Header hijau dengan checkmark
  - Detail reservasi (kolom kiri)
  - Pembayaran info (kolom kanan)
  - Status verifikasi: "⏳ Menunggu Verifikasi"

**Expected:**
- ✅ Halaman sukses tampil
- ✅ Status verifikasi pending

### Test 7.2 - Pending Verification Warning
- [ ] Lihat warning section
- [ ] Verifikasi text: "⏳ Proses Verifikasi..."

**Expected:**
- ✅ Warning muncul
- ✅ Info 5-10 menit verifikasi terlihat

### Test 7.3 - Print Button
- [ ] Click "🖨️ Cetak Bukti"
- [ ] Verifikasi print dialog muncul

**Expected:**
- ✅ Print dialog terbuka
- ✅ Layout print-friendly

### Test 7.4 - Home Button
- [ ] Click "🏠 Kembali ke Beranda"
- [ ] Verifikasi redirect ke index.php

**Expected:**
- ✅ Redirect ke halaman utama

---

## 8️⃣ ADMIN DASHBOARD (admin/verifikasi_bukti.php) ⭐

### Test 8.1 - Admin Login Page
- [ ] Buka: `http://localhost/sasuki_app/admin/verifikasi_bukti.php`
- [ ] Verifikasi login form tampil

**Expected:**
- ✅ Login page muncul
- ✅ Input password field ada

### Test 8.2 - Login Invalid Password
- [ ] Input password: "salah"
- [ ] Click Login
- [ ] Verifikasi error message

**Expected:**
- ✅ Error: "Password salah!"
- ✅ Tetap di halaman login

### Test 8.3 - Login Valid Password
- [ ] Input password: "admin123" (atau password yg dikonfigurasi)
- [ ] Click Login

**Expected:**
- ✅ Login berhasil
- ✅ Redirect ke dashboard verifikasi

### Test 8.4 - Dashboard Table Display
- [ ] Dari login admin, lihat tabel
- [ ] Verifikasi kolom:
  - ID
  - Nama
  - Total
  - Metode
  - Status
  - Aksi

**Expected:**
- ✅ Tabel terlihat dengan data
- ✅ Minimal 1 baris data (dari upload sebelumnya)

### Test 8.5 - View Bukti
- [ ] Klik tombol "Lihat" pada row pertama
- [ ] Verifikasi modal muncul dengan:
  - Gambar bukti
  - Nama pemesan

**Expected:**
- ✅ Modal bukti muncul
- ✅ Gambar bisa dilihat dengan jelas

### Test 8.6 - Close View Modal
- [ ] Click X button atau area luar
- [ ] Verifikasi modal close

**Expected:**
- ✅ Modal tertutup
- ✅ Kembali ke tabel

### Test 8.7 - Approve Bukti
- [ ] Klik "Approve" pada bukti dengan status Pending
- [ ] Verifikasi alert "Bukti pembayaran disetujui!"
- [ ] Tunggu redirect

**Expected:**
- ✅ Status berubah ke "✅ Approved"
- ✅ Database `bukti_verified = 1`
- ✅ WhatsApp approval notifikasi terkirim

### Test 8.8 - Reject Bukti
- [ ] (Optional: buat bukti baru dulu dari flow pembayaran)
- [ ] Klik "Reject" pada bukti pending
- [ ] Modal reject muncul
- [ ] Input alasan: "Bukti tidak terlihat jelas"
- [ ] Click "Tolak"

**Expected:**
- ✅ Modal reject muncul
- ✅ Alasan terisi
- ✅ Status berubah ke "❌ Rejected"
- ✅ Database `bukti_verified = -1`
- ✅ WhatsApp rejection notifikasi terkirim dengan alasan

### Test 8.9 - Logout
- [ ] Click "Logout" button
- [ ] Verify konfirmasi muncul
- [ ] Click OK

**Expected:**
- ✅ Logout berhasil
- ✅ Redirect ke login page

---

## 9️⃣ WHATSAPP NOTIFICATIONS

### Test 9.1 - Reservation Pending WA
- [ ] Submit reservasi form
- [ ] Verifikasi WA notification terkirim ke telepon reservasi
- [ ] Message berisi: ID, Nama, Tanggal, Jam, Total

**Expected:**
- ✅ WA message terkirim
- ✅ Info lengkap terlihat

### Test 9.2 - Bukti Received WA
- [ ] Upload bukti pembayaran
- [ ] Verifikasi WA notification terkirim
- [ ] Message: "📱 *Bukti Pembayaran Diterima*"

**Expected:**
- ✅ WA message terkirim
- ✅ Mention bukti diterima

### Test 9.3 - Approval WA
- [ ] Approve bukti dari admin
- [ ] Verifikasi WA notification terkirim
- [ ] Message: "✅ *Pembayaran Anda Telah Terverifikasi!*"

**Expected:**
- ✅ WA message terkirim
- ✅ Approval confirmation jelas

### Test 9.4 - Rejection WA
- [ ] Reject bukti dengan alasan
- [ ] Verifikasi WA notification terkirim
- [ ] Message: "⚠️ *Bukti Pembayaran Ditolak*"
- [ ] Alasan tertera di message

**Expected:**
- ✅ WA message terkirim
- ✅ Alasan penolakan include
- ✅ Instruksi upload ulang ada

---

## 🔟 RESPONSIVE DESIGN

### Test 10.1 - Mobile View (Pembayaran)
- [ ] Buka `pembayaran.php` di mobile (375px)
- [ ] Verifikasi layout:
  - Stack vertical (tidak side-by-side)
  - Tombol full-width
  - Text readable

**Expected:**
- ✅ Layout responsive
- ✅ Tidak ada horizontal scroll

### Test 10.2 - Mobile View (Upload Bukti)
- [ ] Buka `upload_bukti.php` di mobile
- [ ] Verifikasi upload area responsive

**Expected:**
- ✅ Drag & drop bekerja
- ✅ Layout mobile-friendly

### Test 10.3 - Mobile View (Admin)
- [ ] Buka admin dashboard di mobile
- [ ] Verifikasi tabel responsive

**Expected:**
- ✅ Tabel bisa scroll horizontal
- ✅ Button responsive

---

## 1️⃣1️⃣ EDGE CASES

### Test 11.1 - Rapid Successive Uploads
- [ ] Submit form reservasi
- [ ] Rapid-click tombol "Lanjutkan ke Pembayaran" 2x
- [ ] Verifikasi hanya 1 request terproses

**Expected:**
- ✅ No duplicate submission
- ✅ Button disabled setelah click

### Test 11.2 - Back Button After Upload
- [ ] Upload bukti
- [ ] Redirect ke sukses
- [ ] Click browser back button
- [ ] Verifikasi behavior

**Expected:**
- ✅ Upload data tetap tersimpan
- ✅ Tidak re-upload

### Test 11.3 - Refresh After Upload
- [ ] Upload bukti
- [ ] Refresh page (F5)
- [ ] Verifikasi data tetap ada

**Expected:**
- ✅ Data dari database tetap ada
- ✅ Tidak ada duplicate upload

### Test 11.4 - File Size Boundary
- [ ] Upload file exactly 5MB
- [ ] Verifikasi upload berhasil

**Expected:**
- ✅ Upload accepted
- ✅ Tidak error size

---

## 1️⃣2️⃣ DATABASE INTEGRITY

### Test 12.1 - Database Record
```sql
SELECT * FROM reservasi ORDER BY id DESC LIMIT 1;
```
- [ ] Verify fields:
  - `id` ada
  - `nama_pelanggan` terisi
  - `telepon` terisi
  - `status` = 'pending' / 'dibayar'
  - `metode_pembayaran` terisi (transfer/qris)
  - `bukti_pembayaran` path terisi
  - `bukti_verified` = 0 awalnya

**Expected:**
- ✅ Semua field terisi benar
- ✅ Data konsisten

### Test 12.2 - After Approval
```sql
SELECT bukti_verified FROM reservasi WHERE id = [id_test];
```
- [ ] Verify `bukti_verified = 1` after admin approve

**Expected:**
- ✅ Value = 1

### Test 12.3 - After Rejection
```sql
SELECT bukti_verified FROM reservasi WHERE id = [id_test];
```
- [ ] Verify `bukti_verified = -1` after admin reject

**Expected:**
- ✅ Value = -1

---

## 1️⃣3️⃣ SECURITY CHECKS

### Test 13.1 - File Access Protection
- [ ] Try access: `http://localhost/sasuki_app/bukti_pembayaran/[filename].php`
- [ ] Verifikasi file tidak bisa diakses/dijalankan

**Expected:**
- ✅ Forbidden error (403) atau blank
- ✅ .htaccess working

### Test 13.2 - File Type Validation
- [ ] Rename file.jpg ke file.php
- [ ] Try upload sebagai JPG
- [ ] Verifikasi file tetap JPG (tidak dijalankan sebagai PHP)

**Expected:**
- ✅ File tetap image type
- ✅ No code execution

### Test 13.3 - SQL Injection Test
- [ ] Di form input: `' OR '1'='1`
- [ ] Submit
- [ ] Verifikasi error handling proper

**Expected:**
- ✅ Input di-escape dengan baik
- ✅ No SQL injection vulnerability
- ✅ Error message user-friendly

---

## SUMMARY TABLE

| # | Test | Status | Notes |
|---|------|--------|-------|
| 1.1 | Database Migration | ☐ | |
| 1.2 | Database Verification | ☐ | |
| 2.1 | Form Valid | ☐ | |
| 2.2 | Form Invalid | ☐ | |
| 3.1 | Layout Display | ☐ | |
| 3.2 | Payment Methods | ☐ | |
| 3.3 | Transfer Selection | ☐ | |
| 3.4 | QRIS Selection | ☐ | |
| 4.1 | Bank Modal | ☐ | |
| 4.2 | Select Bank | ☐ | |
| 4.3 | Copy Account | ☐ | |
| 4.4 | All Banks | ☐ | |
| 4.5 | Continue Upload | ☐ | |
| 5.1 | QRIS Modal | ☐ | |
| 5.2 | QR Validity | ☐ | |
| 5.3 | Continue Upload | ☐ | |
| 6.1 | Upload Page | ☐ | |
| 6.2 | Click Upload | ☐ | |
| 6.3 | Valid File | ☐ | |
| 6.4 | Invalid Format | ☐ | |
| 6.5 | Oversized File | ☐ | |
| 6.6 | Drag & Drop | ☐ | |
| 6.7 | Submit Upload | ☐ | |
| 7.1 | Sukses Display | ☐ | |
| 7.2 | Pending Warning | ☐ | |
| 7.3 | Print Button | ☐ | |
| 7.4 | Home Button | ☐ | |
| 8.1 | Admin Login | ☐ | |
| 8.2 | Invalid Password | ☐ | |
| 8.3 | Valid Password | ☐ | |
| 8.4 | Dashboard Table | ☐ | |
| 8.5 | View Bukti | ☐ | |
| 8.6 | Close Modal | ☐ | |
| 8.7 | Approve | ☐ | |
| 8.8 | Reject | ☐ | |
| 8.9 | Logout | ☐ | |
| 9.1 | Reservation WA | ☐ | |
| 9.2 | Bukti Received WA | ☐ | |
| 9.3 | Approval WA | ☐ | |
| 9.4 | Rejection WA | ☐ | |
| 10.1 | Mobile Payment | ☐ | |
| 10.2 | Mobile Upload | ☐ | |
| 10.3 | Mobile Admin | ☐ | |
| 11.1 | Rapid Submission | ☐ | |
| 11.2 | Back Button | ☐ | |
| 11.3 | Page Refresh | ☐ | |
| 11.4 | File Size Boundary | ☐ | |
| 12.1 | DB Record | ☐ | |
| 12.2 | DB After Approval | ☐ | |
| 12.3 | DB After Rejection | ☐ | |
| 13.1 | File Protection | ☐ | |
| 13.2 | Type Validation | ☐ | |
| 13.3 | SQL Injection | ☐ | |

**Total Tests**: 53
**Pass**: ☐ / 53

---

**Testing Date**: ________________
**Tested By**: ________________
**Status**: ☐ PASS / ☐ FAIL

**Notes/Issues Found:**

_______________________________________________________________

_______________________________________________________________

_______________________________________________________________

---

**Sign-off**: _______________________
