<?php
session_start();
require_once '../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $message = "Order #{$order_id} status updated to {$new_status}.";
    }
}

// Fetch Orders with Customer Details
$stmt = $pdo->query("SELECT o.*, u.full_name, u.email 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Apex Replica Admin</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; margin: 0; padding: 20px; min-height: 100vh; }
        .container { max-width: 1100px; margin: auto; }
        
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 18px 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 22px; color: #38bdf8; }
        header nav a { color: #94a3b8; text-decoration: none; font-weight: 600; margin-left: 20px; }
        header nav a:hover, header nav a.active { color: #38bdf8; }

        .alert { background: #064e3b; color: #6ee7b7; border: 1px solid #047857; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }

        .orders-list { display: flex; flex-direction: column; gap: 20px; }
        .order-card { background: #1e293b; border-radius: 12px; border: 1px solid #334155; padding: 20px; }
        .order-card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 15px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
        
        .order-meta { font-size: 14px; color: #94a3b8; line-height: 1.6; }
        .order-meta strong { color: #f8fafc; }
        .total-price { font-size: 20px; color: #4ade80; font-weight: bold; margin-top: 5px; }

        .status-form { display: flex; align-items: center; gap: 10px; }
        .status-form select { background: #0f172a; border: 1px solid #334155; color: #fff; padding: 8px 12px; border-radius: 8px; font-weight: bold; outline: none; }
        .status-form button { background: #0284c7; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .status-form button:hover { background: #0369a1; }

        .items-table { width: 100%; margin-top: 15px; border-collapse: collapse; background: #0f172a; border-radius: 8px; overflow: hidden; }
        .items-table th, .items-table td { padding: 10px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid #1e293b; }
        .items-table th { color: #94a3b8; background: #182234; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>📦 Order Management</h1>
        <nav>
            <a href="dashboard.php">Overview</a>
            <a href="orders.php" class="active">Manage Orders</a>
            <a href="../index.php">View Site</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>

    <?php if (isset($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="orders-list">
        <?php foreach ($orders as $order): 
            // Fetch items for this order
            $item_stmt = $pdo->prepare("SELECT oi.*, p.title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $item_stmt->execute([$order['id']]);
            $items = $item_stmt->fetchAll();
        ?>
            <div class="order-card" id="order-<?= $order['id'] ?>">
                <div class="order-card-header">
                    <div>
                        <span style="font-size:18px; font-weight:bold; color:#38bdf8;">Order #<?= $order['id'] ?></span>
                        <span style="color:#94a3b8; font-size:13px; margin-left:10px;"><?= date('M j, Y - g:i A', strtotime($order['created_at'])) ?></span>
                    </div>

                    <form action="orders.php" method="POST" class="status-form">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status">
                            <?php foreach (['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </div>

                <div class="order-meta">
                    <strong>Customer:</strong> <?= htmlspecialchars($order['full_name']) ?> (<?= htmlspecialchars($order['email']) ?>)<br>
                    <strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?>
                    <div class="total-price">$<?= number_format($order['total_amount'], 2) ?></div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>