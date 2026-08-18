<?php
session_start();
require_once 'config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { die("Product not found."); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['title']) ?> - Details</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 20px; background: #f4f6f8; }
        .card { background: white; max-width: 800px; margin: auto; padding: 25px; border-radius: 8px; display: flex; gap: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card img { width: 350px; height: 280px; object-fit: cover; border-radius: 6px; }
        .details { flex: 1; }
        .price { font-size: 24px; color: #16a34a; font-weight: bold; margin: 15px 0; }
        .btn { background: #16a34a; color: white; border: none; padding: 10px 20px; font-weight: bold; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<p><a href="index.php">← Back to Store</a></p>
<div class="card">
    <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/350') ?>" alt="Product">
    <div class="details">
        <h2><?= htmlspecialchars($product['title']) ?></h2>
        <p><strong>Scale:</strong> <?= htmlspecialchars($product['scale']) ?> | <strong>Type:</strong> <?= htmlspecialchars($product['type']) ?></p>
        <p><?= htmlspecialchars($product['description']) ?></p>
        <div class="price">$<?= number_format($product['price'], 2) ?></div>
        
        <?php if ($product['stock'] > 0): ?>
            <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <label>Qty:</label>
                <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" style="width: 60px; padding: 5px;">
                <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
            </form>
        <?php else: ?>
            <p style="color: red; font-weight: bold;">Currently Out of Stock</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>