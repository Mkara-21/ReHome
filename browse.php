<?php
session_start();
require_once 'includes/db.php';

// Filter & Search Logic
$search   = trim($_GET['search'] ?? '');
$category = filter_var($_GET['category'] ?? 0, FILTER_VALIDATE_INT);

// Build Dynamic SQL Query using Prepared Statements
$sql = "SELECT l.*, c.name AS category_name, u.full_name AS seller_name 
        FROM listings l 
        JOIN categories c ON l.category_id = c.id 
        JOIN users u ON l.user_id = u.id 
        WHERE l.status = 'approved'";

$params = [];

if (!empty($search)) {
    $sql .= " AND (l.title LIKE ? OR l.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category > 0) {
    $sql .= " AND l.category_id = ?";
    $params[] = $category;
}

$sql .= " ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();

// Fetch categories for sidebar filter
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Marketplace - ReHome</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php require_once 'includes/header.php'; ?>

    <main class="grid-layout" style="grid-template-columns: 260px 1fr; align-items: start; gap: 32px;">
        
        <!-- Filter Sidebar -->
        <aside class="card">
            <h3>Filter Marketplace</h3>
            <form action="browse.php" method="GET" style="margin-top: 16px;">
                <div style="margin-bottom: 16px;">
                    <label for="search">Keyword</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search sofa, desk..." style="width:100%; padding:8px;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="category">Category</label>
                    <select id="category" name="category" style="width:100%; padding:8px;">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;">Apply Filters</button>
            </form>
        </aside>

        <!-- Product Grid -->
        <section>
            <h2>Available Transition Items (<?php echo count($listings); ?>)</h2>
            
            <?php if (empty($listings)): ?>
                <p style="margin-top: 24px;">No approved listings found matching your search.</p>
            <?php else: ?>
                <div class="grid-layout" style="margin-top: 16px;">
                    <?php foreach ($listings as $item): ?>
                        <article class="card">
                            <img src="assets/uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="Item Image" style="width:100%; height:180px; object-fit:cover; border-radius:4px;">
                            <span class="badge-sage" style="margin-top:8px; display:inline-block;"><?php echo htmlspecialchars($item['transition_type']); ?></span>
                            <h3 style="margin-top: 8px;"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p style="font-weight: bold; color: var(--color-terracotta); font-size: 18px;">KSh <?php echo number_format($item['price']); ?></p>
                            <p style="font-size: 13px; color: #666;">Seller: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                            
                            <a href="item.php?id=<?php echo $item['id']; ?>" class="btn-primary" style="display:block; text-align:center; text-decoration:none; margin-top:12px;">View Details</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <?php require_once 'includes/footer.php'; ?>

</body>
</html>