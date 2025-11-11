# 🔧 ERROR RECOVERY GUIDE - SASUKI BBQ Payment Flow v2.0

**Purpose**: Troubleshoot common errors and maintain seamless operation  
**Status**: Comprehensive guide for production use  
**Last Updated**: November 11, 2025

---

## 🚨 QUICK DIAGNOSTICS

### Step 1: Run System Health Check
```bash
URL: http://localhost/sasuki_app/integrity_check.php
This will:
- ✅ Check all files exist
- ✅ Verify database schema
- ✅ Check payment flow continuity
- ✅ Verify WhatsApp integration
- ✅ Check folder permissions
- 🔧 Auto-fix common issues
```

### Step 2: Check Error Logs
```bash
Logs location: c:\xampp\htdocs\sasuki_app\logs\

Files to check:
- php_errors.log - PHP errors
- pembayaran.log - Payment errors
- reservasi.log - Reservation errors
- wa_messages.log - WhatsApp messages
```

### Step 3: Verify Database
```bash
PHPMyAdmin: http://localhost/phpmyadmin/
1. Select database: sasuki_app
2. Select table: reservasi
3. Check columns exist:
   - bukti_pembayaran ✅
   - bukti_verified ✅
```

---

## 🐛 COMMON ERRORS & SOLUTIONS

### ERROR 1: "Column 'bukti_pembayaran' doesn't exist"

**Symptoms**:
```
Fatal error: Uncaught mysqli_sql_exception: Unknown column 'bukti_pembayaran'
Appears on: upload_bukti.php or sukses.php
```

**Solution**:
```
1. Visit: http://localhost/sasuki_app/migrate.php
2. Click: "Tambah kolom bukti pembayaran"
3. Verify success message appears
4. Refresh the problematic page
```

**Manual SQL (if migration fails)**:
```sql
ALTER TABLE reservasi ADD COLUMN bukti_pembayaran VARCHAR(255) AFTER metode_pembayaran;
ALTER TABLE reservasi ADD COLUMN bukti_verified INT DEFAULT 0 AFTER bukti_pembayaran;
```

---

### ERROR 2: "File upload failed"

**Symptoms**:
```
Upload error page appears
File not saved to database
User stuck on upload page
```

**Solutions**:

**A) Folder permissions issue**:
```
1. Open integrity_check.php
2. Click "Auto-Fix Common Issues"
3. This will fix folder permissions automatically
4. Try upload again
```

**Manual fix**:
```bash
# Set proper permissions on Windows:
icacls "c:\xampp\htdocs\sasuki_app\bukti_pembayaran" /grant Everyone:(OI)(CI)F
```

**B) File size too large**:
```
- Check file size: Maximum 5MB
- Compress image if needed
- Try again with smaller file
```

**C) File format not supported**:
```
- Supported formats: JPG, JPEG, PNG only
- Check file extension
- Convert if needed
- Try again
```

---

### ERROR 3: "Payment page not showing modals"

**Symptoms**:
```
pembayaran.php page loads but:
- No modal when clicking "Transfer Bank"
- No modal when clicking "QRIS"
- No buttons appear
```

**Solution 1: Check browser console for JavaScript errors**:
```
1. Open pembayaran.php
2. Press F12 (Developer Tools)
3. Go to Console tab
4. Look for red errors
5. If errors exist, take screenshot and report
```

**Solution 2: Clear cache**:
```
1. Hard refresh: Ctrl+Shift+Delete (Windows)
2. Close all browser windows
3. Reopen page
```

**Solution 3: Verify file exists**:
```
Check: reservasi/pembayaran.php exists
- File should be ~880 lines
- Should contain: bankModal, qrisModal
- Should contain: confirmBankPayment() function
```

---

### ERROR 4: "Upload successful but didn't redirect to success page"

**Symptoms**:
```
- File uploaded successfully
- Message shows "Bukti diterima"
- But still on upload_bukti.php page
- Didn't redirect to sukses.php
```

**Solution**:
```
1. Check browser console (F12 → Console tab)
2. Look for JavaScript errors
3. Try refreshing page manually
4. Manually visit: sukses.php?id=[reservation_id]

If still failing:
- Check PHP error logs: logs/php_errors.log
- Verify upload_bukti.php file not corrupted
- Re-download and re-upload upload_bukti.php
```

---

### ERROR 5: "WhatsApp message not received"

