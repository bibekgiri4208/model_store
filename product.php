<?php
session_start();
require_once 'config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

$img_src = !empty($product['image_url']) ? $product['image_url'] : $product['image'];
if (empty($img_src) || $img_src === 'placeholder.jpg') {
    $img_src = 'assets/images/placeholder.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> - Apex Replica Store</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; margin: 0; padding: 20px; min-height: 100vh; }
        .container { max-width: 1100px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 18px 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 25px; }
        header h1 a { color: #38bdf8; text-decoration: none; font-size: 24px; font-weight: bold; }
        header nav a { color: #94a3b8; text-decoration: none; font-weight: 600; margin-left: 20px; }
        header nav a:hover { color: #38bdf8; }

        .product-detail-card { background: #1e293b; border-radius: 12px; border: 1px solid #334155; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; padding: 30px; }
        @media (max-width: 768px) { .product-detail-card { grid-template-columns: 1fr; } }
        .product-media img { width: 100%; height: 380px; object-fit: cover; border-radius: 8px; border: 1px solid #334155; }
        .badge { display: inline-block; background: #0284c7; color: #fff; font-size: 11px; text-transform: uppercase; padding: 4px 10px; border-radius: 6px; font-weight: bold; margin-bottom: 12px; }
        .product-title { font-size: 28px; margin: 0 0 10px 0; color: #f8fafc; }
        .product-price { font-size: 28px; color: #4ade80; font-weight: bold; margin-bottom: 15px; }
        .product-meta { color: #94a3b8; font-size: 14px; margin-bottom: 20px; line-height: 1.6; }
        .product-desc { background: #0f172a; padding: 16px; border-radius: 8px; border: 1px solid #334155; color: #cbd5e1; margin-bottom: 25px; line-height: 1.6; }
        
        .add-cart-form { display: flex; gap: 12px; }
        .add-cart-form input[type="number"] { width: 80px; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 12px; border-radius: 8px; text-align: center; font-size: 16px; }
        .add-cart-btn { flex: 1; background: #38bdf8; color: #0f172a; border: none; padding: 12px; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; transition: background 0.2s; }
        .add-cart-btn:hover { background: #7dd3fc; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1><a href="index.php">Apex Replica Store</a></h1>
        <nav>
            <a href="cart.php">🛒 Cart</a>
            <a href="my-orders.php">📦 My Orders</a>
        </nav>
    </header>

    <div class="product-detail-card">
        <div class="product-media">
            <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
        </div>
        <div class="product-info-panel">
            <span class="badge"><?= htmlspecialchars($product['category_name'] ?? 'Scale Replica') ?></span>
            <h1 class="product-title"><?= htmlspecialchars($product['title']) ?></h1>
            <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
            <div class="product-meta">
                <strong>Scale:</strong> <?= htmlspecialchars($product['scale'] ?? 'N/A') ?><br>
                <strong>Type:</strong> <?= htmlspecialchars($product['type'] ?? 'Diecast') ?><br>
                <strong>In Stock:</strong> <?= (int)$product['stock'] ?> units
            </div>
            <div class="product-desc">
                <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?>
            </div>
            <form action="cart.php" method="POST" class="add-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                <button type="submit" class="add-cart-btn">Add to Cart</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>