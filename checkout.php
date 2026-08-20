<?php
require_once 'config/db.php';

// Require login to check out
if (!isset($_SESSION['user_id'])) {
    $redirect = 'checkout.php';
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect .= '?' . $_SERVER['QUERY_STRING'];
    }
    header('Location: login.php?redirect=' . urlencode($redirect));
    exit;
}

// 1. Initialize session cart if not present
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check for single product checkout OR multi-item cart checkout
$product_id = intval($_REQUEST['id'] ?? $_POST['product_id'] ?? 0);
$cart_items = $_SESSION['cart'];

if ($product_id <= 0 && empty($cart_items)) {
    header('Location: index.php');
    exit;
}

// 2. Fetch and normalize checkout items into a structured array
$items_to_checkout = [];
$grand_total = 0.0;

if ($product_id > 0) {
    // Single product direct checkout flow
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit;
    }

    $items_to_checkout[] = [
        'product_id' => $product['id'],
        'title'      => $product['title'],
        'price'      => (float)$product['price'],
        'quantity'   => 1,
        'image_url'  => $product['image_url']
    ];
    $grand_total = (float)$product['price'];
} else {
    // Session cart checkout flow
    foreach ($cart_items as $id => $item) {
        if (!is_array($item)) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([intval($id)]);
            $p = $stmt->fetch();
            if (!$p) continue;
            $item = [
                'product_id' => $p['id'],
                'title'      => $p['title'],
                'price'      => (float)$p['price'],
                'quantity'   => 1,
                'image_url'  => $p['image_url']
            ];
        } else {
            $item['product_id'] = $item['id'] ?? $id;
        }

        $items_to_checkout[] = $item;
        $grand_total += $item['price'] * $item['quantity'];
    }
}

$error = '';

