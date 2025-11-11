# ⚡ Setup Awal - Payment Flow Baru

## 📋 Checklist Setup

### 1. **Database Migration** ✅ WAJIB
```
1. Buka: http://localhost/sasuki_app/migrate.php
2. Klik "Tambah kolom bukti pembayaran"
3. Tunggu hingga berhasil
```

**Jika migration manual:**
```sql
ALTER TABLE reservasi ADD COLUMN bukti_pembayaran VARCHAR(255) AFTER metode_pembayaran;
ALTER TABLE reservasi ADD COLUMN bukti_verified INT DEFAULT 0 AFTER bukti_pembayaran;
```

### 2. **Update Konfigurasi Bank** ✅ PENTING

File: `reservasi/pembayaran.php` (cari line ~450)

Update nomor rekening dengan data bank real Anda:

```javascript
const bankData = {
    'BCA': { number: '1234567890', name: 'PT Sasuki BBQ' },
    'Mandiri': { number: '1110022333', name: 'PT Sasuki BBQ' },
    'BRI': { number: '0123456789', name: 'PT Sasuki BBQ' },
    'CIMB': { number: '7001234567', name: 'PT Sasuki BBQ' },
    'OVO': { number: '081234567890', name: 'PT Sasuki BBQ' },
    'Dana': { number: '081234567890', name: 'PT Sasuki BBQ' }
};
```

**Ganti dengan:**
```javascript
const bankData = {
    'BCA': { number: '[BCA REAL Anda]', name: '[Nama Bisnis]' },
    'Mandiri': { number: '[Mandiri REAL Anda]', name: '[Nama Bisnis]' },
    // ... dst
};
```

### 3. **Update Admin Password** ✅ CRITICAL

File: `admin/verifikasi_bukti.php` (line 8)

**Sebelum:**
```php
$admin_password = 'admin123';
```

**Sesudah (GANTI!):**
```php
$admin_password = 'PASSWORD_YANG_SANGAT_KUAT_DAN_SULIT_DITEBAK!';
```

**Contoh password kuat:**
```
$admin_password = 'Sasuki@BBQ#2025!Admin$Secure';
```

### 4. **Setup WhatsApp Integration** ✅ WAJIB (untuk notifikasi)

File: `config/wa_config.php`

**Update Fontre Token:**
```php
define('FONTRE_TOKEN', 'YOUR_FONTRE_API_TOKEN_HERE');
```

**Dapatkan token dari:** https://fontre.com
1. Daftar akun Fontre
2. Copy API Token
3. Paste ke kode di atas

### 5. **Test Setup Lengkap** ✅ REKOMENDASI

**Test Flow:**

1. Buka: `http://localhost/sasuki_app`
2. Submit form reservasi (lengkap semua field)
3. Masuk halaman pembayaran:
   - [ ] Lihat detail reservasi di kiri
   - [ ] Lihat pilihan Transfer Bank & QRIS di kanan
   - [ ] Klik Transfer Bank → Lihat modal bank
   - [ ] Pilih bank → Lihat nomor rekening
   - [ ] Click "Salin Nomor Rekening" → Verify tercopas
   - [ ] Klik "Lanjut Upload Bukti"
4. Masuk halaman upload bukti:
   - [ ] Lihat summary reservasi
   - [ ] Upload file gambar (jpg/png, max 5MB)
   - [ ] Lihat validasi file
   - [ ] Submit
5. Masuk halaman sukses:
   - [ ] Lihat status "⏳ Menunggu Verifikasi"
   - [ ] Lihat warning verification pending
6. Admin verifikasi:
   - [ ] Buka: `http://localhost/sasuki_app/admin/verifikasi_bukti.php`
   - [ ] Input password admin
   - [ ] Lihat tabel bukti pending
   - [ ] Klik "Lihat" → Preview bukti
   - [ ] Klik "Approve" → Cek notifikasi WA
   - [ ] (Optional) Test reject → Input alasan → Cek WA

## 🔧 File Yang Berubah/Baru

### ✅ File Baru:
- `reservasi/upload_bukti.php` - Halaman upload bukti
- `admin/verifikasi_bukti.php` - Admin dashboard verifikasi
- `bukti_pembayaran/` folder - Folder menyimpan bukti
- `PAYMENT_FLOW_GUIDE.md` - Dokumentasi lengkap flow

