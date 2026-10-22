<?php
include 'koneksi.php';

// Filter RT jika diinginkan
$filter_rt = isset($_GET['rt']) ? intval($_GET['rt']) : 0;
$where_rt  = ($filter_rt > 0) ? "WHERE rt = $filter_rt" : "";

// Label Teks Filter Aktif
$label_rt_aktif = ($filter_rt > 0) ? "RT 0" . $filter_rt : "-- Seluruh RW 03 --";

// 1. Total Ringkasan
$total_warga = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_rt"))['total'];
$total_kk    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga " . ($filter_rt > 0 ? "$where_rt AND" : "WHERE") . " status_keluarga = 'Kepala Keluarga'"))['total'];
$total_laki  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga " . ($filter_rt > 0 ? "$where_rt AND" : "WHERE") . " jenis_kelamin = 'L'"))['total'];
$total_perempuan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga " . ($filter_rt > 0 ? "$where_rt AND" : "WHERE") . " jenis_kelamin = 'P'"))['total'];

// 2. Data Statistik Tingkat Pendidikan Warga (KODE BARU)
$filter_rt    = isset($_GET['rt']) ? intval($_GET['rt']) : 0;
$where_rt_and = ($filter_rt > 0) ? "WHERE rt = $filter_rt AND" : "WHERE";

$q_pendidikan = mysqli_query($koneksi, "SELECT pendidikan, COUNT(*) as jumlah FROM warga $where_rt_and pendidikan IS NOT NULL AND pendidikan != '' GROUP BY pendidikan ORDER BY jumlah DESC LIMIT 6");

$label_pendidikan = [];
$data_pendidikan  = [];
while($p = mysqli_fetch_assoc($q_pendidikan)) {
    $label_pendidikan[] = $p['pendidikan'];
    $data_pendidikan[]  = intval($p['jumlah']);
}

// 3. Data Kelompok Usia
$q_balita   = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga " . ($filter_rt > 0 ? "$where_rt AND" : "WHERE") . " usia <= 5");
$q_anak     = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga " . ($filter_rt > 0 ? "$where_rt AND" : "WHERE") . " usia BETWEEN 6 AND 17");
$q_produktif= mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga " . ($filter_rt > 0 ? "$where_rt AND" : "WHERE") . " usia BETWEEN 18 AND 59");
$q_lansia   = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga " . ($filter_rt > 0 ? "$where_rt AND" : "WHERE") . " usia >= 60");

$cnt_balita    = mysqli_fetch_assoc($q_balita)['total'];
$cnt_anak      = mysqli_fetch_assoc($q_anak)['total'];
$cnt_produktif = mysqli_fetch_assoc($q_produktif)['total'];
$cnt_lansia    = mysqli_fetch_assoc($q_lansia)['total'];

