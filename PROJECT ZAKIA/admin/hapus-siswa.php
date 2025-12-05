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

if ($nisn) {
    try {
        // Get user_id first
        $get_user = "SELECT user_id FROM siswa WHERE nisn = :nisn";
        $stmt = $db->prepare($get_user);
        $stmt->bindParam(':nisn', $nisn);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student) {
            // Delete user (will cascade delete siswa)
            $delete_user = "DELETE FROM users WHERE id = :user_id";
            $stmt = $db->prepare($delete_user);
            $stmt->bindParam(':user_id', $student['user_id']);
            $stmt->execute();
        }
        
    } catch (Exception $e) {
        // Handle error silently
    }
}

header("Location: data-siswa.php");
exit();
?>