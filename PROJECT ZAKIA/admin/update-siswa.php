<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$nisn = $_GET['nisn'] ?? '';

// Get current student data
$query = "SELECT s.*, u.username, u.email FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.nisn = :nisn";
$stmt = $db->prepare($query);
$stmt->bindParam(':nisn', $nisn);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: data-siswa.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $id_kelas = $_POST['id_kelas'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $id_spp = $_POST['id_spp'];
    
    try {
        $db->beginTransaction();
        
        // Update siswa
        $update_siswa = "UPDATE siswa SET nama = :nama, id_kelas = :id_kelas, alamat = :alamat, no_telp = :no_telp, id_spp = :id_spp WHERE nisn = :nisn";
        $stmt = $db->prepare($update_siswa);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':id_kelas', $id_kelas);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':no_telp', $no_telp);
        $stmt->bindParam(':id_spp', $id_spp);
        $stmt->bindParam(':nisn', $nisn);
        $stmt->execute();
        
        $db->commit();
        header("Location: data-siswa.php");
        exit();
        
    } catch (Exception $e) {
        $db->rollback();
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Get kelas and SPP data
$kelas_query = "SELECT * FROM kelas ORDER BY nama_kelas";
$kelas_stmt = $db->prepare($kelas_query);
$kelas_stmt->execute();
$kelas_list = $kelas_stmt->fetchAll(PDO::FETCH_ASSOC);

$spp_query = "SELECT * FROM spp ORDER BY tahun DESC";
$spp_stmt = $db->prepare($spp_query);
$spp_stmt->execute();
$spp_list = $spp_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa - Admin</title>
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Edit Data Siswa</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nisn" class="form-label">NISN</label>
                                        <input type="text" class="form-control" value="<?= $student['nisn'] ?>" readonly>
                                        <small class="text-muted">NISN tidak dapat diubah</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nis" class="form-label">NIS</label>
                                        <input type="text" class="form-control" value="<?= $student['nis'] ?>" readonly>
                                        <small class="text-muted">NIS tidak dapat diubah</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama" value="<?= $student['nama'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="id_kelas" class="form-label">Kelas</label>
                                        <select class="form-control" name="id_kelas" required>
                                            <?php foreach ($kelas_list as $kelas): ?>
                                                <option value="<?= $kelas['id_kelas'] ?>" <?= $student['id_kelas'] == $kelas['id_kelas'] ? 'selected' : '' ?>>
                                                    <?= $kelas['nama_kelas'] ?> - <?= $kelas['kompetensi_keahlian'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" name="alamat" rows="3"><?= $student['alamat'] ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="no_telp" class="form-label">No. Telepon</label>
                                        <input type="text" class="form-control" name="no_telp" value="<?= $student['no_telp'] ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="id_spp" class="form-label">SPP</label>
                                        <select class="form-control" name="id_spp" required>
                                            <?php foreach ($spp_list as $spp): ?>
                                                <option value="<?= $spp['id_spp'] ?>" <?= $student['id_spp'] == $spp['id_spp'] ? 'selected' : '' ?>>
                                                    Tahun <?= $spp['tahun'] ?> - Rp <?= number_format($spp['nominal'], 0, ',', '.') ?>/bulan
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="data-siswa.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>