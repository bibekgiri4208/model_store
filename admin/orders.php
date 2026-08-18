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
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    header('Location: orders.php');
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #09090b;
            --bg-card: #121215;
            --border-color: #27272a;
            --text-primary: #f4f4f5;
            --text-muted: #a1a1aa;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-main); color: var(--text-primary); padding: 24px; }
        .container { max-width: 1100px; margin: 0 auto; }

        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 32px; }
        header h1 { font-size: 18px; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase; color: var(--text-primary); }
        
        nav a { color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 400; margin-left: 24px; transition: color 0.15s ease; }
        nav a:hover, nav a.active { color: var(--text-primary); }

        .table-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #000; padding: 14px 20px; color: var(--text-muted); font-size: 12px; font-weight: 500; border-bottom: 1px solid var(--border-color); text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 16px 20px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-primary); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        select { background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); padding: 6px 10px; border-radius: 4px; font-size: 13px; outline: none; }
        .btn-update { background: var(--text-primary); color: var(--bg-main); border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; margin-left: 6px; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Orders</h1>
        <nav>
            <a href="dashboard.php">Overview</a>
            <a href="products.php">Products</a>
            <a href="orders.php" class="active">Orders</a>
            <a href="../index.php">View Store</a>
            <a href="../logout.php">Sign Out</a>
        </nav>
    </header>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr id="order-<?= $order['id'] ?>">
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td>
                            <div><?= htmlspecialchars($order['full_name']) ?></div>
                            <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($order['email']) ?></div>
                        </td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                        <td>
                            <form action="orders.php" method="POST" style="display: flex; align-items: center;">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status">
                                    <?php foreach (['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'] as $st): ?>
                                        <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn-update">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>