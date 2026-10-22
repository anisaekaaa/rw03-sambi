<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Cek parameter ID warga
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    // Query hapus data warga
    $query = "DELETE FROM warga WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        // Berhasil hapus -> kembalikan ke dashboard dengan pesan sukses
        header("Location: admin_dashboard.php?pesan=sukses_hapus");
        exit;
    } else {
        // Jika gagal hapus
        header("Location: admin_dashboard.php?pesan=gagal_hapus");
        exit;
    }
} else {
    header("Location: admin_dashboard.php");
    exit;
}
?>