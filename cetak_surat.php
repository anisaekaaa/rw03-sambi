<?php
session_start();
include 'koneksi.php';

$warga_id    = isset($_GET['warga_id']) ? intval($_GET['warga_id']) : 0;
$jenis_surat = isset($_GET['jenis_surat']) ? htmlspecialchars($_GET['jenis_surat']) : 'Surat Keterangan';
$no_surat    = isset($_GET['no_surat']) ? htmlspecialchars($_GET['no_surat']) : '.../RW03/' . date('m/Y');
$keperluan   = isset($_GET['keperluan']) ? htmlspecialchars($_GET['keperluan']) : '-';

// Ambil data warga dari DB
$q = mysqli_query($koneksi, "SELECT * FROM warga WHERE id = $warga_id");
$d = mysqli_fetch_assoc($q);

if (!$d) {
    echo "<div style='padding:20px; text-align:center;'>Data warga tidak ditemukan! <br><br><a href='buat_surat.php'>← Kembali</a></div>";
    exit;
}

// Format Tanggal Indonesia
$bulan_indo = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$tgl_indo = date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $jenis_surat . ' - ' . htmlspecialchars($d['nama']); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600&display=swap" rel="stylesheet">
    <!-- html2pdf JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- AOS Animation Library CSS -->
    <link rel="icon" type="image/png" href="image/cetaksurat_icon.png">
    <style>
        * {
            box-sizing: border-box;
        }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            background-color: #f1f5f9; 
            color: #000; 
            margin: 0;
            padding: 0;
        }

        .btn-ui {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 0.8rem !important;
        }

        /* Pembungkus Luar yang Bisa Di-scroll di HP */
        .wrapper-cetak {
            width: 100%;
            overflow-x: auto;
            padding: 15px 10px 40px 10px;
            display: block;
        }

        /* UKURAN KERTAS A4 UNTUK PREVIEW & PDF (BINGKAI DIHILANGKAN) */
        .kertas-a4 { 
            background: #ffffff; 
            width: 794px; 
            min-height: 1090px; 
            padding: 45px 55px; 
            margin: 0 auto;
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        .line-kop { 
            border-bottom: 3px solid #000; 
            height: 2px; 
            border-top: 1px solid #000; 
            margin-top: 3px; 
        }

        .tabel-surat {
            width: 100%;
            border-collapse: collapse;
        }

        .tabel-surat td { 
            padding: 6px 4px; 
            vertical-align: top; 
            font-size: 1.05rem;
        }

        /* CSS KHUSUS CETAK/PRINT BROWSER */
        @media print { 
            .no-print { display: none !important; } 
            body { background: #fff; } 
            .wrapper-cetak { padding: 0 !important; overflow: visible !important; }
            .kertas-a4 { 
                box-shadow: none !important;
                border: none !important;
                padding: 30px !important;
                margin: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="py-3">

    <!-- TOMBOL AKSI ATAS -->
    <div class="d-flex justify-content-center align-items-center gap-2 mb-3 px-3 no-print">
        <a href="buat_surat.php" class="btn btn-secondary btn-sm rounded-pill px-3 py-1-5 fw-semibold btn-ui shadow-sm">
            ← Kembali
        </a>
        <button onclick="downloadSuratPDF()" class="btn btn-danger btn-sm rounded-pill px-3 py-1-5 fw-bold btn-ui shadow-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF Surat
        </button>
    </div>

    <!-- WADAH PREVIEW KERTAS -->
    <div class="wrapper-cetak">
        <div id="areaSurat" class="kertas-a4">
            
            <!-- KOP SURAT RW 03 SAMBI -->
            <div class="text-center">
                <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px; font-size: 1.15rem;">PENGURUS RUKUN WARGA 03 (RW 03)</h5>
                <h4 class="fw-bold mb-0 text-uppercase" style="font-size: 1.35rem;">DESA SAMBI KECAMATAN SAMBI</h4>
                <h5 class="fw-bold mb-0" style="font-size: 1.15rem;">KABUPATEN BOYOLALI - JAWA TENGAH</h5>
                <small class="fst-italic text-muted" style="font-size: 0.85rem;">Sekretariat: Desa Sambi RT 01-06 / RW 03, Boyolali 57376</small>
            </div>
            <div class="line-kop mb-4"></div>

            <!-- JUDUL SURAT -->
            <div class="text-center mb-4">
                <h5 class="fw-bold text-uppercase text-decoration-underline mb-1" style="font-size: 1.2rem;"><?= $jenis_surat; ?></h5>
                <div style="font-size: 1rem;">Nomor: <?= $no_surat; ?></div>
            </div>

            <!-- ISI SURAT -->
            <p class="lh-base mb-3" style="font-size: 1.05rem;">Yang bertanda tangan di bawah ini Ketua Rukun Warga 03 (RW 03) Desa Sambi, Kecamatan Sambi, Kabupaten Boyolali, menerangkan dengan sebenarnya bahwa:</p>

            <table class="tabel-surat my-3" style="margin-left: 20px; width: 95%;">
                <tr>
                    <td style="width: 210px;">Nama Lengkap</td>
                    <td style="width: 15px;">:</td>
                    <td class="fw-bold"><?= htmlspecialchars($d['nama']); ?></td>
                </tr>
                <tr>
                    <td>No. Kartu Keluarga</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($d['no_kk']); ?></td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>:</td>
                    <td><?= ($d['jenis_kelamin'] == 'L') ? 'Laki-Laki' : 'Perempuan'; ?></td>
                </tr>
                <tr>
                    <td>Pendidikan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($d['pendidikan']); ?></td>
                </tr>
                <tr>
                    <td>Pekerjaan / Profesi</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($d['profesi']); ?></td>
                </tr>
                <tr>
                    <td>Alamat / Wilayah</td>
                    <td>:</td>
                    <td>RT 0<?= $d['rt']; ?> / RW 03 Desa Sambi, Kec. Sambi</td>
                </tr>
            </table>

            <p class="lh-base mt-3 mb-2" style="font-size: 1.05rem;">
                Orang tersebut di atas adalah benar-benar warga terdaftar di wilayah RT 0<?= $d['rt']; ?> / RW 03 Desa Sambi. Surat keterangan ini dibuat untuk keperluan:
            </p>
            
            <div class="text-center my-3 p-3 border border-secondary rounded bg-light fw-bold" style="font-size: 1.05rem; line-height: 1.4;">
                "<?= $keperluan; ?>"
            </div>

            <p class="lh-base mt-3" style="font-size: 1.05rem;">Demikian surat pengantar ini dibuat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.</p>

            <!-- TANDA TANGAN -->
            <div class="row text-center" style="margin-top: 60px;">
                <div class="col-6">
                    <p class="mb-1" style="font-size: 1.05rem;">Mengetahui,</p>
                    <p class="fw-bold" style="margin-bottom: 75px; font-size: 1.05rem;">Ketua RT 0<?= $d['rt']; ?></p>
                    <p class="fw-bold text-decoration-underline" style="font-size: 1.05rem;">( ............................................ )</p>
                </div>
                <div class="col-6">
                    <p class="mb-1" style="font-size: 1.05rem;">Sambi, <?= $tgl_indo; ?></p>
                    <p class="fw-bold" style="margin-bottom: 75px; font-size: 1.05rem;">Ketua RW 03 Sambi</p>
                    <p class="fw-bold text-decoration-underline" style="font-size: 1.05rem;">( ............................................ )</p>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT DOWNLOAD PDF (TANPA BINGKAI ABU) -->
    <script>
        function downloadSuratPDF() {
            const el = document.getElementById('areaSurat');

            const opt = {
                margin:       [10, 10, 10, 10],
                filename:     'Surat_Pengantar_<?= str_replace(' ', '_', $d['nama']); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, width: 794 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(el).save();
        }
    </script>
</body>
</html>