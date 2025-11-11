# ✅ QUICK START CHECKLIST - Payment Flow v2.0

Panduan lengkap untuk setup dan verifikasi Payment Flow v2.0 SASUKI BBQ.

---

## 🎯 Phase 1: Database Migration (CRITICAL) ⭐

**Status**: 🔴 NOT DONE  
**Time**: 2-3 minutes  
**Importance**: CRITICAL

### Steps:

```
1. ✓ Buka browser: http://localhost/sasuki_app/migrate.php
2. ✓ Cari tombol: "Tambah kolom bukti pembayaran"
3. ✓ Klik tombol tersebut
4. ✓ Lihat output: "✅ Query berhasil"
5. ✓ Refresh halaman untuk verifikasi
```

### Verification:
```sql
-- Jalankan di PHPMyAdmin atau MySQL CLI:
DESC reservasi;
-- Cek ada 2 kolom baru:
-- - bukti_pembayaran (VARCHAR 255)
-- - bukti_verified (INT default 0)
```

### ✅ Completion Checklist
- [ ] Migration executed successfully
- [ ] No error messages displayed
- [ ] Database columns verified via PHPMyAdmin
- [ ] Can see both new columns in reservasi table

---

## 🔐 Phase 2: Update Configuration (CRITICAL) ⭐

**Status**: 🔴 NOT DONE  
**Time**: 5-10 minutes  
**Importance**: CRITICAL (for admin password & bank data)

### 2A. Update Admin Password (SECURITY!)

**File**: `admin/verifikasi_bukti.php`  
**Line**: ~8

❌ **OLD**:
```php
$admin_password = 'admin123';
```

✅ **NEW** (Replace dengan password kuat):
```php
$admin_password = 'UBAH_DENGAN_PASSWORD_KUAT_ANDA_123!@#';
```

**Password Requirements**:
- Min 8 karakter
- Mix: uppercase, lowercase, numbers, symbols
- Jangan gunakan: nama, tanggal lahir, atau info mudah ditebak

### Steps:
```
1. ✓ Edit admin/verifikasi_bukti.php
2. ✓ Cari line: $admin_password = 'admin123';
3. ✓ Ganti dengan password kuat Anda
4. ✓ Save file
5. ✓ Test login di: http://localhost/sasuki_app/admin/verifikasi_bukti.php
```

### ✅ Completion Checklist
- [ ] Password updated (NOT 'admin123')
- [ ] File saved
- [ ] Can login with new password
- [ ] Cannot login with 'admin123'

---

### 2B. Update Bank Account Data

**File**: `reservasi/pembayaran.php`  
**Section**: JavaScript bankData object (~line 450-480)

❌ **OLD**:
```javascript
const bankData = {
    'BCA': {
        number: '1234567890',
        name: 'PT SASUKI BBQ',
        holder: 'Nama Pemilik'
    },
    // ... other banks
};
```

✅ **NEW** (Replace dengan data real):
```javascript
const bankData = {
    'BCA': {
        number: '1234567890',      // <- Nomor rekening BCA real
        name: 'PT SASUKI BBQ',      // <- Nama bisnis
        holder: 'Nama Pemilik'      // <- Nama pemilik rekening
    },
    'Mandiri': {
        number: '0987654321',       // <- Nomor rekening Mandiri real
        name: 'PT SASUKI BBQ',
        holder: 'Nama Pemilik'
    },
    // ... update semua 6 bank
};
```

### Bank Data Template:

| Bank | Nomor | Nama | Pemilik |
|------|-------|------|---------|
| BCA | [REAL NUMBER] | PT SASUKI BBQ | Nama Anda |
| Mandiri | [REAL NUMBER] | PT SASUKI BBQ | Nama Anda |
| BRI | [REAL NUMBER] | PT SASUKI BBQ | Nama Anda |
| CIMB | [REAL NUMBER] | PT SASUKI BBQ | Nama Anda |
| OVO | [REAL NUMBER] | PT SASUKI BBQ | Nama Anda |
| Dana | [REAL NUMBER] | PT SASUKI BBQ | Nama Anda |

### ✅ Completion Checklist
- [ ] All 6 banks updated with real account numbers
- [ ] Nama bisnis sesuai
- [ ] Nama pemilik sesuai
- [ ] File saved
- [ ] Tested in pembayaran.php - data tampil correct

---

### 2C. Verify WhatsApp Configuration

