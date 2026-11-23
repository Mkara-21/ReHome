<header>
    <div class="logo">
        <a href="index.php" style="text-decoration: none; color: var(--color-charcoal);">
            <h1>ReHome</h1>
        </a>
    </div>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="browse.php">Marketplace</a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li>
                        <a href="admin/dashboard.php" class="badge-sage" style="background-color: var(--color-terracotta); padding: 6px 12px; font-weight: bold;">
                            🛡️ Admin Panel
                        </a>
                    </li>
                <?php endif; ?>

                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="create-listing.php">➕ Sell Item</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="logout.php">Logout</a></li>

            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>