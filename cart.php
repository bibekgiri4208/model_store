<?php
require_once 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Cart Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $id = (int)$_POST['product_id'];
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    } elseif (isset($_POST['update_cart'])) {
        foreach ($_POST['qty'] as $id => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
        }
    }
    header('Location: cart.php');
    exit;
}

if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header('Location: cart.php');
    exit;
}

// Fetch Cart Products
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $item_total = $p['price'] * $qty;
        $subtotal += $item_total;
        $cart_items[] = [
            'product' => $p,
            'qty' => $qty,
            'item_total' => $item_total
        ];
    }
}
?>

<style>
    .cart-container { max-width: 900px; margin: 0 auto; padding: 60px 24px 96px; }
    .cart-title { font-size: 24px; font-weight: 500; margin-bottom: 32px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; }
    
    .cart-table { width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 32px; }
    .cart-table th { font-size: 12px; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-color); padding: 12px 0; font-weight: 500; }
    .cart-table td { padding: 20px 0; border-bottom: 1px solid var(--border-color); font-size: 14px; vertical-align: middle; }

    .item-cell { display: flex; align-items: center; gap: 16px; }
    .item-thumb { width: 60px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color); background: #000; }
    
    .qty-input { width: 60px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 6px; border-radius: 4px; text-align: center; }
    .remove-link { color: var(--text-muted); text-decoration: none; font-size: 12px; transition: color 0.15s; }
    .remove-link:hover { color: #ef4444; }

    .cart-summary { background: var(--bg-card); border: 1px solid var(--border-color); padding: 24px; border-radius: 6px; max-width: 360px; margin-left: auto; }
    .summary-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 12px; color: var(--text-muted); }
    .summary-row.total { font-size: 16px; font-weight: 600; color: var(--text-primary); border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 12px; }

    .btn-action { display: block; width: 100%; background: var(--text-primary); color: var(--bg-main); border: none; padding: 12px; border-radius: 6px; font-weight: 600; text-align: center; text-decoration: none; font-size: 14px; margin-top: 16px; cursor: pointer; }
    .btn-secondary { background: transparent; color: var(--text-primary); border: 1px solid var(--border-color); margin-top: 8px; }
</style>

<main class="cart-container">
    <h1 class="cart-title">Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
        <p style="color: var(--text-muted); margin-bottom: 24px;">Your cart is currently empty.</p>
        <a href="index.php" class="btn-action" style="display: inline-block; width: auto; padding: 10px 20px;">Explore Catalog</a>
    <?php else: ?>
        <form action="cart.php" method="POST">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="item-cell">
                                    <img src="<?= htmlspecialchars($item['product']['image_url']) ?>" class="item-thumb" alt="">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($item['product']['title']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($item['product']['scale']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>$<?= number_format($item['product']['price'], 2) ?></td>
                            <td>
                                <input type="number" name="qty[<?= $item['product']['id'] ?>]" value="<?= $item['qty'] ?>" min="1" class="qty-input">
                            </td>
                            <td>$<?= number_format($item['item_total'], 2) ?></td>
                            <td><a href="cart.php?remove=<?= $item['product']['id'] ?>" class="remove-link">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <button type="submit" name="update_cart" class="btn-action btn-secondary" style="width: auto; padding: 10px 20px; margin: 0;">Update Quantities</button>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <a href="checkout.php" class="btn-action">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</main>
</body>
</html>