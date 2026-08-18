<?php
require_once 'config/db.php';
include 'includes/header.php';

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}
?>

<style>
    .product-detail-container { max-width: 1000px; margin: 48px auto; padding: 0 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 48px; }
    @media (max-width: 768px) { .product-detail-container { grid-template-columns: 1fr; } }
    .product-detail-image { width: 100%; aspect-ratio: 16/10; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); background: #000; }
    .product-info h1 { font-size: 28px; margin-bottom: 8px; }
    .category-badge { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .price-tag { font-size: 22px; font-weight: 600; margin: 16px 0; }
    .description { color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 24px; }
    .btn-checkout { background: var(--text-primary); color: var(--bg-main); border: none; padding: 14px 28px; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; width: 100%; text-align: center; }
    .btn-checkout:hover { opacity: 0.9; }
</style>

<div class="product-detail-container">
    <div>
        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-detail-image">
    </div>
    <div class="product-info">
        <span class="category-badge"><?= htmlspecialchars($product['category_name'] ?? 'Scale Model') ?></span>
        <h1><?= htmlspecialchars($product['title']) ?></h1>
        <div class="price-tag"><?= format_price($product['price']) ?></div>
        <p class="description"><?= htmlspecialchars($product['description'] ?? 'Precision scale replica.') ?></p>

        <!-- Redirect Form sending product ID via GET -->
        <form action="checkout.php" method="GET">
            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
            <button type="submit" class="btn-checkout">Proceed to Checkout</button>
        </form>
    </div>
</div>

</body>
</html>