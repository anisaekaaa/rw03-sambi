<?php
session_start();
include 'koneksi.php';

// Mencegah error variabel
$admin_username = isset($_SESSION['admin_username']) ? strtolower($_SESSION['admin_username']) : '';
$admin_rt = 0;

if (isset($_SESSION['wilayah_rt']) && $_SESSION['wilayah_rt'] > 0) {
    $admin_rt = intval($_SESSION['wilayah_rt']);
} else {
    for ($i = 1; $i <= 6; $i++) {
        if (strpos($admin_username, "rt0$i") !== false || strpos($admin_username, "rt$i") !== false) {
            $admin_rt = $i;
            break;
        }
    }
}

// Cek apakah ada filter RT dari URL (misal: export_excel.php?rt=3)
$filter_rt = isset($_GET['rt']) ? intval($_GET['rt']) : $admin_rt;

// Susun kondisi WHERE agar cuma RT yang dipilih saja yang di-download
$where_rt = ($filter_rt > 0) ? "WHERE rt = $filter_rt" : "";

// Set Header Download Excel
header("Content-type: application/vnd-ms-excel");
$nama_file = ($filter_rt > 0) ? "Data_Warga_RT0{$filter_rt}_RW03.xls" : "Data_Warga_RW03.xls";
header("Content-Disposition: attachment; filename=$nama_file");

// Query mengambil data warga terfilter
$query = mysqli_query($koneksi, "SELECT * FROM warga $where_rt ORDER BY rt ASC, CAST(no_kk AS UNSIGNED) ASC, id ASC");
?>

<h2>DATA WARGA <?= ($filter_rt > 0) ? "RT 0$filter_rt RW 03" : "RW 03"; ?></h2>
<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>No KK</th>
            <th>Nama Lengkap</th>
            <th>RT</th>
            <th>Status Keluarga</th>
            <th>Pendidikan</th>
            <th>Profesi</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while ($d = mysqli_fetch_assoc($query)): 
            $status_k = $d['status_keluarga'] ?? $d['status_perkawinan'] ?? '';
            if ($status_k === 'Suami/Istri') { $status_k = 'Istri'; }
        ?>
        <tr>
            <td align="center"><?= $no++; ?></td>
            <!-- Tanda petik ' agar No KK tidak terpotong formatnya di Excel -->
            <td>'<?= htmlspecialchars($d['no_kk']); ?></td>
            <td><?= htmlspecialchars($d['nama']); ?></td>
            <td align="center">RT 0<?= $d['rt']; ?></td>
            <td><?= htmlspecialchars($status_k); ?></td>
            <td><?= htmlspecialchars($d['pendidikan']); ?></td>
            <td><?= htmlspecialchars($d['profesi']); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>