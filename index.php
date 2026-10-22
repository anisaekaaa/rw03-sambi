<?php
include 'koneksi.php';

// Ringkasan Statistik Keseluruhan RW 03
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga");
$total_warga = mysqli_fetch_assoc($q_total)['total'];

$q_kk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga WHERE status_keluarga = 'Kepala Keluarga'");
$total_kk = mysqli_fetch_assoc($q_kk)['total'];

$q_laki = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga WHERE jenis_kelamin = 'L'");
$total_laki = mysqli_fetch_assoc($q_laki)['total'];

$q_perempuan = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM warga WHERE jenis_kelamin = 'P'");
$total_perempuan = mysqli_fetch_assoc($q_perempuan)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Beranda - Portal Resmi RW 03 Sambi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
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

        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            transform: translateY(-2px);
        }

        .hero-section {
            background: radial-gradient(circle at top right, #198754, #0f5132);
            color: white;
            padding: 80px 0 70px 0;
            position: relative;
            overflow: hidden;
        }

        .btn-hover-effect {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .btn-hover-effect:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
        }

        .custom-card {
            border: none;
            border-radius: 16px;
            background: #ffffff;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .custom-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 30px -10px rgba(15, 81, 50, 0.18) !important;
        }

        .icon-box {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: transform 0.4s ease;
        }
        .custom-card:hover .icon-box {
            transform: scale(1.15) rotate(8deg);
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulse 1.6s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        @media (max-width: 768px) {
            .hero-section { padding: 50px 15px 45px 15px; }
            .hero-section h1 { font-size: 1.8rem; }
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
                        <a class="nav-link active fw-bold px-3 rounded-pill bg-white bg-opacity-10" href="index.php"><i class="fa-solid me-1"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold px-3" href="data_warga.php"><i class="fa-solid me-1"></i> Data Warga</a>
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

    <!-- HERO BANNER BERANIMASI -->
    <section class="hero-section text-center">
        <div class="container px-3" data-aos="fade-up" data-aos-duration="1000">
            <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 text-white fw-bold px-3 py-2 rounded-pill mb-3 border border-white border-opacity-25 shadow-sm" style="font-size: 0.85rem;">
                <span class="pulse-dot"></span> Sistem Informasi Kependudukan
            </div>
            <h1 class="fw-extrabold mb-2 display-6">Selamat Datang di Portal RW 03</h1>
            <p class="lead col-lg-7 mx-auto opacity-90 mb-4" style="font-size: 1rem;">
                Desa Sambi, Kecamatan Sambi, Kabupaten Boyolali, Jawa Tengah.
            </p>
            
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="data_warga.php" class="btn btn-warning fw-bold px-4 py-2 text-dark rounded-pill shadow btn-hover-effect">
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Eksplorasi Data Warga
                </a>
                <a href="statistik.php" class="btn btn-outline-light fw-semibold px-4 py-2 rounded-pill btn-hover-effect">
                    Lihat Ringkasan
                </a>
            </div>
        </div>
    </section>

    <!-- SECTION STATISTIK DENGAN EFEK GULIR -->
    <section id="statistik" class="py-5">
        <div class="container px-3">
            <div class="text-center mb-4" data-aos="fade-up">
                <h4 class="fw-bold text-success mb-1">Statistik Kependudukan RW 03</h4>
                <p class="text-muted small">Gambaran umum demografi dan populasi warga terdaftar</p>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="card custom-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-success bg-opacity-10 text-success flex-shrink-0">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 fw-medium" style="font-size: 0.75rem;">Total Warga</h6>
                                <h3 class="fw-extrabold mb-0 text-dark"><?= number_format($total_warga); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="card custom-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary flex-shrink-0">
                                <i class="fa-solid fa-house-chimney"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 fw-medium" style="font-size: 0.75rem;">Kepala KK</h6>
                                <h3 class="fw-extrabold mb-0 text-dark"><?= number_format($total_kk); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="card custom-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-info bg-opacity-10 text-info flex-shrink-0">
                                <i class="fa-solid fa-mars"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 fw-medium" style="font-size: 0.75rem;">Laki-laki</h6>
                                <h3 class="fw-extrabold mb-0 text-dark"><?= number_format($total_laki); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="card custom-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-danger bg-opacity-10 text-danger flex-shrink-0">
                                <i class="fa-solid fa-venus"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 fw-medium" style="font-size: 0.75rem;">Perempuan</h6>
                                <h3 class="fw-extrabold mb-0 text-dark"><?= number_format($total_perempuan); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROFIL WILAYAH SAMBI DENGAN AOS -->
            <div class="row g-4 mt-3">
                <div class="col-md-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="card custom-card shadow-sm p-4 h-100 border-start border-4 border-success">
                        <h5 class="fw-bold text-success mb-2"><i class="fa-solid fa-map-location-dot me-2"></i>Cakupan Wilayah</h5>
                        <p class="text-muted small mb-0">
                            Wilayah Rukun Warga 03 Sambi menaungi <b>6 Rukun Tetangga (RT 01 s/d RT 06)</b>. Pengelolaan data terpusat ini membantu pengurus dalam pemerataan layanan administrasi warga.
                        </p>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-left" data-aos-delay="300">
                    <div class="card custom-card shadow-sm p-4 h-100 border-start border-4 border-warning">
                        <h5 class="fw-bold text-warning-emphasis mb-2"><i class="fa-solid fa-shield-halved me-2"></i>Layanan Mandiri</h5>
                        <p class="text-muted small mb-0">
                            Data kependudukan terintegrasi dapat diakses secara transparan oleh publik, serta dikelola secara aman oleh Ketua RT dan RW melalui portal admin khusus.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html>