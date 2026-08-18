<?php
session_start();
$order_id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; text-align: center; padding: 50px; }
        .card { background: white; max-width: 500px; margin: auto; padding: 30px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color: #16a34a;">Order Placed Successfully!</h2>
        <p>Your Order ID is <strong>#<?= htmlspecialchars($order_id) ?></strong>.</p>
        <p><a href="index.php">Return to Shop</a></p>
    </div>
</body>
</html>