**Symptoms**:
```
- Reservation created but no WhatsApp
- Bukti uploaded but no WhatsApp
- Admin approval but no notification
```

**Verification steps**:

**A) Check WhatsApp token is configured**:
```php
File: config/wa_config.php
Line: define('FONTRE_TOKEN', 'xxxxx');
Must NOT be 'xxxxx' or 'YOUR_TOKEN'
Should be actual token from Fontre
```

**B) Check phone number format**:
```
Phone must be:
✅ Correct: 62812345678 or +62812345678
❌ Wrong: 0812345678
❌ Wrong: 812345678
```

**C) Check Fontre account**:
```
1. Login to https://fontre.id
2. Check API quota remaining
3. Check account active
4. Check credentials valid
```

**D) Check logs**:
```
File: logs/wa_messages.log
Should show:
✅ Message sent successfully
or
❌ [Error reason]

If error, take action based on error message
```

**E) Manual test**:
```
Edit: config/wa_config.php
Change: define('WA_GATEWAY', 'fontre');
To: define('WA_GATEWAY', 'local');

Now messages go to logs/wa_messages.log instead of Fontre
This tests if code works even if Fontre fails
```

---

### ERROR 6: "Admin login not working"

**Symptoms**:
```
- Go to admin/verifikasi_bukti.php
- Enter password
- Click login
- Page refreshes but still on login
```

**Solution**:

**A) Check password**:
```php
File: admin/verifikasi_bukti.php
Around line 8:
$admin_password = 'admin123';

This is the default password
Change if you've updated it
Try the password you set
```

**B) Check if PHP sessions working**:
```
1. Create test file: test_session.php
Content:
<?php
session_start();
$_SESSION['test'] = 'works';
echo $_SESSION['test'];
?>

2. Visit: http://localhost/sasuki_app/test_session.php
3. Should see: "works"
4. If error, sessions may not be working
```

**C) Check logs for session errors**:
```
File: logs/php_errors.log
Search for: "session"
If errors about sessions, check server PHP config
```

---

### ERROR 7: "Form validation not working"

**Symptoms**:
```
- Submit empty form and it proceeds
- Submit invalid data and it saves
- No error messages displayed
```

**Solution**:

**A) Check JavaScript validation (client-side)**:
```
pembayaran.php should have:
1. Required field checks
2. Modal requirement
3. File size check in upload_bukti.php

If not working:
- Open browser console (F12)
- Check for JavaScript errors
- Verify JavaScript code not commented out
```

**B) Check server validation (PHP)**:
```php
Check upload_bukti.php has:
if (!in_array($file['type'], $allowed_types)) {
    $error_message = '...';
}

If not validating:
- File may be corrupted
- Re-download upload_bukti.php
- Ensure all validation code present
```

---

### ERROR 8: "Database saving fails"

**Symptoms**:
```
- File uploads successfully
- But database UPDATE fails
- Error message: "Gagal menyimpan data"
```

**Solution**:

**A) Check database connection**:
```php
File: config/koneksi.php
Verify:
- Database server running (MySQL)
- Database name correct: sasuki_app
- Username correct
- Password correct
- Host: localhost
```

**B) Test connection**:
```
Create test file: test_db.php
<?php
include 'config/koneksi.php';
if ($koneksi->connect_error) {
    echo "Connection failed: " . $koneksi->connect_error;
} else {
    echo "✅ Database connected successfully";
}
?>

Visit: http://localhost/sasuki_app/test_db.php
```

**C) Check query syntax**:
```
Check MySQL error in logs:
File: logs/php_errors.log
Or check error at time of failure

Common issues:
- Column names misspelled
- Column types mismatch
- SQL injection protection interfering
```

---

### ERROR 9: "Admin dashboard not showing bukti"

**Symptoms**:
```
- Admin logs in successfully
- But table is empty
- Shows "No bukti found"
```

**Solution**:

**A) Check if uploads actually saved**:
```sql
# In PHPMyAdmin, run:
SELECT id, bukti_pembayaran, bukti_verified FROM reservasi 
WHERE bukti_pembayaran IS NOT NULL;

Should show uploads
If empty: No uploads made yet
If errors: Check database schema
```

**B) Check if files exist**:
```
Folder: c:\xampp\htdocs\sasuki_app\bukti_pembayaran\
Should contain: bukti_*.jpg or bukti_*.png files

If empty: Files not saving
If errors: Check folder permissions
```

