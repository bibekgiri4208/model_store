<?php
require_once 'config/db.php';
require_once 'config/esewa.php';
include 'includes/header.php';

$success = false;
$ref_id = '';
$order = null;

// Handle eSewa Callback Response
if (isset($_GET['data'])) {
    $json_data = base64_decode($_GET['data']);
    $response = json_decode($json_data, true);

    if ($response && isset($response['status']) && $response['status'] === 'COMPLETE') {
        $transaction_uuid = $response['transaction_uuid'];
        $ref_id = $response['reference_id'] ?? '';

        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'completed', esewa_ref_id = ? WHERE transaction_uuid = ?");
        $stmt->execute([$ref_id, $transaction_uuid]);

        $order_stmt = $pdo->prepare("SELECT * FROM orders WHERE transaction_uuid = ?");
        $order_stmt->execute([$transaction_uuid]);
        $order = $order_stmt->fetch();

        $success = true;
    }
} 
// Handle Cash on Delivery Confirmation
elseif (isset($_GET['status']) && $_GET['status'] === 'cod' && isset($_GET['uuid'])) {
    $transaction_uuid = $_GET['uuid'];
    $order_stmt = $pdo->prepare("SELECT * FROM orders WHERE transaction_uuid = ?");
    $order_stmt->execute([$transaction_uuid]);
    $order = $order_stmt->fetch();

    if ($order) {
        $success = true;
    }
}
?>

<style>
    .status-card { max-width: 500px; margin: 80px auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 32px; text-align: center; }
    .status-card h2 { margin-bottom: 8px; }
    .status-card p { color: var(--text-muted); font-size: 14px; }
    .btn-home { display: inline-block; margin-top: 24px; background: var(--text-primary); color: var(--bg-main); padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; }
</style>

<div class="status-card">
    <?php if ($success): ?>
        <h2 style="color: #60bb46;">Order Placed Successfully!</h2>
        <p>Thank you for your purchase.</p>
        <?php if (!empty($ref_id)): ?>
            <p><strong>eSewa Reference ID:</strong> <?= htmlspecialchars($ref_id) ?></p>
        <?php endif; ?>
        <?php if ($order): ?>
            <p><strong>Order Reference:</strong> <?= htmlspecialchars($order['transaction_uuid']) ?></p>
        <?php endif; ?>
    <?php else: ?>
        <h2 style="color: #ef4444;">Verification Failed</h2>
        <p>Could not verify the transaction details.</p>
    <?php endif; ?>

    <a href="index.php" class="btn-home">Return to Catalog</a>
</div>

</body>
</html>