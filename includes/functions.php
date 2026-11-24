<?php
// 1. Escape output safely against XSS
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// 2. Format Kenyan Shillings consistently
function formatKES($amount) {
    return 'KSh ' . number_format((float)$amount);
}

// 3. Enforce authenticated user session
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// 4. Enforce administrator privileges
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ../login.php');
        exit;
    }
}
?>