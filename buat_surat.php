<?php
session_start();
include 'koneksi.php';

// Cek status login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Deteksi Otomatis Wilayah RT Admin
$admin_nama     = $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? 'Admin';
$admin_username = strtolower($_SESSION['admin_username'] ?? '');
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

// Filter Warga Sesuai RT Admin yang Login
$where_rt = ($admin_rt > 0) ? "WHERE rt = $admin_rt" : "";
$selected_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query Ambil Warga
$query_warga = mysqli_query($koneksi, "SELECT id, nama, no_kk, rt, profesi FROM warga $where_rt ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Surat Pengantar - RW 03 Sambi</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="map_favicon.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Select2 CSS (Pencarian & Stylist Dropdown) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- AOS Animation Library CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Icon Web -->
    <link rel="icon" type="image/png" href="image/buatsurat_icon.png">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
        }
        .card-form {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        .form-label {
            font-size: 0.825rem;
            color: #475569;
        }
        /* Penyesuaian Style Select2 agar Pas & Rapi */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 10px !important;
            padding: 0.45rem 0.75rem !important;
            font-size: 0.875rem !important;
            min-height: 42px !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container {
            width: 100% !important;
        }
        /* Menyeragamkan Font Size Opsi Dropdown Select2 */
        .select2-results__option {
            font-size: 0.875rem !important;
            padding: 8px 12px !important;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-3 py-md-5">

<div class="container px-3" style="max-width: 650px;">
    <!-- Ditambahkan atribut data-aos untuk animasi Fade-In Up -->
    <div class="card card-form shadow-lg p-3 p-sm-4 p-md-5" data-aos="fade-up" data-aos-duration="800">
        
        <!-- HEADER FORM RESPONSIF -->
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-4 pb-3 border-bottom gap-2">
            <div>
                <h5 class="fw-bold mb-1 text-success d-flex align-items-center gap-2 fs-6 fs-sm-5">
                    <i class="fa-solid fa-file-signature"></i> Buat Surat Pengantar
                </h5>
                <span class="badge bg-success-subtle text-success border border-success fw-semibold" style="font-size: 0.7rem;">
                    <?= $admin_rt > 0 ? "Akses RT 0$admin_rt Sambi" : "Akses Seluruh RW 03"; ?>
                </span>
            </div>
            <div>
                <a href="admin_dashboard.php" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold w-100 w-sm-auto mt-2 mt-sm-0 shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <form action="cetak_surat.php" method="GET" target="_blank">
            
            <!-- 1. PILIH WARGA (SEARCHABLE DROPDOWN) -->
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Nama Warga</label>
                <select name="warga_id" id="wargaSelect" class="form-select" required>
                    <option value="">-- Ketik nama atau pilih warga --</option>
                    <?php while ($w = mysqli_fetch_assoc($query_warga)): ?>
                        <option value="<?= $w['id']; ?>" <?= ($w['id'] == $selected_id) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($w['nama']); ?> (RT 0<?= $w['rt']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- 2. PILIH JENIS SURAT (SELECT2 STYLED) -->
            <div class="mb-3">
                <label class="form-label fw-bold">Jenis Surat Pengantar</label>
                <select name="jenis_surat" id="jenisSuratSelect" class="form-select" required>
                    <option value="Surat Keterangan Domisili">Surat Keterangan Domisili</option>
                    <option value="Surat Keterangan Tidak Mampu (SKTM)">Surat Keterangan Tidak Mampu (SKTM)</option>
                    <option value="Surat Pengantar SKCK">Surat Pengantar SKCK</option>
                    <option value="Surat Pengantar Izin Keramaian">Surat Pengantar Izin Keramaian</option>
                    <option value="Surat Keterangan Usaha">Surat Keterangan Usaha</option>
                </select>
            </div>

            <!-- 3. NOMOR SURAT -->
            <div class="mb-3">
                <label class="form-label fw-bold">Nomor Surat (Opsional)</label>
                <input type="text" name="no_surat" class="form-control rounded-3 py-2 shadow-none" style="font-size: 0.875rem;" placeholder="Contoh: 045/RW03/X/2026" value=".../RW03/<?= date('m/Y'); ?>">
            </div>

            <!-- 4. KEPERLUAN / KETERANGAN TAMBAHAN -->
            <div class="mb-4">
                <label class="form-label fw-bold">Keperluan / Keterangan Tambahan</label>
                <textarea name="keperluan" id="keperluanInput" class="form-control rounded-3 shadow-none" style="font-size: 0.875rem;" rows="3" placeholder="Isi keperluan pembuatan surat..." required></textarea>
                <div class="form-text small" style="font-size: 0.7rem;">Teks keperluan terisi otomatis dan masih bisa kamu ubah manual.</div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="btn btn-success w-100 fw-bold rounded-pill py-2.5 shadow-sm">
                <i class="fa-solid me-2"></i>Generate & Cetak Surat
            </button>
        </form>
    </div>
</div>

<!-- JQUERY & SELECT2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- AOS JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi Animasi AOS
        AOS.init({
            once: true // Animasi jalan 1 kali saja saat dimuat
        });

        // Aktifkan Select2 dengan pencarian pada Dropdown Warga
        $('#wargaSelect').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Ketik nama atau pilih warga --',
            allowClear: true,
            width: '100%'
        });

        // Aktifkan Select2 tanpa kotak cari pada Dropdown Jenis Surat agar tampilannya rapi
        $('#jenisSuratSelect').select2({
            theme: 'bootstrap-5',
            minimumResultsForSearch: Infinity,
            width: '100%'
        });

        // Event trigger saat Jenis Surat diganti
        $('#jenisSuratSelect').on('change', function() {
            autoFillKeperluan();
        });

        // Jalankan autofill awal saat halaman dibuka
        autoFillKeperluan();
    });

    const templateKeperluan = {
        "Surat Keterangan Domisili": "Persyaratan administratif tempat tinggal / domisili warga di wilayah RW 03 Desa Sambi.",
        "Surat Keterangan Tidak Mampu (SKTM)": "Persyaratan pengajuan bantuan pendidikan / keringanan biaya pengobatan kesehatan.",
        "Surat Pengantar SKCK": "Persyaratan penerbitan Surat Keterangan Catatan Kepolisian (SKCK) di Polsek Sambi.",
        "Surat Pengantar Izin Keramaian": "Persyaratan permohonan izin penyelenggaraan acara/hajatan di wilayah RW 03 Desa Sambi.",
        "Surat Keterangan Usaha": "Persyaratan pengajuan tambahan modal usaha / kelengkapan administrasi UMKM."
    };

    function autoFillKeperluan() {
        const jenis = $('#jenisSuratSelect').val();
        const inputKeperluan = document.getElementById('keperluanInput');
        
        if (templateKeperluan[jenis]) {
            inputKeperluan.value = templateKeperluan[jenis];
        }
    }
</script>

</body>
</html>