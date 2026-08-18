<?php
session_start();
require_once 'config/db.php';

$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

$selectedType     = $_GET['type'] ?? '';
$selectedScale    = $_GET['scale'] ?? '';
$selectedCategory = $_GET['category'] ?? '';

$query = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if (!empty($selectedType)) {
    $query .= " AND p.type = ?";
    $params[] = $selectedType;
}
if (!empty($selectedScale)) {
    $query .= " AND p.scale = ?";
    $params[] = $selectedScale;
}
if (!empty($selectedCategory)) {
    $query .= " AND p.category_id = ?";
    $params[] = $selectedCategory;
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Model Store - Catalog</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 20px; background: #f4f6f8; color: #333; }
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; color: #fff; padding: 15px 25px; border-radius: 8px; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 22px; }
        header a { color: #38bdf8; text-decoration: none; font-weight: bold; margin-left: 15px; }
        .layout { display: flex; gap: 25px; }
        .sidebar { flex: 0 0 240px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); height: fit-content; }
        .sidebar h3 { margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .filter-group { margin-bottom: 15px; }
        .filter-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .filter-group select { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; }
        .filter-btn { width: 100%; background: #0284c7; color: #fff; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .reset-link { display: block; text-align: center; margin-top: 10px; color: #64748b; font-size: 13px; text-decoration: none; }
        .catalog { flex: 1; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .product-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; }
        .product-card img { width: 100%; height: 160px; object-fit: cover; background: #e2e8f0; }
        .card-body { padding: 15px; flex: 1; display: flex; flex-direction: column; }
        .tags { display: flex; gap: 5px; margin-bottom: 8px; }
        .tag { background: #e2e8f0; color: #334155; font-size: 11px; padding: 3px 6px; border-radius: 4px; font-weight: bold; }
        .product-title { font-size: 16px; margin: 0 0 8px 0; }
        .product-title a { color: #1e293b; text-decoration: none; }
        .product-price { font-size: 18px; font-weight: bold; color: #16a34a; margin-top: auto; }
        .add-cart-btn { background: #16a34a; color: white; border: none; width: 100%; padding: 8px; border-radius: 4px; margin-top: 10px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<header>
    <h1>Model Store</h1>
    <div>
        <a href="cart.php">🛒 Cart (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>)</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="margin-left: 15px;">Hi, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <h3>Filter Models</h3>
        <form action="index.php" method="GET">
            <div class="filter-group">
                <label>Vehicle Type</label>
                <select name="type">
                    <option value="">All Types</option>
                    <option value="Diecast" <?= $selectedType === 'Diecast' ? 'selected' : '' ?>>Diecast</option>
                    <option value="RC" <?= $selectedType === 'RC' ? 'selected' : '' ?>>RC Car</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Scale</label>
                <select name="scale">
                    <option value="">All Scales</option>
                    <option value="1:18" <?= $selectedScale === '1:18' ? 'selected' : '' ?>>1:18</option>
                    <option value="1:64" <?= $selectedScale === '1:64' ? 'selected' : '' ?>>1:64</option>
                    <option value="1:10" <?= $selectedScale === '1:10' ? 'selected' : '' ?>>1:10</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="filter-btn">Apply Filters</button>
            <a href="index.php" class="reset-link">Reset All</a>
        </form>
    </aside>

    <main class="catalog">
        <?php if (empty($products)): ?>
            <p>No models found matching criteria.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($products as $item): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($item['image_url'] ?: 'https://via.placeholder.com/300') ?>" alt="Product">
                        <div class="card-body">
                            <div class="tags">
                                <span class="tag"><?= htmlspecialchars($item['type']) ?></span>
                                <span class="tag"><?= htmlspecialchars($item['scale']) ?></span>
                            </div>
                            <h4 class="product-title">
                                <a href="product.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                            </h4>
                            <div class="product-price">$<?= number_format($item['price'], 2) ?></div>
                            <?php if ($item['stock'] > 0): ?>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" name="add_to_cart" class="add-cart-btn">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#dc2626; font-size:12px; margin-top:5px; font-weight:bold;">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>