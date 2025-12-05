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
    $delete_query = "DELETE FROM kelas WHERE id_kelas = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $id);
    if ($delete_stmt->execute()) {
        $message = "Data kelas berhasil dihapus";
    }
}

// Get class data with student count
$query = "SELECT k.*, COUNT(s.nisn) as jumlah_siswa 
          FROM kelas k 
          LEFT JOIN siswa s ON k.id_kelas = s.id_kelas 
          GROUP BY k.id_kelas 
          ORDER BY k.nama_kelas";
$stmt = $db->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas - Admin</title>
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
                <h5><i class="fas fa-school"></i> Data Kelas</h5>
                <a href="tambah-kelas.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah Kelas
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
                                <th>Nama Kelas</th>
                                <th>Kompetensi Keahlian</th>
                                <th>Jumlah Siswa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($classes as $class): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $class['nama_kelas'] ?></td>
                                    <td><?= $class['kompetensi_keahlian'] ?></td>
                                    <td><?= $class['jumlah_siswa'] ?> siswa</td>
                                    <td>
                                        <a href="update-kelas.php?id=<?= $class['id_kelas'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete=<?= $class['id_kelas'] ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin hapus kelas ini?')">
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