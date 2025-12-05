<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM users WHERE id = :id AND role IN ('admin', 'petugas')";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $id);
    if ($delete_stmt->execute()) {
        $message = "Data petugas berhasil dihapus";
    }
}

// Get staff data
$query = "SELECT u.*, p.nama_petugas, p.nip 
          FROM users u 
          LEFT JOIN petugas p ON u.id = p.user_id 
          WHERE u.role IN ('admin', 'petugas') 
          ORDER BY u.role, u.username";
$stmt = $db->prepare($query);
$stmt->execute();
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petugas - Admin</title>
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
                <h5><i class="fas fa-user-tie"></i> Data Petugas</h5>
                <a href="tambah-petugas.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah Petugas
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
                                <th>Username</th>
                                <th>Email</th>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($staff as $s): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $s['username'] ?></td>
                                    <td><?= $s['email'] ?></td>
                                    <td><?= $s['nama_petugas'] ?? '-' ?></td>
                                    <td><?= $s['nip'] ?? '-' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $s['role'] == 'admin' ? 'danger' : 'primary' ?>">
                                            <?= ucfirst($s['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="update-petugas.php?id=<?= $s['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($s['id'] != $_SESSION['user_id']): ?>
                                            <a href="?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin hapus petugas ini?')">
                                                <i class="fas fa-trash"></i>
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