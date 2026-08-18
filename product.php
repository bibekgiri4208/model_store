<?php
session_start();
require_once 'config/db.php';

$product_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { header("Location: index.php"); exit; }

// Image Fallback
$img_src = $product['image'];
if (empty($img_src) || $img_src === 'placeholder.jpg') {
    $img_src = "https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=1000&q=80";
} elseif (!str_starts_with($img_src, 'http')) {
    $img_src = 'assets/images/' . $img_src;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $qty = max(1, (int)$_POST['quantity']);
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $qty;
    $success = "Added {$qty} unit(s) to your cart!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['title']) ?> - Apex Replica</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; padding: 20px; margin: 0; }
        .container { max-width: 1000px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 18px 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 20px; color: #38bdf8; }
        header a { color: #94a3b8; text-decoration: none; font-weight: 600; }
        
        .alert { background: #064e3b; color: #6ee7b7; border: 1px solid #047857; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        
        .product-card { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; background: #1e293b; padding: 30px; border-radius: 12px; border: 1px solid #334155; }
        @media (max-width: 768px) { .product-card { grid-template-columns: 1fr; } }
        
        .product-card img { width: 100%; height: 380px; object-fit: cover; border-radius: 8px; background: #0f172a; }
        .badge { color: #38bdf8; font-weight: bold; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        .price { font-size: 28px; color: #4ade80; font-weight: bold; margin: 15px 0; }
        .desc { color: #94a3b8; line-height: 1.6; border-top: 1px solid #334155; border-bottom: 1px solid #334155; padding: 15px 0; margin-bottom: 25px; }
        
        .cart-form { display: flex; gap: 10px; align-items: center; }
        .cart-form input { width: 70px; background: #0f172a; border: 1px solid #334155; color: white; padding: 12px; border-radius: 8px; text-align: center; }
        .cart-form button { flex-grow: 1; background: #38bdf8; color: #0f172a; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>🏎️ Apex Replica</h1>
        <div><a href="index.php">← Back to Catalog</a> | <a href="cart.php">🛒 Cart</a></div>
    </header>

    <?php if (isset($success)): ?>
        <div class="alert"><?= htmlspecialchars($success) ?> <a href="cart.php" style="color: #6ee7b7;">View Cart</a></div>
    <?php endif; ?>

    <div class="product-card">
        <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
        <div>
            <span class="badge"><?= htmlspecialchars($product['category_name'] ?: 'Scale Model') ?></span>
            <h2 style="margin: 10px 0; font-size: 28px;"><?= htmlspecialchars($product['title']) ?></h2>
            <div class="price">$<?= number_format($product['price'], 2) ?></div>
            <div class="desc"><?= nl2br(htmlspecialchars($product['description'] ?: 'High precision die-cast scale model car.')) ?></div>

            <form action="product.php?id=<?= $product['id'] ?>" method="POST" class="cart-form">
                <input type="number" name="quantity" value="1" min="1" max="10">
                <button type="submit" name="add_to_cart">Add to Cart</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>