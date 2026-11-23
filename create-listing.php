<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch categories directly from MySQL so IDs match the foreign key
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
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

    <?php require_once 'includes/header.php'; ?>

    <main>
        <h2>Create New Item Listing</h2>
        <p style="color: #555; margin-top: 4px;">List an individual item or part of your moving/graduation clearance.</p>
        
        <div style="display: flex; flex-wrap: wrap; gap: 32px; margin-top: 24px; align-items: start;">
            
            <!-- Left Side: Semantic Form -->
            <section class="card" style="flex: 1; min-width: 300px;">
                <form id="listingForm" action="process-listing.php" method="POST" enctype="multipart/form-data">
                    
                    <div style="margin-bottom: 16px;">
                        <label for="title">Item Title *</label>
                        <input type="text" id="title" name="title" placeholder="e.g. Wooden Study Desk" style="width:100%; padding: 8px; margin-top: 4px;">
                        <span id="title-error" class="error-text"></span>
                    </div>

                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div style="flex: 1;">
                            <label for="price">Price (KSh) *</label>
                            <input type="number" id="price" name="price" placeholder="5500" style="width:100%; padding: 8px; margin-top: 4px;">
                            <span id="price-error" class="error-text"></span>
                        </div>

                        <div style="flex: 1;">
                            <label for="category">Category *</label>
                            <select id="category" name="category_id" style="width:100%; padding: 8px; margin-top: 4px;">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span id="category-error" class="error-text"></span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div style="flex: 1;">
                            <label for="condition">Condition *</label>
                            <select id="condition" name="condition" style="width:100%; padding: 8px; margin-top: 4px;">
                                <option value="New">New</option>
                                <option value="Good" selected>Good</option>
                                <option value="Fair">Fair</option>
                            </select>
                        </div>

                        <div style="flex: 1;">
                            <label for="transition_type">Transition Reason</label>
                            <select id="transition_type" name="transition_type" style="width:100%; padding: 8px; margin-top: 4px;">
                                <option value="Moving House">Moving House</option>
                                <option value="Graduation">Graduation Clearance</option>
                                <option value="Upgrading">Upgrading</option>
                                <option value="General">General Declutter</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="4" placeholder="Describe dimensions, age, pickup location (e.g. Roysambu / Kilimani)..." style="width:100%; padding: 8px; margin-top: 4px;"></textarea>
                        <span id="description-error" class="error-text"></span>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="image">Item Photo (JPG, PNG, WEBP)</label>
                        <input type="file" id="image" name="image" accept="image/*" style="width:100%; margin-top: 4px;">
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%;">Submit Listing for Review</button>
                </form>
            </section>

            <!-- Right Side: JS Live Listing Preview -->
            <aside style="width: 320px;">
                <h3>Live Listing Preview</h3>
                <p style="font-size: 13px; color: #666; margin-bottom: 12px;">Updates automatically as you type</p>
                
                <article class="card">
                    <div id="preview-image-box" style="height: 180px; background: var(--color-beige); border: 1px dashed #CCC; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #777;">
                        Photo Preview
                    </div>
                    <div style="margin-top: 12px; display: flex; gap: 6px;">
                        <span id="preview-condition" class="badge-sage">Good</span>
                        <span id="preview-transition" class="badge-sage" style="background: var(--color-charcoal);">Moving House</span>
                    </div>
                    <h4 id="preview-title" style="margin-top: 10px; font-size: 18px;">Item Name Placeholder</h4>
                    <p id="preview-price" style="font-weight: bold; color: var(--color-terracotta); font-size: 18px; margin-top: 4px;">KSh 0</p>
                    <p style="font-size: 12px; color: #666; margin-top: 8px;">Status: Pending Admin Approval</p>
                </article>
            </aside>

        </div>
    </main>

    <?php require_once 'includes/footer.php'; ?>