// 4. Top 5 Profesi/Pekerjaan
$profesi_labels = [];
$profesi_totals = [];
$q_profesi = mysqli_query($koneksi, "SELECT profesi, COUNT(*) as total FROM warga $where_rt GROUP BY profesi ORDER BY total DESC LIMIT 5");
while ($r_prof = mysqli_fetch_assoc($q_profesi)) {
    $profesi_labels[] = !empty($r_prof['profesi']) ? $r_prof['profesi'] : 'Lainnya';
    $profesi_totals[] = $r_prof['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Statistik Demografi - RW 03 Sambi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            overflow-x: hidden;
        }

        .glass-nav {
            background: rgba(15, 81, 50, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .custom-card {
            border: none;
            border-radius: 16px;
            background: #ffffff;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }
        .custom-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 28px -8px rgba(15, 81, 50, 0.15) !important;
        }

        .chart-box {
            position: relative;
            width: 100%;
            height: 210px;
        }

        .btn-hover-effect {
            transition: all 0.3s ease;
        }
        .btn-hover-effect:hover {
            transform: translateY(-2px);
        }

        .print-header {
            display: none;
        }

        .header-actions {
            position: relative;
            z-index: 100;
        }

        /* CUSTOM DROPDOWN STYLE BIAR TIDAK DIACAK-ACAK OS HP */
        .custom-dropdown-menu {
            min-width: 180px;
            font-size: 0.85rem;
            border-radius: 12px;
            overflow: hidden;
            z-index: 1055 !important;
        }

        .custom-dropdown-menu .dropdown-item {
            padding: 8px 16px;
            color: #334155;
            transition: all 0.2s;
        }

        .custom-dropdown-menu .dropdown-item:hover,
        .custom-dropdown-menu .dropdown-item.active {
            background-color: #e8f5e9;
            color: #198754;
            font-weight: 600;
        }

        /* LAYOUT CETAK A4 PRESISI */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
                }

            body {
                background: white !important;   color: #000 !important; font-size: 8.5pt; -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important; 
            }

    .no-print, .glass-nav, footer {
        display: none !important;
    }

    .print-header {
        display: block !important;
        border-bottom: 2px solid #000;
        padding-bottom: 4px;
        margin-bottom: 12px;
        text-align: center;
    }

    .print-header h5 {
        font-size: 12pt;
        font-weight: 800;
        margin: 0;
        text-transform: uppercase;
    }

    .print-header p {
        font-size: 8pt;
        margin: 0;
    }

    .print-footer-sign {
        display: block !important;
    }

    #area-laporan {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .custom-card {
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        transform: none !important;
        background: #fff !important;
        padding: 8px !important;
    }

    /* BATASI CHART AGAR TIDAK TEMBUS DAN TIDAK NONGOL KE LUAR KARTU */
    .chart-box {
        position: relative !important;
        width: 100% !important;
        max-width: 100% !important;
        height: 170px !important;
        overflow: hidden !important;
        display: block !important;
    }

    canvas {
        max-width: 100% !important;
        width: 100% !important;
        height: 170px !important;
        display: block !important;
    }

    /* SISTEM GRID FLEXBOX MURNI (TANPA FLOAT KUDAN) */
    .row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 8px !important;
        margin-right: 0 !important;
        margin-left: 0 !important;
    }

        .col-md-3, .col-6 { width: 25% !important; max-width: 25% !important; flex: 0 0 25% !important; padding: 0 !important; }

        .col-md-5 { width: 35% !important; max-width: 35% !important; flex: 0 0 35% !important; padding: 0 !important; }

        .col-md-7 { width: 65% !important; max-width: 65% !important; flex: 0 0 65% !important; padding: 0 !important; }
    }

/* MEMAKSA TABEL MATRIKS RT DAN KARTU BISA MENYESUAIKAN LEBAR DENGAN KETAT */
.table-matriks-rt {
    table-layout: fixed !important;
    width: 100% !important;
}

.table-matriks-rt th, 
.table-matriks-rt td {
    padding: 3px 1px !important;
    font-size: 0.7rem !important;
    word-break: break-all;
}

