<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Replicas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #09090b;
            --bg-card: #121215;
            --bg-card-hover: #18181b;
            --border-color: #27272a;
            --border-light: #3f3f46;
            --text-primary: #f4f4f5;
            --text-muted: #a1a1aa;
            --accent: #e4e4e7;
            --accent-hover: #ffffff;
            --font-main: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: var(--bg-main); color: var(--text-primary); font-family: var(--font-main); -webkit-font-smoothing: antialiased; line-height: 1.5; }

        .site-header { border-bottom: 1px solid var(--border-color); background: rgba(9, 9, 11, 0.85); backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 100; }
        .header-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; height: 72px; display: flex; align-items: center; justify-content: space-between; }
        
        .brand-logo { font-size: 16px; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-primary); text-decoration: none; }
        .brand-logo span { color: var(--text-muted); font-weight: 400; }

        .main-nav { display: flex; align-items: center; gap: 32px; }
        .main-nav a { color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 400; transition: color 0.15s ease; }
        .main-nav a:hover, .main-nav a.active { color: var(--text-primary); }

        .nav-actions { display: flex; align-items: center; gap: 20px; }
        .btn-link { color: var(--text-primary); text-decoration: none; font-size: 14px; font-weight: 500; border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 6px; transition: all 0.15s ease; }
        .btn-link:hover { background: var(--bg-card-hover); border-color: var(--border-light); }
    </style>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="brand-logo">Apex <span>Replicas</span></a>
        <nav class="main-nav">
            <a href="index.php">Catalog</a>
            <a href="index.php?category=hypercars">Hypercars</a>
            <a href="index.php?category=supercars">Supercars</a>
            <a href="index.php?category=classic-vintage">Classics</a>
        </nav>
        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn-link">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn-link">Sign Out</a>
            <?php else: ?>
                <a href="login.php" class="btn-link">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</header>