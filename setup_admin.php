<?php
require_once 'includes/db_connect.php';

try {
    $conn = connectDB();
    
    // Clear existing admin accounts
    $conn->exec("TRUNCATE TABLE admin");
    
    // Create new admin account with properly hashed password
    $username = 'admin';
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $result = $stmt->execute([$username, $hashedPassword]);
    
    if ($result) {
        echo "Admin account created successfully!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        echo "Failed to create admin account.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>