/* Memperkecil padding kartu agar tidak memakan ruang horizontal */
#area-laporan .custom-card {
    padding: 10px !important;
}
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- NAVIGASI -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top shadow-sm no-print">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
                <i class="fa-solid fa-building-flag text-warning"></i> 
                <span>RW 03 SAMBI</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 pt-2 pt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold px-3" href="index.php"><i class="fa-solid me-1"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold px-3" href="data_warga.php"><i class="fa-solid me-1"></i> Data Warga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold px-3 rounded-pill bg-white bg-opacity-10" href="statistik.php"><i class="fa-solid me-1"></i> Statistik</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#kontak" data-bs-toggle="modal" data-bs-target="#modalKontak"><i class="fa-solid me-1 text-warning"></i> Kontak Pengurus</a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="login.php" class="btn btn-warning text-dark fw-bold px-3 btn-sm rounded-pill shadow-sm w-100 w-lg-auto btn-hover-effect">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Login Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENT STATISTIK -->
    <main class="py-4 py-md-5">
        <div class="container px-3">

            <!-- HEADER TITLE & DROPDOWN ACTION -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 no-print header-actions" data-aos="fade-down">
                <div>
                    <h3 class="fw-bold text-success mb-1"><i class="fa-solid fa-chart-line me-2"></i>Visualisasi & Demografi Warga</h3>
                    <p class="text-muted small mb-0">Analisis data populasi kependudukan di wilayah RW 03 Sambi</p>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- CUSTOM DROPDOWN FILTER RT (MENGGANTIKAN SELECT BROWSER HP) -->
                    <div class="dropdown position-relative" style="z-index: 1050;">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold dropdown-toggle shadow-sm bg-white text-dark btn-hover-effect" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-filter text-success me-1"></i> <?= $label_rt_aktif; ?>
                        </button>
                        <ul class="dropdown-menu shadow border-0 custom-dropdown-menu mt-1">
                            <li>
                                <a class="dropdown-item <?= $filter_rt == 0 ? 'active' : ''; ?>" href="statistik.php?rt=0">
                                    -- Seluruh RW 03 --
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <?php for($i=1; $i<=6; $i++): ?>
                            <li>
                                <a class="dropdown-item <?= $filter_rt == $i ? 'active' : ''; ?>" href="statistik.php?rt=<?= $i; ?>">
                                    <i class="fa-solid fa-location-dot me-2 text-success opacity-75"></i> RT 0<?= $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </div>

                    <!-- DROPDOWN OPSIONAL CETAK -->
                    <div class="dropdown position-relative" style="z-index: 1050;">
                        <button class="btn btn-success btn-sm rounded-pill px-3 fw-bold dropdown-toggle shadow-sm btn-hover-effect" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-print me-1"></i> Opsi Cetak / Laporan
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 custom-dropdown-menu mt-1">
                            <li>
                                <a class="dropdown-item py-2" href="#" onclick="window.print(); return false;">
                                    <i class="fa-solid fa-print text-success me-2"></i> Cetak Langsung (Printer)
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2" href="#" onclick="unduhPDF(); return false;">
                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i> Unduh File PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- AREA YANG DI-PRINT DAN DI-EXPORT KE PDF -->
            <div id="area-laporan">

                <!-- KOP SURAT (MUNCUL PAS PRINT/PDF) -->
<div class="print-header text-center mb-3" style="display: none; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px;">
    <h5 class="fw-bold mb-0 text-uppercase" style="font-size: 13pt; letter-spacing: 0.5px; color: #000;">PENGURUS RUKUN WARGA (RW) 03 DESA SAMBI</h5>
    <p class="mb-1 text-dark" style="font-size: 8.5pt;">Kecamatan Sambi, Kabupaten Boyolali, Jawa Tengah 57376</p>
    <p class="fw-bold mb-0 text-uppercase text-dark" style="font-size: 9.5pt; margin-top: 6px; letter-spacing: 0.3px;">
        LAPORAN DEMOGRAFI & STATISTIK KEPENDUDUKAN <?= ($filter_rt > 0) ? "(WILAYAH RT 0$filter_rt)" : "(SELURUH WILAYAH RW 03)"; ?>
    </p>
</div>

<!-- KARTU SUMMARY ANGKA (PERSIS GAMBAR) -->
<div class="row g-3 mb-4">
    <!-- Total Warga -->
    <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="100">
        <div class="card custom-card shadow-sm p-3 text-center border-0 border-bottom border-4 border-success rounded-4">
            <span class="text-muted small fw-medium" style="font-size: 0.8rem;">Total Warga</span>
            <h2 class="fw-extrabold text-success my-2"><?= number_format($total_warga); ?></h2>
            <span class="text-secondary small" style="font-size: 0.75rem;">Jiwa Terdaftar</span>
        </div>
    </div>
    
    <!-- Kepala Keluarga -->
    <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="200">
        <div class="card custom-card shadow-sm p-3 text-center border-0 border-bottom border-4 border-primary rounded-4">
            <span class="text-muted small fw-medium" style="font-size: 0.8rem;">Kepala Keluarga</span>
            <h2 class="fw-extrabold text-primary my-2"><?= number_format($total_kk); ?></h2>
            <span class="text-secondary small" style="font-size: 0.75rem;">Kepala KK</span>
        </div>
    </div>
    
    <!-- Laki-Laki -->
    <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="300">
        <div class="card custom-card shadow-sm p-3 text-center border-0 border-bottom border-4 border-info rounded-4">
            <span class="text-muted small fw-medium" style="font-size: 0.8rem;">Laki-Laki</span>
            <h2 class="fw-extrabold text-info my-2"><?= number_format($total_laki); ?></h2>
            <span class="text-secondary small" style="font-size: 0.75rem;">Jiwa</span>
        </div>
    </div>
    
    <!-- Perempuan -->
    <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="400">
        <div class="card custom-card shadow-sm p-3 text-center border-0 border-bottom border-4 border-danger rounded-4">
            <span class="text-muted small fw-medium" style="font-size: 0.8rem;">Perempuan</span>
            <h2 class="fw-extrabold text-danger my-2"><?= number_format($total_perempuan); ?></h2>
            <span class="text-secondary small" style="font-size: 0.75rem;">Jiwa</span>
        </div>
    </div>
