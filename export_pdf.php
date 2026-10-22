<?php
session_start();
include 'koneksi.php';

// Tangkap parameter RT dari URL (misal: export_pdf.php?rt=3)
$rt_param = isset($_GET['rt']) ? intval($_GET['rt']) : 0;
$where_rt = ($rt_param > 0) ? "WHERE rt = '$rt_param'" : "";

$query = mysqli_query($koneksi, "SELECT * FROM warga $where_rt ORDER BY rt ASC, CAST(no_kk AS UNSIGNED) ASC, id ASC");
$nama_file_pdf = ($rt_param > 0) ? "Data_Warga_RT0{$rt_param}_RW03.pdf" : "Data_Warga_RW03.pdf";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Data Warga <?= ($rt_param > 0) ? "RT 0$rt_param" : "RW 03"; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Library html2pdf.js untuk Auto-Download PDF tanpa Dialog Print -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-size: 0.85rem; background-color: #f8fafc; }
        .cetak-container { background: #ffffff; padding: 30px; border-radius: 8px; }
        .table { font-size: 0.8rem; }
    </style>
</head>
<body class="p-4">

    <!-- TOMBOL NAVIGASI & DOWNLOAD -->
    <div class="d-flex justify-content-between align-items-center mb-4 container">
        <a href="admin_dashboard.php" class="btn btn-secondary btn-sm fw-semibold">← Kembali ke Dashboard</a>
        
        <!-- TOMBOL INI LANGSUNG MENGUNDUH FILE PDF -->
        <button onclick="downloadPDF()" class="btn btn-danger btn-sm fw-semibold shadow-sm">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
        </button>
    </div>

    <!-- AREA DOKUMEN YANG AKAN DIJADIKAN PDF -->
    <div class="container">
        <div id="areaCetak" class="cetak-container border shadow-sm">
            
            <!-- HEADER LAPORAN -->
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1 text-dark">LAPORAN DATA WARGA <?= ($rt_param > 0) ? "RT 0$rt_param RW 03" : "RW 03"; ?></h4>
                <p class="text-muted small mb-0">Sistem Informasi Kependudukan Desa Sambi — Tanggal Unduh: <?= date('d-m-Y H:i'); ?></p>
            </div>

            <!-- TABEL DATA -->
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>No KK</th>
                        <th>Nama Lengkap</th>
                        <th style="width: 60px;">RT</th>
                        <th>Status</th>
                        <th>Pendidikan</th>
                        <th>Profesi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($d = mysqli_fetch_assoc($query)): 
                        $status_k = $d['status_keluarga'] ?? $d['status_perkawinan'] ?? '-';
                        if ($status_k === 'Suami/Istri') { $status_k = 'Istri'; }
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td>'<?= htmlspecialchars($d['no_kk']); ?></td>
                        <td class="fw-medium"><?= htmlspecialchars($d['nama']); ?></td>
                        <td class="text-center">RT 0<?= $d['rt']; ?></td>
                        <td><?= htmlspecialchars($status_k); ?></td>
                        <td><?= htmlspecialchars($d['pendidikan']); ?></td>
                        <td><?= htmlspecialchars($d['profesi']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>

    <!-- SCRIPT AUTO DOWNLOAD PDF -->
    <script>
        function downloadPDF() {
            const elemen = document.getElementById('areaCetak');
            const opsi = {
                margin:       10,
                filename:     '<?= $nama_file_pdf; ?>',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Eksekusi Download PDF Otomatis
            html2pdf().set(opsi).from(elemen).save();
        }
    </script>

</body>
</html>