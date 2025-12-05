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
    $nisn = $_POST['nisn'];
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $id_kelas = $_POST['id_kelas'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $id_spp = $_POST['id_spp'];
    $password = password_hash($nis, PASSWORD_DEFAULT); // Password = NIS
    
    // Check if NISN or NIS exists
    $check_query = "SELECT * FROM siswa WHERE nisn = :nisn OR nis = :nis";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':nisn', $nisn);
    $check_stmt->bindParam(':nis', $nis);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $error = "NISN atau NIS sudah digunakan";
    } else {
        try {
            $db->beginTransaction();
            
            // Insert user (username = NIS, password = NIS)
            $email = $nisn . '@student.com';
            $user_query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'siswa')";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bindParam(':username', $nis);
            $user_stmt->bindParam(':email', $email);
            $user_stmt->bindParam(':password', $password);
            $user_stmt->execute();
            
            $user_id = $db->lastInsertId();
            
            // Insert siswa
            $siswa_query = "INSERT INTO siswa (nisn, nis, user_id, nama, id_kelas, alamat, no_telp, id_spp) 
                           VALUES (:nisn, :nis, :user_id, :nama, :id_kelas, :alamat, :no_telp, :id_spp)";
            $siswa_stmt = $db->prepare($siswa_query);
            $siswa_stmt->bindParam(':nisn', $nisn);
            $siswa_stmt->bindParam(':nis', $nis);
            $siswa_stmt->bindParam(':user_id', $user_id);
            $siswa_stmt->bindParam(':nama', $nama);
            $siswa_stmt->bindParam(':id_kelas', $id_kelas);
            $siswa_stmt->bindParam(':alamat', $alamat);
            $siswa_stmt->bindParam(':no_telp', $no_telp);
            $siswa_stmt->bindParam(':id_spp', $id_spp);
            $siswa_stmt->execute();
            
            $db->commit();
            header("Location: data-siswa.php");
            exit();
            
        } catch (Exception $e) {
            $db->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
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
    <title>Tambah Siswa - Admin</title>
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
                        <h5>Tambah Data Siswa</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <strong>Info:</strong> Username login siswa = NIS, Password default = NIS
                        </div>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nisn" class="form-label">NISN</label>
                                        <input type="text" class="form-control" name="nisn" minlength="10" maxlength="10" pattern="[0-9]+" required>
                                        <small class="text-muted">NISN harus 10 digit angka</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nis" class="form-label">NIS</label>
                                        <input type="text" class="form-control" name="nis" minlength="3" maxlength="10" pattern="[0-9]+" required>
                                        <small class="text-muted">NIS akan menjadi username & password login (hanya angka, 3-10 digit)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama" minlength="3" maxlength="50" required>
                                        <small class="text-muted">Nama lengkap siswa (3-50 karakter)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="id_kelas" class="form-label">Kelas</label>
                                        <select class="form-control" name="id_kelas" required>
                                            <option value="">Pilih Kelas</option>
                                            <?php foreach ($kelas_list as $kelas): ?>
                                                <option value="<?= $kelas['id_kelas'] ?>"><?= $kelas['nama_kelas'] ?> - <?= $kelas['kompetensi_keahlian'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" name="alamat" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="no_telp" class="form-label">No. Telepon</label>
                                        <input type="text" class="form-control" name="no_telp" pattern="[0-9+\-\s]+" placeholder="08xxxxxxxxxx">
                                        <small class="text-muted">Opsional - Format: 08xxxxxxxxxx</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="id_spp" class="form-label">SPP</label>
                                        <select class="form-control" name="id_spp" required>
                                            <option value="">Pilih Tahun SPP</option>
                                            <?php foreach ($spp_list as $spp): ?>
                                                <option value="<?= $spp['id_spp'] ?>">Tahun <?= $spp['tahun'] ?> - Rp <?= number_format($spp['nominal'], 0, ',', '.') ?>/bulan</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Pilih tahun SPP sesuai angkatan siswa</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
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