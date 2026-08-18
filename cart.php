<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add/Update/Remove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($action === 'add') {
        $qty = (int)($_POST['quantity'] ?? 1);
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $qty;
    } elseif ($action === 'update') {
        $qty = (int)($_POST['quantity'] ?? 1);
        if ($qty > 0) $_SESSION['cart'][$product_id] = $qty;
        else unset($_SESSION['cart'][$product_id]);
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    }
    header('Location: cart.php');
    exit;
}

$cart_items = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $in_clause = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in_clause)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $total_amount += $subtotal;
        $cart_items[] = [
            'product' => $p,
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Apex Replica Store</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; margin: 0; padding: 20px; min-height: 100vh; }
        .container { max-width: 1000px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 18px 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 25px; }
        header h1 a { color: #38bdf8; text-decoration: none; font-size: 24px; font-weight: bold; }
        header nav a { color: #94a3b8; text-decoration: none; font-weight: 600; margin-left: 20px; }

        .cart-table-card { background: #1e293b; border-radius: 12px; border: 1px solid #334155; overflow: hidden; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #0f172a; padding: 16px; color: #94a3b8; border-bottom: 1px solid #334155; font-size: 14px; }
        td { padding: 16px; border-bottom: 1px solid #334155; }
        
        .qty-input { width: 60px; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 6px; border-radius: 6px; text-align: center; }
        .update-btn { background: #334155; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
        .remove-btn { background: #ef4444; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }

        .cart-summary { background: #1e293b; padding: 25px; border-radius: 12px; border: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; }
        .total-price { font-size: 24px; font-weight: bold; color: #4ade80; }
        .checkout-btn { background: #4ade80; color: #0f172a; padding: 12px 28px; border-radius: 8px; font-weight: bold; text-decoration: none; }
        .empty-msg { text-align: center; padding: 40px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1><a href="index.php">Apex Replica Store</a></h1>
        <nav><a href="index.php">← Back to Shop</a></nav>
    </header>

    <div class="cart-table-card">
        <?php if (empty($cart_items)): ?>
            <div class="empty-msg">
                <h2>Your cart is empty</h2>
                <a href="index.php" style="color:#38bdf8;">Browse die-cast models</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['product']['title']) ?></strong></td>
                            <td>$<?= number_format($item['product']['price'], 2) ?></td>
                            <td>
                                <form action="cart.php" method="POST" style="display:inline-flex; gap:6px;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                    <button type="submit" class="update-btn">Save</button>
                                </form>
                            </td>
                            <td>$<?= number_format($item['subtotal'], 2) ?></td>
                            <td>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                    <button type="submit" class="remove-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($cart_items)): ?>
        <div class="cart-summary">
            <div>
                <span style="color:#94a3b8;">Total Amount:</span>
                <div class="total-price">$<?= number_format($total_amount, 2) ?></div>
            </div>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout →</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>