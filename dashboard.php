<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM listings WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$myListings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard - ReHome</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <main>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
            <a href="create-listing.php" class="btn-primary" style="text-decoration: none;">+ Create New Listing</a>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div style="background: var(--color-sage); color: white; padding: 12px; border-radius: 6px; margin-top: 16px;">
                <?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            </div>
        <?php endif; ?>

        <section style="margin-top: 32px;">
            <h3>My Listed Items (<?php echo count($myListings); ?>)</h3>
            <?php if (empty($myListings)): ?>
                <p style="margin-top: 12px;">You haven't listed any items yet.</p>
            <?php else: ?>
                <table border="1" cellpadding="12" cellspacing="0" style="width: 100%; margin-top: 16px; background: white; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-beige);">
                            <th>Title</th>
                            <th>Price</th>
                            <th>Condition</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myListings as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td>KSh <?php echo number_format($item['price']); ?></td>
                                <td><?php echo htmlspecialchars($item['item_condition']); ?></td>
                                <td><span class="badge-sage"><?php echo htmlspecialchars($item['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
    <?php require_once 'includes/footer.php'; ?>