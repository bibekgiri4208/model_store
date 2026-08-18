<?php
require_once 'config/db.php';
include 'includes/header.php';

// Capture Search and Filter parameters
$search   = trim($_GET['q'] ?? '');
$scale    = trim($_GET['scale'] ?? '');
$category = trim($_GET['category'] ?? '');

// Fetch active categories for the filter dropdown
$categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$all_categories  = $categories_stmt->fetchAll();

// Dynamic Query Builder
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($scale)) {
    $sql .= " AND p.scale = ?";
    $params[] = $scale;
}

if (!empty($category)) {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<style>
    .hero-section { padding: 48px 24px 32px; max-width: 1200px; margin: 0 auto; }
    .hero-section h1 { font-size: 32px; font-weight: 500; letter-spacing: -0.02em; margin-bottom: 8px; }
    .hero-section p { color: var(--text-muted); font-size: 15px; max-width: 500px; font-weight: 300; }

    /* Filter Bar Styling */
    .filter-bar { max-width: 1200px; margin: 0 auto; padding: 0 24px 32px; border-bottom: 1px solid var(--border-color); }
    .filter-form { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }

    .filter-input, .filter-select {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.15s ease;
    }
    .filter-input { flex-grow: 1; min-width: 220px; }
    .filter-input:focus, .filter-select:focus { border-color: var(--border-light); }
    
    .btn-filter {
        background: var(--text-primary);
        color: var(--bg-main);
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-reset {
        color: var(--text-muted);
        font-size: 13px;
        text-decoration: none;
        padding: 10px 12px;
        transition: color 0.15s ease;
    }
    .btn-reset:hover { color: var(--text-primary); }

    /* Catalog Grid */
    .catalog-container { max-width: 1200px; margin: 0 auto; padding: 48px 24px 96px; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 32px; }
    
    .product-card { 
        background: var(--bg-card); 
        border: 1px solid var(--border-color); 
        border-radius: 8px; 
        overflow: hidden; 
        transition: border-color 0.2s ease, transform 0.2s ease; 
        display: flex; 
        flex-direction: column; 
    }
    .product-card:hover { border-color: var(--border-light); transform: translateY(-3px); }

    .image-wrapper { width: 100%; aspect-ratio: 16 / 10; background: #000; overflow: hidden; position: relative; display: block; }
    .image-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; opacity: 0.9; }
    .product-card:hover .image-wrapper img { transform: scale(1.04); opacity: 1; }

    .badge-scale { position: absolute; top: 12px; left: 12px; background: rgba(9, 9, 11, 0.85); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 11px; font-weight: 500; padding: 4px 8px; border-radius: 4px; backdrop-filter: blur(4px); z-index: 2; }

    .card-content { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
    .category-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 6px; }
    
    .product-title-link { text-decoration: none; color: inherit; }
    .product-title { font-size: 16px; font-weight: 500; color: var(--text-primary); margin-bottom: 16px; line-height: 1.4; }
    .product-title-link:hover .product-title { color: var(--text-light, #fff); }

    .card-footer { margin-top: auto; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 12px; }
    .price { font-size: 15px; font-weight: 600; color: var(--text-primary); }
    
    .btn-add-cart {
        background: var(--text-primary);
        color: var(--bg-main);
        border: none;
        padding: 8px 14px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.15s ease;
    }
    .btn-add-cart:hover { opacity: 0.85; }
</style>

<section class="hero-section">
    <h1>Scale Models</h1>
    <p>Precision-engineered diecast and resin miniatures for collector purists.</p>
</section>

<!-- Search & Filter Controls -->
<div class="filter-bar">
    <form method="GET" action="index.php" class="filter-form">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search models (e.g., Porsche, GT3)..." class="filter-input">
        
        <select name="scale" class="filter-select">
            <option value="">All Scales</option>
            <option value="1:18" <?= $scale === '1:18' ? 'selected' : '' ?>>1:18 Scale</option>
            <option value="1:43" <?= $scale === '1:43' ? 'selected' : '' ?>>1:43 Scale</option>
            <option value="1:64" <?= $scale === '1:64' ? 'selected' : '' ?>>1:64 Scale</option>
        </select>

        <select name="category" class="filter-select">
            <option value="">All Categories</option>
            <?php foreach ($all_categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn-filter">Filter</button>

        <?php if (!empty($search) || !empty($scale) || !empty($category)): ?>
            <a href="index.php" class="btn-reset">Reset Filters</a>
        <?php endif; ?>
    </form>
</div>

<main class="catalog-container">
    <?php if (empty($products)): ?>
        <p style="color: var(--text-muted);">No scale models matched your query.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <!-- Image click goes to product.php -->
                    <a href="product.php?id=<?= $product['id'] ?>" class="image-wrapper">
                        <span class="badge-scale"><?= htmlspecialchars($product['scale'] ?? '1:18') ?></span>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" loading="lazy">
                    </a>

                    <div class="card-content">
                        <span class="category-label"><?= htmlspecialchars($product['category_name'] ?? 'Scale Model') ?></span>
                        
                        <!-- Title click goes to product.php -->
                        <a href="product.php?id=<?= $product['id'] ?>" class="product-title-link">
                            <h2 class="product-title"><?= htmlspecialchars($product['title']) ?></h2>
                        </a>

                        <div class="card-footer">
                            <span class="price"><?= format_price($product['price']) ?></span>
                            
                            <!-- Add to Cart Form -->
                            <form action="cart.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-add-cart">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>