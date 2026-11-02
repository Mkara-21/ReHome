<?php
session_start();
require_once 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password  = $_POST['password'];

    // PHP Backend Validation (Security Requirement)
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";

    if (empty($errors)) {
        // Check if email exists using prepared statement
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "Email is already registered.";
        } else {
            // Secure Password Hashing (BCRYPT)
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert User
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
            if ($stmt->execute([$full_name, $email, $hashedPassword])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $full_name;
                header('Location: dashboard.php');
                exit;
            }
        }
    }
}
?>
