<?php

require_once "phpqrcode/qrlib.php";
$base_url = "http://localhost/PROJEK_PW/Pemesanan/menu.php?table=";
$total_meja = 22;

$folder = "qrcode";
if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
}

echo "<h2>QR Code Meja SASUKI BBQ</h2>";
echo "<p>Scan QR ini untuk langsung ke halaman pemesanan.</p>";
echo "<div style='display:flex;flex-wrap:wrap;gap:20px;'>";

for ($i = 1; $i <= $total_meja; $i++) {
    $table = str_pad($i, 2, "0", STR_PAD_LEFT);
    $url = $base_url . $table;
    $filename = $folder . "/qr_meja_" . $table . ".png";

    QRcode::png($url, $filename, QR_ECLEVEL_L, 5, 2);

    echo "
    <div style='margin:10px;text-align:center;'>
        <img src='$filename' width='150'><br>
        <strong>Meja $table</strong>
    </div>";
}

echo "</div>";
?>
