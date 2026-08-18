<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate active page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Calculate total items in cart
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Determine base path for assets (works from root and subdirectories)
$base_path = '';
if (strpos($current_page, 'admin') !== false) {
    $base_path = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Scale Models</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css?v=3">
    <script src="<?= $base_path ?>assets/js/app.js" defer></script>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="<?= $base_path ?>index.php" class="brand-logo">
            <span class="brand-mark" aria-hidden="true">A</span>
            <span class="brand-text">Apex Models</span>
        </a>

        <button class="nav-toggle" aria-label="Toggle navigation">&#9776;</button>

        <nav class="main-nav">
            <a href="<?= $base_path ?>index.php" class="nav-link <?= $current_page === 'index.php' || $current_page === 'product.php' ? 'active' : '' ?>">Catalog</a>

            <a href="<?= $base_path ?>cart.php" class="nav-link <?= $current_page === 'cart.php' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                Cart
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <span class="nav-divider"></span>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= $base_path ?>my-orders.php" class="nav-user <?= $current_page === 'my-orders.php' ? 'active' : '' ?>">
                    <span class="nav-avatar" aria-hidden="true"><?= strtoupper(substr(trim($_SESSION['full_name'] ?? 'U'), 0, 1)) ?></span>
                    <span class="nav-user-name"><?= htmlspecialchars(explode(' ', trim($_SESSION['full_name'] ?? 'User'))[0]) ?></span>
                </a>

                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="<?= $base_path ?>admin/dashboard.php" class="nav-link">Admin</a>
                <?php endif; ?>

                <a href="<?= $base_path ?>logout.php" class="nav-link nav-link-ghost">Sign Out</a>
            <?php else: ?>
                <a href="<?= $base_path ?>login.php" class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>">Sign In</a>
                <a href="<?= $base_path ?>register.php" class="nav-link nav-link-cta <?= $current_page === 'register.php' ? 'active' : '' ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
