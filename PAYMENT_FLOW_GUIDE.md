# 🏢 Panduan Fitur Payment Flow Baru - SASUKI BBQ

## 📋 Ringkasan Perubahan

Sistem pembayaran telah diupdate dengan flow yang lebih terstruktur dan aman:

```
Reservasi → Pembayaran (Pilih Bank/QRIS) → Upload Bukti → Verifikasi Admin → Sukses
```

## 🔄 Alur Pembayaran

### 1. **Halaman Reservasi** (index.php)
- User mengisi form reservasi (nama, telepon, tanggal, jam, jumlah orang, catatan)
- Setelah submit → Redirect ke **pembayaran.php**

### 2. **Halaman Pembayaran** (pembayaran.php)
**Tampilan:**
- Detail reservasi (kolom kiri)
- Pilihan metode pembayaran (kolom kanan) - **HANYA 2 pilihan:**
  - 🏦 Transfer Bank
  - 📱 QRIS

**Interaksi:**
- User memilih metode pembayaran
- Tombol "Lanjutkan ke Pembayaran" aktif

**Ketika memilih Transfer Bank:**
- Pop-up muncul dengan pilihan bank:
  - BCA
  - Mandiri
  - BRI
  - CIMB
  - OVO
  - Dana
- Setelah pilih bank → Tampil nomor rekening
- Button "Salin Nomor Rekening" untuk copy otomatis
- Button "Lanjut Upload Bukti" → Redirect ke **upload_bukti.php**

**Ketika memilih QRIS:**
- Pop-up muncul dengan QR Code QRIS
- Instruksi cara pembayaran (scan dengan e-wallet)
- Button "Lanjut Upload Bukti" → Redirect ke **upload_bukti.php**

### 3. **Halaman Upload Bukti Pembayaran** (upload_bukti.php) ⭐ HALAMAN BARU
**Fitur:**
- Drag & drop zone untuk upload file
- Validasi file:
  - Format: JPG, JPEG, PNG
  - Ukuran max: 5MB
- Validasi informasi (warning checklist):
  - ✓ Nama penerima (PT Sasuki BBQ)
  - ✓ Jumlah transfer (sesuai total)
  - ✓ Tanggal dan jam transaksi
  - ✓ Status "Berhasil" atau "Sukses"

**Proses:**
1. User upload bukti pembayaran
2. File disimpan ke folder: `bukti_pembayaran/`
3. Path disimpan ke database: `reservasi.bukti_pembayaran`
4. Status awal: `bukti_verified = 0` (pending)
5. WhatsApp notifikasi: "Bukti pembayaran diterima, menunggu verifikasi"
6. Redirect ke **sukses.php**

### 4. **Halaman Sukses** (sukses.php) - UPDATED
**Status Pembayaran:**
- **Jika bukti_verified = 0**: "⏳ Menunggu Verifikasi"
  - Tampilkan warning: "Bukti pembayaran sedang kami verifikasi (5-10 menit)"
- **Jika bukti_verified = 1**: "✅ Pembayaran Terverifikasi"
  - Tampilkan success: "Meja akan disiapkan sesuai jadwal"

### 5. **Admin Dashboard - Verifikasi Bukti** (admin/verifikasi_bukti.php) ⭐ HALAMAN BARU

**Akses:** `http://localhost/sasuki_app/admin/verifikasi_bukti.php`

**Login:**
- Password: `admin123` (⚠️ GANTI DENGAN PASSWORD YANG KUAT!)

**Fitur:**
- Tabel list semua bukti pembayaran dengan status:
  - ⏳ Pending (bukti_verified = 0)
  - ✅ Approved (bukti_verified = 1)
  - ❌ Rejected (bukti_verified = -1)

- **Tombol Aksi untuk setiap bukti:**
  - **Lihat**: Preview gambar bukti pembayaran
  - **Approve**: Setujui bukti → Update `bukti_verified = 1` → Kirim WA notifikasi approval
  - **Reject**: Tolak bukti → Update `bukti_verified = -1` → Pop-up input alasan → Kirim WA notifikasi rejection

