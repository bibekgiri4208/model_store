<?php
require_once 'config/db.php';
include 'includes/header.php';

if (isset($_GET['data'])) {
    $json_data = base64_decode($_GET['data']);
    $response = json_decode($json_data, true);

    if (isset($response['transaction_uuid'])) {
        $transaction_uuid = $response['transaction_uuid'];

        // Fetch the pending order
        $order_stmt = $pdo->prepare("SELECT * FROM orders WHERE transaction_uuid = ?");
        $order_stmt->execute([$transaction_uuid]);
        $order = $order_stmt->fetch();

        if ($order) {
            // Restore stock for each item in the order
            $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $items_stmt->execute([$order['id']]);
            $items = $items_stmt->fetchAll();

            $restore_stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            foreach ($items as $item) {
                $restore_stmt->execute([$item['quantity'], $item['product_id']]);
            }
        }

        // Mark the order payment as failed
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE transaction_uuid = ?");
        $stmt->execute([$transaction_uuid]);
    }
}
?>

<div class="status-container">
    <div class="status-icon error">&#10007;</div>
    <h2 style="margin: 0 0 8px; color: var(--danger);">Payment Cancelled or Failed</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Your transaction was not completed. You can retry the order anytime.</p>
    <a href="index.php" class="btn btn-primary" style="margin-top: 24px;">Back to Catalog</a>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