</div>

                <!-- BARIS 1: GRAFIK POPULASI RT & KELOMPOK USIA -->
                <div class="row g-3 mb-3">
<div class="col-md-7" data-aos="fade-right" data-aos-delay="200">
    <div class="card custom-card shadow-sm p-3 h-100">
        <h6 class="fw-bold text-dark mb-2 small d-flex align-items-center gap-2"><i class="fa-solid fa-graduation-cap text-success no-print"></i><span>Tingkat Pendidikan Warga</span></h6>
        <div class="chart-box">
            <canvas id="chartPendidikan"></canvas>
        </div>
    </div>
</div>

                    <div class="col-md-5" data-aos="fade-left" data-aos-delay="300">
                        <div class="card custom-card shadow-sm p-3 h-100">
                            <h6 class="fw-bold text-dark mb-2 small"><i class="fa-solid fa-chart-pie text-primary me-2 no-print"></i>Komposisi Kelompok Usia</h6>
                            <div class="chart-box d-flex justify-content-center">
                                <canvas id="chartUsia"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARIS 2: GRAFIK PROFESI & TABEL MATRIKS RT -->
                <div class="row g-3 mb-2">
                    <div class="col-md-5" data-aos="fade-up" data-aos-delay="200">
                        <div class="card custom-card shadow-sm p-3 h-100">
                            <h6 class="fw-bold text-dark mb-2 small"><i class="fa-solid fa-briefcase text-warning me-2 no-print"></i>Top 5 Profesi / Pekerjaan Warga</h6>
                            <div class="chart-box">
                                <canvas id="chartProfesi"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7" data-aos="fade-up" data-aos-delay="300">
                        <div class="card custom-card shadow-sm p-3 h-100">
                            <h6 class="fw-bold text-dark mb-2 small"><i class="fa-solid fa-table text-info me-2 no-print"></i>Matriks Perbandingan Wilayah RT 01 - 06</h6>
<div class="table-responsive" style="overflow: hidden;">
    <table class="table table-hover align-middle mb-0 text-center table-matriks-rt">
        <thead class="table-success">
            <tr>
                <th style="width: 22%;">Wilayah</th>
                <th style="width: 18%;">Total</th>
                <th style="width: 16%;">KK</th>
                <th style="width: 22%;">Laki-Laki</th>
                <th style="width: 22%;">Perempuan</th>
            </tr>
        </thead>
                                    <tbody>
                                        <?php for($i=1; $i<=6; $i++): 
                                            $q_sub_total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga WHERE rt = $i"))['total'];
                                            $q_sub_kk    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga WHERE rt = $i AND status_keluarga = 'Kepala Keluarga'"))['total'];
                                            $q_sub_l     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga WHERE rt = $i AND jenis_kelamin = 'L'"))['total'];
                                            $q_sub_p     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga WHERE rt = $i AND jenis_kelamin = 'P'"))['total'];
                                        ?>
                                        <tr>
                                            <td class="fw-bold text-start"><span class="badge bg-success-subtle text-success border border-success">RT 0<?= $i; ?></span></td>
                                            <td class="fw-bold"><?= number_format($q_sub_total); ?></td>
                                            <td><?= number_format($q_sub_kk); ?></td>
                                            <td class="text-primary"><?= number_format($q_sub_l); ?></td>
                                            <td class="text-danger"><?= number_format($q_sub_p); ?></td>
                                        </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TANDA TANGAN FORMAL KIRI & KANAN (DISET SESUAI CETAK/PDF) -->
<?php
// Array Bulan Indonesia
$bulan_indo = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$tgl_sekarang = date('d') . ' ' . $bulan_indo[intval(date('n'))] . ' ' . date('Y');
?>

