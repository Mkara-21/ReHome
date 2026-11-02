<?php
session_start();
require_once '../includes/db.php';

// Security Check: Ensure user is Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Process Approval / Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $listing_id = filter_var($_POST['listing_id'] ?? 0, FILTER_VALIDATE_INT);

    if ($listing_id && in_array($action, ['approve', 'reject'])) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $pdo->prepare("UPDATE listings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $listing_id]);
    }
}

// Fetch Metrics (FR10)
$totalUsers       = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeListings   = $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'approved'")->fetchColumn();
$pendingListings  = $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'pending'")->fetchColumn();
$completedSales   = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'completed'")->fetchColumn();

// Fetch Pending Approval Queue
$stmt = $pdo->prepare("SELECT l.*, u.full_name AS seller_name, c.name AS category_name 
                       FROM listings l 
                       JOIN users u ON l.user_id = u.id 
                       JOIN categories c ON l.category_id = c.id 
                       WHERE l.status = 'pending' 
                       ORDER BY l.created_at ASC");
$stmt->execute();
$pendingQueue = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ReHome</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header>
        <h1>ReHome Admin Portal</h1>
        <nav>
            <ul>
                <li><a href="../dashboard.php">User View</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Platform Activity Statistics</h2>
        
        <!-- FR10 Summary Metric Cards -->
        <div class="grid-layout" style="margin-top: 16px;">
            <div class="card"><h4>Total Users</h4><p style="font-size: 28px; font-weight: bold;"><?php echo $totalUsers; ?></p></div>
            <div class="card"><h4>Active Listings</h4><p style="font-size: 28px; font-weight: bold; color: var(--color-sage);"><?php echo $activeListings; ?></p></div>
            <div class="card"><h4>Pending Approvals</h4><p style="font-size: 28px; font-weight: bold; color: var(--color-terracotta);"><?php echo $pendingListings; ?></p></div>
            <div class="card"><h4>Completed Sales</h4><p style="font-size: 28px; font-weight: bold;"><?php echo $completedSales; ?></p></div>
        </div>

        <h2 style="margin-top: 40px;">Pending Listings Review Queue (FR9)</h2>
        
        <?php if (empty($pendingQueue)): ?>
            <p style="margin-top: 16px;">All pending listings have been reviewed!</p>
        <?php else: ?>
            <table border="1" cellpadding="12" cellspacing="0" style="width: 100%; margin-top: 16px; background: white; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--color-beige);">
                        <th>Seller</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingQueue as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['seller_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td>KSh <?php echo number_format($row['price']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="listing_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn-primary" style="background: var(--color-sage);">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn-primary">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </main>

    <?php require_once '../includes/footer.php'; ?>

</body>
</html>