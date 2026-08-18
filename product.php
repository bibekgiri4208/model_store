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

<div class="product-detail-container">
    <div>
        <a href="index.php" class="btn-back">&larr; Back to Catalog</a>
        <div class="product-image-frame">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-detail-image">
        </div>
    </div>
    <div class="product-info">
        <span class="category-badge"><?= htmlspecialchars($product['category_name'] ?? 'Scale Model') ?></span>
        <h1><?= htmlspecialchars($product['title']) ?></h1>

        <div class="product-meta">
            <span class="meta-chip">Scale: <?= htmlspecialchars($product['scale'] ?? '1:18') ?></span>
            <?php if (!empty($product['type'])): ?>
                <span class="meta-chip"><?= htmlspecialchars($product['type']) ?></span>
            <?php endif; ?>
        </div>

        <div class="price-tag"><?= format_price($product['price']) ?></div>
        <p class="description"><?= htmlspecialchars($product['description'] ?? 'Precision scale replica.') ?></p>

        <?php $in_stock = (int)($product['stock'] ?? 0) > 0; ?>
        <div class="stock-info <?= $in_stock ? 'in-stock' : 'out-of-stock' ?>">
            <?php if ($in_stock): ?>
                In Stock &middot; <?= (int)$product['stock'] ?> available
            <?php else: ?>
                Currently Out of Stock
            <?php endif; ?>
        </div>

        <form action="cart.php" method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn btn-ghost btn-lg btn-block" <?= $in_stock ? '' : 'disabled' ?>>Add to Cart</button>
        </form>

        <form action="checkout.php" method="GET">
            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
            <button type="submit" class="btn btn-primary btn-lg btn-block" <?= $in_stock ? '' : 'disabled' ?>>Buy Now</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
