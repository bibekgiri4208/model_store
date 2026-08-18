<?php
session_start();
require_once '../config/db.php';

// Access Control: Ensure user is logged in as Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch Metrics
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'")->fetchColumn() ?: 0;
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Fetch Recent Orders
$stmt = $pdo->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Apex Replica</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; margin: 0; padding: 20px; min-height: 100vh; }
        .container { max-width: 1100px; margin: auto; }
        
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 18px 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 22px; color: #38bdf8; }
        header nav a { color: #94a3b8; text-decoration: none; font-weight: 600; margin-left: 20px; transition: color 0.2s; }
        header nav a:hover, header nav a.active { color: #38bdf8; }

        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .metric-card { background: #1e293b; border: 1px solid #334155; padding: 20px; border-radius: 12px; }
        .metric-card span { color: #94a3b8; font-size: 13px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .metric-card .value { font-size: 28px; font-weight: bold; color: #38bdf8; margin-top: 8px; }
        .metric-card .value.green { color: #4ade80; }

        .table-card { background: #1e293b; border-radius: 12px; border: 1px solid #334155; overflow: hidden; }
        .table-card .card-title { padding: 20px; font-size: 18px; font-weight: bold; border-bottom: 1px solid #334155; margin: 0; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #0f172a; padding: 14px 20px; color: #94a3b8; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #334155; }
        td { padding: 16px 20px; border-bottom: 1px solid #334155; font-size: 14px; }
        tr:last-child td { border-bottom: none; }

        .status-pill { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; }
        .status-Pending { background: #854d0e; color: #fef08a; }
        .status-Processing { background: #075985; color: #bae6fd; }
        .status-Shipped { background: #1e40af; color: #bfdbfe; }
        .status-Completed { background: #166534; color: #bbf7d0; }
        .status-Cancelled { background: #991b1b; color: #fecaca; }

        .action-link { color: #38bdf8; text-decoration: none; font-weight: bold; }
        .action-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>⚙️ Admin Dashboard</h1>
        <nav>
            <a href="dashboard.php" class="active">Overview</a>
            <a href="orders.php">Manage Orders</a>
            <a href="../index.php">View Site</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>

    <div class="metrics-grid">
        <div class="metric-card">
            <span>Total Revenue</span>
            <div class="value green">$<?= number_format($total_revenue, 2) ?></div>
        </div>
        <div class="metric-card">
            <span>Total Orders</span>
            <div class="value"><?= $total_orders ?></div>
        </div>
        <div class="metric-card">
            <span>Active Products</span>
            <div class="value"><?= $total_products ?></div>
        </div>
        <div class="metric-card">
            <span>Registered Customers</span>
            <div class="value"><?= $total_users ?></div>
        </div>
    </div>

    <div class="table-card">
        <h3 class="card-title">Recent Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><?= htmlspecialchars($order['full_name']) ?></td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><span class="status-pill status-<?= $order['status'] ?>"><?= $order['status'] ?></span></td>
                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td><a href="orders.php?id=<?= $order['id'] ?>" class="action-link">Manage →</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>