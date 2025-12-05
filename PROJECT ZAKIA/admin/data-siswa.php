<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get students data
$query = "SELECT s.*, k.nama_kelas, k.kompetensi_keahlian, spp.nominal, spp.tahun 
          FROM siswa s 
          JOIN kelas k ON s.id_kelas = k.id_kelas 
          JOIN spp ON s.id_spp = spp.id_spp 
          ORDER BY s.nama";
$stmt = $db->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                <h5><i class="fas fa-users"></i> Data Siswa</h5>
                <a href="tambah-siswa.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah Siswa
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Alamat</th>
                                <th>No. Telp</th>
                                <th>SPP</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($students as $student): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $student['nisn'] ?></td>
                                    <td><?= $student['nis'] ?></td>
                                    <td><?= $student['nama'] ?></td>
                                    <td><?= $student['nama_kelas'] ?></td>
                                    <td><?= $student['alamat'] ?></td>
                                    <td><?= $student['no_telp'] ?></td>
                                    <td>Rp <?= number_format($student['nominal'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="update-siswa.php?nisn=<?= $student['nisn'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="hapus-siswa.php?nisn=<?= $student['nisn'] ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin hapus siswa ini?')">
                                            <i class="fas fa-trash"></i>
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