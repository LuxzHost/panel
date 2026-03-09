<?php
header('Content-Type: text/plain');
$logFile = 'logs.txt';
if (file_exists($logFile)) {
    file_put_contents($logFile, "");
    echo "✅ Semua log berhasil dihapus!";
} else {
    echo "⚠️ File logs.txt tidak ditemukan.";
}
?>