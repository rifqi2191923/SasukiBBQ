<?php include '../config/koneksi.php'; ?>
<?php
// Konfigurasi durasi makan AYCE (menit)
$MAX_MINUTES = 90;

// Ambil parameter tanggal & jam dari query untuk memfilter ketersediaan
$selectedDate = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$selectedTime = isset($_GET['jam']) ? $_GET['jam'] : '';

// Fungsi untuk mengecek ketersediaan meja (jika belum didefinisikan di file yang memanggil)
if (!function_exists('isTableAvailable')) {
    function isTableAvailable($koneksi, $kodeMeja, $tanggal, $jam, $durationMinutes = 90) {
        if ($tanggal === '' || $jam === '') return true; // Jika belum pilih tanggal/jam, anggap tersedia
        $tanggalEsc = mysqli_real_escape_string($koneksi, $tanggal);
        $jamEsc = mysqli_real_escape_string($koneksi, $jam);
        $kodeEsc = mysqli_real_escape_string($koneksi, $kodeMeja);

        // Hitung interval waktu
        $query = "SELECT 1 FROM reservasi
                  WHERE kode_meja = '$kodeEsc'
                    AND tanggal = '$tanggalEsc'
                    AND (
                        TIMESTAMP(tanggal, jam) < TIMESTAMP('$tanggalEsc', '$jamEsc') + INTERVAL $durationMinutes MINUTE
                        AND TIMESTAMP('$tanggalEsc', '$jamEsc') < TIMESTAMP(tanggal, jam) + INTERVAL $durationMinutes MINUTE
                    )
                    AND status NOT IN ('batal','selesai','waiting_ditolak')
                  LIMIT 1";

        $res = mysqli_query($koneksi, $query);
        if ($res && mysqli_num_rows($res) > 0) {
            return false; // ada bentrok
        }
        return true; // tidak bentrok
    }
}

// Ambil daftar semua meja (jika ada) untuk mengetahui status
$mejaMap = [];
$mejaQuery = mysqli_query($koneksi, "SELECT kode_meja, status FROM meja");
if ($mejaQuery) {
    while ($row = mysqli_fetch_assoc($mejaQuery)) {
        $mejaMap[$row['kode_meja']] = $row['status'];
    }
}

// Fungsi hitung sisa menit berdasarkan reservasi aktif hari ini
function getRemainingMinutes($koneksi, $kode_meja, $MAX_MINUTES) {
    date_default_timezone_set('Asia/Jakarta');
    $today = date('Y-m-d');
    $now   = new DateTime();

    // Ambil reservasi hari ini untuk meja terkait yang paling mendekati/terbaru, dimana now berada dalam window 90 menit
    $sql = "SELECT tanggal, jam, status FROM reservasi
            WHERE kode_meja='" . mysqli_real_escape_string($koneksi, $kode_meja) . "'
              AND tanggal='" . $today . "'
              AND jam <= TIME(NOW())
            ORDER BY tanggal DESC, jam DESC
            LIMIT 1";

    $res = mysqli_query($koneksi, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $start = DateTime::createFromFormat('Y-m-d H:i:s', $row['tanggal'] . ' ' . $row['jam']);
        if (!$start) {
            // Fallback kalau format jam tanpa detik
            $start = DateTime::createFromFormat('Y-m-d H:i', $row['tanggal'] . ' ' . substr($row['jam'], 0, 5));
        }
        if ($start) {
            $diffMinutes = (int) floor(($now->getTimestamp() - $start->getTimestamp()) / 60);
            $remaining   = $MAX_MINUTES - $diffMinutes;
            if ($remaining > 0 && $remaining <= $MAX_MINUTES) {
                return $remaining; // masih aktif
            }
        }
    }
    return 0; // tidak ada sesi aktif
}
?>

