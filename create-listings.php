<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing - ReHome</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <h1>ReHome</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="browse.php">Browse</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Create New Item Listing</h2>
        
        <div style="display: flex; gap: 32px; margin-top: 24px;">
            
            <!-- Left Side: Semantic Form -->
            <section style="flex: 1;">
                <form id="listingForm" action="process-listing.php" method="POST" enctype="multipart/form-data">
                    <div style="margin-bottom: 16px;">
                        <label for="title">Item Title</label>
                        <input type="text" id="title" name="title" style="width:100%; padding: 8px;">
                        <span id="title-error" class="error-text"></span>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="price">Price (KSh)</label>
                        <input type="number" id="price" name="price" style="width:100%; padding: 8px;">
                        <span id="price-error" class="error-text"></span>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="category">Category</label>
                        <select id="category" name="category" style="width:100%; padding: 8px;">
                            <option value="">-- Select Category --</option>
                            <option value="furniture">Furniture</option>
                            <option value="electronics">Electronics</option>
                            <option value="kitchen">Kitchenware</option>
                        </select>
                        <span id="category-error" class="error-text"></span>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="condition">Condition</label>
                        <select id="condition" name="condition" style="width:100%; padding: 8px;">
                            <option value="New">New</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">Submit Listing</button>
                </form>
            </section>

            <!-- Right Side: JS Live Preview Box -->
            <aside style="width: 320px;">
                <h3>Live Listing Preview</h3>
                <article class="card" style="margin-top: 16px;">
                    <div style="height: 150px; background: #E0E0E0; text-align: center; line-height: 150px;">Photo Placeholder</div>
                    <h4 id="preview-title" style="margin-top: 12px;">Item Name Placeholder</h4>
                    <p id="preview-price" style="font-weight: bold; color: var(--color-terracotta);">KSh 0</p>
                    <span id="preview-condition" class="badge-sage">Condition</span>
                </article>
            </aside>

        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> ReHome Transition Marketplace</p>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>