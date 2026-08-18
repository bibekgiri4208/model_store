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
    <title>Admin Overview - Apex Replicas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #09090b;
            --bg-card: #121215;
            --border-color: #27272a;
            --border-light: #3f3f46;
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

        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .metric-card { background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px; border-radius: 6px; }
        .metric-card label { display: block; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 8px; }
        .metric-card .val { font-size: 24px; font-weight: 600; color: var(--text-primary); }

        .table-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden; }
        .table-title { padding: 16px 20px; font-size: 14px; font-weight: 500; border-bottom: 1px solid var(--border-color); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #000; padding: 12px 20px; color: var(--text-muted); font-size: 12px; font-weight: 500; border-bottom: 1px solid var(--border-color); }
        td { padding: 14px 20px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-primary); }
        tr:last-child td { border-bottom: none; }
        
        .status { font-size: 12px; color: var(--text-muted); }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Apex Admin</h1>
        <nav>
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
            <div class="val">$<?= number_format($total_revenue, 2) ?></div>
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
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['full_name']) ?></td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><span class="status"><?= htmlspecialchars($order['status']) ?></span></td>
                        <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>