<div class="print-footer-sign d-none mt-4 pt-2">
    <table style="width: 100% !important; border-collapse: collapse !important; border: none !important;">
        <tr>
            <?php if ($filter_rt > 0): ?>
                <!-- JIKA PILIH RT KERTAS: KETUA RT DI KIRI, KETUA RW DI KANAN -->
                <td style="width: 45%; text-align: center; border: none !important; vertical-align: top;">
                    <p class="mb-1 small">Mengetahui,</p>
                    <p class="fw-bold small mb-0">Ketua RT 0<?= $filter_rt; ?> Sambi</p>
                    <br><br><br><br>
                    <p class="fw-bold text-decoration-underline mb-0 small">( .................................... )</p>
                </td>
                <td style="width: 10%; border: none !important;"></td>
                <td style="width: 45%; text-align: center; border: none !important; vertical-align: top;">
                    <p class="mb-1 small">Sambi, <?= $tgl_sekarang; ?></p>
                    <p class="fw-bold small mb-0">Ketua RW 03 Sambi</p>
                    <br><br><br><br>
                    <p class="fw-bold text-decoration-underline mb-0 small">( .................................... )</p>
                </td>
            <?php else: ?>
                <!-- JIKA PILIH SELURUH RW: KETUA RW SAJA DI KANAN -->
                <td style="width: 55%; border: none !important;"></td>
                <td style="width: 45%; text-align: center; border: none !important; vertical-align: top;">
                    <p class="mb-1 small">Sambi, <?= $tgl_sekarang; ?></p>
                    <p class="fw-bold small mb-0">Ketua RW 03 Sambi</p>
                    <br><br><br><br>
                    <p class="fw-bold text-decoration-underline mb-0 small">( .................................... )</p>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</div>

            </div>

        </div>
    </main>

<!-- MODAL POPUP KONTAK PENGURUS RW 03 SAMBI (COMPACT & RESPONSIVE) -->
<div class="modal fade" id="modalKontak" tabindex="-1" aria-labelledby="modalKontakLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            
            <!-- HEADER MODAL COMPACT -->
            <div class="modal-header bg-success text-white p-3 border-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="modalKontakLabel">Kontak & Pelayanan</h6>
                        <small class="text-white-50" style="font-size: 0.75rem;">Pengurus RW 03 Sambi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- BODY MODAL -->
            <div class="modal-body p-3 bg-light">
                <p class="text-muted mb-2" style="font-size: 0.75rem;">Silakan hubungi pengurus RW 03 Sambi untuk pelayanan kependudukan:</p>
                
                <div class="d-flex flex-column gap-2">
                    
                    <!-- 1. HOTLINE WHATSAPP -->
                    <a href="https://wa.me/6288239587567?text=Halo%20Pengurus%20RW%2003%20Sambi,%20saya%20ingin%20bertanya..." target="_blank" class="text-decoration-none">
                        <div class="p-2 bg-white rounded-3 shadow-sm border border-success border-opacity-20 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-success bg-opacity-10 text-success p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small" style="font-size: 0.8rem;">Hotline WA RW 03</div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Pelayanan Umum & Admin</small>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                        </div>
                    </a>

                    <!-- 2. SEKRETARIAT + GOOGLE MAPS -->
                    <a href="https://maps.google.com/?q=Desa+Sambi,+Boyolali" target="_blank" class="text-decoration-none">
                        <div class="p-2 bg-white rounded-3 shadow-sm border border-primary border-opacity-20 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                        <span>Sekretariat RW 03</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary text-uppercase" style="font-size:0.6rem; padding: 2px 4px;">Maps</span>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Desa Sambi, Boyolali</small>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-up-right-from-square text-muted" style="font-size: 0.75rem;"></i>
                        </div>
                    </a>

                    <!-- 3. JAM OPERASIONAL -->
                    <div class="p-2 bg-white rounded-3 shadow-sm border d-flex align-items-center gap-2">
                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small" style="font-size: 0.8rem;">Jam Pelayanan</div>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Senin - Sabtu: 08.00 - 17.00</small>
                        </div>
                    </div>

                </div>
            </div>

            <!-- FOOTER MODAL -->
            <div class="modal-footer bg-white border-0 justify-content-center p-2">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-4 fw-semibold text-secondary" style="font-size: 0.8rem;" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

 <!-- FOOTER -->
