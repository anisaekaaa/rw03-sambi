<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Mencegah error variabel
$admin_nama     = isset($_SESSION['admin_nama']) ? $_SESSION['admin_nama'] : (isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin RW');
$admin_username = isset($_SESSION['admin_username']) ? strtolower($_SESSION['admin_username']) : '';

// Deteksi Otomatis Wilayah RT Admin
$admin_rt = 0;
if (isset($_SESSION['wilayah_rt']) && $_SESSION['wilayah_rt'] > 0) {
    $admin_rt = intval($_SESSION['wilayah_rt']);
} else {
    for ($i = 1; $i <= 6; $i++) {
        if (strpos($admin_username, "rt0$i") !== false || strpos($admin_username, "rt$i") !== false || strpos(strtolower($admin_nama), "rt 0$i") !== false || strpos(strtolower($admin_nama), "rt $i") !== false) {
            $admin_rt = $i;
            break;
        }
    }
}

// Filter RT
$where_admin = ($admin_rt > 0) ? "WHERE rt = $admin_rt" : "";

// Function Eksekusi Query Aman
function safe_query($koneksi, $sql) {
    $res = mysqli_query($koneksi, $sql);
    if (!$res) {
        return false;
    }
    return $res;
}

// 1. Hitung Ringkasan Data
$res_tw = safe_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_admin");
$total_warga = $res_tw ? mysqli_fetch_assoc($res_tw)['total'] : 0;

$where_kk = ($admin_rt > 0) ? "$where_admin AND status_keluarga = 'Kepala Keluarga'" : "WHERE status_keluarga = 'Kepala Keluarga'";
$res_kk = safe_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_kk");
$total_kk = $res_kk ? mysqli_fetch_assoc($res_kk)['total'] : 0;

$where_l = ($admin_rt > 0) ? "$where_admin AND jenis_kelamin = 'L'" : "WHERE jenis_kelamin = 'L'";
$res_l = safe_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_l");
$total_laki = $res_l ? mysqli_fetch_assoc($res_l)['total'] : 0;

$where_p = ($admin_rt > 0) ? "$where_admin AND jenis_kelamin = 'P'" : "WHERE jenis_kelamin = 'P'";
$res_p = safe_query($koneksi, "SELECT COUNT(*) as total FROM warga $where_p");
$total_p = $res_p ? mysqli_fetch_assoc($res_p)['total'] : 0;

// Query Pengelompokan Otomatis per Kepala Keluarga
$order_rule = "ORDER BY rt ASC, 
               CAST(no_kk AS UNSIGNED) ASC, 
               no_kk ASC,
               CASE status_keluarga 
                   WHEN 'Kepala Keluarga' THEN 1 
                   WHEN 'Istri' THEN 2 
                   WHEN 'Suami/Istri' THEN 2 
                   WHEN 'Anak' THEN 3 
                   ELSE 4 
               END ASC, id ASC";

$q_warga = safe_query($koneksi, "SELECT * FROM warga $where_admin $order_rule");
if (!$q_warga) {
    $q_warga = safe_query($koneksi, "SELECT * FROM warga $order_rule");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Admin - RW 03 Sambi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
            transition: all 0.3s ease;
        }
        .custom-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -8px rgba(15, 81, 50, 0.12) !important;
        }

        .table-custom {
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-custom thead th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            border: none;
        }
        .table-custom tbody tr {
            transition: all 0.2s ease;
        }
        .table-custom tbody tr:hover {
            background-color: #f1f5f9;
        }
        .table-custom td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        .row-kepala-keluarga {
            background-color: rgba(25, 135, 84, 0.03);
        }

        .btn-hover-effect {
            transition: all 0.25s ease;
        }
        .btn-hover-effect:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR ADMIN -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="admin_dashboard.php">
                <i class="fa-solid fa-user-shield text-warning"></i> 
                <span>PANEL ADMIN RW 03</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- NAVBAR MENU ITEMS -->
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2 pt-2 pt-lg-0">
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm rounded-pill px-3 fw-bold btn-hover-effect shadow-sm text-dark" href="buat_surat.php">
                            <i class="fa-solid me-1"></i> Buat Surat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm rounded-pill px-3 fw-semibold btn-hover-effect" href="index.php" target="_blank">
                            <i class="fa-solid me-1"></i> Lihat Web Utama
                        </a>
                    </li>
                    <li class="nav-item ms-lg-1">
                        <a class="btn btn-danger btn-sm rounded-pill px-3 fw-bold btn-hover-effect shadow-sm" href="logout.php">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="py-4 py-md-5">
        <div class="container px-3">

            <!-- WELCOME HEADER -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3" data-aos="fade-down">
                <div>
                    <h3 class="fw-bold text-success mb-1">
                        Selamat Datang, <?= htmlspecialchars($admin_nama); ?>! 👋
                    </h3>
                    <p class="text-muted small mb-0">
                        Akses Kelola Data Kependudukan Wilayah <span class="badge bg-success-subtle text-success border border-success fw-bold"><?= $admin_rt > 0 ? "RT 0$admin_rt Sambi" : "Seluruh Wilayah RW 03"; ?></span>
                    </p>
                </div>

                <!-- TOMBOL TAMBAH WARGA BARU -->
                <div>
                    <a href="warga_tambah.php" class="btn btn-success btn-sm rounded-pill px-3 py-1-5 fw-semibold shadow-sm d-inline-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Tambah Warga Baru</span>
                    </a>
                </div>
            </div>

            <!-- RINGKASAN KARTU ANGKA STATISTIK -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="100">
                    <div class="card custom-card shadow-sm p-3 text-center border-bottom border-4 border-success">
                        <span class="text-muted small fw-medium" style="font-size:0.75rem;">Total Warga</span>
                        <h3 class="fw-extrabold text-success my-1"><?= number_format($total_warga); ?></h3>
                        <span class="text-secondary small" style="font-size:0.7rem;">Jiwa Terdaftar</span>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="200">
                    <div class="card custom-card shadow-sm p-3 text-center border-bottom border-4 border-primary">
                        <span class="text-muted small fw-medium" style="font-size:0.75rem;">Kepala Keluarga</span>
                        <h3 class="fw-extrabold text-primary my-1"><?= number_format($total_kk); ?></h3>
                        <span class="text-secondary small" style="font-size:0.7rem;">Kepala KK</span>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="300">
                    <div class="card custom-card shadow-sm p-3 text-center border-bottom border-4 border-info">
                        <span class="text-muted small fw-medium" style="font-size:0.75rem;">Laki-Laki</span>
                        <h3 class="fw-extrabold text-info my-1"><?= number_format($total_laki); ?></h3>
                        <span class="text-secondary small" style="font-size:0.7rem;">Jiwa</span>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="400">
                    <div class="card custom-card shadow-sm p-3 text-center border-bottom border-4 border-danger">
                        <span class="text-muted small fw-medium" style="font-size:0.75rem;">Perempuan</span>
                        <h3 class="fw-extrabold text-danger my-1"><?= number_format($total_p); ?></h3>
                        <span class="text-secondary small" style="font-size:0.7rem;">Jiwa</span>
                    </div>
                </div>
            </div>

            <!-- TABEL DATA WARGA -->
            <div class="card custom-card shadow-sm p-4" data-aos="fade-up" data-aos-delay="200">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-users-gear text-success me-2"></i>Daftar Master Data Warga</h5>
                        <p class="text-muted small mb-0">Diurutkan berdasarkan hierarki data kependudukan</p>
                    </div>

                    <!-- SEARCHBAR REAL-TIME -->
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari nama, pekerjaan, atau profesi..." onkeyup="searchWarga()">
                        </div>
                    </div>
                </div>

                <!-- DROPDOWN EXPORT DATA -->
                <div class="dropdown mb-3">
                    <button class="btn btn-success btn-sm dropdown-toggle fw-semibold shadow-sm px-2 py-1 small" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-download me-1"></i> Export Data
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border p-1 rounded-2" style="min-width: 155px; font-size: 0.78rem;">
                        <li>
                            <a class="dropdown-item py-1 px-2 rounded text-success fw-medium d-flex align-items-center gap-1" href="export_excel.php?rt=<?= $admin_rt; ?>">
                                <i class="fa-solid fa-file-excel"></i>
                                <span>Export Excel</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-1 px-2 rounded text-danger fw-medium d-flex align-items-center gap-1" href="export_pdf.php?rt=<?= $admin_rt; ?>" target="_blank">
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>Cetak PDF</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0" id="wargaTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th class="text-center">Nama Lengkap</th>
                                <th class="text-center">Status Keluarga</th>
                                <th class="text-center">L/P</th>
                                <th class="text-center">Tgl Lahir</th>
                                <th class="text-center">Usia</th>
                                <th class="text-center">Pekerjaan</th>
                                <th class="text-center">RT</th>
                                <th class="text-center" style="width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($q_warga && mysqli_num_rows($q_warga) > 0):
                                $no = 1;
                                while ($w = mysqli_fetch_assoc($q_warga)):
                                    // Deteksi nama kolom tgl lahir (tgl_lahir / tanggal_lahir)
                                    $tgl_val   = !empty($w['tgl_lahir']) ? $w['tgl_lahir'] : (!empty($w['tanggal_lahir']) ? $w['tanggal_lahir'] : '');
                                    $tgl_lahir = !empty($tgl_val) ? htmlspecialchars($tgl_val) : '-';
                                    
                                    $status_k  = isset($w['status_keluarga']) ? $w['status_keluarga'] : '';
                                    $is_kk     = ($status_k == 'Kepala Keluarga');
                                    $status_p  = isset($w['status_perkawinan']) ? $w['status_perkawinan'] : '';
                                    $jk        = isset($w['jenis_kelamin']) ? $w['jenis_kelamin'] : 'L';
                                    $rt_val    = isset($w['rt']) ? $w['rt'] : 1;

                                    // Pengecekan status Meninggal gabungan dari SEMUA kolom
                                    $gabungan_status = strtolower($w['status_warga'] ?? '') . ' ' . strtolower($status_p) . ' ' . strtolower($status_k);
                                    $is_meninggal    = (strpos($gabungan_status, 'meninggal') !== false);

                                    // AMAN KAN KALKULASI USIA OTOMATIS
                                    if ($is_meninggal) {
                                        $usia_tampil = isset($w['usia']) ? $w['usia'] . ' th' : '-';
                                    } else {
                                        if (!empty($tgl_val) && $tgl_val != '0000-00-00') {
                                            try {
                                                $birthDate   = new DateTime($tgl_val);
                                                $today       = new DateTime('today');
                                                $usia_tampil = $today->diff($birthDate)->y . ' th';
                                            } catch (Exception $e) {
                                                $usia_tampil = isset($w['usia']) ? $w['usia'] . ' th' : '-';
                                            }
                                        } else {
                                            $usia_tampil = isset($w['usia']) ? $w['usia'] . ' th' : '-';
                                        }
                                    }
                                    
                                    // Deteksi pekerjaan / profesi
                                    $pek_val   = isset($w['pekerjaan']) && !empty($w['pekerjaan']) ? $w['pekerjaan'] : (isset($w['profesi']) ? $w['profesi'] : '');
                                    $pekerjaan = !empty($pek_val) ? htmlspecialchars($pek_val) : '-';
                            ?>
                            <tr class="<?= $is_kk ? 'row-kepala-keluarga' : ''; ?>">
                                <td class="text-center fw-semibold text-secondary"><?= $no++; ?></td>
                                
                                <!-- KOLOM NAMA LENGKAP -->
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($w['nama']); ?></div>
                                    <div class="small text-muted mt-1">
                                        <span><?= htmlspecialchars($status_p); ?></span>
                                    </div>
                                </td>

                                <!-- KOLOM STATUS KELUARGA -->
                                <td>
                                    <?php if ($is_meninggal): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger fw-semibold rounded-pill">
                                            <?= htmlspecialchars($status_k); ?>
                                        </span>
                                    <?php elseif ($is_kk): ?>
                                        <span class="badge bg-success-subtle text-success border border-success fw-semibold rounded-pill">
                                            Kepala Keluarga
                                        </span>
                                    <?php elseif ($status_k === 'Istri'): ?>
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle fw-semibold rounded-pill" style="background-color: #fce7f3 !important; color: #9d174d !important; border-color: #fbcfe8 !important;">
                                            Istri
                                        </span>
                                    <?php elseif ($status_k === 'Anak'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info fw-semibold rounded-pill">
                                            Anak
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary fw-normal rounded-pill">
                                            <?= htmlspecialchars($status_k); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- KOLOM JENIS KELAMIN -->
                                <td class="text-center">
                                    <?php if ($jk == 'L'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info fw-bold">L</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger fw-bold">P</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $tgl_lahir; ?></td>
                                <td class="text-center fw-medium text-nowrap"><?= $usia_tampil; ?></td>
                                <td><?= $pekerjaan; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success fw-bold">RT 0<?= $rt_val; ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <a href="warga_edit.php?id=<?= $w['id']; ?>" class="btn btn-warning btn-sm fw-bold px-2 py-1 text-nowrap rounded-2 btn-hover-effect">
                                            Edit
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm fw-bold px-2 py-1 text-nowrap rounded-2 btn-hover-effect" onclick="konfirmasiHapus(<?= $w['id']; ?>, '<?= htmlspecialchars(addslashes($w['nama'])); ?>')">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                    Belum ada data warga terdaftar untuk wilayah ini.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-dark text-white mt-auto py-3 border-top border-secondary">
        <div class="container text-center small text-secondary">
            &copy; <?= date('Y'); ?> Panel Administrasi Pengurus RW 03 Desa Sambi. All Rights Reserved.
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        // SCRIPT LIVE SEARCH
        function searchWarga() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('wargaTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let row = tr[i];
                let textContent = row.textContent || row.innerText;
                
                if (textContent.toLowerCase().indexOf(filter) > -1) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        }

        // KONFIRMASI HAPUS (SWEETALERT2)
        function konfirmasiHapus(id, nama) {
            Swal.fire({
                title: 'Hapus Data Warga?',
                text: "Apakah Anda yakin ingin menghapus data " + nama + "? Data tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                width: '360px',
                padding: '1.2rem',
                customClass: {
                    popup: 'rounded-4 border-0 shadow-lg',
                    title: 'fs-6 fw-bold text-dark pt-2',
                    htmlContainer: 'small text-muted py-1',
                    confirmButton: 'btn btn-danger btn-sm px-3 py-1.5 fw-bold rounded-2',
                    cancelButton: 'btn btn-secondary btn-sm px-3 py-1.5 fw-bold rounded-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'warga_hapus.php?id=' + id;
                }
            });
        }

        // DETEKSI NOTIFIKASI SUKSES
        const urlParams = new URLSearchParams(window.location.search);
        const pesan = urlParams.get('pesan');

        if (pesan === 'sukses_tambah') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Added!',
                text: 'Data warga baru berhasil ditambahkan.',
                width: '340px',
                timer: 2000,
                showConfirmButton: false,
                customClass: { popup: 'rounded-4 border-0 shadow-lg', title: 'fs-6 fw-bold' }
            });
        } else if (pesan === 'sukses_edit') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Perbarui!',
                text: 'Perubahan data warga berhasil disimpan.',
                width: '340px',
                timer: 2000,
                showConfirmButton: false,
                customClass: { popup: 'rounded-4 border-0 shadow-lg', title: 'fs-6 fw-bold' }
            });
        } else if (pesan === 'sukses_hapus') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Dihapus!',
                text: 'Data warga telah dihapus dari sistem.',
                width: '340px',
                timer: 2000,
                showConfirmButton: false,
                customClass: { popup: 'rounded-4 border-0 shadow-lg', title: 'fs-6 fw-bold' }
            });
        }
    </script>
</body>
</html>