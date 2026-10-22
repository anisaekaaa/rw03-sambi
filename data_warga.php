<?php
include 'koneksi.php';

// Ambil & Amankan Parameter Filter (Gunakan 'rt' agar seragam dengan query string)
$keyword         = isset($_GET['keyword']) ? trim(mysqli_real_escape_string($koneksi, $_GET['keyword'])) : '';
$filter_rt       = isset($_GET['rt']) ? intval($_GET['rt']) : 0;
$filter_kategori = isset($_GET['kategori']) ? trim(mysqli_real_escape_string($koneksi, $_GET['kategori'])) : 'all';

$limit  = 20;
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Susun Query Filter Berdasarkan RT, Kata Kunci, & Kategori Ringkasan
$conditions = [];
if (!empty($keyword)) {
    $conditions[] = "(nama LIKE '%$keyword%' OR profesi LIKE '%$keyword%' OR pekerjaan LIKE '%$keyword%')";
}
if ($filter_rt > 0) {
    $conditions[] = "rt = $filter_rt";
}

// Filter Kategori dari Kartu Ringkasan
if ($filter_kategori === 'kk') {
    $conditions[] = "status_keluarga = 'Kepala Keluarga'";
} elseif ($filter_kategori === 'L') {
    $conditions[] = "jenis_kelamin = 'L'";
} elseif ($filter_kategori === 'P') {
    $conditions[] = "jenis_kelamin = 'P'";
}

$where_sql = count($conditions) > 0 ? "WHERE " . implode(' AND ', $conditions) : "";

// Hitung Total Data Terfilter
$q_count     = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_sql");
$total_rows  = mysqli_fetch_assoc($q_count)['total'];
$total_pages = ceil($total_rows / $limit);

// URUTAN SESUAI EXCEL: Kelompokkan per RT, urutkan No. KK dari terkecil, lalu urutkan baris sesuai urutan input Excel (id ASC)
$query_warga = "SELECT * FROM warga $where_sql 
                ORDER BY rt ASC, 
                         CAST(no_kk AS UNSIGNED) ASC, 
                         id ASC 
                LIMIT $limit OFFSET $offset";
$res_warga   = mysqli_query($koneksi, $query_warga);

// RINGKASAN STATISTIK DITAMPILKAN SESUAI FILTER RT YANG DIPILIH
$where_stat = ($filter_rt > 0) ? "WHERE rt = $filter_rt" : "";

$q_rt_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_stat");
$rt_total_warga = mysqli_fetch_assoc($q_rt_total)['total'];

$q_rt_kk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_stat " . ($filter_rt > 0 ? "AND" : "WHERE") . " status_keluarga = 'Kepala Keluarga'");
$rt_total_kk = mysqli_fetch_assoc($q_rt_kk)['total'];

$q_rt_laki = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_stat " . ($filter_rt > 0 ? "AND" : "WHERE") . " jenis_kelamin = 'L'");
$rt_total_laki = mysqli_fetch_assoc($q_rt_laki)['total'];

