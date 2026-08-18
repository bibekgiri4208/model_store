<?php
require_once 'config/db.php';
include 'includes/header.php';

// Initialize session cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. Handle Cart Actions (Add, Update, Remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD ITEM TO CART
    if ($action === 'add') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));

        if ($product_id > 0) {
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if ($product) {
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product['id'],
                        'title' => $product['title'],
                        'price' => (float)$product['price'],
                        'scale' => $product['scale'] ?? 'N/A',
                        'image_url' => $product['image_url'],
                        'category_name' => $product['category_name'] ?? 'Scale Model',
                        'quantity' => $quantity
                    ];
                }
            }
        }
        header('Location: cart.php');
        exit;
    }

    // UPDATE QUANTITY
    if ($action === 'update') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
        header('Location: cart.php');
        exit;
    }

    // REMOVE ITEM
    if ($action === 'remove') {
        $product_id = intval($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        header('Location: cart.php');
        exit;
    }
}

// Calculate grand total
$grand_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}
?>

<style>
    .cart-wrapper { max-width: 1000px; margin: 40px auto 96px; padding: 0 24px; display: grid; grid-template-columns: 1fr 340px; gap: 40px; }
    @media (max-width: 850px) { .cart-wrapper { grid-template-columns: 1fr; } }

    .section-title { font-size: 22px; font-weight: 500; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; }

    /* Cart Table / Items */
    .cart-table { width: 100%; border-collapse: collapse; }
    .cart-table th { text-align: left; font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; padding-bottom: 16px; border-bottom: 1px solid var(--border-color); }
    .cart-item-row { border-bottom: 1px solid var(--border-color); }
    .cart-item-row td { padding: 20px 0; vertical-align: middle; }

    .item-info { display: flex; gap: 16px; align-items: center; }
    .item-info img { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color); background: #000; }
    .item-title { font-size: 15px; font-weight: 500; margin: 0 0 4px 0; }
    .item-meta { font-size: 12px; color: var(--text-muted); margin: 0; }

    .qty-form { display: flex; align-items: center; gap: 8px; }
    .qty-input { width: 54px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 6px 8px; border-radius: 4px; font-size: 14px; text-align: center; }
    .btn-update { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 12px; text-decoration: underline; padding: 0; }
    .btn-update:hover { color: var(--text-primary); }

    .btn-remove { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 13px; opacity: 0.8; transition: opacity 0.15s ease; }
    .btn-remove:hover { opacity: 1; }

    /* Summary Sidebar */
    .summary-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 24px; height: fit-content; }
    .summary-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 14px; color: var(--text-muted); }
    .summary-row.total { border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: 16px; font-size: 16px; font-weight: 600; color: var(--text-primary); }

    .btn-checkout { display: block; width: 100%; background: var(--text-primary); color: var(--bg-main); border: none; padding: 14px; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; margin-top: 20px; box-sizing: border-box; transition: opacity 0.2s ease; }
    .btn-checkout:hover { opacity: 0.9; }

    .empty-cart { text-align: center; padding: 64px 0; }
    .empty-cart p { color: var(--text-muted); font-size: 15px; margin-bottom: 20px; }
    .btn-shop { display: inline-block; background: var(--text-primary); color: var(--bg-main); padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; }
</style>

<div class="cart-wrapper">
    <?php if (empty($_SESSION['cart'])): ?>
        <div style="grid-column: 1 / -1;">
            <h2 class="section-title">Shopping Cart</h2>
            <div class="empty-cart">
                <p>Your shopping cart is currently empty.</p>
                <a href="index.php" class="btn-shop">Explore Scale Models</a>
            </div>
        </div>
    <?php else: ?>
        <div>
            <h2 class="section-title">Shopping Cart (<?= count($_SESSION['cart']) ?>)</h2>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $id => $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                        <tr class="cart-item-row">
                            <td>
                                <div class="item-info">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                    <div>
                                        <h4 class="item-title"><?= htmlspecialchars($item['title']) ?></h4>
                                        <p class="item-meta">Scale: <?= htmlspecialchars($item['scale']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 14px;"><?= format_price($item['price']) ?></td>
                            <td>
                                <form method="POST" action="cart.php" class="qty-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                    <button type="submit" class="btn-update">Update</button>
                                </form>
                            </td>
                            <td style="font-size: 14px; font-weight: 600;"><?= format_price($subtotal) ?></td>
                            <td>
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <button type="submit" class="btn-remove" title="Remove item">&times; Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Order Summary Sidebar & Checkout Form Binding -->
        <div class="summary-card">
            <h3 class="section-title" style="font-size: 16px; margin-bottom: 16px;">Summary</h3>
            
            <div class="summary-row">
                <span>Items Subtotal</span>
                <span><?= format_price($grand_total) ?></span>
            </div>
            <div class="summary-row">
                <span>Estimated Shipping</span>
                <span>Free</span>
            </div>

            <div class="summary-row total">
                <span>Grand Total</span>
                <span><?= format_price($grand_total) ?></span>
            </div>

            <!-- 
                CHECKOUT BINDING:
                If cart contains 1 item, pass its product_id so single-item checkout.php detects it.
                If cart contains multiple items, checkout.php will read $_SESSION['cart'].
            -->
            <?php 
                $cart_keys = array_keys($_SESSION['cart']);
                $first_product_id = reset($cart_keys);
            ?>
            <form action="checkout.php" method="GET">
                <input type="hidden" name="id" value="<?= $first_product_id ?>">
                <button type="submit" class="btn-checkout">Proceed to Checkout</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>