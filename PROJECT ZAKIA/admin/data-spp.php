<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM spp WHERE id_spp = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $id);
    if ($delete_stmt->execute()) {
        $message = "Data SPP berhasil dihapus";
    }
}

// Get SPP data
$query = "SELECT * FROM spp ORDER BY tahun DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$spp_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data SPP - Admin</title>
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
            <div class="card-header d-flex justify-content-between">
                <h5><i class="fas fa-money-bill"></i> Data SPP</h5>
                <a href="tambah-spp.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah SPP
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($message)): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tahun</th>
                                <th>Nominal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($spp_list as $spp): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $spp['tahun'] ?></td>
                                    <td>Rp <?= number_format($spp['nominal'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="update-spp.php?id=<?= $spp['id_spp'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete=<?= $spp['id_spp'] ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin hapus data ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
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