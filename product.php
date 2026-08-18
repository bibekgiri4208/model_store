<?php
require_once 'config/db.php';

// Handle Add to Cart form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    header('Location: cart.php');
    exit;
}

include 'includes/header.php';

$product_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<main style='max-width: 1200px; margin: 80px auto; padding: 0 24px;'><p style='color: var(--text-muted);'>Product not found.</p><a href='index.php' style='color: var(--text-primary);'>&larr; Back to Catalog</a></main>";
    exit;
}
?>

<style>
    .product-detail-container { max-width: 1100px; margin: 40px auto 96px; padding: 0 24px; }
    .btn-back { display: inline-block; color: var(--text-muted); text-decoration: none; font-size: 13px; margin-bottom: 32px; transition: color 0.15s ease; }
    .btn-back:hover { color: var(--text-primary); }

    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: start; }
    
    .media-box { background: #000; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; position: relative; aspect-ratio: 4/3; }
    .media-box img { width: 100%; height: 100%; object-fit: cover; }
    
    .info-box { display: flex; flex-direction: column; }
    .spec-badges { display: flex; gap: 8px; margin-bottom: 12px; }
    .badge { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-muted); font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 500; text-transform: uppercase; }
    
    .title { font-size: 28px; font-weight: 600; color: var(--text-primary); margin-bottom: 12px; line-height: 1.2; }
    .price { font-size: 22px; font-weight: 600; color: var(--text-primary); margin-bottom: 24px; }
    .description { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 32px; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 20px 0; }
    
    .action-form { display: flex; gap: 16px; align-items: center; }
    .qty-input { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 6px; width: 70px; font-size: 14px; text-align: center; }
    .btn-add { background: var(--text-primary); color: var(--bg-main); border: none; padding: 12px 28px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; flex-grow: 1; transition: opacity 0.15s ease; }
    .btn-add:hover { opacity: 0.9; }
    .btn-disabled { background: var(--border-color); color: var(--text-muted); cursor: not-allowed; }
</style>

<main class="product-detail-container">
    <a href="index.php" class="btn-back">&larr; Back to Catalog</a>

    <div class="detail-grid">
        <div class="media-box">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
        </div>

        <div class="info-box">
            <div class="spec-badges">
                <span class="badge"><?= htmlspecialchars($product['scale'] ?? '1:18') ?></span>
                <span class="badge"><?= htmlspecialchars($product['type'] ?? 'Diecast') ?></span>
                <span class="badge"><?= htmlspecialchars($product['category_name'] ?? 'General') ?></span>
            </div>

            <h1 class="title"><?= htmlspecialchars($product['title']) ?></h1>
            <div class="price"><?= format_price($product['price']) ?></div>

            <div class="description">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <form method="POST" class="action-form">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
                    <button type="submit" class="btn-add">Add to Cart</button>
                </form>
            <?php else: ?>
                <button class="btn-add btn-disabled" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
</main>

</body>
</html>