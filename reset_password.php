<?php
include 'koneksi.php';

// Generate password hash baru untuk 'admin123'
$password_baru = password_hash('admin123', PASSWORD_DEFAULT);

$query = "UPDATE users SET password = '$password_baru'";

if (mysqli_query($koneksi, $query)) {
    echo "<h2 style='color:green;'>Password Semua Admin Berhasil Direset!</h2>";
    echo "<p>Sekarang semua akun (<b>rw03</b>, <b>rt01</b> s/d <b>rt06</b>) bisa login menggunakan password: <b>admin123</b></p>";
    echo "<a href='login.php' style='font-size:18px; font-weight:bold;'>Klik di sini untuk Login</a>";
} else {
    echo "Gagal mereset password: " . mysqli_error($koneksi);
}
?>