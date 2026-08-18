<?php
session_start();
require_once 'config/db.php';

// Enforce login requirement
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=my-orders.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user with item summaries
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.title, ' (x', oi.quantity, ')') SEPARATOR ', ') AS item_summary
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Model Store</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { padding: 20px; background: #f4f6f8; color: #333; max-width: 900px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; color: #fff; padding: 15px 25px; border-radius: 8px; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 20px; }
        header a { color: #38bdf8; text-decoration: none; font-weight: bold; }
        
        .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background: #f8fafc; font-weight: bold; color: #475569; }
        
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-Pending { background: #fef3c7; color: #92400e; }
        .status-Processing { background: #e0f2fe; color: #075985; }
        .status-Shipped { background: #dcfce7; color: #166534; }
        .status-Completed { background: #bbf7d0; color: #14532d; }
        .status-Cancelled { background: #fee2e2; color: #991b1b; }
        
        .empty-box { background: #fff; padding: 40px; text-align: center; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .empty-box p { margin-bottom: 15px; color: #64748b; font-size: 16px; }
        .empty-box a { background: #0284c7; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block; }
        .empty-box a:hover { background: #0369a1; }
    </style>
</head>
<body>

<header>
    <h1>My Order History</h1>
    <a href="index.php">← Back to Store</a>
</header>

<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="alert-success">
        🎉 Your order has been placed successfully! You can track its status below.
    </div>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <div class="empty-box">
        <p>You haven't placed any orders yet.</p>
        <a href="index.php">Start Shopping</a>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Items Purchased</th>
                <th>Total</th>
                <th>Shipping Address</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong>#<?= $order['id'] ?></strong></td>
                    <td><?= htmlspecialchars($order['item_summary'] ?: 'No items recorded') ?></td>
                    <td><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                    <td style="max-width: 200px;"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></td>
                    <td>
                        <span class="status-badge status-<?= $order['status'] ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td><small style="color: #64748b;"><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></small></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>