-- =====================================================
-- SASUKI BBQ - Database Structure v2.0
-- Payment Flow System dengan Upload Bukti & Verifikasi
-- =====================================================

-- =====================================================
-- 1. TABLE RESERVASI (Main Reservation Table)
-- =====================================================
CREATE TABLE IF NOT EXISTS `reservasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nama_pelanggan` varchar(100) NOT NULL COMMENT 'Nama pelanggan',
  `telepon` varchar(20) NOT NULL COMMENT 'Nomor telepon (WhatsApp)',
  `jumlah_orang` int(11) NOT NULL COMMENT 'Jumlah orang yang datang',
  `tanggal` date NOT NULL COMMENT 'Tanggal reservasi',
  `jam` time NOT NULL COMMENT 'Jam reservasi',
  `metode_pembayaran` varchar(50) NOT NULL COMMENT 'transfer_bank atau qris',
  `bukti_pembayaran` varchar(255) NULL COMMENT 'Path file bukti pembayaran (v2.0)',
  `bukti_verified` int(11) DEFAULT 0 COMMENT 'Status verifikasi: 0=pending, 1=verified, -1=rejected (v2.0)',
  `catatan` text NULL COMMENT 'Catatan khusus dari pelanggan',
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, dibayar, dikonfirmasi, selesai, batal',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan reservasi',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
  
  -- Indexes untuk optimasi query
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
  KEY `idx_bukti_verified` (`bukti_verified`),
  KEY `idx_telepon` (`telepon`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel Reservasi Meja SASUKI BBQ';

-- =====================================================
-- 2. TABLE ADMIN (Admin Users)
-- =====================================================
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) NOT NULL UNIQUE COMMENT 'Username admin',
  `password` varchar(255) NOT NULL COMMENT 'Password terenkripsi',
  `email` varchar(100) NOT NULL UNIQUE COMMENT 'Email admin',
  `nama` varchar(100) NOT NULL COMMENT 'Nama lengkap admin',
  `role` varchar(50) DEFAULT 'admin' COMMENT 'admin, owner, manager',
  `status` varchar(20) DEFAULT 'active' COMMENT 'active, inactive',
  `last_login` timestamp NULL COMMENT 'Waktu login terakhir',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan akun',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
  
  KEY `idx_username` (`username`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel Admin Users';

-- =====================================================
-- 3. TABLE PAYMENT LOG (Tracking Pembayaran)
-- =====================================================
CREATE TABLE IF NOT EXISTS `payment_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `reservasi_id` int(11) NOT NULL COMMENT 'Foreign key ke reservasi',
  `metode_pembayaran` varchar(50) NOT NULL COMMENT 'transfer_bank atau qris',
  `nominal` decimal(15, 2) NOT NULL COMMENT 'Nominal pembayaran',
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, completed, failed, cancelled',
  `bank_tujuan` varchar(100) NULL COMMENT 'Bank tujuan transfer',
  `bukti_file` varchar(255) NULL COMMENT 'Path file bukti',
  `verified_by` int(11) NULL COMMENT 'ID admin yang verifikasi',
  `verified_at` timestamp NULL COMMENT 'Waktu verifikasi',
  `rejection_reason` text NULL COMMENT 'Alasan penolakan jika ada',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu penciptaan record',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
  
  FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`verified_by`) REFERENCES `admin`(`id`) ON DELETE SET NULL,
  KEY `idx_reservasi_id` (`reservasi_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log Pembayaran dengan Bukti';

-- =====================================================
-- 4. TABLE BANK ACCOUNTS (Rekening Bank untuk Transfer)
-- =====================================================
CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `bank_name` varchar(100) NOT NULL COMMENT 'Nama bank (BCA, MANDIRI, dsb)',
  `account_number` varchar(30) NOT NULL COMMENT 'Nomor rekening',
  `account_holder` varchar(100) NOT NULL COMMENT 'Nama pemilik rekening',
  `bank_code` varchar(10) NULL COMMENT 'Kode bank untuk API',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=aktif, 0=tidak aktif',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu penambahan rekening',
  
  UNIQUE KEY `idx_bank_account` (`bank_name`, `account_number`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rekening Bank untuk Penerimaan Pembayaran';

-- =====================================================
-- 5. TABLE MEJA (Table/Meja)
-- =====================================================
CREATE TABLE IF NOT EXISTS `meja` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `kode_meja` varchar(10) NOT NULL UNIQUE COMMENT 'Kode meja (A1, A2, B1, dst)',
  `kapasitas` int(11) NOT NULL COMMENT 'Kapasitas orang per meja',
  `lokasi` varchar(50) NULL COMMENT 'Lokasi meja (indoor, outdoor, dll)',
  `status` varchar(20) DEFAULT 'available' COMMENT 'available, reserved, maintenance',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Daftar Meja Restoran';

-- =====================================================
-- 6. TABLE MEJA RESERVATION (Mapping Reservasi ke Meja)
-- =====================================================
CREATE TABLE IF NOT EXISTS `meja_reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `reservasi_id` int(11) NOT NULL COMMENT 'Foreign key ke reservasi',
  `meja_id` int(11) NOT NULL COMMENT 'Foreign key ke meja',
  `jam_mulai` time NOT NULL COMMENT 'Jam mulai penggunaan meja',
  `jam_selesai` time NOT NULL COMMENT 'Jam selesai penggunaan meja',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`meja_id`) REFERENCES `meja`(`id`) ON DELETE RESTRICT,
  KEY `idx_reservasi_id` (`reservasi_id`),
  KEY `idx_meja_id` (`meja_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mapping Reservasi dengan Meja';

-- =====================================================
-- 7. TABLE NOTIFICATION LOG (WhatsApp & Email)
-- =====================================================
CREATE TABLE IF NOT EXISTS `notification_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `reservasi_id` int(11) NOT NULL COMMENT 'Foreign key ke reservasi',
  `type` varchar(50) NOT NULL COMMENT 'whatsapp, email, sms',
  `recipient` varchar(100) NOT NULL COMMENT 'Nomor/email penerima',
  `message` text NOT NULL COMMENT 'Isi pesan',
  `status` varchar(20) DEFAULT 'pending' COMMENT 'pending, sent, failed',
  `error_message` text NULL COMMENT 'Pesan error jika gagal',
  `sent_at` timestamp NULL COMMENT 'Waktu pengiriman',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi`(`id`) ON DELETE CASCADE,
  KEY `idx_reservasi_id` (`reservasi_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log Notifikasi WhatsApp & Email';

-- =====================================================
-- 8. TABLE ACTIVITY LOG (Admin Activity Tracking)
-- =====================================================
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `admin_id` int(11) NULL COMMENT 'ID admin yang melakukan aksi',
  `action` varchar(100) NOT NULL COMMENT 'Jenis aksi (verify_payment, reject_payment, delete_reservasi, etc)',
  `table_name` varchar(50) NULL COMMENT 'Tabel yang diubah',
  `record_id` int(11) NULL COMMENT 'ID record yang diubah',
  `old_value` json NULL COMMENT 'Nilai sebelum perubahan (JSON)',
  `new_value` json NULL COMMENT 'Nilai sesudah perubahan (JSON)',
  `ip_address` varchar(50) NULL COMMENT 'IP address admin',
  `user_agent` text NULL COMMENT 'Browser user agent',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`admin_id`) REFERENCES `admin`(`id`) ON DELETE SET NULL,
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log Aktivitas Admin untuk Audit Trail';

-- =====================================================
-- 9. TABLE SETTINGS (Sistem Configuration)
-- =====================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key` varchar(100) NOT NULL UNIQUE COMMENT 'Setting key',
  `value` longtext NOT NULL COMMENT 'Setting value (JSON untuk data kompleks)',
  `description` text NULL COMMENT 'Deskripsi setting',
  `category` varchar(50) NULL COMMENT 'Kategori setting',
  `updated_by` int(11) NULL COMMENT 'ID admin yang update',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  KEY `idx_key` (`key`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Konfigurasi Sistem';

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert Bank Accounts
INSERT INTO `bank_accounts` (`bank_name`, `account_number`, `account_holder`, `bank_code`, `is_active`) VALUES
('BCA', '1234567890', 'SASUKI BBQ', 'bca', 1),
('MANDIRI', '1234567890', 'SASUKI BBQ', 'mandiri', 1),
('BNI', '1234567890', 'SASUKI BBQ', 'bni', 1),
('CIMB NIAGA', '1234567890', 'SASUKI BBQ', 'cimb', 1),
('OVO', '081234567890', 'SASUKI BBQ', 'ovo', 1),
('DANA', '081234567890', 'SASUKI BBQ', 'dana', 1);

-- Insert Default Admin
INSERT INTO `admin` (`username`, `password`, `email`, `nama`, `role`, `status`) VALUES
('admin', SHA2('admin123', 256), 'admin@sasukibbq.com', 'Administrator', 'admin', 'active'),
('owner', SHA2('owner123', 256), 'owner@sasukibbq.com', 'Owner', 'owner', 'active'),
('manager', SHA2('manager123', 256), 'manager@sasukibbq.com', 'Manager', 'manager', 'active');

-- Insert Meja (22 meja total)
INSERT INTO `meja` (`kode_meja`, `kapasitas`, `lokasi`, `status`) VALUES
('A1', 2, 'Indoor', 'available'),
('A2', 2, 'Indoor', 'available'),
('A3', 4, 'Indoor', 'available'),
('A4', 4, 'Indoor', 'available'),
('A5', 6, 'Indoor', 'available'),
('A6', 6, 'Indoor', 'available'),
('B1', 2, 'Indoor', 'available'),
('B2', 2, 'Indoor', 'available'),
('B3', 4, 'Indoor', 'available'),
('B4', 4, 'Indoor', 'available'),
('B5', 6, 'Indoor', 'available'),
('B6', 6, 'Indoor', 'available'),
('C1', 2, 'Outdoor', 'available'),
('C2', 2, 'Outdoor', 'available'),
('C3', 4, 'Outdoor', 'available'),
('C4', 4, 'Outdoor', 'available'),
('C5', 6, 'Outdoor', 'available'),
('C6', 6, 'Outdoor', 'available'),
('D1', 8, 'Indoor', 'available'),
('D2', 8, 'Indoor', 'available'),
('D3', 10, 'Outdoor', 'available'),
('D4', 10, 'Outdoor', 'available');

-- Insert Settings
INSERT INTO `settings` (`key`, `value`, `description`, `category`) VALUES
('app_name', 'SASUKI BBQ', 'Nama aplikasi', 'general'),
('app_version', '2.0', 'Versi aplikasi', 'general'),
('price_per_person', '50000', 'Harga per orang (Rupiah)', 'pricing'),
('min_reservation_time', '60', 'Minimum waktu reservasi (menit)', 'reservation'),
('max_reservation_days', '90', 'Maksimal hari reservasi ke depan', 'reservation'),
('admin_email', 'admin@sasukibbq.com', 'Email admin untuk notifikasi', 'notification'),
('whatsapp_token', '', 'Token WhatsApp API (Fontre/Twilio)', 'notification'),
('working_hours_open', '11:00', 'Jam buka restoran', 'business'),
('working_hours_close', '22:00', 'Jam tutup restoran', 'business');

-- =====================================================
-- VIEWS untuk Reporting
-- =====================================================

-- View: Daily Revenue
CREATE OR REPLACE VIEW `vw_daily_revenue` AS
SELECT 
  DATE(r.tanggal) as tanggal,
  COUNT(r.id) as total_reservasi,
  SUM(CASE WHEN r.bukti_verified = 1 THEN 1 ELSE 0 END) as verified_count,
  SUM(CASE WHEN r.bukti_verified = 1 THEN r.jumlah_orang * 50000 ELSE 0 END) as daily_revenue
FROM reservasi r
WHERE r.bukti_verified = 1
GROUP BY DATE(r.tanggal)
ORDER BY tanggal DESC;

-- View: Monthly Revenue
CREATE OR REPLACE VIEW `vw_monthly_revenue` AS
SELECT 
  DATE_FORMAT(r.tanggal, '%Y-%m') as bulan,
  COUNT(r.id) as total_reservasi,
  SUM(CASE WHEN r.bukti_verified = 1 THEN 1 ELSE 0 END) as verified_count,
  SUM(CASE WHEN r.bukti_verified = 1 THEN r.jumlah_orang * 50000 ELSE 0 END) as monthly_revenue
FROM reservasi r
WHERE r.bukti_verified = 1
GROUP BY DATE_FORMAT(r.tanggal, '%Y-%m')
ORDER BY bulan DESC;

-- View: Top Customers
CREATE OR REPLACE VIEW `vw_top_customers` AS
SELECT 
  r.nama_pelanggan,
  COUNT(r.id) as booking_count,
  SUM(r.jumlah_orang * 50000) as total_spent,
  MAX(r.created_at) as last_booking
FROM reservasi r
WHERE r.bukti_verified = 1
GROUP BY r.nama_pelanggan
ORDER BY total_spent DESC;

-- View: Reservation Status Summary
CREATE OR REPLACE VIEW `vw_reservation_summary` AS
SELECT 
  r.status,
  COUNT(r.id) as total,
  SUM(r.jumlah_orang * 50000) as total_value
FROM reservasi r
GROUP BY r.status;

-- View: Payment Verification Status
CREATE OR REPLACE VIEW `vw_payment_verification` AS
SELECT 
  CASE 
    WHEN r.bukti_verified = 1 THEN 'Verified'
    WHEN r.bukti_verified = 0 AND r.bukti_pembayaran IS NOT NULL THEN 'Pending'
    WHEN r.bukti_verified = -1 THEN 'Rejected'
    ELSE 'No Payment'
  END as verification_status,
  COUNT(r.id) as total_count,
  ROUND(COUNT(r.id) * 100.0 / (SELECT COUNT(*) FROM reservasi), 2) as percentage
FROM reservasi r
GROUP BY r.bukti_verified;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure: Get Available Tables for a Time Slot
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS `sp_get_available_tables`(
  IN p_tanggal DATE,
  IN p_jam TIME,
  IN p_durasi_menit INT
)
BEGIN
  SELECT m.id, m.kode_meja, m.kapasitas, m.lokasi
  FROM meja m
  WHERE m.status = 'available'
  AND m.id NOT IN (
    SELECT DISTINCT mr.meja_id
    FROM meja_reservation mr
    JOIN reservasi r ON mr.reservasi_id = r.id
    WHERE r.tanggal = p_tanggal
    AND r.status NOT IN ('batal', 'selesai')
    AND (
      (mr.jam_mulai <= p_jam AND DATE_ADD(CONCAT(r.tanggal, ' ', mr.jam_mulai), INTERVAL p_durasi_menit MINUTE) > CONCAT(p_tanggal, ' ', p_jam))
      OR (p_jam < DATE_ADD(mr.jam_mulai, INTERVAL p_durasi_menit MINUTE) AND p_jam >= mr.jam_mulai)
    )
  )
  ORDER BY m.kapasitas ASC;
END$$

DELIMITER ;

-- Procedure: Create Payment Log Entry
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS `sp_create_payment_log`(
  IN p_reservasi_id INT,
  IN p_metode_pembayaran VARCHAR(50),
  IN p_nominal DECIMAL(15,2),
  IN p_bukti_file VARCHAR(255)
)
BEGIN
  INSERT INTO payment_log (reservasi_id, metode_pembayaran, nominal, bukti_file, status)
  VALUES (p_reservasi_id, p_metode_pembayaran, p_nominal, p_bukti_file, 'pending');
END$$

DELIMITER ;

-- Procedure: Verify Payment
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS `sp_verify_payment`(
  IN p_payment_log_id INT,
  IN p_admin_id INT,
  IN p_approved BOOLEAN
)
BEGIN
  DECLARE v_reservasi_id INT;
  
  IF p_approved THEN
    UPDATE payment_log SET status = 'completed', verified_by = p_admin_id, verified_at = NOW()
    WHERE id = p_payment_log_id;
    
    SELECT reservasi_id INTO v_reservasi_id FROM payment_log WHERE id = p_payment_log_id;
    UPDATE reservasi SET bukti_verified = 1 WHERE id = v_reservasi_id;
  ELSE
    UPDATE payment_log SET status = 'failed', verified_by = p_admin_id, verified_at = NOW()
    WHERE id = p_payment_log_id;
    
    SELECT reservasi_id INTO v_reservasi_id FROM payment_log WHERE id = p_payment_log_id;
    UPDATE reservasi SET bukti_verified = -1 WHERE id = v_reservasi_id;
  END IF;
END$$

DELIMITER ;

-- =====================================================
-- INDEXES untuk Performance
-- =====================================================

-- Additional indexes untuk query optimization
ALTER TABLE reservasi ADD FULLTEXT INDEX `ft_nama_pelanggan` (`nama_pelanggan`);
ALTER TABLE reservasi ADD FULLTEXT INDEX `ft_catatan` (`catatan`);

-- =====================================================
-- END OF DATABASE SCRIPT
-- =====================================================
-- 
-- Petunjuk Instalasi:
-- 1. Buat database baru: CREATE DATABASE sasuki_bbq;
-- 2. Select database: USE sasuki_bbq;
-- 3. Jalankan script ini: SOURCE database.sql;
-- 4. Verifikasi: SHOW TABLES; SHOW VIEWS;
--
-- Default Admin Credentials:
-- Username: admin    | Password: admin123
-- Username: owner    | Password: owner123
-- Username: manager  | Password: manager123
--
-- JANGAN LUPA MENGUBAH PASSWORD SEBELUM PRODUCTION!
-- =====================================================
