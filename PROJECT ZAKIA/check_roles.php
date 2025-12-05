<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h3>Check User Roles</h3>";
    
    $query = "SELECT id, username, email, role FROM users ORDER BY role, username";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td><strong>" . $user['role'] . "</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Fix petugas role if needed
    echo "<br><h3>Fix Petugas Role</h3>";
    echo "<a href='fix_petugas_role.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Fix Petugas Role</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>