// 3. Process Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $full_name      = trim($_POST['full_name'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $city           = trim($_POST['city'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'esewa';
    $user_id        = $_SESSION['user_id'] ?? NULL;

    if (empty($full_name) || empty($phone) || empty($address) || empty($city)) {
        $error = "Please fill in all required shipping fields.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert into orders table
            $order_stmt = $pdo->prepare("
                INSERT INTO orders 
                (user_id, full_name, phone, address, city, total_amount, payment_method, payment_status, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'Pending')
            ");
            $order_stmt->execute([
                $user_id,
                $full_name,
                $phone,
                $address,
                $city,
                $grand_total,
                $payment_method
            ]);

            $order_id = $pdo->lastInsertId();

            // Unique transaction UUID
            $transaction_uuid = "ORD-{$order_id}-" . time();

            // Store UUID in order record
            $update_stmt = $pdo->prepare("UPDATE orders SET transaction_uuid = ? WHERE id = ?");
            $update_stmt->execute([$transaction_uuid, $order_id]);

            // Insert items into order_items table
            $item_stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                VALUES (?, ?, ?, ?)
            ");
            foreach ($items_to_checkout as $item) {
                $item_stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            // Verify stock availability and decrement it for each item
            $stock_check = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
            $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            foreach ($items_to_checkout as $item) {
                $stock_check->execute([$item['product_id']]);
                $available = (int)$stock_check->fetchColumn();
                if ($available < (int)$item['quantity']) {
                    throw new Exception("Insufficient stock for one or more items. Please adjust your cart.");
                }
                $stock_stmt->execute([$item['quantity'], $item['product_id']]);
            }

            $pdo->commit();

            // Clear session cart
            unset($_SESSION['cart']);

            // 4. eSewa v2.0 Signature & Redirection Block
            if ($payment_method === 'esewa') {
                // Ensure plain string conversion for amounts
                $amount                  = (string)$grand_total;
                $tax_amount              = "0";
                $product_service_charge  = "0";
                $product_delivery_charge = "0";

                $total_calc              = (float)$amount + (float)$tax_amount + (float)$product_service_charge + (float)$product_delivery_charge;
                $total_amount            = (string)$total_calc;

                // eSewa Sandbox Credentials
                $product_code = "EPAYTEST"; 
                $secret_key   = "8gBm/:&EnhH.1/q"; 

                // Raw parameter string formatted in strict order
                $data_to_hash = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";

                // Base64-encoded HMAC-SHA256 signature
                $raw_hash  = hash_hmac('sha256', $data_to_hash, $secret_key, true);
                $signature = base64_encode($raw_hash);
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Redirecting to eSewa...</title>
                    <link rel="preconnect" href="https://fonts.googleapis.com">
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">
                        <script>
        (function() {
            try {
                var stored = localStorage.getItem('theme');
                var theme = stored || (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
<link rel="stylesheet" href="assets/css/style.css?v=22">
                </head>
                <body class="loader-page">
                    <div class="loader-content">
                        <div class="spinner"></div>
                        <p>Redirecting to eSewa Payment Gateway...</p>
                    </div>

                    <form id="esewa_form" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
                        <input type="hidden" name="amount" value="<?= $amount; ?>" required>
                        <input type="hidden" name="tax_amount" value="<?= $tax_amount; ?>" required>
                        <input type="hidden" name="total_amount" value="<?= $total_amount; ?>" required>
                        <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid; ?>" required>
                        <input type="hidden" name="product_code" value="<?= $product_code; ?>" required>
                        <input type="hidden" name="product_service_charge" value="<?= $product_service_charge; ?>" required>
                        <input type="hidden" name="product_delivery_charge" value="<?= $product_delivery_charge; ?>" required>
                        
                        <!-- Redirecting to order-success.php -->
                        <input type="hidden" name="success_url" value="http://localhost/model_store/order-success.php" required>
                        <input type="hidden" name="failure_url" value="http://localhost/model_store/failure.php" required>
                        
                        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required>
                        <input type="hidden" name="signature" value="<?= $signature; ?>" required>
                    </form>
                    <script>
                        window.onload = function () {
                            document.getElementById('esewa_form').submit();
                        };
                    </script>
                </body>
                </html>
                <?php
                exit;
            } else {
                // Cash on Delivery redirection to order-success.php
                header("Location: order-success.php?transaction_uuid=" . urlencode($transaction_uuid) . "&status=cod");
                exit;
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to place order: " . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="checkout-wrapper">
    <div>
        <?php
        $back_url = 'cart.php';
        $back_label = 'Back to Cart';
        if ($product_id > 0 && empty($cart_items)) {
            $back_url = 'product.php?id=' . $product_id;
            $back_label = 'Back to Product';
        }
        ?>
        <a href="<?= $back_url ?>" class="btn-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <?= $back_label ?>
        </a>

        <h2 class="section-title">Shipping & Payment Details</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="checkout.php">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <input type="hidden" name="place_order" value="1">

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" id="phone" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" class="form-control" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Street Address / Delivery Location *</label>
                <input type="text" id="address" name="address" class="form-control" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>

            <div class="form-group" style="margin-top: 24px;">
                <label>Select Payment Method *</label>
                <div class="payment-options">
                    <label class="payment-card">
                        <input type="radio" name="payment_method" value="esewa" checked>
                        <span>eSewa <span class="badge-esewa">ePay</span></span>
                    </label>

                    <label class="payment-card">
                        <input type="radio" name="payment_method" value="cod">
                        <span>Cash on Delivery</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Pay with eSewa / Place Order</button>
        </form>
    </div>

    <div class="summary-card">
        <h3 class="section-title" style="font-size: 16px;">Order Summary (<?= count($items_to_checkout) ?>)</h3>
        
        <?php foreach ($items_to_checkout as $item): ?>
            <div class="summary-product">
                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                <div>
                    <h4 style="margin: 0 0 4px; font-size: 14px;"><?= htmlspecialchars($item['title']) ?></h4>
                    <p style="margin: 0; font-size: 12px; color: var(--text-muted);">
                        Qty: <?= $item['quantity'] ?> &times; Rs. <?= number_format($item['price'], 2) ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="summary-row"><span>Items Subtotal</span><span>Rs. <?= number_format($grand_total, 2) ?></span></div>
        <div class="summary-row"><span>Shipping</span><span>Free</span></div>
        <div class="summary-row total"><span>Total Amount</span><span>Rs. <?= number_format($grand_total, 2) ?></span></div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