**C) Check query**:
```
File: admin/verifikasi_bukti.php
Around line 150:
$query = "SELECT * FROM reservasi WHERE bukti_pembayaran IS NOT NULL...";

Verify this query works in PHPMyAdmin
```

---

### ERROR 10: "Payment flow interrupted (stuck between pages)"

**Symptoms**:
```
User at one of these pages and stuck:
1. Stuck on pembayaran.php
2. Stuck on upload_bukti.php
3. Stuck on admin dashboard
Can't proceed to next step
```

**Solution 1: Manual navigation**:
```
If at pembayaran.php:
- Try refreshing page
- Select payment method again
- Click "Lanjutkan ke Pembayaran"

If at upload_bukti.php:
- Upload file manually
- Try again

If at sukses.php:
- Check reservation status in database
- Go to admin and verify payment
```

**Solution 2: Check logs**:
```
Look for errors in:
- logs/php_errors.log
- logs/pembayaran.log
- Browser console (F12)
```

**Solution 3: Reset session**:
```
1. Close browser completely
2. Delete all cookies for localhost
3. Open fresh browser window
4. Visit page again
```

---

## 🛠️ MAINTENANCE PROCEDURES

### Daily Tasks
```
✅ Check logs/pembayaran.log for errors
✅ Monitor bukti_pembayaran folder size
✅ Verify admin dashboard accessible
✅ Test payment flow manually
```

### Weekly Tasks
```
✅ Check database growth
✅ Review WhatsApp logs
✅ Test file upload
✅ Verify admin notifications
```

### Monthly Tasks
```
✅ Backup database
✅ Check server disk space
✅ Review security settings
✅ Update admin password
✅ Check for PHP updates
```

---

## 📝 DATABASE REPAIR

### If database becomes corrupted

**Option 1: Auto-repair**:
```
1. Visit: integrity_check.php
2. All checks will detect issues
3. Click "Auto-Fix" button
4. Most issues auto-corrected
```

**Option 2: Manual repair**:
```sql
# In PHPMyAdmin:

-- Check table structure
DESC reservasi;

-- Look for issues

-- If missing columns, add them:
ALTER TABLE reservasi ADD COLUMN bukti_pembayaran VARCHAR(255);
ALTER TABLE reservasi ADD COLUMN bukti_verified INT DEFAULT 0;

-- If column types wrong, modify:
ALTER TABLE reservasi MODIFY bukti_verified INT DEFAULT 0;

-- If data corrupted, backup and restore from backup
```

---

## 🔄 ROLLBACK PROCEDURES

### If something breaks, rollback to previous state

**Option 1: Database rollback**:
```
1. Restore from backup:
   MySQL backup file (e.g., sasuki_app_backup.sql)
   
2. In PHPMyAdmin:
   - Drop current database
   - Create new database: sasuki_app
   - Import backup SQL file
   
3. Verify all data restored
```

**Option 2: File rollback**:
```
1. Backup current files
2. Re-download original files
3. Re-apply modifications
4. Test payment flow again
```

---

## 📞 SUPPORT CHECKLIST

When reporting errors, provide:

```
☐ Error message (exact text)
☐ Which page/step when error occurred
☐ Browser type (Chrome, Firefox, etc)
☐ Screenshots of error
☐ Contents of logs/php_errors.log
☐ Database check results (run integrity_check.php)
☐ Steps to reproduce
☐ What already tried
```

---

## ✅ FINAL VERIFICATION CHECKLIST

Before considering system ready:

```
Database:
☐ Columns bukti_pembayaran exists
☐ Columns bukti_verified exists
☐ Data can be inserted
☐ Data can be updated

Files:
☐ upload_bukti.php exists and readable
☐ admin/verifikasi_bukti.php exists and readable
☐ .htaccess in bukti_pembayaran
☐ All required files present

Flow:
☐ Reservasi page works
☐ Pembayaran page shows modals
☐ Upload bukti page works
☐ Sukses page displays correctly
☐ Admin dashboard accessible

Notifications:
☐ WhatsApp message on reservasi
☐ WhatsApp message on bukti upload
☐ WhatsApp message on approval
☐ WhatsApp message on rejection

Security:
☐ Admin password changed from 'admin123'
☐ Bank data configured with real accounts
☐ File permissions correct
☐ Database backup in place
```

---

**This guide ensures seamless operation without errors. Use it as reference when issues occur.**

**All systems operational when all checkmarks above are completed! ✅**
