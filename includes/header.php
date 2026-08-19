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

// Categories for the navbar dropdown
$nav_categories = [];
if (isset($pdo)) {
    try {
        $nav_categories = $pdo->query("SELECT name, slug FROM categories ORDER BY name ASC")->fetchAll();
    } catch (Exception $e) {
        $nav_categories = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jester Scale Models</title>
    <script>
        (function() {
            try {
                var stored = localStorage.getItem('theme');
                var theme = stored || (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css?v=11">
    <script src="<?= $base_path ?>assets/js/app.js" defer></script>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="<?= $base_path ?>index.php" class="brand-logo">
            <span class="brand-mark" aria-hidden="true">J</span>
            <span class="brand-text">
                Jester
                <span class="brand-sub">Scale Models</span>
            </span>
        </a>

        <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle color theme">
            <svg class="theme-icon theme-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg class="theme-icon theme-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>

        <button class="nav-toggle" aria-label="Toggle navigation">&#9776;</button>

        <nav class="main-nav">
            <div class="nav-menu">
                <a href="<?= $base_path ?>index.php" class="nav-link <?= $current_page === 'index.php' || $current_page === 'product.php' ? 'active' : '' ?>">Catalog</a>

                <?php if (!empty($nav_categories)): ?>
                    <div class="nav-dropdown">
                        <button type="button" class="nav-link nav-dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                            Categories
                            <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="<?= $base_path ?>index.php">All Models</a>
                            <?php foreach ($nav_categories as $cat): ?>
                                <a href="<?= $base_path ?>index.php?category=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= $base_path ?>my-orders.php" class="nav-link <?= $current_page === 'my-orders.php' ? 'active' : '' ?>">My Orders</a>
                <?php endif; ?>
            </div>

            <form class="nav-search" action="<?= $base_path ?>index.php" method="GET" role="search">
                <svg class="nav-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="search" name="q" placeholder="Search models..." value="<?= htmlspecialchars(trim($_GET['q'] ?? '')) ?>" aria-label="Search models">
            </form>

            <a href="<?= $base_path ?>cart.php" class="nav-link nav-cart <?= $current_page === 'cart.php' ? 'active' : '' ?>">
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