<?php
session_start();
require_once '../config/db.php';

// 1. Enforce Admin Access Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    die("Access Denied: You must be an administrator to view this page.");
}

$message = '';

// 2. Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id   = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $message = "Order #{$order_id} status updated to '{$new_status}'.";
    }
}

// 3. Fetch All Orders with User Information & Item Details
$query = "SELECT o.*, u.full_name, u.email,
          GROUP_CONCAT(CONCAT(p.title, ' (x', oi.quantity, ')') SEPARATOR ', ') AS item_summary
          FROM orders o
          JOIN users u ON o.user_id = u.id
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          GROUP BY o.id
          ORDER BY o.created_at DESC";

$orders = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order Management</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { padding: 20px; background: #f4f6f8; color: #333; margin: 0; }
        .container { max-width: 1100px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; color: #fff; padding: 15px 25px; border-radius: 8px; margin-bottom: 20px; }
        header a { color: #38bdf8; text-decoration: none; font-weight: bold; }
        
        .alert { background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; vertical-align: top; font-size: 14px; }
        th { background: #f8fafc; font-weight: bold; color: #475569; }
        
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-Pending { background: #fef3c7; color: #92400e; }
        .status-Processing { background: #e0f2fe; color: #075985; }
        .status-Shipped { background: #dcfce7; color: #166534; }
        .status-Completed { background: #bbf7d0; color: #14532d; }
        .status-Cancelled { background: #fee2e2; color: #991b1b; }

        .update-form { display: flex; gap: 5px; margin-top: 5px; }
        .update-form select { padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; }
        .update-form button { background: #0284c7; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; cursor: pointer; }
        .update-form button:hover { background: #0369a1; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Admin Panel: Order Management</h1>
        <a href="../index.php">← Back to Main Store</a>
    </header>

    <?php if ($message): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <p>No orders placed yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Items Purchased</th>
                    <th>Total</th>
                    <th>Shipping Address</th>
                    <th>Status & Action</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($order['full_name']) ?></strong><br>
                            <small style="color: #64748b;"><?= htmlspecialchars($order['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($order['item_summary'] ?: 'No items recorded') ?></td>
                        <td><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                        <td style="max-width: 200px;"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></td>
                        <td>
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>

                            <form action="orders.php" method="POST" class="update-form">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status">
                                    <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status">Save</button>
                            </form>
                        </td>
                        <td><small><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>