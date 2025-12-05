<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $_POST['nama'];
    $nisn = $_POST['nisn'];
    $nis = $_POST['nis'];
    $id_kelas = $_POST['id_kelas'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    
    // Check if username or email exists
    $check_query = "SELECT * FROM users WHERE username = :username OR email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':username', $username);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $error = "Username atau email sudah digunakan";
    } else {
        try {
            $db->beginTransaction();
            
            // Insert user
            $user_query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'siswa')";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bindParam(':username', $username);
            $user_stmt->bindParam(':email', $email);
            $user_stmt->bindParam(':password', $password);
            $user_stmt->execute();
            
            $user_id = $db->lastInsertId();
            
            // Insert siswa
            $siswa_query = "INSERT INTO siswa (nisn, nis, user_id, nama, id_kelas, alamat, no_telp, id_spp) 
                           VALUES (:nisn, :nis, :user_id, :nama, :id_kelas, :alamat, :no_telp, 10)";
            $siswa_stmt = $db->prepare($siswa_query);
            $siswa_stmt->bindParam(':nisn', $nisn);
            $siswa_stmt->bindParam(':nis', $nis);
            $siswa_stmt->bindParam(':user_id', $user_id);
            $siswa_stmt->bindParam(':nama', $nama);
            $siswa_stmt->bindParam(':id_kelas', $id_kelas);
            $siswa_stmt->bindParam(':alamat', $alamat);
            $siswa_stmt->bindParam(':no_telp', $no_telp);
            $siswa_stmt->execute();
            
            $db->commit();
            $success = "Registrasi berhasil! Silakan login.";
            
        } catch (Exception $e) {
            $db->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Get kelas data
$database = new Database();
$db = $database->getConnection();
$kelas_query = "SELECT * FROM kelas ORDER BY nama_kelas";
$kelas_stmt = $db->prepare($kelas_query);
$kelas_stmt->execute();
$kelas_list = $kelas_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SPP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-3">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3>Registrasi Siswa</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nisn" class="form-label">NISN</label>
                                        <input type="text" class="form-control" id="nisn" name="nisn" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nis" class="form-label">NIS</label>
                                        <input type="text" class="form-control" id="nis" name="nis" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="id_kelas" class="form-label">Kelas</label>
                                        <select class="form-control" id="id_kelas" name="id_kelas" required>
                                            <option value="">Pilih Kelas</option>
                                            <?php foreach ($kelas_list as $kelas): ?>
                                                <option value="<?= $kelas['id_kelas'] ?>"><?= $kelas['nama_kelas'] ?> - <?= $kelas['kompetensi_keahlian'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="no_telp" class="form-label">No. Telepon</label>
                                        <input type="text" class="form-control" id="no_telp" name="no_telp">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Daftar</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>