**File**: `config/wa_config.php`

```php
$fontre_api_token = 'YOUR_FONTRE_TOKEN_HERE';
```

**Steps**:
```
1. ✓ Cek file config/wa_config.php
2. ✓ Lihat $fontre_api_token
3. ✓ Jika belum ada token:
   - Daftar di https://fontre.id
   - Copy API token
   - Update di config/wa_config.php
4. ✓ Save file
```

### ✅ Completion Checklist
- [ ] Fontre API token configured
- [ ] Token bukan 'YOUR_FONTRE_TOKEN_HERE'
- [ ] Can access Fontre dashboard

---

## 🧪 Phase 3: Testing (COMPREHENSIVE)

**Status**: 🔴 NOT DONE  
**Time**: 45-60 minutes  
**Importance**: HIGH (verify everything works)

### 3A. Quick Smoke Test (5 minutes)

```
1. ✓ Visit http://localhost/sasuki_app
2. ✓ See home page (not error)
3. ✓ Click "Pesan Meja" or similar
4. ✓ Fill reservasi form completely:
   - Nama: Test User
   - Telepon: 081234567890
   - Tanggal: [Today + 1 day]
   - Jam: 19:00
   - Jumlah Orang: 4
5. ✓ Submit form
6. ✓ See pembayaran page (2 method buttons)
7. ✓ Click "Transfer Bank" button → See modal
8. ✓ Click "QRIS" button → See QR modal
9. ✓ Close modal (ESC or click close)
10. ✓ No JavaScript errors in console
```

**Expected Results**:
- ✅ Pembayaran page shows 2 options
- ✅ Bank modal shows 6 banks
- ✅ QRIS modal shows QR code
- ✅ Modals open/close smoothly
- ✅ No errors in browser console

### ✅ Completion Checklist
- [ ] Homepage loads
- [ ] Reservation form works
- [ ] Payment page displays correctly
- [ ] Both modals work
- [ ] No console errors
- [ ] Mobile view responsive

---

### 3B. Full Payment Flow Test (20 minutes)

```
STEP 1: Fill Reservasi Form
┌─────────────────────────────────┐
│ Nama: Test User                 │
│ Telepon: 081234567890           │
│ Tanggal: [Today + 1 day]         │
│ Jam: 19:00                       │
│ Jumlah Orang: 4                 │
│ Meja: [Select any]              │
│ Catatan: Test pembayaran        │
│ Kode Promo: [Leave empty]       │
│ Konfirmasi: [Check checkbox]    │
└─────────────────────────────────┘
↓
Submit → Should go to PEMBAYARAN page

STEP 2: Pembayaran Page - Select Bank Transfer
┌─────────────────────────────────┐
│ [Transfer Bank] [QRIS]          │
│  Click "Transfer Bank"          │
└─────────────────────────────────┘
↓
Modal appears with 6 banks

STEP 3: Select Bank & Copy Number
┌─────────────────────────────────┐
│ Select: BCA                     │
│ See: 1234567890                 │
│ Click: "Salin Nomor Rekening"   │
│ Check: Nomor copied to clipboard│
│ Confirm: "Nomor tersalin!"      │
│ Click: "Konfirmasi Pembayaran"  │
└─────────────────────────────────┘
↓
Redirect to UPLOAD_BUKTI page

STEP 4: Upload Bukti Pembayaran
┌─────────────────────────────────┐
│ See summary with:               │
│ - ID Reservasi                  │
│ - Nama                          │
│ - Total: [Calculated]           │
│ - Metode: Transfer Bank         │
│                                 │
│ Drag & drop JPG file OR         │
│ Click to select file            │
│                                 │
│ Show warning checklist:         │
│ ☐ File is JPG/PNG              │
│ ☐ File size < 5MB              │
│ ☐ Clearly shows receipt        │
│ ☐ Shows correct amount         │
└─────────────────────────────────┘
↓
Upload file → "Bukti diterima" message
↓
Redirect to SUKSES page

STEP 5: Sukses Page - Pending Status
┌─────────────────────────────────┐
│ ✅ Status: RESERVASI DITERIMA   │
│                                 │
│ 📊 Detail Reservasi:            │
│ ID: [Shown]                     │
│ Nama: Test User                 │
│ Tanggal/Jam: [Shown]            │
│ Jumlah Orang: 4                 │
│ Total: Rp xxx.xxx               │
│                                 │
│ 💳 Info Pembayaran:             │
│ Metode: Transfer Bank           │
│ Status: ⏳ MENUNGGU VERIFIKASI  │
│                                 │
│ ⚠️ WARNING MESSAGE:             │
│ "Admin sedang memverifikasi..." │
└─────────────────────────────────┘
```