### ✅ File Dimodifikasi:
- `reservasi/pembayaran.php` - Update modal & styling
- `reservasi/sukses.php` - Update status verifikasi
- `migrate.php` - Tambah migration bukti pembayaran
- `reservasi/proses_pembayaran.php` - Auto-detect metode pembayaran

## 📂 Struktur Folder

```
sasuki_app/
├── reservasi/
│   ├── index.php
│   ├── pembayaran.php (DIMODIFIKASI)
│   ├── upload_bukti.php (BARU)
│   ├── sukses.php (DIMODIFIKASI)
│   ├── proses_tambah.php
│   ├── proses_pembayaran.php (DIMODIFIKASI)
│   └── map_meja.php
├── admin/ (BARU)
│   └── verifikasi_bukti.php (BARU)
├── bukti_pembayaran/ (BARU - untuk menyimpan bukti)
│   └── .htaccess (BARU - proteksi folder)
├── config/
│   ├── koneksi.php
│   ├── wa_config.php
│   └── helpers.php
├── PAYMENT_FLOW_GUIDE.md (BARU)
├── SETUP_GUIDE.md (EXISTING)
├── migrate.php (DIMODIFIKASI)
└── ... (file lainnya)
```

## 🔐 Security Tips

### ✅ Wajib Dilakukan:

1. **Ganti Admin Password** - JANGAN gunakan `admin123`
2. **Enable HTTPS** - Di production, gunakan SSL certificate
3. **Update Bank Data** - Gunakan nomor rekening real
4. **Backup Database** - Regular backup schedule
5. **File Permissions:**
   ```bash
   chmod 755 bukti_pembayaran/
   chmod 644 bukti_pembayaran/.htaccess
   ```

### ⚠️ Jangan Lakukan:

- ❌ Jangan hardcode password di file PHP yang accessible
- ❌ Jangan expose Fontre API token di public file
- ❌ Jangan share admin URL / password
- ❌ Jangan disable `.htaccess` protection

## 🚀 Production Deployment

### Pre-Deployment Checklist:

- [ ] Database migration sudah dijalankan
- [ ] Admin password sudah di-update
- [ ] Fontre WhatsApp token sudah dikonfigurasi
- [ ] Bank data sudah dengan nomor real
- [ ] Database backup sudah dibuat
- [ ] File permissions sudah benar
- [ ] Testing flow sudah lengkap
- [ ] HTTPS/SSL sudah aktif
- [ ] Error logging sudah setup

### Post-Deployment:

1. Monitor admin dashboard untuk bukti pembayaran
2. Cek notifikasi WhatsApp terkirim dengan baik
3. Monitor error logs
4. Test sample transactions
5. Train staff cara verify bukti

## 🐛 Troubleshooting

### Error: "Kolom bukti_pembayaran sudah ada"
**Solusi:** Kolom sudah ada, abaikan warning dan lanjut.

### Error: "File upload gagal"
**Solusi:** 
- Cek folder `bukti_pembayaran/` readable/writable
- Cek file size tidak lebih dari 5MB
- Cek format file (jpg/png)

### Admin login error
**Solusi:**
- Cek password benar (case-sensitive)
- Clear browser cache/cookies
- Coba incognito mode

### WhatsApp notifikasi tidak terkirim
**Solusi:**
- Cek Fontre API token benar
- Cek internet connection
- Cek nomor telepon format +62 atau 62
- Check Fontre quota masih ada

### Modal bank tidak muncul
**Solusi:**
- Cek browser console untuk error
- Cek JavaScript tidak di-block
- Hard refresh (Ctrl+F5)

## 📞 Support & Dokumentasi

- Dokumentasi lengkap: `PAYMENT_FLOW_GUIDE.md`
- Setup awal: File ini (`SETUP_BUKTI_PEMBAYARAN.md`)
- WhatsApp integration: `config/wa_config.php`
- Database migration: `migrate.php`

---

**Setup Date**: November 11, 2025
**Status**: Ready to Deploy
**Version**: 2.0
