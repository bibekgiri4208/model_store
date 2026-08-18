<?php
session_start();
require_once 'config/db.php';

$items_per_page = 8;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$search = trim($_GET['search'] ?? '');
$category_id = (int)($_GET['category'] ?? 0);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$where_clauses = ["1=1"];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($category_id > 0) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_id;
}

$where_sql = implode(" AND ", $where_clauses);

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE {$where_sql}");
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();

$total_pages = ceil($total_products / $items_per_page);
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
$offset = ($current_page - 1) * $items_per_page;

$product_sql = "SELECT p.*, c.name AS category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$where_sql} 
                ORDER BY p.id DESC 
                LIMIT {$items_per_page} OFFSET {$offset}";

$product_stmt = $pdo->prepare($product_sql);
$product_stmt->execute($params);
$products = $product_stmt->fetchAll();

// Fallback high-res online car photos (Unsplash)
$fallback_images = [
    "https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=800&q=80",
    "https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=800&q=80",
    "https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=800&q=80",
    "https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?auto=format&fit=crop&w=800&q=80"
];

function build_page_url($page_num, $search, $category_id) {
    $query_params = ['page' => $page_num];
    if (!empty($search)) $query_params['search'] = $search;
    if ($category_id > 0) $query_params['category'] = $category_id;
    return 'index.php?' . http_build_query($query_params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Replica - Scale Model Cars</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; margin: 0; padding: 20px; min-height: 100vh; }
        .container { max-width: 1200px; margin: auto; }

        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 18px 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 24px; color: #38bdf8; letter-spacing: 1px; }
        header nav a { color: #94a3b8; text-decoration: none; font-weight: 600; margin-left: 20px; transition: color 0.2s; }
        header nav a:hover { color: #38bdf8; }

        .filter-card { background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 30px; }
        .filter-form { display: flex; gap: 12px; flex-wrap: wrap; }
        .filter-form input, .filter-form select { background: #0f172a; border: 1px solid #334155; color: #fff; padding: 12px 16px; border-radius: 8px; font-size: 14px; outline: none; }
        .filter-form input { flex: 2; min-width: 200px; }
        .filter-form select { flex: 1; min-width: 160px; }
        .filter-form button { background: #0284c7; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .filter-form button:hover { background: #0369a1; }
        .filter-form .reset-btn { background: #475569; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; font-size: 14px; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
        .product-card { background: #1e293b; border-radius: 12px; border: 1px solid #334155; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.4); }
        .product-card img { width: 100%; height: 200px; object-fit: cover; background: #0f172a; }
        .product-info { padding: 20px; flex-grow: 1; }
        .product-category { font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: bold; letter-spacing: 1px; }
        .product-title { font-size: 18px; margin: 8px 0; font-weight: bold; }
        .product-title a { color: #f8fafc; text-decoration: none; }
        .product-price { font-size: 20px; color: #4ade80; font-weight: bold; margin-top: 10px; }
        
        .card-actions { padding: 15px 20px; background: #0f172a; border-top: 1px solid #334155; }
        .view-btn { display: block; text-align: center; background: #38bdf8; color: #0f172a; padding: 10px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        .view-btn:hover { background: #7dd3fc; }

        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
        .pagination a, .pagination span { padding: 10px 16px; background: #1e293b; border: 1px solid #334155; border-radius: 8px; color: #38bdf8; text-decoration: none; font-weight: bold; }
        .pagination .active { background: #0284c7; color: #fff; border-color: #0284c7; }
        .pagination .disabled { color: #475569; pointer-events: none; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>🏎️ Apex Replica Store</h1>
        <nav>
            <a href="cart.php">🛒 Cart</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my-orders.php">📦 My Orders</a>
                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <a href="admin/orders.php">⚙️ Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="filter-card">
        <form action="index.php" method="GET" class="filter-form">
            <input type="text" name="search" placeholder="Search die-cast models..." value="<?= htmlspecialchars($search) ?>">
            <select name="category">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category_id === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
            <?php if (!empty($search) || $category_id > 0): ?>
                <a href="index.php" class="reset-btn">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="product-grid">
        <?php foreach ($products as $idx => $product): 
            // Determine image source (local file, database URL, or random online fallback)
            $img_src = $product['image'];
            if (empty($img_src) || $img_src === 'placeholder.jpg') {
                $img_src = $fallback_images[$idx % count($fallback_images)];
            } elseif (!str_starts_with($img_src, 'http')) {
                $img_src = 'assets/images/' . $img_src;
            }
        ?>
            <div class="product-card">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                </a>
                <div class="product-info">
                    <span class="product-category"><?= htmlspecialchars($product['category_name'] ?: 'Scale Replica') ?></span>
                    <h3 class="product-title">
                        <a href="product.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['title']) ?></a>
                    </h3>
                    <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                </div>
                <div class="card-actions">
                    <a href="product.php?id=<?= $product['id'] ?>" class="view-btn">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="<?= build_page_url($i, $search, $category_id) ?>" class="<?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>