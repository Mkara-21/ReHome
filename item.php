<?php
session_start();
require_once 'includes/db.php';

$item_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$item_id) {
    header('Location: browse.php');
    exit;
}

// Fetch item details along with seller name and category
$stmt = $pdo->prepare("SELECT l.*, c.name AS category_name, u.full_name AS seller_name, u.email AS seller_email 
                       FROM listings l 
                       JOIN categories c ON l.category_id = c.id 
                       JOIN users u ON l.user_id = u.id 
                       WHERE l.id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: browse.php');
    exit;
}

$message = '';
$error   = '';

// Handle Purchase Request Submission (FR5)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_item'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $buyer_id  = $_SESSION['user_id'];
    $seller_id = $item['user_id'];

    if ($buyer_id == $seller_id) {
        $error = "You cannot submit a purchase request for your own listing.";
    } elseif ($item['status'] !== 'approved') {
        $error = "This item is no longer available for purchase.";
    } else {
        // Prevent duplicate pending requests from the same buyer
        $check = $pdo->prepare("SELECT id FROM transactions WHERE listing_id = ? AND buyer_id = ? AND status = 'pending'");
        $check->execute([$item_id, $buyer_id]);

        if ($check->fetch()) {
            $error = "You already have a pending purchase request for this item.";
        } else {
            $insert = $pdo->prepare("INSERT INTO transactions (listing_id, buyer_id, seller_id, status) VALUES (?, ?, ?, 'pending')");
            if ($insert->execute([$item_id, $buyer_id, $seller_id])) {
                $_SESSION['flash_message'] = "Purchase request sent! Track its status below.";
                header('Location: transactions.php');
                exit;
            } else {
                $error = "Could not submit purchase request. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - ReHome</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php require_once 'includes/header.php'; ?>

    <main>
        <a href="browse.php" style="text-decoration: none; color: var(--color-charcoal); font-weight: 500;">← Back to Marketplace</a>

        <?php if ($error): ?>
            <div style="background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 6px; margin-top: 16px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div style="display: flex; flex-wrap: wrap; gap: 32px; margin-top: 24px; align-items: start;">
            
            <!-- Left: Item Image -->
            <section class="card" style="flex: 1; min-width: 300px;">
                <img src="assets/uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width: 100%; max-height: 420px; object-fit: cover; border-radius: 6px;">
            </section>

            <!-- Right: Details & Purchase Action -->
            <section class="card" style="flex: 1; min-width: 300px;">
                <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                    <span class="badge-sage"><?php echo htmlspecialchars($item['transition_type']); ?></span>
                    <span class="badge-sage" style="background: var(--color-charcoal);"><?php echo htmlspecialchars($item['category_name']); ?></span>
                    <span class="badge-sage" style="background: #666;">Condition: <?php echo htmlspecialchars($item['item_condition']); ?></span>
                </div>

                <h2 style="font-size: 28px;"><?php echo htmlspecialchars($item['title']); ?></h2>
                <p style="font-size: 24px; font-weight: bold; color: var(--color-terracotta); margin: 12px 0;">
                    KSh <?php echo number_format($item['price']); ?>
                </p>

                <hr style="border: 0; border-top: 1px solid #E0E0E0; margin: 16px 0;">

                <h4>Description & Pickup Details</h4>
                <p style="margin-top: 8px; color: #333;"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>

                <div style="background: var(--color-beige); padding: 12px; border-radius: 6px; margin-top: 20px;">
                    <strong>Seller:</strong> <?php echo htmlspecialchars($item['seller_name']); ?><br>
                    <small style="color: #555;">Listed on <?php echo date('M j, Y', strtotime($item['created_at'])); ?></small>
                </div>

                <div style="margin-top: 24px;">
                    <?php if ($item['status'] === 'sold'): ?>
                        <button disabled class="btn-primary" style="width: 100%; background: #999; cursor: not-allowed;">Item Sold</button>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn-primary" style="display: block; text-align: center; text-decoration: none;">Log In to Request Item</a>
                    <?php elseif ($_SESSION['user_id'] == $item['user_id']): ?>
                        <p style="color: #555; font-style: italic;">This is your own listing. Manage it from your <a href="dashboard.php">Dashboard</a>.</p>
                    <?php else: ?>
                        <form method="POST">
                            <button type="submit" name="request_item" class="btn-primary" style="width: 100%;">Submit Purchase Request</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </main>

    <?php require_once 'includes/footer.php'; ?>