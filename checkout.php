<?php
require_once 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

// Fetch Cart Total
$ids = implode(',', array_keys($_SESSION['cart']));
$stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
$products = $stmt->fetchAll();

$total_amount = 0;
foreach ($products as $p) {
    $total_amount += $p['price'] * $_SESSION['cart'][$p['id']];
}

$success_order_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    
    if (!empty($shipping_address)) {
        $pdo->beginTransaction();
        try {
            // Create Order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Pending')");
            $stmt->execute([$_SESSION['user_id'], $total_amount]);
            $order_id = $pdo->lastInsertId();

            // Insert Items
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($products as $p) {
                $qty = $_SESSION['cart'][$p['id']];
                $item_stmt->execute([$order_id, $p['id'], $qty, $p['price']]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            $success_order_id = $order_id;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to process order. Please try again.";
        }
    } else {
        $error = "Please provide a shipping address.";
    }
}
?>

<style>
    .checkout-container { max-width: 600px; margin: 0 auto; padding: 60px 24px 96px; }
    .checkout-card { background: var(--bg-card); border: 1px solid var(--border-color); padding: 32px; border-radius: 8px; }
    .title { font-size: 20px; font-weight: 500; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; }
    
    .form-group { margin-bottom: 20px; }
    label { display: block; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 8px; }
    textarea { width: 100%; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 6px; font-size: 14px; outline: none; height: 100px; resize: vertical; }
    
    .summary-box { background: var(--bg-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 6px; margin-bottom: 24px; font-size: 14px; color: var(--text-muted); }
    .summary-box div { display: flex; justify-content: space-between; margin-bottom: 8px; }
    .summary-box div.total { color: var(--text-primary); font-weight: 600; font-size: 16px; border-top: 1px solid var(--border-color); padding-top: 8px; margin-bottom: 0; }

    .btn-submit { width: 100%; background: var(--text-primary); color: var(--bg-main); border: none; padding: 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; }
    .success-box { text-align: center; padding: 32px 0; }
    .success-box h2 { font-weight: 500; margin-bottom: 12px; }
    .success-box p { color: var(--text-muted); font-size: 14px; margin-bottom: 24px; }
</style>

<main class="checkout-container">
    <div class="checkout-card">
        <?php if ($success_order_id): ?>
            <div class="success-box">
                <h2>Order Confirmed</h2>
                <p>Your order <strong>#<?= $success_order_id ?></strong> has been successfully placed.</p>
                <a href="index.php" class="btn-submit" style="display: inline-block; text-decoration: none; width: auto; padding: 10px 24px;">Return to Catalog</a>
            </div>
        <?php else: ?>
            <h1 class="title">Checkout</h1>

            <?php if (isset($error)): ?>
                <div style="color: #ef4444; font-size: 14px; margin-bottom: 16px;"><?= $error ?></div>
            <?php endif; ?>

            <form action="checkout.php" method="POST">
                <div class="summary-box">
                    <div>
                        <span>Items Count</span>
                        <span><?= array_sum($_SESSION['cart']) ?></span>
                    </div>
                    <div class="total">
                        <span>Total Payable</span>
                        <span>$<?= number_format($total_amount, 2) ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Shipping Address</label>
                    <textarea name="shipping_address" required placeholder="Enter street address, city, and postal code"></textarea>
                </div>

                <button type="submit" class="btn-submit">Confirm and Place Order</button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>