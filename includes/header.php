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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Scale Models</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #09090b;
            --bg-card: #121215;
            --border-color: #27272a;
            --border-light: #3f3f46;
            --text-primary: #f4f4f5;
            --text-muted: #a1a1aa;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-main); color: var(--text-primary); -webkit-font-smoothing: antialiased; }

        .site-header {
            border-bottom: 1px solid var(--border-color);
            background: rgba(9, 9, 11, 0.85);
            backdrop-filter: blur(8px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand-logo {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-primary);
            text-decoration: none;
        }

        .main-nav {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            transition: color 0.15s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text-primary);
        }

        .cart-badge {
            background: var(--border-color);
            color: var(--text-primary);
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 10px;
            border: 1px solid var(--border-light);
        }

        .nav-divider {
            width: 1px;
            height: 16px;
            background: var(--border-color);
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="brand-logo">Apex Models</a>

        <nav class="main-nav">
            <a href="index.php" class="nav-link <?= $current_page === 'index.php' || $current_page === 'product.php' ? 'active' : '' ?>">Catalog</a>
            
            <a href="cart.php" class="nav-link <?= $current_page === 'cart.php' ? 'active' : '' ?>">
                Cart 
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-divider"></div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my-orders.php" class="nav-link <?= $current_page === 'my-orders.php' ? 'active' : '' ?>">My Orders</a>
                
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="admin/dashboard.php" class="nav-link">Admin</a>
                <?php endif; ?>

                <a href="logout.php" class="nav-link">Sign Out</a>
            <?php else: ?>
                <a href="login.php" class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>">Sign In</a>
                <a href="register.php" class="nav-link <?= $current_page === 'register.php' ? 'active' : '' ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>