<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM siswa) as total_siswa,
    (SELECT COUNT(*) FROM pembayaran WHERE status = 'pending') as pending_payments,
    (SELECT COUNT(*) FROM pembayaran WHERE status = 'verified') as verified_payments,
    (SELECT SUM(jumlah_bayar) FROM pembayaran WHERE status = 'verified') as total_income";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get recent payments
$recent_query = "SELECT p.*, s.nama, s.nisn 
                FROM pembayaran p 
                JOIN siswa s ON p.nisn = s.nisn 
                ORDER BY p.tgl_upload DESC 
                LIMIT 5";
$recent_stmt = $db->prepare($recent_query);
$recent_stmt->execute();
$recent_payments = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SPP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Admin SPP</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Halo, <?= $_SESSION['role'] == 'admin' ? 'Administrator' : 'Petugas' ?></span>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

   <div class="container mt-4">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card card-total-siswa text-white">
                <div class="card-body text-center">
                    <h5>Total Siswa</h5>
                    <h2><?= $stats['total_siswa'] ?></h2>
                    <i class="fas fa-users fa-2x"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card card-pending-payments text-white">
                <div class="card-body text-center">
                    <h5>Pembayaran Pending</h5>
                    <h2><?= $stats['pending_payments'] ?></h2>
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Menu Utama</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <a href="verify_payments.php" class="btn btn-warning w-100">
                                    <i class="fas fa-check-circle"></i><br>
                                    Verifikasi Pembayaran
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="data-siswa.php" class="btn btn-primary w-100">
                                    <i class="fas fa-users"></i><br>
                                    Data Siswa
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="data-kelas.php" class="btn btn-success w-100">
                                    <i class="fas fa-school"></i><br>
                                    Data Kelas
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="data-spp.php" class="btn btn-info w-100">
                                    <i class="fas fa-money-bill"></i><br>
                                    Data SPP
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="history-pembayaran.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-history"></i><br>
                                    History Pembayaran
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="data-petugas.php" class="btn btn-dark w-100">
                                    <i class="fas fa-user-tie"></i><br>
                                    Data Petugas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Pembayaran Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_payments) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($recent_payments as $payment): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?= $payment['nama'] ?></strong><br>
                                                <small><?= ucfirst($payment['bulan_dibayar']) ?> <?= $payment['tahun_dibayar'] ?></small>
                                            </div>
                                            <div>
                                                <?php if ($payment['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($payment['status'] == 'verified'): ?>
                                                    <span class="badge bg-success">Verified</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Belum ada pembayaran</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>