<style>
    .map-container {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        border: 2px solid #e9ecef;
    }
    
    .map-header { 
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        gap: 15px; 
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .map-title { 
        margin: 0; 
        color: #2c3e50; 
        font-size: 1.5em;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .badge-row { 
        display: flex; 
        gap: 10px; 
        flex-wrap: wrap; 
    }
    
    .badge { 
        font-size: 0.85em; 
        padding: 6px 12px; 
        border-radius: 20px; 
        border: 1px solid rgba(0,0,0,0.1); 
        background: linear-gradient(135deg, #f6f8fa 0%, #e9ecef 100%);
        color: #495057;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .badge-time { 
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-color: #90caf9;
        color: #1565c0;
    }

    .map-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(100px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .table-card {
        border-radius: 12px;
        padding: 15px 10px;
        text-align: center;
        color: #222;
        cursor: pointer;
        user-select: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .table-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }
    
    .table-card:hover::before {
        left: 100%;
    }
    
    .table-card:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }
    
    .table-card:active {
        transform: translateY(-1px) scale(1);
    }
    
    .status-free { 
        background: linear-gradient(135deg, #e8f8f0 0%, #d4f4e6 100%);
        border-color: #20bf6b;
        cursor: pointer;
    }
    
    .status-free:hover {
        background: linear-gradient(135deg, #d4f4e6 0%, #c0f0d8 100%);
        border-color: #16a085;
    }
    
    .status-busy { 
        background: linear-gradient(135deg, #fdecea 0%, #fadbd8 100%);
        border-color: #eb4d4b;
        cursor: not-allowed;
        opacity: 0.8;
    }
    
    .status-soon { 
        background: linear-gradient(135deg, #fff7e6 0%, #ffeaa7 100%);
        border-color: #f1c40f;
    }
    
    .table-name { 
        font-weight: 700; 
        margin-bottom: 8px; 
        letter-spacing: 0.5px;
        font-size: 1.1em;
        color: #2c3e50;
    }
    
    .time-remaining { 
        font-size: 0.85em; 
        color: #6c757d;
        font-weight: 500;
    }
    
    .legend { 
        display: flex; 
        gap: 20px; 
        align-items: center; 
        margin-bottom: 15px; 
        flex-wrap: wrap;
        padding: 15px;
        background: rgba(248, 249, 250, 0.8);
        border-radius: 10px;
    }
    
    .legend-item { 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        font-size: 0.9em; 
        color: #495057;
        font-weight: 500;
    }
    
    .legend-dot { 
        width: 16px; 
        height: 16px; 
        border-radius: 4px; 
        display: inline-block; 
        border: 2px solid transparent;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .legend-free { 
        background: linear-gradient(135deg, #e8f8f0 0%, #d4f4e6 100%);
        border-color: #20bf6b;
    }
    
    .legend-busy { 
        background: linear-gradient(135deg, #fdecea 0%, #fadbd8 100%);
        border-color: #eb4d4b;
    }
    
    .legend-soon { 
        background: linear-gradient(135deg, #fff7e6 0%, #ffeaa7 100%);
        border-color: #f1c40f;
    }
    
    .note { 
        font-size: 0.9em; 
        color: #6c757d; 
        margin-bottom: 15px;
        padding: 12px;
        background: #e7f3ff;
        border-left: 4px solid #3498db;
        border-radius: 5px;
    }
    
    .selected { 
        outline: 3px solid #2d98da;
        box-shadow: 0 0 0 4px rgba(45,152,218,0.3), 0 6px 20px rgba(45,152,218,0.4);
        border-color: #2d98da !important;
        transform: scale(1.05);
    }
    
    .table-card.selected::after {
        content: '✓';
        position: absolute;
        top: 5px;
        right: 5px;
        background: #2d98da;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    
    @media (max-width: 1024px) {
        .map-grid { 
            grid-template-columns: repeat(4, minmax(100px, 1fr)); 
        }
    }
    
    @media (max-width: 768px) {
        .map-grid { 
            grid-template-columns: repeat(3, minmax(90px, 1fr)); 
            gap: 10px;
        }
        
        .map-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .table-card {
            padding: 12px 8px;
        }
        
        .table-name {
            font-size: 1em;
        }
        
        .time-remaining {
            font-size: 0.75em;
        }
    }
    
    @media (max-width: 480px) {
        .map-grid {
            grid-template-columns: repeat(2, minmax(80px, 1fr));
        }
    }
</style>

<div class="map-container">
    <div class="map-header">
        <h2 class="map-title">
            <span>🗺️</span>
            <span>Peta Meja</span>
        </h2>
        <div class="badge-row">
            <span class="badge">📊 Total 22 meja</span>
            <span class="badge badge-time">⏱️ Maks. <?php echo $MAX_MINUTES; ?> menit</span>
        </div>
    </div>
    <div class="legend">
        <div class="legend-item">
            <span class="legend-dot legend-free"></span>
            <span>Tersedia</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot legend-busy"></span>
            <span>Terpakai</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot legend-soon"></span>
            <span>< 15 menit lagi</span>
        </div>
    </div>
    <div class="note">
        <strong>💡 Tips:</strong> Klik meja yang tersedia (hijau) untuk memilih. Meja yang aktif menampilkan sisa waktu makan.
        <?php if (isset($selectedDate) && isset($selectedTime) && $selectedDate && $selectedTime): ?>
            <br><strong>Waktu dipilih:</strong> <?php echo date('d/m/Y', strtotime($selectedDate)); ?> pukul <?php echo date('H:i', strtotime($selectedTime)); ?>
        <?php endif; ?>
    </div>
    <div class="map-grid" id="mapGrid">
        <?php for ($i = 1; $i <= 22; $i++) { ?>
            <?php
                $kode = str_pad($i, 2, "0", STR_PAD_LEFT);
                $remaining = getRemainingMinutes($koneksi, $kode, $MAX_MINUTES);
                $isBusyByTimer = $remaining > 0;
                $statusDb = isset($mejaMap[$kode]) ? $mejaMap[$kode] : 'tersedia';
                $isBusyByDb = ($statusDb !== 'tersedia');
                
                // Cek ketersediaan berdasarkan tanggal & jam yang dipilih (jika ada)
                $availableForBooking = true;
                if (isset($selectedDate) && isset($selectedTime) && $selectedDate && $selectedTime) {
                    $availableForBooking = isTableAvailable($koneksi, $kode, $selectedDate, $selectedTime);
                }
                
                $isBusy = $isBusyByTimer || $isBusyByDb || (!$availableForBooking && $selectedDate && $selectedTime);
                $soon  = ($remaining > 0 && $remaining <= 15);
                $statusClass = $isBusy ? ($soon ? 'status-soon' : 'status-busy') : 'status-free';
                $label = $isBusyByTimer ? ('⏱️ ' . $remaining . ' menit') : ($isBusyByDb ? '🚫 ' . ucfirst($statusDb) : ($availableForBooking ? '✅ Tersedia' : '❌ Terbooking'));
                $pref = isset($_GET['pref_meja']) ? $_GET['pref_meja'] : '';
                $selectedClass = ($pref !== '' && $pref === $kode) ? ' selected' : '';
            ?>
            <div class="table-card <?php echo $statusClass . $selectedClass; ?>"
                 data-kodemeja="<?php echo htmlspecialchars($kode); ?>"
                 data-available="<?php echo ($isBusy || !$availableForBooking) ? '0' : '1'; ?>"
                 onclick="selectTable('<?php echo htmlspecialchars($kode); ?>', <?php echo ($isBusy || !$availableForBooking) ? 'false' : 'true'; ?>)">
                <div class="table-name">🪑 Meja <?php echo htmlspecialchars($kode); ?></div>
                <div class="time-remaining"><?php echo htmlspecialchars($label); ?></div>
            </div>
        <?php } ?>
    </div>
</div>

<script>
function selectTable(kodeMeja, available) {
    if (!available) {
        // Smooth animation untuk feedback
        const card = document.querySelector(`[data-kodemeja="${kodeMeja}"]`);
        if (card) {
            card.style.animation = 'shake 0.5s';
            setTimeout(() => {
                card.style.animation = '';
            }, 500);
        }
        
        // Show toast notification
        showToast('⚠️ Meja ini sedang terpakai atau tidak tersedia. Silakan pilih meja lain.', 'warning');
        return;
    }
    
    // Remove previous selection
    document.querySelectorAll('.table-card.selected').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selection to clicked table
    const selectedCard = document.querySelector(`[data-kodemeja="${kodeMeja}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
        
        // Smooth scroll to form
        setTimeout(() => {
            const formSection = document.querySelector('.form-section');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 300);
    }
    
    // Update select dropdown
    const selectMeja = document.getElementById('selectMeja');
    if (selectMeja && !selectMeja.disabled) {
        selectMeja.value = kodeMeja;
        
        // Trigger change event to update UI
        const event = new Event('change', { bubbles: true });
        selectMeja.dispatchEvent(event);
    } else {
        // If select is disabled, update URL and reload
        const url = new URL(window.location.href);
        url.searchParams.set('pref_meja', kodeMeja);
        window.location.href = url.toString();
    }
    
    showToast(`✅ Meja ${kodeMeja} dipilih!`, 'success');
}

function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#27ae60' : type === 'warning' ? '#f39c12' : '#3498db'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
        font-weight: 500;
        max-width: 300px;
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Update selected table when select dropdown changes
document.addEventListener('DOMContentLoaded', function() {
    const selectMeja = document.getElementById('selectMeja');
    if (selectMeja) {
        selectMeja.addEventListener('change', function() {
            const selectedValue = this.value;
            if (selectedValue) {
                // Remove previous selection
                document.querySelectorAll('.table-card.selected').forEach(card => {
                    card.classList.remove('selected');
                });
                
                // Add selection
                const selectedCard = document.querySelector(`[data-kodemeja="${selectedValue}"]`);
                if (selectedCard) {
                    selectedCard.classList.add('selected');
                    selectedCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // Set initial selection if exists
        if (selectMeja.value) {
            const selectedCard = document.querySelector(`[data-kodemeja="${selectMeja.value}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
        }
    }
});

// Auto-refresh untuk update status meja setiap 60 detik (opsional, bisa di-disable jika tidak diperlukan)
// setInterval(function() { 
//     // Hanya refresh jika tidak ada form yang sedang diisi
//     const inputs = document.querySelectorAll('input, select');
//     let hasValue = false;
//     inputs.forEach(input => {
//         if (input.value && input.type !== 'submit') {
//             hasValue = true;
//         }
//     });
//     if (!hasValue) {
//         window.location.reload();
//     }
// }, 60000);
</script>


