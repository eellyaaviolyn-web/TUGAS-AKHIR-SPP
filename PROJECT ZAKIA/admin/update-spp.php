<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

// Get current data
$query = "SELECT * FROM spp WHERE id_spp = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$spp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$spp) {
    header("Location: data-spp.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tahun = $_POST['tahun'];
    $nominal = $_POST['nominal'];
    
    $update_query = "UPDATE spp SET tahun = :tahun, nominal = :nominal WHERE id_spp = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':tahun', $tahun);
    $update_stmt->bindParam(':nominal', $nominal);
    $update_stmt->bindParam(':id', $id);
    
    if ($update_stmt->execute()) {
        header("Location: data-spp.php");
        exit();
    } else {
        $error = "Gagal mengupdate data SPP";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit SPP - Admin</title>
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
                        <h5>Edit Data SPP</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="tahun" class="form-label">Tahun</label>
                                <select class="form-control" name="tahun" required>
                                    <?php for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= $spp['tahun'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nominal" class="form-label">Nominal</label>
                                <input type="number" class="form-control" name="nominal" value="<?= $spp['nominal'] ?>" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update</button>
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