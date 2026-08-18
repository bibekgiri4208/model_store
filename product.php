<?php
session_start();
require_once 'config/db.php';

// 1. Get and sanitize product ID from URL
$product_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}

// 2. Fetch product details along with category name
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// If product doesn't exist, redirect to index
if (!$product) {
    header("Location: index.php");
    exit;
}

// 3. Handle Add to Cart form submission with quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($qty < 1) $qty = 1;

    // Initialize cart session if not set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update product quantity in session cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }

    $success_message = "Added {$qty} unit(s) to your shopping cart!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> - Model Store</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { padding: 20px; background: #f4f6f8; color: #333; max-width: 1000px; margin: auto; }
        
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; color: #fff; padding: 15px 25px; border-radius: 8px; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 20px; }
        header a { color: #38bdf8; text-decoration: none; font-weight: bold; margin-left: 15px; }

        .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }

        /* Product Detail Layout */
        .product-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        @media (max-width: 768px) { .product-container { grid-template-columns: 1fr; } }

        .product-gallery img { width: 100%; height: 350px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; }
        
        .product-details { display: flex; flex-direction: column; }
        .category-badge { font-size: 12px; text-transform: uppercase; color: #0284c7; font-weight: bold; letter-spacing: 0.5px; }
        .product-title { font-size: 26px; margin: 10px 0; color: #0f172a; }
        .product-price { font-size: 24px; color: #166534; font-weight: bold; margin-bottom: 20px; }
        .product-description { font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 25px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; padding: 15px 0; }

        /* Purchase Controls */
        .cart-form { display: flex; gap: 10px; align-items: center; margin-top: auto; }
        .cart-form label { font-weight: bold; font-size: 14px; }
        .cart-form input[type="number"] { width: 70px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px; text-align: center; }
        .cart-btn { flex-grow: 1; background: #0f172a; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; font-size: 15px; cursor: pointer; }
        .cart-btn:hover { background: #1e293b; }
    </style>
</head>
<body>

<header>
    <h1>Model Car Store</h1>
    <div>
        <a href="index.php">← Back to Catalog</a>
        <a href="cart.php">🛒 Cart</a>
    </div>
</header>

<?php if (isset($success_message)): ?>
    <div class="alert-success">
        <?= htmlspecialchars($success_message) ?> <a href="cart.php" style="color: #15803d; text-decoration: underline;">View Shopping Cart</a>
    </div>
<?php endif; ?>

<div class="product-container">
    <div class="product-gallery">
        <img 
            src="assets/images/<?= htmlspecialchars($product['image'] ?: 'placeholder.jpg') ?>" 
            alt="<?= htmlspecialchars($product['title']) ?>"
        >
    </div>

    <div class="product-details">
        <span class="category-badge"><?= htmlspecialchars($product['category_name'] ?: 'Uncategorized') ?></span>
        <h2 class="product-title"><?= htmlspecialchars($product['title']) ?></h2>
        <div class="product-price">$<?= number_format($product['price'], 2) ?></div>

        <div class="product-description">
            <?= nl2br(htmlspecialchars($product['description'] ?: 'No detailed description available for this model car.')) ?>
        </div>

        <form action="product.php?id=<?= $product['id'] ?>" method="POST" class="cart-form">
            <label for="quantity">Qty:</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" required>
            <button type="submit" name="add_to_cart" class="cart-btn">Add to Cart</button>
        </form>
    </div>
</div>

</body>
</html>