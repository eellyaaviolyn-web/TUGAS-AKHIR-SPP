<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

// Get current data
$query = "SELECT u.*, p.nama_petugas, p.nip 
          FROM users u 
          LEFT JOIN petugas p ON u.id = p.user_id 
          WHERE u.id = :id AND u.role IN ('admin', 'petugas')";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    header("Location: data-petugas.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $nama_petugas = $_POST['nama_petugas'];
    $nip = $_POST['nip'];
    $role = $_POST['role'];
    
    try {
        $db->beginTransaction();
        
        // Update user
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $user_query = "UPDATE users SET username = :username, email = :email, password = :password, role = :role WHERE id = :id";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bindParam(':password', $password);
        } else {
            $user_query = "UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id";
            $user_stmt = $db->prepare($user_query);
        }
        
        $user_stmt->bindParam(':username', $username);
        $user_stmt->bindParam(':email', $email);
        $user_stmt->bindParam(':role', $role);
        $user_stmt->bindParam(':id', $id);
        $user_stmt->execute();
        
        // Update or insert petugas
        $check_petugas = "SELECT * FROM petugas WHERE user_id = :user_id";
        $check_stmt = $db->prepare($check_petugas);
        $check_stmt->bindParam(':user_id', $id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $petugas_query = "UPDATE petugas SET nama_petugas = :nama_petugas, nip = :nip WHERE user_id = :user_id";
        } else {
            $petugas_query = "INSERT INTO petugas (user_id, nama_petugas, nip) VALUES (:user_id, :nama_petugas, :nip)";
        }
        
        $petugas_stmt = $db->prepare($petugas_query);
        $petugas_stmt->bindParam(':user_id', $id);
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Petugas - Admin</title>
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
                        <h5>Edit Data Petugas</h5>
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
                                        <input type="text" class="form-control" name="username" value="<?= $staff['username'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?= $staff['email'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password (Kosongkan jika tidak diubah)</label>
                                        <input type="password" class="form-control" name="password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_petugas" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama_petugas" value="<?= $staff['nama_petugas'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nip" class="form-label">NIP</label>
                                        <input type="text" class="form-control" name="nip" value="<?= $staff['nip'] ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-control" name="role" required>
                                            <option value="admin" <?= $staff['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="petugas" <?= $staff['role'] == 'petugas' ? 'selected' : '' ?>>Petugas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update</button>
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