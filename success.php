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

<div class="status-container">
    <?php if ($success): ?>
        <div class="status-icon success">&#10003;</div>
        <h2 style="margin: 0 0 8px; color: var(--accent);">Order Placed Successfully!</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Thank you for your purchase.</p>
        <?php if (!empty($ref_id)): ?>
            <p style="font-size: 14px;"><strong>eSewa Reference ID:</strong> <?= htmlspecialchars($ref_id) ?></p>
        <?php endif; ?>
        <?php if ($order): ?>
            <p style="font-size: 14px;"><strong>Order Reference:</strong> <?= htmlspecialchars($order['transaction_uuid']) ?></p>
        <?php endif; ?>
    <?php else: ?>
        <div class="status-icon error">&#10007;</div>
        <h2 style="margin: 0 0 8px; color: var(--danger);">Verification Failed</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Could not verify the transaction details.</p>
    <?php endif; ?>

    <a href="index.php" class="btn btn-primary" style="margin-top: 24px;">Return to Catalog</a>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
