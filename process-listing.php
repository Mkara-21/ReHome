<?php
session_start();
require_once 'includes/db.php';

// Security: Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title           = trim($_POST['title'] ?? '');
    $price           = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $category_id     = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
    $item_condition  = $_POST['condition'] ?? 'Good';
    $transition_type = $_POST['transition_type'] ?? 'General';
    $description     = trim($_POST['description'] ?? '');
    $user_id         = $_SESSION['user_id'];

    // Server-Side Validation (PHP Guardrail)
    if (empty($title)) $errors[] = "Item title is required.";
    if (!$price || $price <= 0) $errors[] = "Please enter a valid price in KSh.";
    if (!$category_id) $errors[] = "Please select a valid category.";
    if (empty($description)) $errors[] = "Description is required.";

    // Secure Image Upload Processing
    $image_filename = 'default-item.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['image']['tmp_name'];
        $fileName      = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            // Generate unique name to prevent overwriting
            $image_filename = uniqid('item_', true) . '.' . $fileExtension;
            $uploadFileDir  = 'assets/uploads/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            move_uploaded_path($fileTmpPath, $uploadFileDir . $image_filename);
        } else {
            $errors[] = "Invalid image file type. Allowed: JPG, PNG, WEBP.";
        }
    }

    // Insert into MySQL via Prepared Statements
    if (empty($errors)) {
        $sql = "INSERT INTO listings (user_id, category_id, title, description, price, item_condition, transition_type, image_url, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$user_id, $category_id, $title, $description, $price, $item_condition, $transition_type, $image_filename])) {
            $_SESSION['flash_message'] = "Your listing has been submitted for admin approval!";
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = "Failed to save listing. Please try again.";
        }
    }
}
?>