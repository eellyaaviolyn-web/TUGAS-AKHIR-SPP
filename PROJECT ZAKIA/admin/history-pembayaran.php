<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get payment history
$query = "SELECT p.*, s.nama, s.nisn, s.nis, k.nama_kelas, u.username as verified_by_name
          FROM pembayaran p 
          JOIN siswa s ON p.nisn = s.nisn 
          JOIN kelas k ON s.id_kelas = k.id_kelas 
          LEFT JOIN users u ON p.verified_by = u.id
          ORDER BY p.tgl_upload DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Pembayaran - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin SPP</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> History Pembayaran SPP</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Siswa</th>
                                <th>NISN/NIS</th>
                                <th>Kelas</th>
                                <th>Bulan/Tahun</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Tgl Upload</th>
                                <th>Verifikator</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $payment['nama'] ?></td>
                                    <td><?= $payment['nisn'] ?> / <?= $payment['nis'] ?></td>
                                    <td><?= $payment['nama_kelas'] ?></td>
                                    <td><?= ucfirst($payment['bulan_dibayar']) ?> <?= $payment['tahun_dibayar'] ?></td>
                                    <td>Rp <?= number_format($payment['jumlah_bayar'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($payment['status'] == 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif ($payment['status'] == 'verified'): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($payment['tgl_upload'])) ?></td>
                                    <td><?= $payment['verified_by_name'] ?? '-' ?></td>
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
            </div>
        </div>
    </div>
</body>
</html>