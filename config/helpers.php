<?php
/**
 * Helper Functions untuk Sistem Reservasi
 * File ini berisi fungsi-fungsi utility yang digunakan di seluruh aplikasi
 */

date_default_timezone_set('Asia/Jakarta');

/**
 * Format mata uang IDR
 * @param int|float $amount
 * @return string
 */
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format tanggal Indonesia
 * @param string $date Format: YYYY-MM-DD
 * @return string
 */
function formatTanggal($date) {
    $bulan = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $pecah = explode('-', $date);
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

/**
 * Format jam HH:MM
 * @param string $time
 * @return string
 */
function formatJam($time) {
    return substr($time, 0, 5);
}

/**
 * Get status badge HTML
 * @param string $status
 * @return string HTML
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span style="background: #f39c12; color: white; padding: 5px 10px; border-radius: 5px; font-weight: 600;">⏳ Menunggu Pembayaran</span>',
        'dibayar' => '<span style="background: #27ae60; color: white; padding: 5px 10px; border-radius: 5px; font-weight: 600;">✅ Dibayar</span>',
        'dikonfirmasi' => '<span style="background: #3498db; color: white; padding: 5px 10px; border-radius: 5px; font-weight: 600;">🔒 Dikonfirmasi</span>',
        'selesai' => '<span style="background: #95a5a6; color: white; padding: 5px 10px; border-radius: 5px; font-weight: 600;">✔️ Selesai</span>',
        'batal' => '<span style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; font-weight: 600;">❌ Batal</span>',
    ];
    
    return isset($badges[$status]) ? $badges[$status] : $status;
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 * @param string $phone
 * @return bool
 */
function isValidPhone($phone) {
    return preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $phone) === 1;
}

/**
 * Generate random code
 * @param int $length
 * @return string
 */
function generateRandomCode($length = 8) {
    return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

/**
 * Sanitize string input
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if date is in past
 * @param string $date Format: YYYY-MM-DD
 * @return bool
 */
function isDateInPast($date) {
    $today = new DateTime('today');
    $checkDate = DateTime::createFromFormat('Y-m-d', $date);
    return $checkDate < $today;
}

/**
 * Check if time slot is available
 * @param mysqli $koneksi
 * @param string $tanggal Format: YYYY-MM-DD
 * @param string $jam Format: HH:MM
 * @param int $durationMinutes Default: 90
 * @return bool
 */
function isTimeSlotAvailable($koneksi, $tanggal, $jam, $durationMinutes = 90) {
    $tanggalEsc = mysqli_real_escape_string($koneksi, $tanggal);
    $jamEsc = mysqli_real_escape_string($koneksi, $jam);
    
    $query = "SELECT COUNT(*) as total FROM reservasi 
              WHERE tanggal = '$tanggalEsc'
              AND status NOT IN ('batal', 'selesai')
              AND (
                  (TIME(jam) <= TIME('$jamEsc') AND DATE_ADD(CONCAT(tanggal, ' ', jam), INTERVAL $durationMinutes MINUTE) > CONCAT('$tanggalEsc', ' ', '$jamEsc'))
                  OR (TIME('$jamEsc') < DATE_ADD(TIME(jam), INTERVAL $durationMinutes MINUTE) AND TIME('$jamEsc') >= TIME(jam))
              )";
    
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['total'] == 0;
}

/**
 * Get total revenue for a date range
 * @param mysqli $koneksi
 * @param string $startDate Format: YYYY-MM-DD
 * @param string $endDate Format: YYYY-MM-DD
 * @return array ['total' => amount, 'count' => number of reservations]
 */
function getRevenueRange($koneksi, $startDate, $endDate) {
    $startEsc = mysqli_real_escape_string($koneksi, $startDate);
    $endEsc = mysqli_real_escape_string($koneksi, $endDate);
    
    $query = "SELECT 
                COUNT(*) as total_reservasi,
                SUM(jumlah_orang * 50000) as total_revenue
              FROM reservasi 
              WHERE status = 'dibayar' 
              AND tanggal BETWEEN '$startEsc' AND '$endEsc'";
    
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    return [
        'count' => $row['total_reservasi'] ?? 0,
        'total' => $row['total_revenue'] ?? 0
    ];
}

/**
 * Get available tables for a time slot
 * @param mysqli $koneksi
 * @param string $tanggal
 * @param string $jam
 * @return array
 */
function getAvailableTables($koneksi, $tanggal, $jam) {
    // Contoh: Jika ada tabel meja
    $tables = [];
    for ($i = 1; $i <= 22; $i++) {
        $kode = str_pad($i, 2, "0", STR_PAD_LEFT);
        if (isTimeSlotAvailable($koneksi, $tanggal, $jam)) {
            $tables[] = $kode;
        }
    }
    return $tables;
}

/**
 * Send email notification
 * @param string $to
 * @param string $subject
 * @param string $message
 * @return bool
 */
function sendEmailNotification($to, $subject, $message) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@sasukibbq.com\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Log activity to file
 * @param string $type
 * @param string $message
 * @param array $data
 */
function logActivity($type, $message, $data = []) {
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $log_file = $log_dir . '/' . $type . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";
    
    if (!empty($data)) {
        $log_entry .= ' | ' . json_encode($data);
    }
    
    file_put_contents($log_file, $log_entry . "\n", FILE_APPEND);
}

/**
 * Get reservasi status statistics
 * @param mysqli $koneksi
 * @return array
 */
function getReservationStats($koneksi) {
    $query = "SELECT 
                status,
                COUNT(*) as total
              FROM reservasi 
              GROUP BY status";
    
    $result = mysqli_query($koneksi, $query);
    $stats = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $stats[$row['status']] = $row['total'];
    }
    
    return [
        'pending' => $stats['pending'] ?? 0,
        'dibayar' => $stats['dibayar'] ?? 0,
        'dikonfirmasi' => $stats['dikonfirmasi'] ?? 0,
        'selesai' => $stats['selesai'] ?? 0,
        'batal' => $stats['batal'] ?? 0,
        'total' => array_sum($stats)
    ];
}

/**
 * Format file size
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Get time remaining until a date
 * @param string $date Format: YYYY-MM-DD
 * @param string $time Format: HH:MM:SS
 * @return array ['days' => int, 'hours' => int, 'minutes' => int, 'seconds' => int]
 */
function getTimeRemaining($date, $time) {
    $now = new DateTime();
    $target = new DateTime($date . ' ' . $time);
    
    if ($target <= $now) {
        return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0];
    }
    
    $diff = $target->diff($now);
    
    return [
        'days' => $diff->days,
        'hours' => $diff->h,
        'minutes' => $diff->i,
        'seconds' => $diff->s
    ];
}

?>
