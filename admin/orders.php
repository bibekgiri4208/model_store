<?php
session_start();
require_once '../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = trim($_POST['status']);
    $allowed = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
    if (in_array($status, $allowed, true)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
    }
    header('Location: orders.php?updated=1');
    exit;
}

// Fetch All Orders
$stmt = $pdo->query("
    SELECT o.*, u.full_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Apex Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=4">
</head>
<body>
<div class="admin-container">
    <header class="admin-header">
        <h1>Orders</h1>
        <nav class="admin-nav">
            <a href="dashboard.php">Overview</a>
            <a href="products.php">Products</a>
            <a href="orders.php" class="active">Orders</a>
            <a href="../index.php">View Store</a>
            <a href="../logout.php">Sign Out</a>
        </nav>
    </header>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Order status updated successfully.</div>
    <?php endif; ?>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order):
                    $pay_method = strtolower($order['payment_method'] ?? '');
                    $pay_state  = strtolower($order['payment_status'] ?? 'pending');
                    $pay_class  = 'status-pending';
                    $pay_label  = 'Pending';
                    if ($pay_state === 'completed') { $pay_class = 'status-completed'; $pay_label = 'Paid'; }
                    elseif ($pay_state === 'failed') { $pay_class = 'status-cancelled'; $pay_label = 'Failed'; }
                ?>
                    <tr id="order-<?= $order['id'] ?>">
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td>
                            <div><?= htmlspecialchars($order['full_name']) ?></div>
                            <div class="admin-sub"><?= htmlspecialchars($order['email']) ?></div>
                        </td>
                        <td><?= format_price($order['total_amount']) ?></td>
                        <td>
                            <div class="pay-cell">
                                <span class="chip <?= $pay_method === 'cod' ? 'chip-cod' : 'chip-esewa' ?>">
                                    <?= $pay_method === 'cod' ? 'COD' : 'eSewa' ?>
                                </span>
                                <span class="badge-status <?= $pay_class ?>"><?= $pay_label ?></span>
                            </div>
                        </td>
                        <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                        <td>
                            <div class="status-cell">
                                <span class="badge-status status-<?= strtolower($order['status']) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                                <form action="orders.php" method="POST" class="form-inline">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status">
                                        <?php foreach (['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'] as $st): ?>
                                            <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">Save</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>