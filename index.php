<?php
require_once 'config/db.php';
include 'includes/header.php';

// Filter by category slug if set
$cat_slug = $_GET['category'] ?? '';
if ($cat_slug) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.slug = ? ORDER BY p.id DESC");
    $stmt->execute([$cat_slug]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
}
$products = $stmt->fetchAll();
?>

<style>
    .hero-section { padding: 60px 24px 40px; max-width: 1200px; margin: 0 auto; text-align: left; border-bottom: 1px solid var(--border-color); }
    .hero-section h1 { font-size: 32px; font-weight: 500; letter-spacing: -0.02em; margin-bottom: 8px; }
    .hero-section p { color: var(--text-muted); font-size: 15px; max-width: 500px; font-weight: 300; }

    .catalog-container { max-width: 1200px; margin: 0 auto; padding: 48px 24px 96px; }
    
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 32px; }
    
    /* Make the entire card linkable */
    .product-card { 
        background: var(--bg-card); 
        border: 1px solid var(--border-color); 
        border-radius: 8px; 
        overflow: hidden; 
        transition: border-color 0.2s ease, transform 0.2s ease; 
        text-decoration: none; 
        color: inherit; 
        display: flex; 
        flex-direction: column; 
        cursor: pointer;
    }
    .product-card:hover { border-color: var(--border-light); transform: translateY(-3px); }

    .image-wrapper { width: 100%; aspect-ratio: 16 / 10; background: #000; overflow: hidden; position: relative; }
    .image-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; opacity: 0.9; }
    .product-card:hover .image-wrapper img { transform: scale(1.04); opacity: 1; }

    .badge-scale { position: absolute; top: 12px; left: 12px; background: rgba(9, 9, 11, 0.85); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 11px; font-weight: 500; padding: 4px 8px; border-radius: 4px; backdrop-filter: blur(4px); }

    .card-content { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
    .category-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 6px; }
    .product-title { font-size: 16px; font-weight: 500; color: var(--text-primary); margin-bottom: 16px; line-height: 1.4; }
    
    .card-footer { margin-top: auto; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 12px; }
    .price { font-size: 15px; font-weight: 600; color: var(--text-primary); }
    .stock-status { font-size: 12px; color: var(--text-muted); }
</style>

<section class="hero-section">
    <h1>Scale Models</h1>
    <p>Precision-engineered diecast and resin miniatures for collector purists.</p>
</section>

<main class="catalog-container">
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <!-- Entire card wrapped in <a> tag -->
            <a href="product.php?id=<?= $product['id'] ?>" class="product-card">
                <div class="image-wrapper">
                    <span class="badge-scale"><?= htmlspecialchars($product['scale'] ?? '1:18') ?></span>
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" loading="lazy">
                </div>
                <div class="card-content">
                    <span class="category-label"><?= htmlspecialchars($product['category_name'] ?? 'Scale Model') ?></span>
                    <h2 class="product-title"><?= htmlspecialchars($product['title']) ?></h2>
                    <div class="card-footer">
                        <span class="price">$<?= number_format($product['price'], 2) ?></span>
                        <span class="stock-status"><?= $product['stock'] > 0 ? $product['stock'] . ' available' : 'Out of stock' ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>