### WhatsApp Notifications Expected:
```
Message 1 (Auto):
"📱 Bukti Pembayaran Diterima
 ID: [id]
 Nama: Test User
 Total: Rp 200.000
 ✅ Bukti diterima, verifikasi 5-10 menit"

Message 2 (After Admin Approve):
"✅ Pembayaran Anda Telah Terverifikasi
 Meja akan disiapkan sesuai jadwal
 Sampai jumpa di SASUKI BBQ! 🔥"
```

### ✅ Completion Checklist
- [ ] Reservasi form submitted successfully
- [ ] Pembayaran page shows both buttons
- [ ] Bank modal opens and displays 6 banks
- [ ] Can copy bank number
- [ ] Redirect to upload_bukti.php works
- [ ] Upload bukti.php shows correct summary
- [ ] Can upload JPG/PNG file
- [ ] File upload successful
- [ ] Redirect to sukses.php works
- [ ] Sukses page shows "MENUNGGU VERIFIKASI" status
- [ ] WhatsApp message 1 received (bukti diterima)

---

### 3C. Admin Verification Test (15 minutes)

```
STEP 1: Access Admin Dashboard
┌─────────────────────────────────┐
│ URL: http://localhost/sasuki_app/
│      admin/verifikasi_bukti.php │
│                                 │
│ See login form                  │
│ Enter password: [YOUR PASSWORD] │
│ Click: Login                    │
└─────────────────────────────────┘
↓
Should login successfully

STEP 2: Admin Dashboard
┌─────────────────────────────────┐
│ Table dengan bukti pembayaran:  │
│ ┌─────────────────────────────┐ │
│ │ ID  │ Nama      │ Total  │ │ │
│ ├─────┼───────────┼────────┤ │
│ │ X   │ Test User │ 200k   │ │
│ │ Metode: Transfer Bank      │ │
│ │ Status: ⏳ PENDING         │ │
│ │ [Lihat] [Approve] [Reject] │ │
│ └─────────────────────────────┘ │
└─────────────────────────────────┘

STEP 3: Click "Lihat" (View Button)
┌─────────────────────────────────┐
│ Modal preview:                  │
│ [Bukti image displayed]         │
│ [Close button]                  │
└─────────────────────────────────┘
↓
Modal should show image correctly

STEP 4: Click "Approve" Button
┌─────────────────────────────────┐
│ Confirmation dialog:            │
│ "Approve bukti ini?"            │
│ [Cancel] [Approve]              │
│                                 │
│ Click: Approve                  │
└─────────────────────────────────┘
↓
Status changes to: ✅ APPROVED
WhatsApp sent to customer

STEP 5: Go Back to Sukses Page
┌─────────────────────────────────┐
│ Refresh sukses.php              │
│ Should now show:                │
│ Status: ✅ PEMBAYARAN TERVERIFIKASI
│ No warning message              │
│                                 │
│ Message: "Meja akan disiapkan..." │
└─────────────────────────────────┘
```

### WhatsApp Notifications Expected:
```
Message 2 (After Admin Approve):
"✅ Pembayaran Anda Telah Terverifikasi
 Meja akan disiapkan sesuai jadwal
 Sampai jumpa di SASUKI BBQ! 🔥"

Message 3 (If Admin Reject):
"⚠️ Bukti Pembayaran Ditolak
 Alasan: [Admin input]
 Silakan upload kembali bukti pembayaran yang benar"
```

### ✅ Completion Checklist
- [ ] Can access admin dashboard
- [ ] Login works with new password
- [ ] Dashboard table displays bukti data
- [ ] Can view bukti image via modal
- [ ] Approve button works
- [ ] Status changes to APPROVED
- [ ] WhatsApp approval message sent
- [ ] Sukses page status updates to verified
- [ ] Can logout

---

### 3D. Reject Flow Test (10 minutes)

