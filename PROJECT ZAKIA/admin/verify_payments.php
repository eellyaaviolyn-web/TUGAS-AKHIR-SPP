<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle verification action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id_pembayaran = $_POST['id_pembayaran'];
    $action = $_POST['action'];
    $keterangan = $_POST['keterangan'] ?? '';
    
    $status = ($action == 'verify') ? 'verified' : 'rejected';
    
    $update_query = "UPDATE pembayaran SET status = :status, tgl_verifikasi = NOW(), verified_by = :verified_by, keterangan = :keterangan WHERE id_pembayaran = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':status', $status);
    $update_stmt->bindParam(':verified_by', $_SESSION['user_id']);
    $update_stmt->bindParam(':keterangan', $keterangan);
    $update_stmt->bindParam(':id', $id_pembayaran);
    
    if ($update_stmt->execute()) {
        $message = ($action == 'verify') ? 'Pembayaran berhasil diverifikasi' : 'Pembayaran ditolak';
    }
}

// Get pending payments
$query = "SELECT p.*, s.nama, s.nis, k.nama_kelas, spp.nominal 
          FROM pembayaran p 
          JOIN siswa s ON p.nisn = s.nisn 
          JOIN kelas k ON s.id_kelas = k.id_kelas 
          JOIN spp ON p.id_spp = spp.id_spp 
          WHERE p.status = 'pending' 
          ORDER BY p.tgl_upload DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - SPP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin SPP</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Halo, <?= $_SESSION['nama'] ?></span>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-check-circle"></i> Verifikasi Pembayaran SPP</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-success"><?= $message ?></div>
                        <?php endif; ?>
                        
                        <?php if (count($pending_payments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Siswa</th>
                                            <th>NISN/NIS</th>
                                            <th>Kelas</th>
                                            <th>Bulan/Tahun</th>
                                            <th>Jumlah</th>
                                            <th>Bukti</th>
                                            <th>Tanggal Upload</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_payments as $payment): ?>
                                            <tr>
                                                <td><?= $payment['nama'] ?></td>
                                                <td><?= $payment['nisn'] ?> / <?= $payment['nis'] ?></td>
                                                <td><?= $payment['nama_kelas'] ?></td>
                                                <td><?= ucfirst($payment['bulan_dibayar']) ?> <?= $payment['tahun_dibayar'] ?></td>
                                                <td>Rp <?= number_format($payment['jumlah_bayar'], 0, ',', '.') ?></td>
                                                <td>
                                                    <a href="../uploads/<?= $payment['bukti_pembayaran'] ?>" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Lihat
                                                    </a>
                                                </td>
                                                <td><?= date('d/m/Y H:i', strtotime($payment['tgl_upload'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-success" onclick="verifyPayment(<?= $payment['id_pembayaran'] ?>, 'verify')">
                                                        <i class="fas fa-check"></i> Verifikasi
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="verifyPayment(<?= $payment['id_pembayaran'] ?>, 'reject')">
                                                        <i class="fas fa-times"></i> Tolak
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>Tidak ada pembayaran yang perlu diverifikasi</h5>
                                <p class="text-muted">Semua pembayaran sudah diproses</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for verification -->
    <div class="modal fade" id="verificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Verifikasi Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_pembayaran" id="paymentId">
                        <input type="hidden" name="action" id="actionType">
                        
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                            <textarea class="form-control" name="keterangan" id="keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="confirmBtn">Konfirmasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verifyPayment(id, action) {
            document.getElementById('paymentId').value = id;
            document.getElementById('actionType').value = action;
            
            if (action === 'verify') {
                document.getElementById('modalTitle').textContent = 'Verifikasi Pembayaran';
                document.getElementById('confirmBtn').textContent = 'Verifikasi';
                document.getElementById('confirmBtn').className = 'btn btn-success';
            } else {
                document.getElementById('modalTitle').textContent = 'Tolak Pembayaran';
                document.getElementById('confirmBtn').textContent = 'Tolak';
                document.getElementById('confirmBtn').className = 'btn btn-danger';
            }
            
            new bootstrap.Modal(document.getElementById('verificationModal')).show();
        }
    </script>
</body>
</html>