**WhatsApp Notifikasi:**
- **Approval**: "✅ Pembayaran Anda telah terverifikasi. Meja akan disiapkan sesuai jadwal."
- **Rejection**: "⚠️ Bukti pembayaran ditolak. Alasan: [alasan]. Silakan upload kembali bukti yang benar."

## 📁 File-File Baru/Dimodifikasi

### File Baru:
✅ `reservasi/upload_bukti.php` - Halaman upload bukti pembayaran
✅ `admin/verifikasi_bukti.php` - Admin dashboard verifikasi bukti
✅ `bukti_pembayaran/` - Folder untuk menyimpan bukti pembayaran
✅ `bukti_pembayaran/.htaccess` - Proteksi folder bukti

### File Dimodifikasi:
✅ `reservasi/pembayaran.php` - Hapus Tunai, tambah modal bank & QRIS
✅ `reservasi/sukses.php` - Tambah status verifikasi pending/terverifikasi
✅ `migrate.php` - Tambah migration untuk kolom bukti pembayaran

## 🗄️ Database Schema

### Kolom Baru di Tabel `reservasi`:
```sql
ALTER TABLE reservasi ADD COLUMN bukti_pembayaran VARCHAR(255) AFTER metode_pembayaran;
ALTER TABLE reservasi ADD COLUMN bukti_verified INT DEFAULT 0 AFTER bukti_pembayaran;
```

**Penjelasan:**
- `bukti_pembayaran`: Path file bukti (contoh: `bukti_pembayaran/bukti_123_1234567890.jpg`)
- `bukti_verified`: Status verifikasi
  - `0`: Pending (default)
  - `1`: Approved/Verified
  - `-1`: Rejected

### Jalankan Migration:
```
1. Buka: http://localhost/sasuki_app/migrate.php
2. Klik "add_bukti_pembayaran"
3. Selesai!
```

## 🔧 Konfigurasi

### 1. Update Bank Data (optional)
File: `reservasi/pembayaran.php`
```javascript
const bankData = {
    'BCA': { number: '1234567890', name: 'PT Sasuki BBQ' },
    'Mandiri': { number: '1110022333', name: 'PT Sasuki BBQ' },
    // ... update dengan nomor rekening real Anda
};
```

### 2. Update Admin Password (PENTING!)
File: `admin/verifikasi_bukti.php`
```php
$admin_password = 'admin123'; // GANTI DENGAN PASSWORD KUAT!
```

### 3. Konfigurasi WhatsApp (jika belum)
File: `config/wa_config.php`
```php
define('FONTRE_TOKEN', 'YOUR_FONTRE_API_TOKEN_HERE');
```

## 📊 Flow Diagram

```
┌─────────────────┐
│  User Reservasi │
└────────┬────────┘
         │
         ↓
   ┌──────────────┐
   │  Pembayaran  │ (Pilih Bank / QRIS)
   └────┬─────────┘
        │
        ├─ TRANSFER BANK ──→ [Pop-up: Pilih Bank]
        │                     ├─ BCA
        │                     ├─ Mandiri
        │                     ├─ BRI
        │                     ├─ CIMB
        │                     ├─ OVO
        │                     └─ Dana
        │
        └─ QRIS ────────────→ [Pop-up: Scan QR Code]
        │
        ↓
   ┌──────────────────────┐
   │  Upload Bukti (NEW)  │ ← Validasi file & informasi
   └────┬─────────────────┘
        │
        ↓
   ┌───────────────────┐
   │ Sukses (Updated)  │ ← Status: Pending Verifikasi
   └────┬──────────────┘
        │
        ↓
   ┌──────────────────────────┐
   │ Admin Verifikasi (NEW)   │
   │  - Lihat Bukti           │
   │  - Approve / Reject      │
   └────┬─────────────────────┘
        │
        ├─ APPROVE ──→ bukti_verified = 1 ─→ WA Approval Notification
        │
        └─ REJECT ───→ bukti_verified = -1 ─→ WA Rejection Notification

```

## 🔐 Keamanan

### Proteksi Folder Bukti:
- Folder `bukti_pembayaran/` dilindungi `.htaccess`
- Hanya file image yang bisa diakses
- Script PHP tidak bisa dijalankan di folder ini

### Validasi File:
- Client-side: Cek format dan ukuran
- Server-side: Validasi ulang sebelum menyimpan
- File naming: `bukti_[reservasi_id]_[timestamp].[ext]`

