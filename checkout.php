<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php"); exit;
}
if (empty($_SESSION['cart'])) {
    header("Location: cart.php"); exit;
}

$error = '';
$cart_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($cart_ids);
$products = $stmt->fetchAll();

$total_amount = 0;
foreach ($products as $p) {
    $total_amount += $p['price'] * $_SESSION['cart'][$p['id']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address = trim($_POST['shipping_address']);

    if (empty($address)) {
        $error = "Shipping address is required.";
    } else {
        try {
            $pdo->beginTransaction();

            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)");
            $orderStmt->execute([$_SESSION['user_id'], $total_amount, $address]);
            $order_id = $pdo->lastInsertId();

            $itemStmt  = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($products as $p) {
                $qty = $_SESSION['cart'][$p['id']];
                $itemStmt->execute([$order_id, $p['id'], $qty, $p['price']]);
                
                $stockStmt->execute([$qty, $p['id'], $qty]);
                if ($stockStmt->rowCount() === 0) {
                    throw new Exception("Not enough stock for " . $p['title']);
                }
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            header("Location: order-success.php?id=" . $order_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { padding: 20px; background: #f4f6f8; max-width: 600px; margin: auto; }
        .box { background: white; padding: 20px; border-radius: 8px; }
        .btn { background: #16a34a; color: white; border: none; padding: 12px; width: 100%; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<div class="box">
    <h2>Order Checkout</h2>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <p><strong>Total Amount:</strong> $<?= number_format($total_amount, 2) ?></p>
    <form action="checkout.php" method="POST">
        <label><strong>Shipping Address:</strong></label><br>
        <textarea name="shipping_address" style="width: 100%; height: 80px; margin: 10px 0;" required></textarea>
        <button type="submit" name="place_order" class="btn">Place Order</button>
    </form>
</div>
</body>
</html>