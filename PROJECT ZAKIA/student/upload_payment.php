<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get student SPP info
$query = "SELECT s.*, spp.nominal, spp.tahun FROM siswa s JOIN spp ON s.id_spp = spp.id_spp WHERE s.nisn = :nisn";
$stmt = $db->prepare($query);
$stmt->bindParam(':nisn', $_SESSION['nisn']);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bulan = $_POST['bulan'];
    $tahun = $_POST['tahun'];
    $jumlah_bayar = $_POST['jumlah_bayar'];
    
    // Check if payment already exists
    $check_query = "SELECT * FROM pembayaran WHERE nisn = :nisn AND bulan_dibayar = :bulan AND tahun_dibayar = :tahun";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':nisn', $_SESSION['nisn']);
    $check_stmt->bindParam(':bulan', $bulan);
    $check_stmt->bindParam(':tahun', $tahun);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $error = "Pembayaran untuk bulan $bulan tahun $tahun sudah ada";
    } else {
        // Handle file upload
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                $filename = $_SESSION['nisn'] . '_' . $bulan . '_' . $tahun . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $upload_path)) {
                    // Insert payment record
                    $insert_query = "INSERT INTO pembayaran (nisn, bulan_dibayar, tahun_dibayar, id_spp, jumlah_bayar, bukti_pembayaran, status) 
                                   VALUES (:nisn, :bulan, :tahun, :id_spp, :jumlah_bayar, :bukti_pembayaran, 'pending')";
                    $insert_stmt = $db->prepare($insert_query);
                    $insert_stmt->bindParam(':nisn', $_SESSION['nisn']);
                    $insert_stmt->bindParam(':bulan', $bulan);
                    $insert_stmt->bindParam(':tahun', $tahun);
                    $insert_stmt->bindParam(':id_spp', $student['id_spp']);
                    $insert_stmt->bindParam(':jumlah_bayar', $jumlah_bayar);
                    $insert_stmt->bindParam(':bukti_pembayaran', $filename);
                    
                    if ($insert_stmt->execute()) {
                        $success = "Pembayaran berhasil diupload dan menunggu verifikasi";
                    } else {
                        $error = "Gagal menyimpan data pembayaran";
                    }
                } else {
                    $error = "Gagal mengupload file";
                }
            } else {
                $error = "Format file tidak didukung. Gunakan JPG, PNG, atau PDF";
            }
        } else {
            $error = "Silakan pilih file bukti pembayaran";
        }
    }
}

$months = [
    'januari', 'februari', 'maret', 'april', 'mei', 'juni',
    'juli', 'agustus', 'september', 'oktober', 'november', 'desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Pembayaran - SMK BAKTI NUSANTARA 666</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">SPP System</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Halo, <?= $_SESSION['nama'] ?></span>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-upload"></i> Upload Bukti Pembayaran SPP</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <?= $success ?>
                                <br><a href="dashboard.php" class="btn btn-sm btn-primary mt-2">Kembali ke Dashboard</a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <strong>Informasi SPP:</strong><br>
                            Nominal per bulan: <strong>Rp <?= number_format($student['nominal'], 0, ',', '.') ?></strong><br>
                            Tahun SPP: <strong><?= $student['tahun'] ?></strong>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bulan" class="form-label">Bulan Pembayaran</label>
                                        <select class="form-control" id="bulan" name="bulan" required>
                                            <option value="">Pilih Bulan</option>
                                            <?php 
                                            $current_month = date('n'); // 1-12
                                            foreach ($months as $index => $month): 
                                                $month_num = $index + 1;
                                                $selected = ($month_num == $current_month) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $month ?>" <?= $selected ?>><?= ucfirst($month) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tahun" class="form-label">Tahun Pembayaran</label>
                                        <select class="form-control" id="tahun" name="tahun" required>
                                            <option value="">Pilih Tahun</option>
                                            <?php 
                                            $current_year = date('Y');
                                            for ($i = $current_year; $i >= $current_year - 2; $i--): 
                                                $selected = ($i == $current_year) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $i ?>" <?= $selected ?>><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="jumlah_bayar" class="form-label">Jumlah Pembayaran</label>
                                <input type="number" class="form-control" id="jumlah_bayar" name="jumlah_bayar" 
                                       value="<?= $student['nominal'] ?>" required>
                                <div class="form-text">Nominal standar: Rp <?= number_format($student['nominal'], 0, ',', '.') ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bukti_pembayaran" class="form-label">Bukti Pembayaran</label>
                                <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" 
                                       accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="form-text">Format yang didukung: JPG, PNG, PDF (Max 5MB)</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Upload Pembayaran
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>