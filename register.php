<?php
session_start();
require_once 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password  = $_POST['password'] ?? '';

    if (empty($full_name)) $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$full_name, $email, $hashedPassword])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $full_name;
                $_SESSION['role'] = 'user';
                header('Location: dashboard.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - ReHome</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <main style="max-width: 480px;">
        <section class="card">
            <h2>Create an Account</h2>
            <?php if (!empty($errors)): ?>
                <div style="background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 6px; margin: 16px 0;">
                    <?php foreach ($errors as $err) echo "<p>$err</p>"; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="register.php" style="margin-top: 16px;">
                <div style="margin-bottom: 16px;">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required style="width:100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required style="width:100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="password">Password (Min 8 characters)</label>
                    <input type="password" id="password" name="password" required style="width:100%; padding: 8px;">
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Register</button>
            </form>
        </section>
    </main>
    <?php require_once 'includes/footer.php'; ?>