```
STEP 1: Back to Admin Dashboard (Create New Bukti)
- Submit another reservasi + bukti
- Go to admin dashboard
- See new bukti with PENDING status

STEP 2: Click "Reject" Button
┌─────────────────────────────────┐
│ Rejection modal:                │
│ "Alasan Penolakan:"             │
│ [Textarea input field]          │
│                                 │
│ Type reason:                    │
│ "Nominal tidak sesuai"          │
│                                 │
│ [Cancel] [Reject]               │
└─────────────────────────────────┘

STEP 3: Confirmation
- Bukti status changes to: ❌ REJECTED
- Alasan shown: "Nominal tidak sesuai"
- WhatsApp sent with rejection reason

STEP 4: Customer WhatsApp
"⚠️ Bukti Pembayaran Ditolak
 Alasan: Nominal tidak sesuai
 Silakan upload kembali bukti pembayaran yang benar"
```

### ✅ Completion Checklist
- [ ] Can reject bukti with reason
- [ ] Status changes to REJECTED
- [ ] WhatsApp rejection message sent with reason
- [ ] Reason displayed correctly in dashboard

---

## 🧹 Phase 4: File Structure Verification

**Status**: 🔴 NOT DONE  
**Time**: 3-5 minutes  
**Importance**: MEDIUM

### Check File Structure:

```
sasuki_app/
├── ✅ reservasi/
│   ├── ✅ pembayaran.php (MODIFIED)
│   ├── ✅ sukses.php (MODIFIED)
│   ├── ✅ upload_bukti.php (NEW) ⭐
│   ├── proses_tambah.php
│   ├── proses_pembayaran.php
│   ├── data_reservasi.php
│   └── index.php
│
├── ✅ admin/
│   ├── ✅ verifikasi_bukti.php (NEW) ⭐
│   └── [other admin files]
│
├── ✅ bukti_pembayaran/ (NEW FOLDER)
│   ├── ✅ .htaccess (NEW)
│   └── [uploaded bukti files will go here]
│
├── ✅ config/
│   ├── koneksi.php
│   ├── helpers.php
│   ├── wa_config.php
│   └── [other configs]
│
├── ✅ migrate.php (MODIFIED)
├── ✅ IMPLEMENTATION_SUMMARY_v2.md (NEW)
├── ✅ PAYMENT_FLOW_GUIDE.md (NEW)
├── ✅ SETUP_BUKTI_PEMBAYARAN.md (NEW)
├── ✅ TESTING_CHECKLIST_v2.md (NEW)
└── [other files]
```

### ✅ Completion Checklist
- [ ] `reservasi/upload_bukti.php` exists
- [ ] `admin/verifikasi_bukti.php` exists
- [ ] `bukti_pembayaran/` folder exists
- [ ] `bukti_pembayaran/.htaccess` exists
- [ ] `pembayaran.php` is updated
- [ ] `sukses.php` is updated
- [ ] `migrate.php` is updated
- [ ] All documentation files exist

---

## 🚀 Phase 5: Production Deployment (OPTIONAL)

**Status**: ⚪ NOT STARTED  
**Time**: 30-60 minutes  
**Importance**: HIGH (if deploying)

### Prerequisites:
- ✅ Phases 1-4 completed successfully
- ✅ All testing passed
- ✅ Admin password updated
- ✅ Bank data verified
- ✅ Database migrated

### Steps:

```
1. ✓ Backup database locally:
   mysqldump -u root -p sasuki_app > backup.sql

2. ✓ Update production config:
   - Update bank data
   - Update admin password
   - Update WhatsApp token if needed

3. ✓ Upload files to production server:
   - admin/verifikasi_bukti.php
   - reservasi/pembayaran.php
   - reservasi/sukses.php
   - reservasi/upload_bukti.php
   - bukti_pembayaran/ folder
   - All documentation files

4. ✓ Run database migration on production:
   - Access migrate.php
   - Click migration
   - Verify success

5. ✓ Enable HTTPS/SSL:
   - Configure SSL certificate
   - Update all URLs to https://

6. ✓ Test on production:
   - Full payment flow test
   - Admin verification test
   - WhatsApp notification test

7. ✓ Monitor and maintain:
   - Check admin dashboard daily
   - Monitor bukti_pembayaran storage
   - Review WhatsApp notifications
```

### ✅ Completion Checklist
- [ ] Database backed up
- [ ] Files uploaded to production
- [ ] Migration executed on production
- [ ] HTTPS/SSL enabled
- [ ] Production tests passed
- [ ] Monitoring in place

---

## 📊 Final Verification Checklist

