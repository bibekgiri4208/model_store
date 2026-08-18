<?php
require_once 'config/db.php';
include 'includes/header.php';

$product_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}
?>

<style>
    .product-detail-container { max-width: 1100px; margin: 0 auto; padding: 60px 24px 96px; display: grid; grid-template-columns: 1fr 1fr; gap: 48px; }
    @media (max-width: 768px) { .product-detail-container { grid-template-columns: 1fr; gap: 32px; padding-top: 32px; } }

    .gallery-viewer { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; aspect-ratio: 4 / 3; width: 100%; }
    .gallery-viewer img { width: 100%; height: 100%; object-fit: cover; }

    .details-meta { display: flex; flex-direction: column; }
    .category-tag { font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 8px; }
    .title { font-size: 28px; font-weight: 500; letter-spacing: -0.01em; margin-bottom: 16px; color: var(--text-primary); }
    .price-row { font-size: 22px; font-weight: 600; color: var(--text-primary); margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; }
    
    .spec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; background: var(--bg-card); border: 1px solid var(--border-color); padding: 16px; border-radius: 6px; }
    .spec-item label { display: block; font-size: 11px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; }
    .spec-item span { font-size: 14px; font-weight: 500; color: var(--text-primary); }

    .description { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 32px; }

    .btn-primary { background: var(--text-primary); color: var(--bg-main); border: none; padding: 14px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.15s ease; text-decoration: none; text-align: center; }
    .btn-primary:hover { opacity: 0.9; }
</style>

<main class="product-detail-container">
    <div class="gallery-viewer">
        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
    </div>

    <div class="details-meta">
        <span class="category-tag"><?= htmlspecialchars($product['category_name'] ?? 'Collector Model') ?></span>
        <h1 class="title"><?= htmlspecialchars($product['title']) ?></h1>
        <div class="price-row">$<?= number_format($product['price'], 2) ?></div>

        <div class="spec-grid">
            <div class="spec-item">
                <label>Scale</label>
                <span><?= htmlspecialchars($product['scale'] ?? '1:18') ?></span>
            </div>
            <div class="spec-item">
                <label>Material</label>
                <span><?= htmlspecialchars($product['type'] ?? 'Diecast') ?></span>
            </div>
            <div class="spec-item">
                <label>Availability</label>
                <span><?= $product['stock'] > 0 ? $product['stock'] . ' In Stock' : 'Out of Stock' ?></span>
            </div>
            <div class="spec-item">
                <label>Product ID</label>
                <span>#<?= $product['id'] ?></span>
            </div>
        </div>

        <p class="description"><?= htmlspecialchars($product['description']) ?></p>

        <?php if ($product['stock'] > 0): ?>
            <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" name="add_to_cart" class="btn-primary" style="width: 100%;">Add to Cart</button>
            </form>
        <?php else: ?>
            <button class="btn-primary" disabled style="opacity: 0.4; cursor: not-allowed; width: 100%;">Out of Stock</button>
        <?php endif; ?>
    </div>
</main>
</body>
</html>