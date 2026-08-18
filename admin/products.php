<?php
session_start();
require_once '../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$edit_product = null;

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: products.php?msg=deleted');
    exit;
}

// Handle Fetch for Editing
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $edit_product = $stmt->fetch();
}

// Handle Add / Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);
    $scale = trim($_POST['scale'] ?? '1:18');
    $type = trim($_POST['type'] ?? 'Diecast');
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($product_id > 0) {
        // Update Product
        $stmt = $pdo->prepare("UPDATE products SET title = ?, price = ?, stock = ?, category_id = ?, description = ?, image_url = ?, scale = ?, type = ? WHERE id = ?");
        $stmt->execute([$title, $price, $stock, $category_id, $description, $image_url, $scale, $type, $product_id]);
        header('Location: products.php?msg=updated');
        exit;
    } else {
        // Add Product
        $stmt = $pdo->prepare("INSERT INTO products (title, price, stock, category_id, description, image_url, scale, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $price, $stock, $category_id, $description, $image_url, $scale, $type]);
        header('Location: products.php?msg=added');
        exit;
    }
}

// Fetch Categories for Dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch All Products with Category Name
$stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Apex Replica Admin</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; margin: 0; padding: 20px; min-height: 100vh; }
        .container { max-width: 1100px; margin: auto; }

        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 18px 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 22px; color: #38bdf8; }
        header nav a { color: #94a3b8; text-decoration: none; font-weight: 600; margin-left: 20px; }
        header nav a:hover, header nav a.active { color: #38bdf8; }

        .alert { background: #064e3b; color: #6ee7b7; border: 1px solid #047857; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }

        .grid-layout { display: grid; grid-template-columns: 350px 1fr; gap: 25px; }
        @media (max-width: 850px) { .grid-layout { grid-template-columns: 1fr; } }

        .card { background: #1e293b; border-radius: 12px; border: 1px solid #334155; padding: 20px; }
        .card h3 { margin-top: 0; color: #38bdf8; border-bottom: 1px solid #334155; padding-bottom: 10px; }

        .form-group { margin-bottom: 12px; }
        label { display: block; font-size: 13px; color: #94a3b8; margin-bottom: 4px; }
        input, select, textarea { width: 100%; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px; border-radius: 8px; font-size: 14px; outline: none; }
        textarea { resize: vertical; height: 80px; }
        
        .btn-submit { width: 100%; background: #38bdf8; color: #0f172a; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-cancel { display: block; text-align: center; color: #94a3b8; text-decoration: none; font-size: 13px; margin-top: 8px; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #0f172a; padding: 12px; color: #94a3b8; font-size: 13px; border-bottom: 1px solid #334155; }
        td { padding: 12px; border-bottom: 1px solid #334155; font-size: 14px; }
        
        .img-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #334155; }
        .btn-edit { color: #38bdf8; text-decoration: none; font-weight: bold; margin-right: 10px; }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>🏎️ Product Management</h1>
        <nav>
            <a href="dashboard.php">Overview</a>
            <a href="products.php" class="active">Products</a>
            <a href="orders.php">Manage Orders</a>
            <a href="../index.php">View Site</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert">Product operation completed successfully.</div>
    <?php endif; ?>

    <div class="grid-layout">
        <!-- Add / Edit Form -->
        <div class="card">
            <h3><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h3>
            <form action="products.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?? 0 ?>">
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($edit_product['title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($edit_product['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="display:flex; gap:10px;">
                    <div>
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" required value="<?= $edit_product['price'] ?? '' ?>">
                    </div>
                    <div>
                        <label>Stock</label>
                        <input type="number" name="stock" required value="<?= $edit_product['stock'] ?? 10 ?>">
                    </div>
                </div>

                <div class="form-group" style="display:flex; gap:10px;">
                    <div>
                        <label>Scale</label>
                        <input type="text" name="scale" value="<?= htmlspecialchars($edit_product['scale'] ?? '1:18') ?>">
                    </div>
                    <div>
                        <label>Type</label>
                        <input type="text" name="type" value="<?= htmlspecialchars($edit_product['type'] ?? 'Diecast') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image_url" value="<?= htmlspecialchars($edit_product['image_url'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-submit"><?= $edit_product ? 'Update Product' : 'Save Product' ?></button>
                <?php if ($edit_product): ?>
                    <a href="products.php" class="btn-cancel">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Product Table List -->
        <div class="card" style="overflow-x: auto;">
            <h3>Catalog Items (<?= count($products) ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $img = !empty($p['image_url']) ? $p['image_url'] : '../assets/images/placeholder.jpg';
                    ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($img) ?>" class="img-thumb" alt=""></td>
                            <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                            <td><span style="color:#94a3b8;"><?= htmlspecialchars($p['category_name'] ?? 'N/A') ?></span></td>
                            <td>$<?= number_format($p['price'], 2) ?></td>
                            <td><?= $p['stock'] ?></td>
                            <td>
                                <a href="products.php?edit=<?= $p['id'] ?>" class="btn-edit">Edit</a>
                                <a href="products.php?delete=<?= $p['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>