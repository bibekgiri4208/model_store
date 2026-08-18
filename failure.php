<?php
require_once 'config/db.php';
include 'includes/header.php';

if (isset($_GET['data'])) {
    $json_data = base64_decode($_GET['data']);
    $response = json_decode($json_data, true);

    if (isset($response['transaction_uuid'])) {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE transaction_uuid = ?");
        $stmt->execute([$response['transaction_uuid']]);
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
