<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kelas = $_POST['nama_kelas'];
    $kompetensi_keahlian = $_POST['kompetensi_keahlian'];
    
    $insert_query = "INSERT INTO kelas (nama_kelas, kompetensi_keahlian) VALUES (:nama_kelas, :kompetensi_keahlian)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':nama_kelas', $nama_kelas);
    $insert_stmt->bindParam(':kompetensi_keahlian', $kompetensi_keahlian);
    
    if ($insert_stmt->execute()) {
        header("Location: data-kelas.php");
        exit();
    } else {
        $error = "Gagal menambah data kelas";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kelas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin SPP</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Tambah Data Kelas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nama_kelas" class="form-label">Nama Kelas</label>
                                <input type="text" class="form-control" name="nama_kelas" placeholder="Contoh: XI RPL 1" required>
                            </div>
                            <div class="mb-3">
                                <label for="kompetensi_keahlian" class="form-label">Kompetensi Keahlian</label>
                                <select class="form-control" name="kompetensi_keahlian" required>
                                    <option value="">Pilih Kompetensi Keahlian</option>
                                    <option value="Rekayasa Perangkat Lunak Dan Game">Rekayasa Perangkat Lunak</option>
                                    <option value="Desain Komunikasi Visual">DKV</option>
                                    <option value="Akuntansi">Akuntansi</option>
                                    <option value="Animasi">Animasi</option>
                                    <option value="Pemasaran">Pemasaran</option>
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="data-kelas.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>