<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepared Statement to Prevent SQL Injection
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerate Session ID to prevent Session Fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role']; // 'user' or 'admin'

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    } else {
        $login_error = "Invalid email or password credentials.";
    }
}
?>