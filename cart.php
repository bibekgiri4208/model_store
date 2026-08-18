<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $p_id = (int)$_POST['product_id'];
    $qty  = (int)($_POST['quantity'] ?? 1);
    $_SESSION['cart'][$p_id] = ($_SESSION['cart'][$p_id] ?? 0) + $qty;
    header("Location: cart.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $p_id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) { unset($_SESSION['cart'][$p_id]); }
        else { $_SESSION['cart'][$p_id] = $qty; }
    }
    header("Location: cart.php"); exit;
}

$cartProducts = [];
$totalPrice = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $cartProducts = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { padding: 20px; background: #f4f6f8; max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        .btn { padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; color: white; border: none; cursor: pointer; }
        .btn-update { background: #0284c7; }
        .btn-checkout { background: #16a34a; }
    </style>
</head>
<body>
<h2>Your Cart</h2>
<p><a href="index.php">← Continue Shopping</a></p>

<?php if (empty($cartProducts)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <form action="cart.php" method="POST">
        <table>
            <thead>
                <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($cartProducts as $p): 
                    $qty = $_SESSION['cart'][$p['id']];
                    $subtotal = $p['price'] * $qty;
                    $totalPrice += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td>$<?= number_format($p['price'], 2) ?></td>
                    <td><input type="number" name="quantities[<?= $p['id'] ?>]" value="<?= $qty ?>" style="width:50px;"></td>
                    <td>$<?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Total: $<?= number_format($totalPrice, 2) ?></h3>
        <button type="submit" name="update_cart" class="btn btn-update">Update Quantities</button>
        <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout →</a>
    </form>
<?php endif; ?>
</body>
</html>