<footer id="kontak" class="bg-dark text-white mt-auto py-4 border-top border-secondary">
    <div class="container px-3">
        <div class="row g-3 py-2">
            <!-- Kolom 1: Profil RW -->
            <div class="col-md-5">
                <h6 class="fw-bold text-warning mb-2"><i class="fa-solid fa-building-flag me-2"></i>RW 03 SAMBI</h6>
                <p class="small text-secondary mb-0 col-lg-10">
                    Sistem Informasi Kependudukan Terpadu Wilayah RW 03, Desa Sambi, Kecamatan Sambi, Kabupaten Boyolali, Jawa Tengah.
                </p>
            </div>

            <!-- Kolom 2: Layanan Warga (Langsung Terhubung ke WA / Pop-up) -->
            <div class="col-6 col-md-3">
                <h6 class="fw-bold mb-2 text-light small">Layanan Warga</h6>
                <ul class="list-unstyled small d-flex flex-column gap-1 mb-0">
                    <li>
                        <a href="https://wa.me/6281228620442?text=Halo%20Bapak%20Ketua%20RW%2003,%20saya%20[nama]%20ingin%20mengajukan%20Surat%20Pengantar." target="_blank" class="text-secondary text-decoration-none">
                            <i class="fa-solid fa-file-lines me-1 text-warning"></i> Pengajuan Surat
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/6281228620442?text=Halo%20Bapak%20Ketua%20RW%2003,%20saya%20[nama]%20ingin%20menyampaikan%20pengaduan." target="_blank" class="text-secondary text-decoration-none">
                            <i class="fa-solid fa-bullhorn me-1 text-warning"></i> Lapor / Pengaduan
                        </a>
                    </li>
                    <li>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalPanduan" class="text-secondary text-decoration-none">
                            <i class="fa-solid fa-circle-info me-1 text-warning"></i> Panduan Web
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Kolom 3: Kontak Darurat (Bisa Langsung Ditekan dari HP) -->
            <div class="col-6 col-md-4">
                <h6 class="fw-bold mb-2 text-light small">Kontak Darurat RW</h6>
                <ul class="list-unstyled small d-flex flex-column gap-1 mb-0">
                    <li>
                        <a href="https://wa.me/6281228620442?text=Halo%20Bapak%20Ketua%20RW%2003,%20saya%20[nama]%20ingin..." target="_blank" class="text-secondary text-decoration-none">
                            <i class="fa-brands fa-whatsapp me-1 text-success"></i> Ketua RW 03
                        </a>
                    </li>
                    <li>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalKontakRT" class="text-secondary text-decoration-none">
                            <i class="fa-solid fa-users-gear me-1 text-warning"></i> Kontak Ketua RT (01 - 06)
                        </a>
                    </li>
                    <li>
                        <span class="text-secondary">
                            <a href="https://wa.me/62876543210" target="_blank" class="text-secondary text-decoration-none">
                            <i class="fa-solid fa-truck-medical me-1 text-info"></i> Layanan Ambulans Rumah Sakit Asy Syifa Sambi</a>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary my-3 opacity-25">
        <div class="text-center small text-secondary">
            &copy; <?= date('Y'); ?> Pengurus RW 03 Desa Sambi. All Rights Reserved.
        </div>
    </div>
</footer>

<!-- MODAL POP-UP PANDUAN (Nggak perlu file PHP baru) -->
<div class="modal fade" id="modalPanduan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-book me-2"></i>Panduan Sistem Informasi RW 03</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body small">
                <ol class="ps-3 mb-0">
                    <li class="mb-2"><strong>Data Warga:</strong> Gunakan menu navigasi atas untuk melihat daftar warga terdaftar.</li>
                    <li class="mb-2"><strong>Layanan Surat & Pengaduan:</strong> Klik menu di footer untuk langsung terhubung via WhatsApp Pengurus RW.</li>
                    <li><strong>Pengurus RW:</strong> Silakan klik menu <em>Login Pengurus</em> untuk masuk ke Dashboard Admin.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- MODAL POP-UP KONTAK KETUA RT (MURNI BOOTSTRAP 5) -->
