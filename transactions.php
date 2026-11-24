<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Transaction Status Updates (FR6 & Cancel Requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'], $_POST['new_status'])) {
    $tx_id      = filter_var($_POST['transaction_id'], FILTER_VALIDATE_INT);
    $new_status = $_POST['new_status'];

    // Fetch transaction to verify ownership
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->execute([$tx_id]);
    $tx = $stmt->fetch();

    if ($tx) {
        // Seller actions: accept, complete, or cancel
        if ($tx['seller_id'] == $user_id && in_array($new_status, ['accepted', 'completed', 'cancelled'])) {
            $update = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
            $update->execute([$new_status, $tx_id]);

            // If marked completed, also mark the listing as 'sold' so nobody else requests it
            if ($new_status === 'completed') {
                $soldStmt = $pdo->prepare("UPDATE listings SET status = 'sold' WHERE id = ?");
                $soldStmt->execute([$tx['listing_id']]);
            }

            $_SESSION['flash_message'] = "Order status updated to " . ucfirst($new_status) . ".";
        }
        // Buyer action: cancel own pending request
        elseif ($tx['buyer_id'] == $user_id && $new_status === 'cancelled' && $tx['status'] === 'pending') {
            $update = $pdo->prepare("UPDATE transactions SET status = 'cancelled' WHERE id = ?");
            $update->execute([$tx_id]);
            $_SESSION['flash_message'] = "Your purchase request has been cancelled.";
        }
    }

    header('Location: transactions.php');
    exit;
}

// 1. Fetch Incoming Sales (Where logged-in user is Seller)
$salesStmt = $pdo->prepare("SELECT t.*, l.title AS item_title, l.price, u.full_name AS buyer_name, u.email AS buyer_email
                            FROM transactions t
                            JOIN listings l ON t.listing_id = l.id
                            JOIN users u ON t.buyer_id = u.id
                            WHERE t.seller_id = ?
                            ORDER BY t.created_at DESC");
$salesStmt->execute([$user_id]);
$incomingSales = $salesStmt->fetchAll();

// 2. Fetch Outgoing Purchases (Where logged-in user is Buyer)
$purchasesStmt = $pdo->prepare("SELECT t.*, l.title AS item_title, l.price, u.full_name AS seller_name, u.email AS seller_email
                                FROM transactions t
                                JOIN listings l ON t.listing_id = l.id
                                JOIN users u ON t.seller_id = u.id
                                WHERE t.buyer_id = ?
                                ORDER BY t.created_at DESC");
$purchasesStmt->execute([$user_id]);
$outgoingPurchases = $purchasesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - ReHome</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php require_once 'includes/header.php'; ?>

    <main>
        <h2>My Transactions & Order History</h2>
        <p style="color: #555; margin-top: 4px;">Manage incoming buyer requests for your items and track your purchases.</p>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div style="background: var(--color-sage); color: white; padding: 12px; border-radius: 6px; margin-top: 16px;">
                <?php echo htmlspecialchars($_SESSION['flash_message']); unset($_SESSION['flash_message']); ?>
            </div>
        <?php endif; ?>

        <!-- SECTION 1: SELLER VIEW (Incoming Purchase Requests) -->
        <section style="margin-top: 32px;">
            <h3>Incoming Buyer Requests (Selling)</h3>
            <?php if (empty($incomingSales)): ?>
                <p style="margin-top: 12px; color: #666;">No buyers have requested your items yet.</p>
            <?php else: ?>
                <table border="1" cellpadding="12" cellspacing="0" style="width: 100%; margin-top: 16px; background: white; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-beige); text-align: left;">
                            <th>Item</th>
                            <th>Buyer</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incomingSales as $sale): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($sale['item_title']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($sale['buyer_name']); ?><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($sale['buyer_email']); ?></small>
                                </td>
                                <td>KSh <?php echo number_format($sale['price']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($sale['created_at'])); ?></td>
                                <td><span class="badge-sage"><?php echo htmlspecialchars($sale['status']); ?></span></td>
                                <td>
                                    <?php if ($sale['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="transaction_id" value="<?php echo $sale['id']; ?>">
                                            <button type="submit" name="new_status" value="accepted" class="btn-primary" style="background: var(--color-sage); padding: 6px 12px;">Accept</button>
                                            <button type="submit" name="new_status" value="cancelled" class="btn-primary" style="padding: 6px 12px;">Decline</button>
                                        </form>
                                    <?php elseif ($sale['status'] === 'accepted'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="transaction_id" value="<?php echo $sale['id']; ?>">
                                            <button type="submit" name="new_status" value="completed" class="btn-primary" style="background: var(--color-charcoal); padding: 6px 12px;">Mark Completed</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #777; font-size: 13px;">No actions</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <!-- SECTION 2: BUYER VIEW (Outgoing Purchase Requests) -->
        <section style="margin-top: 48px;">
            <h3>My Purchase Requests (Buying)</h3>
            <?php if (empty($outgoingPurchases)): ?>
                <p style="margin-top: 12px; color: #666;">You haven't requested any items yet. <a href="browse.php">Browse the marketplace</a>.</p>
            <?php else: ?>
                <table border="1" cellpadding="12" cellspacing="0" style="width: 100%; margin-top: 16px; background: white; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-beige); text-align: left;">
                            <th>Item</th>
                            <th>Seller</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($outgoingPurchases as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['item_title']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($order['seller_name']); ?><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($order['seller_email']); ?></small>
                                </td>
                                <td>KSh <?php echo number_format($order['price']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td><span class="badge-sage"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                <td>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="transaction_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" name="new_status" value="cancelled" class="btn-primary" style="padding: 6px 12px;">Cancel Request</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #777; font-size: 13px;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

    </main>

    <?php require_once 'includes/footer.php'; ?>