<?php
session_start();
require_once '../config/db.php';

// Enforce Admin Access Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    die("Access Denied: You must be an administrator to view this page.");
}

$message = '';
$error = '';

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $title       = trim($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $price       = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $image_name  = 'placeholder.jpg'; // Fallback default image

    // Handle File Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp   = $_FILES['image']['tmp_name'];
        $file_name  = $_FILES['image']['name'];
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_exts)) {
            // Generate unique filename to prevent overwriting existing files
            $image_name = time() . '_' . uniqid() . '.' . $file_ext;
            
            // Destination path: assets/images/
            $upload_dir = '../assets/images/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $dest_path = $upload_dir . $image_name;

            if (!move_uploaded_file($file_tmp, $dest_path)) {
                $error = "Failed to upload image. Check directory permissions.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, and WEBP files are allowed.";
        }
    }

    // Insert Product into Database if no errors
    if (empty($error)) {
        if (!empty($title) && $price > 0 && $category_id > 0) {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, title, description, price, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $title, $description, $price, $image_name]);
            $message = "Product '{$title}' added successfully!";
        } else {
            $error = "Please fill in all required fields properly.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Apex Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=4">
</head>
<body>
<div class="admin-container">
    <header class="admin-header">
        <h1>Add New Model Car</h1>
        <nav class="admin-nav">
            <a href="dashboard.php">Overview</a>
            <a href="products.php">Products</a>
            <a href="orders.php" class="active">Orders</a>
            <a href="../index.php">View Store</a>
            <a href="../logout.php">Sign Out</a>
        </nav>
    </header>

    <div class="card">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="add-product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Product Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Porsche 911 GT3 RS (1:18)" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Price (Rs.) *</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" placeholder="149.99" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" placeholder="Enter scale details, die-cast materials, opening parts..."></textarea>
            </div>

            <div class="form-group">
                <label for="image">Product Image (JPG, PNG, WEBP)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>

            <button type="submit" name="add_product" class="btn-submit">Save Product</button>
        </form>
    </div>
</div>

</body>
</html>