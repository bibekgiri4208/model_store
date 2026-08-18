<?php
require_once 'config/db.php';
include 'includes/header.php';

$order = null;
$message = '';
$is_success = false;

// Secret key for eSewa v2 UAT Environment
$secret_key = "8gBm/:&EnhH.1/q";

// 1. Handle eSewa Return (eSewa v2 sends response encoded in Base64 under 'data' query parameter)
if (isset($_GET['data'])) {
    $encoded_data = $_GET['data'];
    $json_data = base64_decode($encoded_data);
    $response = json_decode($json_data, true);

    if ($response && isset($response['status']) && $response['status'] === 'COMPLETE') {
        $transaction_uuid = $response['transaction_uuid'] ?? '';
        $total_amount     = $response['total_amount'] ?? '';
        $transaction_code = $response['transaction_code'] ?? '';
        $product_code     = $response['product_code'] ?? 'EPAYTEST';
        $signed_field_names = $response['signed_field_names'] ?? '';
        $received_signature = $response['signature'] ?? '';

        // Reconstruct signed payload string dynamically based on signed_field_names
        $fields = explode(',', $signed_field_names);
        $payload_parts = [];
        foreach ($fields as $field) {
            $field = trim($field);
            if (isset($response[$field])) {
                $payload_parts[] = "{$field}={$response[$field]}";
            }
        }
        $data_to_hash = implode(',', $payload_parts);

        // Generate HMAC-SHA256 hash for verification
        $calculated_raw = hash_hmac('sha256', $data_to_hash, $secret_key, true);
        $calculated_signature = base64_encode($calculated_raw);

        // Verify signature match
        if ($calculated_signature === $received_signature) {
            // Retrieve order from database
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE transaction_uuid = ?");
            $stmt->execute([$transaction_uuid]);
            $order = $stmt->fetch();

            if ($order) {
                // Update payment status and store eSewa reference code
                $update_stmt = $pdo->prepare("
                    UPDATE orders 
                    SET payment_status = 'completed', status = 'Processing', esewa_ref_id = ? 
                    WHERE id = ?
                ");
                $update_stmt->execute([$transaction_code, $order['id']]);

                $is_success = true;
                $message = "Payment completed successfully via eSewa!";
            } else {
                $message = "Order record not found in system.";
            }
        } else {
            $message = "Security check failed: Signature mismatch detected.";
        }
    } else {
        $message = "Payment transaction was not marked as COMPLETE by eSewa.";
    }

// 2. Handle Cash on Delivery Return
} elseif (isset($_GET['status']) && $_GET['status'] === 'cod' && isset($_GET['transaction_uuid'])) {
    $transaction_uuid = trim($_GET['transaction_uuid']);
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE transaction_uuid = ?");
    $stmt->execute([$transaction_uuid]);
    $order = $stmt->fetch();

    if ($order) {
        $is_success = true;
        $message = "Order placed successfully with Cash on Delivery!";
    } else {
        $message = "Order record not found.";
    }
} else {
    $message = "Invalid or direct page access request.";
}

// 3. Retrieve order items for display if order was located
$order_items = [];
if ($order) {
    $item_stmt = $pdo->prepare("
        SELECT oi.*, p.title, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $item_stmt->execute([$order['id']]);
    $order_items = $item_stmt->fetchAll();
}
?>

<style>
    .success-container { max-width: 650px; margin: 60px auto 96px; padding: 32px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; text-align: center; }
    .status-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; }
    .status-icon.success { background: rgba(96, 187, 70, 0.15); color: #60bb46; border: 2px solid #60bb46; }
    .status-icon.error { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 2px solid #ef4444; }
    .order-details { text-align: left; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin: 24px 0; }
    .detail-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; color: var(--text-muted); }
    .detail-row strong { color: var(--text-primary); }
    .item-list { border-top: 1px solid var(--border-color); margin-top: 16px; padding-top: 16px; }
    .item-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .item-row img { width: 48px; height: 48px; object-fit: cover; border-radius: 4px; }
    .btn-home { display: inline-block; background: var(--text-primary); color: var(--bg-main); padding: 12px 28px; border-radius: 6px; font-weight: 600; text-decoration: none; margin-top: 12px; }
</style>

<div class="success-container">
    <?php if ($is_success): ?>
        <div class="status-icon success">&#10003;</div>
        <h2 style="margin: 0 0 8px;"><?= htmlspecialchars($message) ?></h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-top: 0;">Thank you for your purchase. Your order has been processed.</p>

        <?php if ($order): ?>
            <div class="order-details">
                <div class="detail-row"><span>Order ID:</span><strong>#<?= htmlspecialchars($order['id']) ?></strong></div>
                <div class="detail-row"><span>Transaction Code:</span><strong><?= htmlspecialchars($order['transaction_uuid']) ?></strong></div>
                <?php if (!empty($order['esewa_ref_id'])): ?>
                    <div class="detail-row"><span>eSewa Ref ID:</span><strong><?= htmlspecialchars($order['esewa_ref_id']) ?></strong></div>
                <?php endif; ?>
                <div class="detail-row"><span>Payment Method:</span><strong style="text-transform: uppercase;"><?= htmlspecialchars($order['payment_method']) ?></strong></div>
                <div class="detail-row"><span>Total Amount:</span><strong>NPR <?= number_format($order['total_amount'], 2) ?></strong></div>

                <div class="item-list">
                    <h4 style="margin: 0 0 12px; font-size: 14px;">Purchased Items</h4>
                    <?php foreach ($order_items as $item): ?>
                        <div class="item-row">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                            <div style="flex: 1;">
                                <div style="font-size: 13px; font-weight: 500;"><?= htmlspecialchars($item['title']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted);">Qty: <?= $item['quantity'] ?> &times; NPR <?= number_format($item['unit_price'], 2) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="status-icon error">&#10007;</div>
        <h2 style="margin: 0 0 8px;">Order Verification Failed</h2>
        <p style="color: var(--text-muted); font-size: 14px;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <a href="index.php" class="btn-home">Return to Home</a>
</div>

</body>
</html>