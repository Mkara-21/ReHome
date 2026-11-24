<?php
// Detect the current file name (e.g., 'index.php', 'browse.php')
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header>
    <div class="logo">
        <a href="index.php" style="text-decoration: none; color: var(--color-charcoal);">
            <h1>ReHome</h1>
        </a>
    </div>
    <nav>
        <ul>
            <li>
                <a href="index.php" class="<?php echo ($current_page === 'index.php') ? 'active-nav' : ''; ?>">
                    Home
                </a>
            </li>
            <li>
                <a href="browse.php" class="<?php echo ($current_page === 'browse.php' || $current_page === 'item.php') ? 'active-nav' : ''; ?>">
                    Marketplace
                </a>
            </li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li>
                        <a href="admin/dashboard.php" style="border: 1px solid var(--color-terracotta); color: var(--color-terracotta); font-weight: 600;">
                            🛡️ Admin Panel
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="dashboard.php" class="<?php echo ($current_page === 'dashboard.php') ? 'active-nav' : ''; ?>">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="create-listing.php" class="<?php echo ($current_page === 'create-listing.php') ? 'active-nav' : ''; ?>">
                        ➕ Sell Item
                    </a>
                </li>
                <li>
                    <a href="transactions.php" class="<?php echo ($current_page === 'transactions.php') ? 'active-nav' : ''; ?>">
                        Transactions
                    </a>
                </li>
                <li>
                    <a href="logout.php">Logout</a>
                </li>

            <?php else: ?>
                <li>
                    <a href="login.php" class="<?php echo ($current_page === 'login.php') ? 'active-nav' : ''; ?>">
                        Login
                    </a>
                </li>
                <li>
                    <a href="register.php" class="<?php echo ($current_page === 'register.php') ? 'active-nav' : ''; ?>">
                        Register
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>