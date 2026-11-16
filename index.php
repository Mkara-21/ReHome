<?php
session_start();
require_once 'includes/db.php';

// Fetch 4 most recent approved items for the homepage spotlight
$stmt = $pdo->query("SELECT l.*, c.name AS category_name FROM listings l JOIN categories c ON l.category_id = c.id WHERE l.status = 'approved' ORDER BY l.created_at DESC LIMIT 4");
$featuredItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReHome — Transition & Resale Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php require_once 'includes/header.php'; ?>

    <main>
        <section class="card" style="background-color: var(--color-cream); padding: 48px 32px; text-align: center; margin-bottom: 40px;">
            <span class="badge-sage">Relocation & Moving Made Simple</span>
            <h2 style="font-size: 32px; margin: 16px 0;">Moving Out, Graduating, or Upgrading?</h2>
            <p style="max-width: 600px; margin: 0 auto 24px auto;">Connect directly with people setting up their next home. Buy and sell quality furniture, appliances, and moving bundles hassle-free.</p>
            <div style="display: flex; gap: 16px; justify-content: center;">
                <a href="browse.php" class="btn-primary" style="text-decoration: none;">Browse Marketplace</a>
                <a href="create-listing.php" class="btn-primary" style="background-color: var(--color-charcoal); text-decoration: none;">Sell Your Items</a>
            </div>
        </section>

        <section>
            <h3>Recently Listed Transition Items</h3>
            <?php if (empty($featuredItems)): ?>
                <p style="margin-top: 16px;">No items listed yet. Be the first to post a moving sale!</p>
            <?php else: ?>
                <div class="grid-layout" style="margin-top: 16px;">
                    <?php foreach ($featuredItems as $item): ?>
                        <article class="card">
                            <img src="assets/uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="Item" style="width:100%; height:180px; object-fit:cover; border-radius:4px;">
                            <span class="badge-sage" style="margin-top: 8px; display: inline-block;"><?php echo htmlspecialchars($item['transition_type']); ?></span>
                            <h4 style="margin-top: 8px;"><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p style="font-weight: bold; color: var(--color-terracotta);">KSh <?php echo number_format($item['price']); ?></p>
                            <a href="item.php?id=<?php echo $item['id']; ?>" class="btn-primary" style="display:block; text-align:center; text-decoration:none; margin-top:12px;">View Item</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require_once 'includes/footer.php'; ?>