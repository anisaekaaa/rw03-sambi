<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Mencegah PHP Warning Session
$admin_nama     = isset($_SESSION['admin_nama']) ? $_SESSION['admin_nama'] : (isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin RW');
$admin_username = isset($_SESSION['admin_username']) ? strtolower($_SESSION['admin_username']) : '';

// Deteksi RT Admin jika login khusus RT
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

// Cek Parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = intval($_GET['id']);

// Ambil Data Warga Yang Akan Diedit
$query_warga = mysqli_query($koneksi, "SELECT * FROM warga WHERE id = $id LIMIT 1");
if (!$query_warga || mysqli_num_rows($query_warga) == 0) {
    header("Location: admin_dashboard.php");
    exit;
}
$warga = mysqli_fetch_assoc($query_warga);

// Format Tanggal Lahir untuk input type="date" (YYYY-MM-DD)
$tgl_lahir_input = '';
if (!empty($warga['tgl_lahir'])) {
    if (strpos($warga['tgl_lahir'], '/') !== false) {
        $parts = explode('/', $warga['tgl_lahir']);
        if (count($parts) == 3) {
            $tgl_lahir_input = $parts[2] . '-' . sprintf('%02d', $parts[1]) . '-' . sprintf('%02d', $parts[0]);
        }
    } else {
        $tgl_lahir_input = date('Y-m-d', strtotime($warga['tgl_lahir']));
    }
}

//no_kk
$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM warga WHERE id = '$id'");
$w = mysqli_fetch_assoc($query);

// Ambil Daftar Kepala Keluarga
$where_kk = ($admin_rt > 0) ? "WHERE rt = $admin_rt AND status_keluarga = 'Kepala Keluarga'" : "WHERE status_keluarga = 'Kepala Keluarga'";
$q_kepala_keluarga = mysqli_query($koneksi, "SELECT id, no_urut, nama, rt FROM warga $where_kk ORDER BY rt ASC, no_urut ASC, nama ASC");

$error_msg = '';

// PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_urut           = isset($_POST['no_urut']) && $_POST['no_urut'] !== '' ? intval($_POST['no_urut']) : NULL;
    $no_kk             = mysqli_real_escape_string($koneksi, trim($_POST['no_kk']));
    $nama              = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $status_keluarga   = mysqli_real_escape_string($koneksi, trim($_POST['status_keluarga']));
    $status_perkawinan = mysqli_real_escape_string($koneksi, trim($_POST['status_perkawinan']));
    $jenis_kelamin     = mysqli_real_escape_string($koneksi, trim($_POST['jenis_kelamin']));
    
    // Format Tanggal Lahir Kembali ke DD/MM/YYYY
    $raw_tgl           = $_POST['tgl_lahir'];
    $tgl_lahir         = !empty($raw_tgl) ? date('d/m/Y', strtotime($raw_tgl)) : '';
    
    $usia              = isset($_POST['usia']) && $_POST['usia'] !== '' ? intval($_POST['usia']) : NULL;
    $pendidikan        = mysqli_real_escape_string($koneksi, trim($_POST['pendidikan']));
    $pekerjaan         = mysqli_real_escape_string($koneksi, trim($_POST['pekerjaan']));
    $profesi           = mysqli_real_escape_string($koneksi, trim($_POST['profesi']));
    $keterangan        = mysqli_real_escape_string($koneksi, trim($_POST['keterangan']));
    $rt                = intval($_POST['rt']);

    if (!empty($nama) && !empty($rt)) {
        $no_urut_val = ($no_urut !== NULL) ? "$no_urut" : "NULL";
        $usia_val    = ($usia !== NULL) ? "$usia" : "NULL";

        $query = "UPDATE warga SET 
                    no_urut           = $no_urut_val,
                    no_kk             = $no_kk,
                    nama              = '$nama',
                    status_keluarga   = '$status_keluarga',
                    status_perkawinan = '$status_perkawinan',
                    jenis_kelamin     = '$jenis_kelamin',
                    tgl_lahir         = '$tgl_lahir',
                    usia              = $usia_val,
                    pendidikan        = '$pendidikan',
                    pekerjaan         = '$pekerjaan',
                    profesi           = '$profesi',
                    keterangan        = '$keterangan',
                    rt                = $rt
                  WHERE id = $id";

        if (mysqli_query($koneksi, $query)) {
            header("Location: admin_dashboard.php?pesan=sukses_edit");
            exit;
        } else {
            $error_msg = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    } else {
        $error_msg = "Nama lengkap dan RT wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Data Warga - RW 03 Sambi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- TomSelect CSS (Dropdown Searchable) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <!-- Icon Web -->
    <link rel="icon" type="image/png" href="image/editdata_icon.png">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .glass-nav {
            background: rgba(15, 81, 50, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-form {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 20px 40px -15px rgba(15, 81, 50, 0.08);
            overflow: hidden;
        }

        /* HEADER DENGAN HIJAU EMERALD SERAGAM */
        .card-form-header {
            background: linear-gradient(135deg, #15803d, #0f5132);
            color: #ffffff;
            padding: 1.5rem 2rem;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.4rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            font-size: 0.875rem;
            border: 1.5px solid #cbd5e1;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: #ffffff;
            border-color: #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.12);
        }

        /* STYLING TOMSELECT HIJAU ELEGANT */
        .ts-control {
            border-radius: 10px !important;
            padding: 0.65rem 0.9rem !important;
            border: 1.5px solid #198754 !important;
            background-color: #ffffff !important;
            font-size: 0.875rem !important;
        }
        .ts-dropdown {
            border-radius: 12px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
            border: 1px solid #cbd5e1 !important;
            overflow: hidden;
        }

        .btn-hover-effect {
            transition: all 0.25s ease;
        }
        .btn-hover-effect:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <!-- NAVBAR ADMIN -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="admin_dashboard.php">
                <i class="fa-solid fa-user-shield text-warning"></i> 
                <span>PANEL ADMIN RW 03</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- CONTENT FORM -->
    <main class="py-4 py-md-5 my-auto">
        <div class="container px-3" style="max-width: 840px;">
            
            <div class="card card-form" data-aos="zoom-in" data-aos-duration="500">
                
                <!-- HEADER FORM EDIT -->
                <div class="card-form-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1 text-white"><i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Edit Data Warga</h5>
                        <p class="mb-0 text-white-50 small">Perbarui rincian informasi data warga berikut ini.</p>
                    </div>
                </div>

                <!-- BODY FORM -->
                <div class="card-body p-4 p-md-5">

                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 py-2 px-3 mb-4 small" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error_msg; ?>
                            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="warga_edit.php?id=<?= $id; ?>" method="POST" autocomplete="off">
                        
<!-- PILIH KELUARGA INDUK (SEARCHABLE DROPDOWN) -->
<div class="mb-4 p-3 rounded-3 bg-light border">
    <label for="pilih_kk" class="form-label text-success fw-bold">
        <i class="fa-solid fa-users me-1"></i> Sambungkan Dengan Kepala Keluarga:
    </label>
    <select id="pilih_kk" placeholder="Cari nama Kepala Keluarga..." autocomplete="off">
        <option value="">-- Pertahankan No. KK Saat Ini / Kepala Keluarga Sendiri --</option>
        <?php 
        if ($q_kepala_keluarga && mysqli_num_rows($q_kepala_keluarga) > 0):
            while($kk = mysqli_fetch_assoc($q_kepala_keluarga)):
        ?>
            <option value="<?= $kk['id']; ?>" data-nokk="<?= htmlspecialchars($kk['no_kk']); ?>" data-rt="<?= $kk['rt']; ?>">
                <?= htmlspecialchars($kk['nama']); ?> (RT 0<?= $kk['rt']; ?> - No. KK: <?= !empty($kk['no_kk']) ? htmlspecialchars($kk['no_kk']) : '-'; ?>)
            </option>
        <?php 
            endwhile;
        endif; 
        ?>
    </select>
    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
        *Gunakan ini jika ingin menghubungkan/mengurutkan data warga ini di bawah Kepala Keluarga tertentu.
    </small>
</div>

                        <!-- BARIS 1: NAMA LENGKAP & NO URUT KK -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-9">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($warga['nama']); ?>" required autofocus>
                            </div>

                            <!-- NO URUT KK -->
<div class="col-md-6 mb-3">
    <label class="form-label fw-bold">No. KK / Kode Keluarga</label>
    <input type="text" 
           name="no_kk" 
           class="form-control" 
           placeholder="Contoh: 1, 2, atau 1.1" 
           value="<?= htmlspecialchars($warga['no_kk'] ?? ''); ?>">
    <div class="form-text text-muted" style="font-size: 0.8rem;">
        *Gunakan kode yang sama untuk anggota dalam satu keluarga.
    </div>
</div>
                        </div>

                        <!-- BARIS 2: STATUS KELUARGA & STATUS PERKAWINAN -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="status_keluarga" class="form-label">Status Hubungan Keluarga</label>
                                <select name="status_keluarga" id="status_keluarga" class="form-select">
                                    <?php 
                                    $opt_sk = ['Kepala Keluarga', 'Istri', 'Suami', 'Anak', 'Mertua', 'Orang Tua', 'Famili Lain', 'Kepala Keluarga (Meninggal)', 'Istri (Meninggal)', 'Anak (Meninggal)'];
                                    foreach ($opt_sk as $sk):
                                        $sel = ($warga['status_keluarga'] == $sk) ? 'selected' : '';
                                        echo "<option value='$sk' $sel>$sk</option>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="status_perkawinan" class="form-label">Status Perkawinan</label>
                                <select name="status_perkawinan" id="status_perkawinan" class="form-select">
                                    <?php 
                                    $opt_sp = ['Kawin', 'Belum Kawin', 'Cerai Mati', 'Cerai Hidup'];
                                    foreach ($opt_sp as $sp):
                                        $sel = ($warga['status_perkawinan'] == $sp) ? 'selected' : '';
                                        echo "<option value='$sp' $sel>$sp</option>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- BARIS 3: JENIS KELAMIN, KALENDER TANGGAL LAHIR, & USIA AUTO -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
                                    <option value="L" <?= ($warga['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki (L)</option>
                                    <option value="P" <?= ($warga['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan (P)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="tgl_lahir" class="form-label">Tanggal Lahir <i class="fa-solid fa-calendar-days text-success ms-1"></i></label>
                                <input type="date" name="tgl_lahir" id="tgl_lahir" class="form-control" value="<?= $tgl_lahir_input; ?>" onchange="hitungUsiaOtomatis()">
                            </div>
                            <div class="col-md-3">
                                <label for="usia" class="form-label">Usia (Tahun)</label>
                                <input type="number" name="usia" id="usia" class="form-control" value="<?= htmlspecialchars($warga['usia']); ?>" placeholder="Otomatis" readonly style="background-color: #e2e8f0;">
                            </div>
                        </div>

                        <!-- BARIS 4: PENDIDIKAN & PEKERJAAN -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="pendidikan" class="form-label">Pendidikan Terakhir</label>
                                <input type="text" name="pendidikan" id="pendidikan" class="form-control" value="<?= htmlspecialchars($warga['pendidikan']); ?>" placeholder="Contoh: SMA / SD / Belum Sekolah">
                            </div>
                            <div class="col-md-6">
                                <label for="pekerjaan" class="form-label">Pekerjaan</label>
                                <input type="text" name="pekerjaan" id="pekerjaan" class="form-control" value="<?= htmlspecialchars($warga['pekerjaan']); ?>" placeholder="Contoh: Buruh / Petani / PNS">
                            </div>
                        </div>

                        <!-- BARIS 5: PROFESI & RT -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label for="profesi" class="form-label">Profesi / Detail Pekerjaan</label>
                                <input type="text" name="profesi" id="profesi" class="form-control" value="<?= htmlspecialchars($warga['profesi']); ?>" placeholder="Contoh: Petani/Pekebun">
                            </div>
                            <div class="col-md-4">
                                <label for="rt" class="form-label">Wilayah RT <span class="text-danger">*</span></label>
                                <select name="rt" id="rt" class="form-select" required>
                                    <?php for($i=1; $i<=6; $i++): ?>
                                        <option value="<?= $i; ?>" <?= ($warga['rt'] == $i) ? 'selected' : ''; ?>>RT 0<?= $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <!-- BARIS 6: KETERANGAN TAMBAHAN -->
                        <div class="mb-4">
                            <label for="keterangan" class="form-label">Keterangan Tambahan</label>
                            <input type="text" name="keterangan" id="keterangan" class="form-control" value="<?= htmlspecialchars($warga['keterangan']); ?>" placeholder="Opsional (misal: Disabilitas, Pindahan, dll.)">
                        </div>

                        <!-- TOMBOL SUBMIT HIJAU -->
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
                            <a href="admin_dashboard.php" class="btn btn-light rounded-pill px-4 fw-semibold text-secondary btn-hover-effect">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm btn-hover-effect d-inline-flex align-items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Update Data Warga</span>
                            </button>
                        </div>

                    </form>

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
    <!-- TomSelect JS (Library Dropdown Searchable) -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        AOS.init({ duration: 800, once: true });

        // INISIALISASI TOMSELECT SEARCHBAR DROPDOWN
        const tomSelectKK = new TomSelect('#pilih_kk', {
            create: false,
            sortField: { field: "text", order: "asc" },
            onChange: function(val) {
                if(!val) return;
                const option = this.options[val];
                if(option) {
                    const noUrut = option.$option.getAttribute('data-nourut');
                    const rtVal = option.$option.getAttribute('data-rt');

                    if (noUrut && noUrut !== 'null') {
                        document.getElementById('no_urut').value = noUrut;
                    }
                    if (rtVal) {
                        document.getElementById('rt').value = rtVal;
                    }
                }
            }
        });

        // SCRIPT HITUNG USIA OTOMATIS DARI KALENDER TANGGAL LAHIR
        function hitungUsiaOtomatis() {
            const tglInput = document.getElementById('tgl_lahir').value;
            if (!tglInput) return;

            const tglLahir = new Date(tglInput);
            const hariIni  = new Date();

            let usia = hariIni.getFullYear() - tglLahir.getFullYear();
            const bedaBulan = hariIni.getMonth() - tglLahir.getMonth();

            if (bedaBulan < 0 || (bedaBulan === 0 && hariIni.getDate() < tglLahir.getDate())) {
                usia--;
            }

            if (usia >= 0) {
                document.getElementById('usia').value = usia;
            }
        }
    </script>
</body>
</html>