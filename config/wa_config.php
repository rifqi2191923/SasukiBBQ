<?php
/**
 * Konfigurasi WhatsApp Gateway
 * Menggunakan Fonnte API (https://fonnte.com)
 */

// ============== OPSI 1: Fonnte API ==============
// Daftar di https://fonnte.com dan dapatkan token API
define('WA_GATEWAY', 'fonnte'); // pilihan: 'fonnte', 'twilio', 'local'
define('FONNTE_TOKEN', 'YOUR_FONNTE_API_TOKEN'); // Ganti dengan token Anda
define('FONNTE_URL', 'https://api.fonnte.com/send');

// ============== OPSI 2: Twilio (Opsional) ==============
// define('TWILIO_ACCOUNT_SID', 'YOUR_TWILIO_ACCOUNT_SID');
// define('TWILIO_AUTH_TOKEN', 'YOUR_TWILIO_AUTH_TOKEN');
// define('TWILIO_WHATSAPP_NUMBER', 'whatsapp:+YOUR_TWILIO_NUMBER');

// ============== OPSI 3: Local Gateway (untuk development) ==============
// define('WA_GATEWAY', 'local');
// define('WA_LOG_FILE', __DIR__ . '/../logs/wa_messages.log');

/**
 * Fungsi untuk mengirim pesan WhatsApp
 * 
 * @param string $phone_number Nomor telepon dengan format 62xxxxxxxxxxxx
 * @param string $message Isi pesan
 * @param array $options Opsi tambahan (gambar, dokumen, dll)
 * @return array ['success' => bool, 'message' => string, 'data' => array]
 */
function sendWhatsAppMessage($phone_number, $message, $options = []) {
    // Normalisasi nomor telepon
    $phone_number = normalizePhoneNumber($phone_number);
    
    if (!$phone_number) {
        return [
            'success' => false,
            'message' => 'Nomor telepon tidak valid',
            'data' => []
        ];
    }

    switch (WA_GATEWAY) {
        case 'fonnte':
            return sendViaFonnte($phone_number, $message, $options);
        case 'twilio':
            return sendViaTwilio($phone_number, $message, $options);
        case 'local':
            return sendViaLocal($phone_number, $message, $options);
        default:
            return [
                'success' => false,
                'message' => 'Gateway WhatsApp tidak dikonfigurasi',
                'data' => []
            ];
    }
}

/**
 * Normalisasi nomor telepon ke format internasional
 * Konversi: 0821234567890 => 6282123456890
 * Atau: +62821234567890 => 6282123456890
 */
function normalizePhoneNumber($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    if (substr($phone, 0, 1) === '+') {
        $phone = substr($phone, 1);
    }
    
    if (substr($phone, 0, 2) === '62') {
        return $phone;
    }
    
    if (substr($phone, 0, 1) === '0') {
        return '62' . substr($phone, 1);
    }
    
    return null;
}

/**
 * Kirim via Fonnte API
 */
function sendViaFonnte($phone_number, $message, $options = []) {
    $token = FONNTE_TOKEN;
    
    if ($token === 'YOUR_FONNTE_API_TOKEN') {
        error_log('Fonnte API Token tidak dikonfigurasi');
        return [
            'success' => false,
            'message' => 'API Token tidak dikonfigurasi',
            'data' => []
        ];
    }

    $payload = [
        'target' => $phone_number,
        'message' => $message,
    ];

    // Tambahkan gambar jika ada
    if (!empty($options['image'])) {
        $payload['image'] = $options['image'];
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => FONNTE_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_HTTPHEADER => [
            "Authorization: {$token}"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        error_log('Fonnte Error: ' . $err);
        return [
            'success' => false,
            'message' => 'Gagal mengirim pesan: ' . $err,
            'data' => []
        ];
    }

    $result = json_decode($response, true);
    
    if (isset($result['status']) && $result['status'] === true) {
        return [
            'success' => true,
            'message' => 'Pesan berhasil dikirim',
            'data' => $result
        ];
    } else {
        $error_msg = isset($result['reason']) ? $result['reason'] : 'Gagal mengirim pesan';
        error_log('Fonnte Response: ' . $response);
        return [
            'success' => false,
            'message' => $error_msg,
            'data' => $result
        ];
    }
}

/**
 * Kirim via Twilio (opsional)
 */
function sendViaTwilio($phone_number, $message, $options = []) {
    // TODO: Implementasi Twilio jika diperlukan
    return [
        'success' => false,
        'message' => 'Twilio belum diimplementasikan',
        'data' => []
    ];
}

/**
 * Kirim via Local (untuk development/testing)
 */
function sendViaLocal($phone_number, $message, $options = []) {
    // Buat folder logs jika belum ada
    $log_dir = dirname(WA_LOG_FILE);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'phone' => $phone_number,
        'message' => $message,
        'options' => $options,
    ];

    file_put_contents(
        WA_LOG_FILE,
        json_encode($log_entry) . "\n",
        FILE_APPEND
    );

    return [
        'success' => true,
        'message' => 'Pesan disimpan ke log (mode development)',
        'data' => $log_entry
    ];
}