### Database
- [ ] `reservasi` table has `bukti_pembayaran` column
- [ ] `reservasi` table has `bukti_verified` column
- [ ] Both columns have correct data types

### Configuration
- [ ] Admin password updated (not 'admin123')
- [ ] All 6 bank accounts configured
- [ ] WhatsApp token configured

### Payment Flow
- [ ] Pembayaran page shows 2 methods (no Tunai)
- [ ] Bank modal works with all 6 banks
- [ ] QRIS modal shows QR code
- [ ] Upload bukti page has validation
- [ ] Sukses page shows pending status

### Admin Features
- [ ] Admin login works
- [ ] Can view bukti images
- [ ] Can approve bukti
- [ ] Can reject bukti with reason
- [ ] WhatsApp notifications sent

### File Structure
- [ ] All new files created
- [ ] All files modified correctly
- [ ] No missing files or folders
- [ ] Permissions correct (755 for folders)

### Testing
- [ ] Smoke test passed
- [ ] Full payment flow passed
- [ ] Admin verification passed
- [ ] Reject flow passed
- [ ] WhatsApp notifications working
- [ ] Mobile responsive tested
- [ ] No console errors

### Security
- [ ] Admin password strong
- [ ] .htaccess in place
- [ ] File upload validated
- [ ] SQL injection prevented
- [ ] XSS prevented

---

## 🎉 Success Criteria

### ✅ System is READY when:

```
✓ All 4 phases completed without errors
✓ All testing checklist items passed
✓ Database migration successful
✓ Configuration updated (admin password, bank data)
✓ Payment flow works end-to-end
✓ Admin verification working
✓ WhatsApp notifications received
✓ No console errors or warnings
✓ Mobile responsive tested
✓ Documentation reviewed
✓ Ready for production deployment
```

---

## 📞 Troubleshooting

### Issue: "Column 'bukti_pembayaran' doesn't exist"
**Solution**: 
- Run database migration again via migrate.php
- Check migrate.php to ensure query is correct
- Manual SQL: `ALTER TABLE reservasi ADD COLUMN bukti_pembayaran VARCHAR(255);`

### Issue: Admin login not working
**Solution**:
- Verify password is updated in verifikasi_bukti.php line 8
- Check password doesn't have special characters that need escaping
- Clear browser cookies and try again

### Issue: Modal not appearing
**Solution**:
- Check browser console for JavaScript errors (F12)
- Verify pembayaran.php is properly updated
- Clear cache: Ctrl+Shift+Delete

### Issue: File upload not working
**Solution**:
- Check bukti_pembayaran folder exists
- Check folder permissions (755)
- Verify file format (JPG/PNG)
- Check file size (< 5MB)
- Check browser console for JS errors

### Issue: WhatsApp notifications not sent
**Solution**:
- Verify Fontre API token in config/wa_config.php
- Check Fontre dashboard for quota
- Verify phone number format (62xxxxxxxx)
- Check internet connection

---

## 📝 Support & Documentation

**Documentation Files Available**:
1. `IMPLEMENTATION_SUMMARY_v2.md` - Overview & statistics
2. `PAYMENT_FLOW_GUIDE.md` - Detailed flow & architecture
3. `SETUP_BUKTI_PEMBAYARAN.md` - Setup instructions
4. `TESTING_CHECKLIST_v2.md` - 53 test cases
5. **THIS FILE** - Quick start checklist

**For Help**:
- Review `PAYMENT_FLOW_GUIDE.md` for architecture
- Check `TESTING_CHECKLIST_v2.md` for test procedures
- See `SETUP_BUKTI_PEMBAYARAN.md` for detailed setup

---

## 🎯 Next Steps

**Immediate (Today)**:
1. ✅ Run database migration (Phase 1)
2. ✅ Update admin password (Phase 2A)
3. ✅ Update bank data (Phase 2B)

**Short Term (This Week)**:
1. ✅ Run smoke test (Phase 3A)
2. ✅ Run full payment flow test (Phase 3B)
3. ✅ Run admin verification test (Phase 3C)

**Medium Term (This Month)**:
1. ✅ Complete all testing checklist
2. ✅ Deploy to production
3. ✅ Monitor live transactions

---

**Status**: 🔴 NOT STARTED  
**Last Updated**: November 11, 2025  
**Version**: 1.0  
**Estimated Time to Complete**: 2-3 hours

**Mark items as complete as you go! ✅**
