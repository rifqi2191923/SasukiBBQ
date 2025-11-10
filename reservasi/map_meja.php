<?php include '../config/koneksi.php'; ?>
<?php
// Konfigurasi durasi makan AYCE (menit)
$MAX_MINUTES = 90;

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
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 10px;
        padding: 16px 18px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        margin-bottom: 20px;
    }
    .map-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px; }
    .map-title { margin: 0; color:#2c3e50; font-size:18px; }
    .badge-row { display:flex; gap:8px; flex-wrap:wrap; }
    .badge { font-size:11px; padding:4px 8px; border-radius:999px; border:1px solid rgba(0,0,0,0.08); background:#f6f8fa; color:#333; }
    .badge-time { background:#eef6ff; border-color:#bcdcff; color:#1e5aa7; }

    .map-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(110px, 1fr));
        gap: 12px;
    }
    .table-card {
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        color: #222;
        cursor: default;
        user-select: none;
        transition: transform .1s ease, box-shadow .1s ease, border-color .1s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        border: 1px solid rgba(0,0,0,0.06);
    }
    .table-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .status-free { background-color: #e8f8f0; border: 1px solid #20bf6b; }
    .status-busy { background-color: #fdecea; border: 1px solid #eb4d4b; }
    .status-soon { background-color: #fff7e6; border: 1px solid #f1c40f; }
    .table-name { font-weight: 700; margin-bottom: 8px; letter-spacing:.2px; }
    .time-remaining { font-size: 12px; color: #4b5563; }
    .legend { display:flex; gap:12px; align-items:center; margin-bottom:8px; flex-wrap:wrap; }
    .legend-item { display:flex; align-items:center; gap:6px; font-size: 12px; color:#4b5563; }
    .legend-dot { width:12px; height:12px; border-radius:3px; display:inline-block; border:1px solid rgba(0,0,0,0.1); }
    .legend-free { background:#e8f8f0; border-color:#20bf6b; }
    .legend-busy { background:#fdecea; border-color:#eb4d4b; }
    .legend-soon { background:#fff7e6; border-color:#f1c40f; }
    .note { font-size:12px; color:#666; margin-bottom: 10px; }
    .selected { outline: 2px solid #2d98da; box-shadow: 0 0 0 3px rgba(45,152,218,0.2); }
    @media (max-width: 1024px) {
        .map-grid { grid-template-columns: repeat(4, minmax(110px, 1fr)); }
    }
    @media (max-width: 768px) {
        .map-grid { grid-template-columns: repeat(3, minmax(100px, 1fr)); }
    }
</style>

<div class="map-container">
    <div class="map-header">
        <h2 class="map-title">Peta Meja</h2>
        <div class="badge-row">
            <span class="badge">Total 22 meja</span>
            <span class="badge badge-time">Maks. <?php echo $MAX_MINUTES; ?> menit</span>
        </div>
    </div>
    <div class="legend">
        <div class="legend-item"><span class="legend-dot legend-free"></span> Tersedia</div>
        <div class="legend-item"><span class="legend-dot legend-busy"></span> Terpakai</div>
        <div class="legend-item"><span class="legend-dot legend-soon"></span> < 15 menit lagi</div>
    </div>
    <div class="note">Klik meja untuk memilih — meja yang aktif menampilkan sisa waktu.</div>
    <div class="map-grid" id="mapGrid">
        <?php for ($i = 1; $i <= 22; $i++) { ?>
            <?php
                $kode = str_pad($i, 2, "0", STR_PAD_LEFT);
                $remaining = getRemainingMinutes($koneksi, $kode, $MAX_MINUTES);
                $isBusyByTimer = $remaining > 0;
                $statusDb = isset($mejaMap[$kode]) ? $mejaMap[$kode] : 'tersedia';
                $isBusyByDb = ($statusDb !== 'tersedia');
                $isBusy = $isBusyByTimer || $isBusyByDb;
                $soon  = ($remaining > 0 && $remaining <= 15);
                $statusClass = $isBusy ? ($soon ? 'status-soon' : 'status-busy') : 'status-free';
                $label = $isBusyByTimer ? ('Sisa ' . $remaining . ' menit') : ($isBusyByDb ? ucfirst($statusDb) : 'Tersedia');
                $pref = isset($_GET['pref_meja']) ? $_GET['pref_meja'] : '';
                $selectedClass = ($pref !== '' && $pref === $kode) ? ' selected' : '';
            ?>
            <div class="table-card <?php echo $statusClass . $selectedClass; ?>"
                 data-kodemeja="<?php echo htmlspecialchars($kode); ?>"
                 data-available="<?php echo $isBusy ? '0' : '1'; ?>">
                <div class="table-name">Meja <?php echo htmlspecialchars($kode); ?></div>
                <div class="time-remaining"><?php echo htmlspecialchars($label); ?></div>
            </div>
        <?php } ?>
    </div>
</div>

<script>
function selectTable(kodeMeja, available) {
    if (!available) {
        alert('Meja sedang terpakai. Silakan pilih meja lain.');
        return;
    }
    // Redirect ke halaman form dengan preselect kode meja
    const url = new URL(window.location.href);
    url.searchParams.set('pref_meja', kodeMeja);
    // Jika kita berada di file terpisah, arahkan ke index.php; jika sudah tertanam di index, biarkan reload
    if (!window.location.pathname.endsWith('/index.php')) {
        window.location.href = 'index.php?pref_meja=' + encodeURIComponent(kodeMeja);
    } else {
        window.location.href = url.toString();
    }
}

// Opsional: refresh indikator tiap 60 detik
setInterval(function() { window.location.reload(); }, 60000);
</script>


