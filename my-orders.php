<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders with items using your exact DB structure
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Apex Replica Store</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; margin: 0; padding: 20px; min-height: 100vh; }
        .container { max-width: 900px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 18px 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 25px; }
        header h1 a { color: #38bdf8; text-decoration: none; font-size: 24px; font-weight: bold; }
        header nav a { color: #94a3b8; text-decoration: none; font-weight: 600; margin-left: 20px; }

        .order-card { background: #1e293b; border-radius: 12px; border: 1px solid #334155; padding: 20px; margin-bottom: 20px; }
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 15px; }
        .order-id { font-size: 18px; font-weight: bold; color: #38bdf8; }
        .status-badge { font-size: 12px; padding: 4px 10px; border-radius: 6px; font-weight: bold; text-transform: uppercase; background: #0284c7; color: #fff; }
        
        .order-details { color: #cbd5e1; font-size: 14px; line-height: 1.6; }
        .order-amount { font-size: 20px; color: #4ade80; font-weight: bold; margin-top: 10px; }
        .empty-orders { background: #1e293b; padding: 40px; text-align: center; border-radius: 12px; border: 1px solid #334155; color: #94a3b8; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1><a href="index.php">Apex Replica Store</a></h1>
        <nav><a href="index.php">← Back to Shop</a></nav>
    </header>

    <h2>My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <p>You haven't placed any orders yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-id">Order #<?= $order['id'] ?></span>
                    <span class="status-badge"><?= htmlspecialchars($order['status']) ?></span>
                </div>
                <div class="order-details">
                    <strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?><br>
                    <strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?>
                    <div class="order-amount">$<?= number_format($order['total_amount'], 2) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>