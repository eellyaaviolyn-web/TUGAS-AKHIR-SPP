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
    $tahun = $_POST['tahun'];
    $nominal = $_POST['nominal'];
    
    $insert_query = "INSERT INTO spp (tahun, nominal) VALUES (:tahun, :nominal)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':tahun', $tahun);
    $insert_stmt->bindParam(':nominal', $nominal);
    
    if ($insert_stmt->execute()) {
        header("Location: data-spp.php");
        exit();
    } else {
        $error = "Gagal menambah data SPP";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah SPP - Admin</title>
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
                        <h5>Tambah Data SPP</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="tahun" class="form-label">Tahun</label>
                                <select class="form-control" name="tahun" required>
                                    <option value="">Pilih Tahun</option>
                                    <?php for ($i = date('Y'); $i <= date('Y') + 5; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nominal" class="form-label">Nominal</label>
                                <input type="number" class="form-control" name="nominal" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="data-spp.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>