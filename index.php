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

<section class="hero-section">
    <span class="hero-eyebrow">Precision Collection</span>
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
        <div class="empty-state">
            <div class="empty-state-icon">&#128269;</div>
            <p>No scale models matched your query.</p>
            <a href="index.php" class="btn btn-primary">Clear Filters</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <!-- Image click goes to product.php -->
                    <a href="product.php?id=<?= $product['id'] ?>" class="image-wrapper">
                        <span class="badge"><?= htmlspecialchars($product['scale'] ?? '1:18') ?></span>
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

<?php include 'includes/footer.php'; ?>
</body>
</html>
