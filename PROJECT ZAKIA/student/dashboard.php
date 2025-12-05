<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get student data
$query = "SELECT s.*, k.nama_kelas, k.kompetensi_keahlian, spp.nominal, spp.tahun 
          FROM siswa s 
          JOIN kelas k ON s.id_kelas = k.id_kelas 
          JOIN spp ON s.id_spp = spp.id_spp 
          WHERE s.nisn = :nisn";
$stmt = $db->prepare($query);
$stmt->bindParam(':nisn', $_SESSION['nisn']);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get payment history with month ordering
$month_order = "CASE bulan_dibayar 
    WHEN 'januari' THEN 1 WHEN 'februari' THEN 2 WHEN 'maret' THEN 3 
    WHEN 'april' THEN 4 WHEN 'mei' THEN 5 WHEN 'juni' THEN 6 
    WHEN 'juli' THEN 7 WHEN 'agustus' THEN 8 WHEN 'september' THEN 9 
    WHEN 'oktober' THEN 10 WHEN 'november' THEN 11 WHEN 'desember' THEN 12 
    END";

$payment_query = "SELECT p.*, spp.nominal 
                  FROM pembayaran p 
                  JOIN spp ON p.id_spp = spp.id_spp 
                  WHERE p.nisn = :nisn 
                  ORDER BY p.tahun_dibayar DESC, $month_order DESC";
$payment_stmt = $db->prepare($payment_query);
$payment_stmt->bindParam(':nisn', $_SESSION['nisn']);
$payment_stmt->execute();
$payments = $payment_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - SMK BAKTI NUSANTARA 666</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">SMK BAKTI NUSANTARA 666</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Halo, <?= $_SESSION['nama'] ?></span>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Profil Siswa</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>NISN:</strong> <?= $student['nisn'] ?></p>
                        <p><strong>NIS:</strong> <?= $student['nis'] ?></p>
                        <p><strong>Nama:</strong> <?= $student['nama'] ?></p>
                        <p><strong>Kelas:</strong> <?= $student['nama_kelas'] ?></p>
                        <p><strong>Jurusan:</strong> <?= $student['kompetensi_keahlian'] ?></p>
                        <p><strong>SPP/Bulan:</strong> Rp <?= number_format($student['nominal'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Riwayat Pembayaran SPP</h5>
                        <a href="upload_payment.php" class="btn btn-success btn-sm">
                            <i class="fas fa-upload"></i> Upload Pembayaran
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (count($payments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Tahun</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Tanggal Upload</th>
                                            <th>Bukti</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?= ucfirst($payment['bulan_dibayar']) ?></td>
                                                <td><?= $payment['tahun_dibayar'] ?></td>
                                                <td>Rp <?= number_format($payment['jumlah_bayar'], 0, ',', '.') ?></td>
                                                <td>
                                                    <?php if ($payment['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning">Menunggu Verifikasi</span>
                                                    <?php elseif ($payment['status'] == 'verified'): ?>
                                                        <span class="badge bg-success">Terverifikasi</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Ditolak</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d/m/Y H:i', strtotime($payment['tgl_upload'])) ?></td>
                                                <td>
                                                    <?php if ($payment['bukti_pembayaran']): ?>
                                                        <a href="../uploads/<?= $payment['bukti_pembayaran'] ?>" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> Lihat
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <p>Belum ada riwayat pembayaran</p>
                                <a href="upload_payment.php" class="btn btn-primary">Upload Pembayaran Pertama</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>