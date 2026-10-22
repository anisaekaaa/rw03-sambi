<?php
include 'koneksi.php';

// Kosongkan tabel warga dulu agar tidak ada data ganda saat re-import
mysqli_query($koneksi, "TRUNCATE TABLE warga");

$files = [
    1 => 'rt01.csv',
    2 => 'rt02.csv',
    3 => 'rt03.csv',
    4 => 'rt04.csv',
    5 => 'rt05.csv',
    6 => 'rt06.csv'
];

$total_sukses = 0;

foreach ($files as $rt => $filename) {
    if (!file_exists($filename)) continue;

    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $total_lines = count($lines);

    for ($i = 0; $i < $total_lines; $i++) {
        $line = trim($lines[$i]);

        // Lewati header
        if (strpos($line, 'No;') === 0 || strpos($line, ';;;;;') === 0 && $i < 2) continue;

        $data = explode(';', $line);

        // Jika ini baris utama data warga (diawali nomor urut)
        if (count($data) >= 11 && is_numeric(trim($data[0]))) {
            $no_urut      = intval(trim($data[0]));
            $nama         = trim($data[1]);
            $status_kel   = trim($data[2]);
            $status_kawin = trim($data[3]);
            $jk           = trim($data[4]);
            $tgl_lahir    = trim($data[5]);
            $usia         = is_numeric(trim($data[6])) ? intval(trim($data[6])) : "NULL";
            $pendidikan   = trim($data[7]);
            $pekerjaan    = trim($data[8]);
            $ket          = trim($data[9]);
            $profesi      = trim($data[10]);

            // Cek apakah baris berikutnya adalah sambungan (tahun lahir / lanjutan teks)
            if ($i + 1 < $total_lines) {
                $next_line = trim($lines[$i + 1]);
                if (strpos($next_line, ';;;;;') === 0) {
                    $next_data = explode(';', $next_line);
                    
                    // Gabungkan Tahun Lahir (kolom indeks 5)
                    if (isset($next_data[5]) && !empty(trim($next_data[5]))) {
                        $tgl_lahir = trim(rtrim($tgl_lahir, '/')) . '/' . trim($next_data[5]);
                    }
                    // Gabungkan Pendidikan jika ada lanjutan
                    if (isset($next_data[7]) && !empty(trim($next_data[7]))) {
                        $pendidikan .= ' ' . trim($next_data[7]);
                    }
                    // Gabungkan Pekerjaan / Profesi jika ada lanjutan
                    if (isset($next_data[8]) && !empty(trim($next_data[8]))) {
                        $pekerjaan .= ' ' . trim($next_data[8]);
                    }
                    if (isset($next_data[10]) && !empty(trim($next_data[10]))) {
                        $profesi .= ' ' . trim($next_data[10]);
                    }

                    $i++; // Melompati baris sambungan karena sudah diproses
                }
            }

            // Clean escape string untuk MySQL
            $nama         = mysqli_real_escape_string($koneksi, $nama);
            $status_kel   = mysqli_real_escape_string($koneksi, $status_kel);
            $status_kawin = mysqli_real_escape_string($koneksi, $status_kawin);
            $jk           = mysqli_real_escape_string($koneksi, $jk);
            $tgl_lahir    = mysqli_real_escape_string($koneksi, $tgl_lahir);
            $pendidikan   = mysqli_real_escape_string($koneksi, $pendidikan);
            $pekerjaan    = mysqli_real_escape_string($koneksi, $pekerjaan);
            $ket          = mysqli_real_escape_string($koneksi, $ket);
            $profesi      = mysqli_real_escape_string($koneksi, $profesi);

            $query = "INSERT INTO warga (no_urut, nama, status_keluarga, status_perkawinan, jenis_kelamin, tgl_lahir, usia, pendidikan, pekerjaan, keterangan, profesi, rt, rw) 
                      VALUES ($no_urut, '$nama', '$status_kel', '$status_kawin', '$jk', '$tgl_lahir', $usia, '$pendidikan', '$pekerjaan', '$ket', '$profesi', $rt, 3)";

            if (mysqli_query($koneksi, $query)) {
                $total_sukses++;
            }
        }
    }
}

echo "<h3>Proses Impor Ulang Selesai!</h3>";
echo "<p>Berhasil memperbarui <b>$total_sukses</b> data warga beserta Tahun Lahir lengkap.</p>";
echo "<a href='index.php'>Lihat Hasil di Halaman Utama (Welcome Page)</a>";
?>