<?php
require_once 'includes/db.php';

$name     = 'Nelson Isaiah';
$email    = 'nelsonisaiah@gmail.com';
$password = 'R!chs31f';
$hashed   = password_hash($password, PASSWORD_BCRYPT);

// Check if Nelson already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$existing = $stmt->fetch();

if ($existing) {
    // Update existing account to admin with the exact hashed password
    $update = $pdo->prepare("UPDATE users SET full_name = ?, password_hash = ?, role = 'admin' WHERE email = ?");
    $update->execute([$name, $hashed, $email]);
    echo "Updated Nelson Isaiah to Admin with properly hashed password! You can delete this file now.";
} else {
    // Insert fresh admin account
    $insert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
    $insert->execute([$name, $email, $hashed]);
    echo "Created Admin account for Nelson Isaiah! You can delete this file now.";
}
?>