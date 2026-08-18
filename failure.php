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

<style>
    .failure-card { max-width: 500px; margin: 80px auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 32px; text-align: center; }
    .btn-retry { display: inline-block; margin-top: 20px; background: var(--text-primary); color: var(--bg-main); padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; }
</style>

<div class="failure-card">
    <h2 style="color: #ef4444;">Payment Cancelled or Failed</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Your transaction was not completed. You can retry the order anytime.</p>
    <a href="index.php" class="btn-retry">Back to Catalog</a>
</div>

</body>
</html>