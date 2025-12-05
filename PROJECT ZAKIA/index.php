<?php
session_start();
// Remove auto redirect to prevent loop
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPP System - SMK Taruna Harapan 1 Cipatat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* HOMEPAGE STYLES */
        .spp-hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .spp-feature-card {
            transition: transform 0.3s;
            border: 2px solid #e9ecef;
        }
        .spp-feature-card:hover {
            transform: translateY(-5px);
            border-color: #007bff;
        }
        .spp-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        .spp-footer {
            background-color: #000000 !important;
            color: #ffffff !important;
        }
        .spp-btn-login {
            background: #ffffff;
            color: #667eea;
            border: 2px solid #ffffff;
            font-weight: bold;
        }
        .spp-btn-login:hover {
            background: transparent;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark spp-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap"></i> SPP System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="auth/login.php">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </nav>

    <section class="spp-hero-section text-center">
        <div class="container">
            <h1 class="display-4 mb-4">
                <i class="fas fa-school"></i><br>
                SMK BAKTI NUSANTARA 666
            </h1>
            <p class="lead mb-4">Sistem Pembayaran SPP Online</p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <a href="auth/login.php" class="btn spp-btn-login btn-lg me-3">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col">
                    <h2>Fitur Sistem SPP</h2>
                    <p class="text-muted">Kemudahan pembayaran SPP untuk siswa dan pengelolaan untuk admin</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card spp-feature-card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                            <h5>Upload Pembayaran</h5>
                            <p class="text-muted">Siswa dapat mengupload bukti pembayaran SPP dengan mudah</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card spp-feature-card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>Verifikasi Otomatis</h5>
                            <p class="text-muted">Admin dapat memverifikasi pembayaran secara real-time</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card spp-feature-card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                            <h5>Laporan Lengkap</h5>
                            <p class="text-muted">Laporan pembayaran dan data siswa yang komprehensif</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="spp-footer py-3 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 SMK BAKTI NUSANTARA 666. Sistem Pembayaran SPP Online.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>