<?php
session_start();
require_once '../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch Metrics
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'")->fetchColumn() ?: 0;
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;

// Fetch Recent Orders
$stmt = $pdo->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Overview - Apex Scale Models</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=3">
</head>
<body>
<div class="admin-container">
    <header class="admin-header">
        <h1>Apex Admin</h1>
        <nav class="admin-nav">
            <a href="dashboard.php" class="active">Overview</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="../index.php">View Store</a>
            <a href="../logout.php">Sign Out</a>
        </nav>
    </header>

    <div class="metrics-grid">
        <div class="metric-card">
            <label>Total Revenue</label>
            <div class="val"><?= format_price($total_revenue) ?></div>
        </div>
        <div class="metric-card">
            <label>Orders Processed</label>
            <div class="val"><?= $total_orders ?></div>
        </div>
        <div class="metric-card">
            <label>Active Products</label>
            <div class="val"><?= $total_products ?></div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-title">Recent Orders</div>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): 
                    $pay_method = strtolower($order['payment_method'] ?? '');
                ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['full_name']) ?></td>
                        <td><?= format_price($order['total_amount']) ?></td>
                        <td><span class="chip <?= $pay_method === 'cod' ? 'chip-cod' : 'chip-esewa' ?>"><?= $pay_method === 'cod' ? 'COD' : 'eSewa' ?></span></td>
                        <td><span class="badge-status status-<?= strtolower($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                        <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>