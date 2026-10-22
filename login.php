<?php
session_start();
include 'koneksi.php';

// Jika admin sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = mysqli_real_escape_string($koneksi, trim($_POST['password']));

    if (!empty($username) && !empty($password)) {
        // MENGGUNAKAN TABEL 'users'
        $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verifikasi Password
            if (password_verify($password, $user['password']) || md5($password) === $user['password'] || $password === $user['password']) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = isset($user['id']) ? $user['id'] : 1;
                $_SESSION['admin_username']  = $user['username'];
                $_SESSION['admin_nama']      = isset($user['nama']) ? $user['nama'] : (isset($user['nama_lengkap']) ? $user['nama_lengkap'] : $user['username']);

                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error_msg = "Password yang Anda masukkan salah!";
            }
        } else {
            $error_msg = "Username tidak ditemukan dalam sistem!";
        }
    } else {
        $error_msg = "Silahkan isi username dan password!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Pengurus - Portal RW 03 Sambi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Icon Web -->
    <link rel="icon" type="image/png" href="image/login_icon.png">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f5132 0%, #064e3b 50%, #022c22 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: rgba(255, 193, 7, 0.12);
            top: -80px;
            left: -80px;
            filter: blur(80px);
            z-index: 0;
        }
        body::after {
            content: "";
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(25, 135, 84, 0.25);
            bottom: -100px;
            right: -100px;
            filter: blur(90px);
            z-index: 0;
        }

        .login-card {
            border: none;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            position: relative;
            z-index: 10;
        }

        .login-hero {
            background: linear-gradient(145deg, #15803d, #0f5132);
            color: #ffffff;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .login-hero::after {
            content: "";
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background: url('https://www.transparenttextures.com/patterns/cubes.png');
            opacity: 0.08;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            border: 1.5px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.25s ease;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #198754;
            box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.12);
        }

        .input-group-text {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background-color: #f8fafc;
            color: #64748b;
        }

        .btn-login {
            background: linear-gradient(135deg, #198754, #15803d);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 16px -4px rgba(25, 135, 84, 0.35);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #15803d, #0f5132);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -4px rgba(25, 135, 84, 0.45);
            color: #ffffff;
        }

        .btn-back-home {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            border-radius: 12px;
            padding: 0.55rem 1.1rem;
            text-decoration: none;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }
        .btn-back-home:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <div class="login-card" data-aos="zoom-in" data-aos-duration="600">
        <div class="row g-0">
            
            <!-- PANEL KIRI: INFO BRANDING -->
            <div class="col-lg-5 login-hero d-none d-lg-flex">
                <div class="position-relative z-1">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="fa-solid fa-building-flag fa-lg"></i>
                        </div>
                        <span class="fw-bold fs-5 text-white tracking-wide">RW 03 SAMBI</span>
                    </div>
                </div>

                <div class="position-relative z-1 my-auto py-4">
                    <h3 class="fw-extrabold text-white mb-3 leading-tight" style="font-size: 1.75rem;">Portal Manajemen Admin & Pengurus</h3>
                    <p class="text-white-50 small mb-0" style="line-height: 1.6;">
                        Sistem Layanan Terpadu Pengelolaan Data Kependudukan & Pelaporan Statistik Wilayah RW 03 Sambi.
                    </p>
                </div>

                <div class="position-relative z-1 mt-auto">
                    <div class="text-white-50 small" style="font-size: 0.75rem;">
                        &copy; <?= date('Y'); ?> RW 03 Sambi. All Rights Reserved.
                    </div>
                </div>
            </div>

            <!-- PANEL KANAN: FORM LOGIN -->
            <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="index.php" class="btn-back-home">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                    <span class="badge bg-success-subtle text-success border border-success fw-bold px-3 py-2 rounded-pill">
                        <i class="fa-solid fa-lock me-1"></i> Admin Area
                    </span>
                </div>

                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-1">Selamat Datang Kembali! 👋</h4>
                    <p class="text-muted small">Silahkan masukkan kredensial akun admin Anda untuk melanjutkan.</p>
                </div>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 py-2 px-3 mb-4 small" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error_msg; ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" autocomplete="off">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold small text-secondary">Username Admin</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="username" id="username" class="form-control border-start-0 ps-0" placeholder="Masukkan username" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label fw-semibold small text-secondary mb-0">Password</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="fa-solid fa-key"></i></span>
                            <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0 ps-0" placeholder="Masukkan password" required>
                            <button class="btn input-group-text bg-white border-start-0" type="button" id="togglePassword">
                                <i class="fa-solid fa-eye text-muted" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login w-100 d-flex align-items-center justify-content-center gap-2 mb-3">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Masuk ke System</span>
                    </button>

                </form>

                <div class="text-center mt-3">
                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Lupa akun login? Hubungi <span class="fw-semibold text-dark">Hotline WA RW 03</span> untuk reset akses.</p>
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

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>