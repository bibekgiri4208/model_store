<?php
require_once 'config/db.php';

// Handle Cart Actions (Update / Remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $product_id = (int)$_POST['product_id'];

    if ($_POST['action'] === 'update') {
        $qty = (int)$_POST['quantity'];
        if ($qty > 0) {
            $_SESSION['cart'][$product_id] = $qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    } elseif ($_POST['action'] === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    }

    header('Location: cart.php');
    exit;
}

include 'includes/header.php';

$cart_items = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $qty = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $qty;
        $total_amount += $subtotal;

        $cart_items[] = [
            'id' => $product['id'],
            'title' => $product['title'],
            'image_url' => $product['image_url'],
            'scale' => $product['scale'],
            'price' => $product['price'],
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>

<style>
    .cart-container { max-width: 900px; margin: 40px auto 96px; padding: 0 24px; }
    .page-title { font-size: 24px; font-weight: 500; margin-bottom: 32px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; }

    .cart-table { width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 32px; }
    .cart-table th { font-size: 12px; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-color); padding: 12px 0; font-weight: 500; }
    .cart-table td { padding: 20px 0; border-bottom: 1px solid var(--border-color); vertical-align: middle; }

    .item-cell { display: flex; align-items: center; gap: 16px; }
    .item-thumb { width: 60px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color); background: #000; }

    .qty-form { display: flex; align-items: center; gap: 8px; }
    .qty-input { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 6px; border-radius: 4px; width: 50px; text-align: center; font-size: 13px; }
    .btn-update { background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); padding: 6px 10px; border-radius: 4px; font-size: 12px; cursor: pointer; }
    .btn-update:hover { color: var(--text-primary); border-color: var(--border-light); }
    
    .btn-remove { background: transparent; border: none; color: #ef4444; font-size: 12px; cursor: pointer; margin-left: 12px; }
    .btn-remove:hover { text-decoration: underline; }

    .cart-summary { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 24px; display: flex; justify-content: space-between; align-items: center; }
    .grand-total { font-size: 18px; font-weight: 600; }
    
    .btn-checkout { background: var(--text-primary); color: var(--bg-main); padding: 12px 28px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; transition: opacity 0.15s ease; }
    .btn-checkout:hover { opacity: 0.9; }
</style>

<main class="cart-container">
    <h1 class="page-title">Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
        <p style="color: var(--text-muted); margin-bottom: 16px;">Your cart is currently empty.</p>
        <a href="index.php" style="color: var(--text-primary); font-size: 14px;">&larr; Return to Catalog</a>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <div class="item-cell">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" class="item-thumb" alt="">
                                <div>
                                    <div style="font-weight: 500; font-size: 14px;"><?= htmlspecialchars($item['title']) ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($item['scale']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size: 14px;"><?= format_price($item['price']) ?></td>
                        <td>
                            <form method="POST" class="qty-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                <button type="submit" class="btn-update">Update</button>
                            </form>
                        </td>
                        <td style="text-align: right; font-weight: 500; font-size: 14px;">
                            <?= format_price($item['subtotal']) ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-remove">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div class="grand-total">
                Total: <?= format_price($total_amount) ?>
            </div>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</main>

</body>
</html>