$q_rt_perempuan = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_stat " . ($filter_rt > 0 ? "AND" : "WHERE") . " jenis_kelamin = 'P'");
$rt_total_perempuan = mysqli_fetch_assoc($q_rt_perempuan)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Data Warga - RW 03 Sambi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
            <!-- Icon Web -->
    <link rel="icon" type="image/png" href="image/datawarga_icon.png">
    
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

        .btn-hover-effect {
            transition: all 0.3s ease;
        }
        .btn-hover-effect:hover {
            transform: translateY(-2px);
        }

        .custom-card {
            border: none;
            border-radius: 16px;
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table-custom {
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-custom thead th {
            background: #e2e8f0;
            color: #1e293b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border: none;
        }
        .table-custom tbody tr {
            transition: all 0.25s ease;
        }
        .table-custom tbody tr:hover {
            background-color: #e2e8f0 !important;
            transform: scale(1.002);
        }

        /* KARTU RINGKASAN INTERAKTIF (KLIKABLE) */
        .stat-badge-box {
            border-radius: 12px;
            padding: 14px 16px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            transition: all 0.25s ease-in-out;
            cursor: pointer;
            text-decoration: none !important;
            display: block;
            color: inherit;
        }
        .stat-badge-box:hover {
            transform: translateY(-4px);
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.06);
            border-color: #cbd5e1;
        }
        .stat-badge-box.active {
            background: #ffffff;
            border-color: #198754 !important;
            box-shadow: 0 8px 16px rgba(25, 135, 84, 0.15);
            position: relative;
        }
        .stat-badge-box.active::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 10px;
            right: 12px;
            color: #198754;
            font-size: 12px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- NAVIGASI -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top shadow-sm">
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
                        <a class="nav-link active fw-bold px-3 rounded-pill bg-white bg-opacity-10" href="data_warga.php"><i class="fa-solid me-1"></i> Data Warga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold px-3" href="statistik.php"><i class="fa-solid me-1"></i> Statistik</a>
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

    <!-- CONTENT DATA WARGA -->
    <main class="py-4 py-md-5">
        <div class="container px-3">
            
            <!-- RINGKASAN STATISTIK DENGAN TOMBOL FILTER KATEGORI -->
            <div class="card custom-card shadow-sm p-4 mb-4 border-start border-4 border-success" data-aos="fade-down">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <h5 class="fw-bold text-success mb-0">
                        <i class="fa-solid fa-chart-column me-2"></i>Ringkasan Data <?= $filter_rt > 0 ? "RT 0$filter_rt" : "Keseluruhan RW 03"; ?>
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small fw-bold">
                            <?= $filter_rt > 0 ? "Wilayah Khusus RT 0$filter_rt" : "Semua Wilayah (RT 01 - 06)"; ?>
                        </span>
                        <?php if($filter_kategori !== 'all'): ?>
                            <a href="data_warga.php?rt=<?= $filter_rt; ?>&keyword=<?= urlencode($keyword); ?>#tabel-warga" class="badge bg-danger text-white text-decoration-none px-2 py-2 rounded-pill small">
                                <i class="fa-solid fa-xmark me-1"></i> Reset Filter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="data_warga.php?rt=<?= $filter_rt; ?>&keyword=<?= urlencode($keyword); ?>&kategori=all#tabel-warga" class="stat-badge-box <?= $filter_kategori === 'all' ? 'active' : ''; ?>">
                            <span class="text-muted small d-block mb-1">Total Warga</span>
                            <span class="fs-4 fw-extrabold text-dark"><?= number_format($rt_total_warga); ?></span> <span class="small text-muted">Jiwa</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="data_warga.php?rt=<?= $filter_rt; ?>&keyword=<?= urlencode($keyword); ?>&kategori=kk#tabel-warga" class="stat-badge-box <?= $filter_kategori === 'kk' ? 'active' : ''; ?>">
                            <span class="text-muted small d-block mb-1">Jumlah Kepala Keluarga</span>
                            <span class="fs-4 fw-extrabold text-primary"><?= number_format($rt_total_kk); ?></span> <span class="small text-muted">KK</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="data_warga.php?rt=<?= $filter_rt; ?>&keyword=<?= urlencode($keyword); ?>&kategori=L#tabel-warga" class="stat-badge-box <?= $filter_kategori === 'L' ? 'active' : ''; ?>">
                            <span class="text-muted small d-block mb-1">Laki-Laki</span>
                            <span class="fs-4 fw-extrabold text-info"><?= number_format($rt_total_laki); ?></span> <span class="small text-muted">Jiwa</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="data_warga.php?rt=<?= $filter_rt; ?>&keyword=<?= urlencode($keyword); ?>&kategori=P#tabel-warga" class="stat-badge-box <?= $filter_kategori === 'P' ? 'active' : ''; ?>">
                            <span class="text-muted small d-block mb-1">Perempuan</span>
                            <span class="fs-4 fw-extrabold text-danger"><?= number_format($rt_total_perempuan); ?></span> <span class="small text-muted">Jiwa</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- SEARCH BAR & FILTER UTAMA (MULTI-FILTER SUPPORT) -->
            <div id="tabel-warga" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
                <div>
                    <h4 class="fw-bold text-success mb-1">
                        <i class="fa-solid fa-address-book me-2"></i>Direktori Data Warga
                    </h4>
                    <p class="text-muted small mb-0">
                        Filter Aktif: 
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                            <?php 
                            if ($filter_kategori === 'kk') echo "Kepala Keluarga";
                            elseif ($filter_kategori === 'L') echo "Hanya Laki-Laki";
                            elseif ($filter_kategori === 'P') echo "Hanya Perempuan";
                            else echo "Semua Anggota Keluarga";
                            ?>
                        </span>
                        <span id="badge_rt_aktif" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 <?= ($filter_rt > 0) ? '' : 'd-none'; ?>">
                            RT 0<span id="text_rt_aktif"><?= $filter_rt; ?></span>
                        </span>
                    </p>
                </div>

                <form method="GET" action="data_warga.php#tabel-warga" id="formFilterWarga" class="row g-2 align-items-center">
                    <!-- Hidden input untuk menjaga kategori & RT terpilih -->
                    <input type="hidden" name="kategori" id="input_kategori" value="<?= htmlspecialchars($filter_kategori); ?>">
                    <input type="hidden" name="rt" id="input_filter_rt" value="<?= intval($filter_rt); ?>">
                    
                    <!-- INPUT PENCARIAN -->
                    <div class="col-12 col-sm-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="keyword" id="searchInput" class="form-control form-control-sm border-start-0 rounded-end-pill shadow-none" placeholder="Cari Nama / Profesi..." value="<?= htmlspecialchars($keyword); ?>" onkeyup="searchWargaLive()">
                        </div>
                    </div>

                    <!-- DROPDOWN KAPSUL FILTER RT -->
                    <div class="col-12 col-sm-auto">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary bg-white text-dark dropdown-toggle rounded-pill px-3 py-1-5 fw-semibold border shadow-sm d-flex align-items-center gap-2" type="button" id="dropdownMenuRT" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                                <i class="fa-solid fa-filter text-success"></i>
                                <span id="labelFilterRT">
                                    <?= ($filter_rt > 0) ? "RT 0" . intval($filter_rt) : "-- Seluruh RW 03 --"; ?>
                                </span>
                            </button>
                            
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" aria-labelledby="dropdownMenuRT" style="min-width: 220px; font-size: 0.875rem;">
                                <li>
                                    <a class="dropdown-item py-2 px-3 rounded-3 <?= ($filter_rt == 0) ? 'text-success fw-bold bg-success bg-opacity-10' : 'text-secondary'; ?>" href="javascript:void(0)" onclick="pilihRT('', '-- Seluruh RW 03 --', this)">
                                        -- Seluruh RW 03 --
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1 opacity-10"></li>
                                <?php for($i = 1; $i <= 6; $i++): ?>
                                    <li>
                                        <a class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center gap-2 <?= ($filter_rt == $i) ? 'text-success fw-bold bg-success bg-opacity-10' : 'text-secondary'; ?>" href="javascript:void(0)" onclick="pilihRT('<?= $i; ?>', 'RT 0<?= $i; ?>', this)">
                                            <i class="fa-solid fa-location-dot text-success"></i> <span>RT 0<?= $i; ?></span>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- TOMBOL CARI & RESET -->
                    <div class="col-12 col-sm-auto d-flex gap-1">
                        <button type="submit" class="btn btn-success btn-sm fw-bold rounded-pill px-3 btn-hover-effect">Cari</button>
                        <?php if(!empty($keyword) || $filter_rt > 0 || $filter_kategori !== 'all'): ?>
                            <a href="data_warga.php" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 31px; height: 31px;" title="Reset Semua Filter">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- TABEL DATA WARGA -->
            <div class="card custom-card shadow-sm overflow-hidden mb-3" data-aos="fade-up">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0" id="wargaTable">
                        <thead>
                            <tr>
                                <th class="py-3 text-center" style="width: 50px;">No</th>
                                <th class="py-3 text-center">Nama Lengkap</th>
                                <th class="py-3 text-center">Status Keluarga</th>
                                <th class="py-3 text-center">L/P</th>
                                <th class="py-3 text-center">Pendidikan</th>
                                <th class="py-3 text-center">Profesi</th>
                                <th class="py-3 text-center">Wilayah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            if (mysqli_num_rows($res_warga) > 0):
                                while($row = mysqli_fetch_assoc($res_warga)): 
                                    $st_keluarga = $row['status_keluarga'] ?? '';
                                    $st_kawin    = $row['status_perkawinan'] ?? '';
                                    $st_warga    = $row['status_warga'] ?? $row['status'] ?? '';

                                    // Gabungkan untuk deteksi 'Meninggal'
                                    $gabungan     = strtolower($st_keluarga . ' ' . $st_kawin . ' ' . $st_warga);
                                    $is_meninggal = (strpos($gabungan, 'meninggal') !== false);
                                    $is_kk        = (stripos($st_keluarga, 'kepala keluarga') !== false);
                            ?>
                            <tr class="<?= $is_kk ? 'table-light fw-semibold' : ''; ?>">
                                <td class="text-center text-muted"><?= $no++; ?></td>
                                <td class="text-dark">
                                    <?= htmlspecialchars($row['nama']); ?>
                                    <?php if($is_kk && !$is_meninggal): ?>
                                        <span class="badge bg-success-subtle text-success ms-1" style="font-size: 10px;">KK</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- BADGE STATUS KELUARGA BERWARNA -->
                                <td class="text-center">
                                    <?php if ($is_meninggal): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger fw-semibold rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars($st_keluarga); ?>
                                        </span>
                                    <?php elseif ($is_kk): ?>
                                        <span class="badge bg-success-subtle text-success border border-success fw-semibold rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                            Kepala Keluarga
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars($st_keluarga); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Tampilkan Jenis Kelamin dengan badge warna biru untuk Laki-laki dan merah untuk Perempuan -->
                                <td class="text-center">
                                    <span class="badge <?= $row['jenis_kelamin'] == 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger'; ?> rounded-pill px-2">
                                        <?= $row['jenis_kelamin']; ?>
                                    </span>
                                </td>

                                <!-- Tampilkan Pendidikan & Profesi dengan htmlspecialchars untuk keamanan -->
<td class="small text-secondary text-center"><?= htmlspecialchars($row['pendidikan']); ?></td>
<td class="small text-secondary"><?= htmlspecialchars($row['profesi']); ?></td>
<td class="text-center">
    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">
        RT 0<?= $row['rt']; ?>
    </span>
</td>
                            </tr>
                            
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open display-6 d-block mb-2 text-secondary"></i>
                                    Data warga tidak ditemukan.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINASI 20 DATA -->
            <?php if ($total_pages > 1): ?>
                <nav class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mb-4" data-aos="fade-up">
                    <span class="small text-muted">Halaman <b><?= $page; ?></b> dari <b><?= $total_pages; ?></b> (Total <?= $total_rows; ?> data)</span>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link rounded-start-pill" href="data_warga.php?keyword=<?= urlencode($keyword); ?>&rt=<?= $filter_rt; ?>&kategori=<?= $filter_kategori; ?>&page=<?= $page - 1; ?>#tabel-warga">Prev</a>
                        </li>

                        <?php 
                        $start_p = max(1, $page - 1);
                        $end_p   = min($total_pages, $page + 1);
                        for($p = $start_p; $p <= $end_p; $p++): 
                        ?>
                            <li class="page-item <?= ($p == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="data_warga.php?keyword=<?= urlencode($keyword); ?>&rt=<?= $filter_rt; ?>&kategori=<?= $filter_kategori; ?>&page=<?= $p; ?>#tabel-warga"><?= $p; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link rounded-end-pill" href="data_warga.php?keyword=<?= urlencode($keyword); ?>&rt=<?= $filter_rt; ?>&kategori=<?= $filter_kategori; ?>&page=<?= $page + 1; ?>#tabel-warga">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

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
                        <a href="https://wa.me/6281228620442?text=Halo%20Bapak%20Ketua%20RW%2003,%20saya%20[nama]%20dari%20RT []%20ingin%20mengajukan%20Surat%20Pengantar." target="_blank" class="text-secondary text-decoration-none">
                            <i class="fa-solid fa-file-lines me-1 text-warning"></i> Pengajuan Surat
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/6281228620442?text=Halo%20Bapak%20Ketua%20RW%2003,%20saya%20[nama]%20dari%20RT []%20ingin%20menyampaikan%20pengaduan." target="_blank" class="text-secondary text-decoration-none">
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
                        <a href="https://wa.me/6281228620442?text=Halo%20Bapak%20Ketua%20RW%2003,%20saya%20[nama]%20dari%20RT []%20ingin..." target="_blank" class="text-secondary text-decoration-none">
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
                    <a href="https://wa.me/6281234567801?text=Halo%20Bapak%20Ketua%20RT%2001%20saya%20[nama lengkap]%20dari%20ingin..." target="_blank" 
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
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
    <script>
    function pilihRT(rtVal, labelTeks, el) {
        // 1. Update Teks Tombol & Hidden Input
        document.getElementById('labelFilterRT').innerText = labelTeks;
        document.getElementById('input_filter_rt').value = rtVal;
        
        // 2. Update Badge Info Filter Aktif di Kiri
        const badgeRT = document.getElementById('badge_rt_aktif');
        const textRT = document.getElementById('text_rt_aktif');
        if (badgeRT && textRT) {
            if (rtVal !== "" && rtVal !== "0") {
                textRT.innerText = rtVal;
                badgeRT.classList.remove('d-none');
            } else {
                badgeRT.classList.add('d-none');
            }
        }

        // 3. Highlight Pilihan Menu
        const items = document.querySelectorAll('.dropdown-menu .dropdown-item');
        items.forEach(item => {
            item.classList.remove('text-success', 'fw-bold', 'bg-success', 'bg-opacity-10');
            item.classList.add('text-secondary');
        });
        
        if (el) {
            el.classList.remove('text-secondary');
            el.classList.add('text-success', 'fw-bold', 'bg-success', 'bg-opacity-10');
        }

        // 4. Jalankan Filter Live pada Tabel
        searchWargaLive();
    }

    function searchWargaLive() {
        const inputKeyword = document.getElementById('searchInput').value.toLowerCase();
        const rtVal        = document.getElementById('input_filter_rt').value;
        const kategoriVal  = document.getElementById('input_kategori') ? document.getElementById('input_kategori').value : 'all';
        
        const table = document.getElementById('wargaTable');
        if (!table) return;

        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let row = tr[i];
            let textContent = row.textContent || row.innerText;
            
            // 1. Cek Keyword Pencarian
            let matchText = textContent.toLowerCase().indexOf(inputKeyword) > -1;
            
            // 2. Cek Filter RT (Deteksi 'RT 01' maupun 'RT 1')
            let matchRT = true;
            if (rtVal !== "" && rtVal !== "0") {
                let targetFormat1 = "RT 0" + rtVal;
                let targetFormat2 = "RT " + rtVal;
                matchRT = (textContent.indexOf(targetFormat1) > -1) || (textContent.indexOf(targetFormat2) > -1);
            }

            // 3. Cek Kategori (Kepala Keluarga / Laki-Laki / Perempuan)
            let matchKategori = true;
            if (kategoriVal === 'kk') {
                matchKategori = textContent.indexOf("Kepala Keluarga") > -1;
            } else if (kategoriVal === 'L') {
                matchKategori = textContent.indexOf("Laki-Laki") > -1 || textContent.indexOf(" L ") > -1 || row.innerHTML.indexOf(">L<") > -1;
            } else if (kategoriVal === 'P') {
                matchKategori = textContent.indexOf("Perempuan") > -1 || textContent.indexOf(" P ") > -1 || row.innerHTML.indexOf(">P<") > -1;
            }

            // Tampilkan baris HANYA jika Keyword, RT, dan Kategori semuanya cocok
            if (matchText && matchRT && matchKategori) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    }

    // Jalankan pencarian live saat halaman selesai di-load
    document.addEventListener("DOMContentLoaded", function() {
        searchWargaLive();
    });
    </script>
</body>
</html>