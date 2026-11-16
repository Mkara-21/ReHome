<?php
session_start();
require_once 'includes/db.php';

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        header($user['role'] === 'admin' ? 'Location: admin/dashboard.php' : 'Location: dashboard.php');
        exit;
    } else {
        $login_error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - ReHome</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <main style="max-width: 480px;">
        <section class="card">
            <h2>Welcome Back</h2>
            <?php if ($login_error): ?>
                <p class="error-text" style="margin: 12px 0;"><?php echo $login_error; ?></p>
            <?php endif; ?>
            <form method="POST" action="login.php" style="margin-top: 16px;">
                <div style="margin-bottom: 16px;">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required style="width:100%; padding: 8px; margin-top: 4px;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="password">Password</label>
                    <div style="display: flex; gap: 8px; margin-top: 4px;">
                        <input type="password" id="password" name="password" required style="flex: 1; padding: 8px;">
                        <button type="button" class="toggle-password" data-target="password" style="padding: 8px 12px; cursor: pointer; background: var(--color-beige); border: 1px solid #CCC; border-radius: 4px;">Show</button>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Log In</button>
            </form>
        </section>
    </main>
    <?php require_once 'includes/footer.php'; ?>