### SQL Injection Protection:
- Menggunakan `mysqli_real_escape_string()`
- Sanitasi semua input user

## 📱 WhatsApp Messages

### 1. Reservasi Pending (dari proses_tambah.php)
```
📋 *Konfirmasi Reservasi*

ID Reservasi: #123
Nama: Budi Santoso
Tanggal: 12 Nov 2025
Jam: 19:00
Jumlah Orang: 4 orang
Total: Rp 200.000

✅ Reservasi Anda telah kami terima.
Silakan lakukan pembayaran untuk mengkonfirmasi reservasi ini.

[Link pembayaran]
```

### 2. Bukti Pembayaran Diterima (dari upload_bukti.php)
```
📱 *Bukti Pembayaran Diterima*

Reservasi ID: #123
Atas Nama: Budi Santoso
Total: Rp 200.000
Metode: Transfer

✅ Bukti pembayaran Anda telah diterima.
Kami akan verifikasi dalam waktu 5-10 menit.
```

### 3. Approval Notifikasi (dari admin verifikasi)
```
✅ *Pembayaran Anda Telah Terverifikasi!*

Terima kasih Budi Santoso
Bukti pembayaran Anda telah kami verifikasi.

Meja akan kami siapkan sesuai jadwal yang telah ditentukan.
Sampai jumpa di SASUKI BBQ! 🔥
```

### 4. Rejection Notifikasi (dari admin verifikasi)
```
⚠️ *Bukti Pembayaran Ditolak*

Halo Budi Santoso
Bukti pembayaran Anda belum sesuai.

Alasan: Bukti tidak terlihat jelas, silakan upload ulang dengan foto yang lebih terang.

Silakan upload kembali bukti pembayaran yang benar.
Terimakasih 🙏
```

## ✅ Testing Checklist

- [ ] Form reservasi submit → Pembayaran page terbuka
- [ ] Transfer Bank → Pilih bank → Lihat nomor rekening
- [ ] Transfer Bank → Copy nomor rekening → Paste di notepad (cek hasilnya)
- [ ] QRIS → Lihat QR code → Scan dengan HP
- [ ] Upload Bukti → Drag & drop file → Validasi size
- [ ] Upload Bukti → Validasi warning checklist
- [ ] Upload sukses → Status: Pending Verifikasi
- [ ] Admin Dashboard → Login → Lihat tabel bukti
- [ ] Admin → Approve bukti → Cek WhatsApp notifikasi
- [ ] Admin → Reject bukti → Input alasan → Cek WhatsApp
- [ ] Sukses page → Update status sesuai bukti_verified

## 🚀 Deployment

### Sebelum Production:

1. **Update Admin Password:**
   ```php
   // admin/verifikasi_bukti.php - Line 8
   $admin_password = 'PASSWORD_SANGAT_KUAT_DAN_RANDOM!';
   ```

2. **Update Bank Data:**
   - Masukkan nomor rekening real Anda
   - Verified account names

3. **Update WhatsApp Token:**
   - Masukkan Fontre API token real

4. **Backup Database:**
   - Backup database sebelum jalankan migrations

5. **Test Flow Lengkap:**
   - Ikuti testing checklist di atas

6. **Setup Folder Permissions:**
   ```bash
   chmod 755 bukti_pembayaran/
   chmod 644 bukti_pembayaran/.htaccess
   ```

## ❓ FAQ

**Q: Bagaimana jika user tidak upload bukti?**
A: Reservasi tetap tersimpan dengan status `pending`, tidak bisa lanjut ke verifikasi sampai upload bukti.

**Q: Berapa lama verifikasi biasanya?**
A: Setting default 5-10 menit, bisa dikustomisasi sesuai kebutuhan.

**Q: Bagaimana jika user upload bukti yang salah?**
A: Admin bisa reject dan user akan dapat notifikasi dengan alasan penolakan. User harus upload ulang.

**Q: Apakah file bukti bisa dilihat publik?**
A: Tidak, folder dilindungi `.htaccess`. Hanya admin yang bisa lihat via dashboard.

---

**Status**: ✅ Fully Implemented
**Last Updated**: November 11, 2025
**Version**: 2.0