<div class="modal fade" id="modalKontakRT" tabindex="-1" aria-labelledby="modalKontakRTLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered"> <!-- modal-sm bikin ukurannya kecil & pas -->
        <div class="modal-content rounded-4 border-0 shadow-lg">
            
            <!-- HEADER MODAL -->
            <div class="modal-header bg-success text-white rounded-top-4 py-3">
                <h6 class="modal-title fw-bold mx-auto" id="modalKontakRTLabel">
                    <i class="fa-brands fa-whatsapp me-2"></i>Pilih Ketua RT (RW 03)
                </h6>
                <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- ISI DAFTAR KONTAK RT -->
            <div class="modal-body p-3 bg-light rounded-bottom-4">
                <div class="d-flex flex-column gap-2">
                    
                    <!-- RT 01 -->
                    <a href="https://wa.me/6281234567801?text=Halo%20Bapak%20Ketua%20RT%2001%20saya%20[nama lengkap]%20ingin..." target="_blank" 
                       class="btn btn-white bg-white border rounded-3 p-2 text-start d-flex align-items-center justify-content-between shadow-sm hover-shadow">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-2 rounded-2">01</span>
                            <div>
                                <div class="fw-bold text-dark small">Ketua RT 01</div>
                                <div class="text-muted extra-small" style="font-size: 0.7rem;">Pak Nama RT 01</div>
                            </div>
                        </div>
                        <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                    </a>

                    <!-- RT 02 -->
                    <a href="https://wa.me/6281234567802?text=Halo%20Bapak%20Ketua%20RT%2002%20saya%20[nama lengkap]%20ingin..." target="_blank" 
                       class="btn btn-white bg-white border rounded-3 p-2 text-start d-flex align-items-center justify-content-between shadow-sm">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-2 rounded-2">02</span>
                            <div>
                                <div class="fw-bold text-dark small">Ketua RT 02</div>
                                <div class="text-muted extra-small" style="font-size: 0.7rem;">Pak Nama RT 02</div>
                            </div>
                        </div>
                        <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                    </a>

                    <!-- RT 03 -->
                    <a href="https://wa.me/6281234567803?text=Halo%20Bapak%20Ketua%20RT%2003%20saya%20[nama lengkap]%20ingin..." target="_blank" 
                       class="btn btn-white bg-white border rounded-3 p-2 text-start d-flex align-items-center justify-content-between shadow-sm">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-2 rounded-2">03</span>
                            <div>
                                <div class="fw-bold text-dark small">Ketua RT 03</div>
                                <div class="text-muted extra-small" style="font-size: 0.7rem;">Pak Nama RT 03</div>
                            </div>
                        </div>
                        <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                    </a>

                    <!-- RT 04 -->
                    <a href="https://wa.me/6281234567804?text=Halo%20Bapak%20Ketua%20RT%2004%20saya%20[nama lengkap]%20ingin..." target="_blank" 
                       class="btn btn-white bg-white border rounded-3 p-2 text-start d-flex align-items-center justify-content-between shadow-sm">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-2 rounded-2">04</span>
                            <div>
                                <div class="fw-bold text-dark small">Ketua RT 04</div>
                                <div class="text-muted extra-small" style="font-size: 0.7rem;">Pak Nama RT 04</div>
                            </div>
                        </div>
                        <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                    </a>

                    <!-- RT 05 -->
                    <a href="https://wa.me/6281234567805?text=Halo%20Bapak%20Ketua%20RT%2005%20saya%20[nama lengkap]%20ingin..." target="_blank" 
                       class="btn btn-white bg-white border rounded-3 p-2 text-start d-flex align-items-center justify-content-between shadow-sm">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-2 rounded-2">05</span>
                            <div>
                                <div class="fw-bold text-dark small">Ketua RT 05</div>
                                <div class="text-muted extra-small" style="font-size: 0.7rem;">Pak Nama RT 05</div>
                            </div>
                        </div>
                        <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                    </a>

                    <!-- RT 06 -->
                    <a href="https://wa.me/6281234567806?text=Halo%20Bapak%20Ketua%20RT%2006%20saya%20[nama lengkap]%20ingin..." target="_blank" 
                       class="btn btn-white bg-white border rounded-3 p-2 text-start d-flex align-items-center justify-content-between shadow-sm">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-2 rounded-2">06</span>
                            <div>
                                <div class="fw-bold text-dark small">Ketua RT 06</div>
                                <div class="text-muted extra-small" style="font-size: 0.7rem;">Pak Nama RT 06</div>
                            </div>
                        </div>
                        <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

