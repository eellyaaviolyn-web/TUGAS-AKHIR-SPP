<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_petugas = $_POST['nama_petugas'];
    $nip = $_POST['nip'];
    $role = $_POST['role'];
    
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
            $user_query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bindParam(':username', $username);
            $user_stmt->bindParam(':email', $email);
            $user_stmt->bindParam(':password', $password);
            $user_stmt->bindParam(':role', $role);
            $user_stmt->execute();
            
            $user_id = $db->lastInsertId();
            
            // Insert petugas
            $petugas_query = "INSERT INTO petugas (user_id, nama_petugas, nip) VALUES (:user_id, :nama_petugas, :nip)";
            $petugas_stmt = $db->prepare($petugas_query);
            $petugas_stmt->bindParam(':user_id', $user_id);
            $petugas_stmt->bindParam(':nama_petugas', $nama_petugas);
            $petugas_stmt->bindParam(':nip', $nip);
            $petugas_stmt->execute();
            
            $db->commit();
            header("Location: data-petugas.php");
            exit();
            
        } catch (Exception $e) {
            $db->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Petugas - Admin</title>
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
                        <h5>Tambah Data Petugas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_petugas" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama_petugas" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nip" class="form-label">NIP</label>
                                        <input type="text" class="form-control" name="nip">
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-control" name="role" required>
                                            <option value="">Pilih Role</option>
                                            <option value="admin">Admin</option>
                                            <option value="petugas">Petugas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="data-petugas.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>