/**
 * Template pesan untuk berbagai keperluan
 */
function getMessageTemplate($type, $data = []) {
    date_default_timezone_set('Asia/Jakarta');
    
    switch ($type) {
        case 'reservasi_pending':
            return sprintf(
                "🍖 *KONFIRMASI RESERVASI SASUKI BBQ*\n\n" .
                "Terima kasih telah melakukan reservasi!\n\n" .
                "*Detail Reservasi:*\n" .
                "📋 ID: #%s\n" .
                "👤 Nama: %s\n" .
                "📅 Tanggal: %s\n" .
                "🕐 Jam: %s\n" .
                "👥 Jumlah Orang: %d\n" .
                "📞 No. Telepon: %s\n\n" .
                "Status: *Menunggu Pembayaran*\n\n" .
                "Silakan lanjutkan ke halaman pembayaran untuk mengkonfirmasi reservasi Anda.\n\n" .
                "Terima kasih! 🙏",
                $data['id'] ?? '—',
                $data['nama'] ?? '—',
                date('d/m/Y', strtotime($data['tanggal'] ?? '')),
                $data['jam'] ?? '—',
                $data['jumlah'] ?? 0,
                $data['telepon'] ?? '—'
            );

        case 'pembayaran_sukses':
            return sprintf(
                "✅ *PEMBAYARAN BERHASIL*\n\n" .
                "Reservasi Anda telah dikonfirmasi!\n\n" .
                "*Detail Reservasi:*\n" .
                "📋 ID: #%s\n" .
                "👤 Nama: %s\n" .
                "📅 Tanggal: %s\n" .
                "🕐 Jam: %s\n" .
                "👥 Jumlah Orang: %d\n" .
                "💳 Metode: %s\n\n" .
                "Kami sudah menerima pembayaran Anda.\n" .
                "Meja akan dipersiapkan untuk Anda.\n\n" .
                "Sampai jumpa di SASUKI BBQ! 🎉",
                $data['id'] ?? '—',
                $data['nama'] ?? '—',
                date('d/m/Y', strtotime($data['tanggal'] ?? '')),
                $data['jam'] ?? '—',
                $data['jumlah'] ?? 0,
                ucfirst($data['metode'] ?? '—')
            );

        case 'pembayaran_pending':
            return sprintf(
                "⏳ *NOTIFIKASI PEMBAYARAN*\n\n" .
                "Reservasi Anda masih menunggu pembayaran.\n\n" .
                "*Detail Reservasi:*\n" .
                "📋 ID: #%s\n" .
                "👤 Nama: %s\n" .
                "📅 Tanggal: %s\n" .
                "🕐 Jam: %s\n" .
                "💰 Total: Rp %s\n\n" .
                "Segera selesaikan pembayaran untuk mengkonfirmasi meja Anda.\n\n" .
                "Terima kasih! 🙏",
                $data['id'] ?? '—',
                $data['nama'] ?? '—',
                date('d/m/Y', strtotime($data['tanggal'] ?? '')),
                $data['jam'] ?? '—',
                isset($data['total']) ? number_format($data['total'], 0, ',', '.') : '—'
            );

        case 'reminder_pembayaran':
            return sprintf(
                "⏰ *REMINDER PEMBAYARAN*\n\n" .
                "Jangan lupa untuk menyelesaikan pembayaran reservasi Anda!\n\n" .
                "📋 ID Reservasi: #%s\n" .
                "📅 Tanggal: %s\n" .
                "🕐 Jam: %s\n\n" .
                "Klik link berikut untuk melanjutkan pembayaran:\n" .
                "%s\n\n" .
                "Terima kasih! 🙏",
                $data['id'] ?? '—',
                date('d/m/Y', strtotime($data['tanggal'] ?? '')),
                $data['jam'] ?? '—',
                $data['payment_link'] ?? ''
            );

        default:
            return $data['custom_message'] ?? 'Pesan dari SASUKI BBQ';
    }
}

?>