// 1. Chart Tingkat Pendidikan (Aman PDF & Semua Batang Muat Pas)
new Chart(document.getElementById('chartPendidikan').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($label_pendidikan); ?>,
        datasets: [{
            label: 'Jumlah Warga',
            data: <?= json_encode($data_pendidikan); ?>,
            backgroundColor: '#10b981',
            borderRadius: 4,
            barPercentage: 0.65,       // Atur ketebalan batang agar tidak kegemukan
            categoryPercentage: 0.8    // Jarak antar kelompok batang
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: {
                right: 15 // Memberi ruang aman di sisi kanan agar batang terakhir tidak terpotong
            }
        },
        plugins: { 
            legend: { display: false } 
        },
        scales: {
            y: { 
                beginAtZero: true, 
                ticks: { precision: 0 } 
            },
            x: {
                ticks: {
                    font: { size: 7.5 },
                    maxRotation: 45, // Miringkan sedikit label jika teks terlalu panjang
                    minRotation: 0
                }
            }
        }
    }
});

// 2. Chart Doughnut Usia (Legend Terkunci Rapi Di Bawah Lingkaran)
new Chart(document.getElementById('chartUsia').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Balita (0-5 thn)', 'Anak (6-17 thn)', 'Produktif (18-59 thn)', 'Lansia (60+ thn)'],
        datasets: [{
            data: [<?= $cnt_balita; ?>, <?= $cnt_anak; ?>, <?= $cnt_produktif; ?>, <?= $cnt_lansia; ?>],
            backgroundColor: ['#38bdf8', '#818cf8', '#22c55e', '#f43f5e'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 1.15,
        plugins: { 
            legend: { 
                position: 'bottom', 
                labels: { 
                    boxWidth: 6,
                    boxHeight: 6,
                    padding: 4, 
                    font: { size: 6.5 }
                } 
            } 
        }
    }
});

// CHART HORIZONTAL TOP 5 PROFESI (DITAMBAH PADDING KIRI)
const ctxProfesi = document.getElementById('chartProfesi').getContext('2d');
new Chart(ctxProfesi, {
    type: 'bar',
    data: {
        labels: <?= json_encode($profesi_labels); ?>,
        datasets: [{
            data: <?= json_encode($profesi_totals); ?>,
            backgroundColor: '#f59e0b',
            borderRadius: 4,
            barPercentage: 0.7
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: { left: 15, right: 10 } // Memberi ruang ekstra di kiri agar label muat utuh
        },
        plugins: { legend: { display: false } },
        scales: { 
            x: { beginAtZero: true, ticks: { precision: 0 } },
            y: { ticks: { font: { size: 7.5 } } }
        }
    }
});

function unduhPDF() {
    const elemen = document.getElementById('area-laporan');
    const printHeader = elemen.querySelector('.print-header');
    const printFooter = elemen.querySelector('.print-footer-sign');
    
    // Simpan style tampilan web
    const originalWidth = elemen.style.width;
    const originalPadding = elemen.style.padding;
    const originalMargin = elemen.style.margin;

    // Tampilkan Kop & TTD
    if (printHeader) printHeader.style.display = 'block';
    if (printFooter) printFooter.classList.remove('d-none');

    // Kunci lebar A4 presisi
    elemen.style.width = '750px';
    elemen.style.padding = '10px';
    elemen.style.margin = '0 auto';

    for (let id in Chart.instances) {
        Chart.instances[id].resize();
    }

    const opsi = {
        margin:       [6, 6, 6, 6],
        filename:     'Laporan_Statistik_RW03_Sambi.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, scrollX: 0, scrollY: 0 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opsi).from(elemen).save().then(() => {
        if (printHeader) printHeader.style.display = '';
        if (printFooter) printFooter.classList.add('d-none');
        
        elemen.style.width = originalWidth;
        elemen.style.padding = originalPadding;
        elemen.style.margin = originalMargin;
        
        for (let id in Chart.instances) {
            Chart.instances[id].resize();
        }
    });
}
    </script>
</body>
</html>