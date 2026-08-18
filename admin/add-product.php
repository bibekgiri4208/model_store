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
    <title>Admin - Add Product</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { padding: 20px; background: #f4f6f8; color: #333; max-width: 700px; margin: auto; }
        
        header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; color: #fff; padding: 15px 25px; border-radius: 8px; margin-bottom: 25px; }
        header h1 { margin: 0; font-size: 20px; }
        header a { color: #38bdf8; text-decoration: none; font-weight: bold; }

        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert-danger { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #475569; font-size: 14px; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        .form-group textarea { resize: vertical; height: 100px; }
        .form-group input[type="file"] { font-size: 14px; }

        .submit-btn { background: #0284c7; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; font-size: 15px; cursor: pointer; width: 100%; }
        .submit-btn:hover { background: #0369a1; }
    </style>
</head>
<body>

<header>
    <h1>Add New Model Car</h1>
    <a href="orders.php">Manage Orders</a>
</header>

<div class="card">
    <?php if ($message): ?>
        <div class="alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="add-product.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Product Title *</label>
            <input type="text" id="title" name="title" placeholder="e.g., Porsche 911 GT3 RS (1:18)" required>
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="price">Price ($) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0" placeholder="149.99" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Enter scale details, die-cast materials, opening parts..."></textarea>
        </div>

        <div class="form-group">
            <label for="image">Product Image (JPG, PNG, WEBP)</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>

        <button type="submit" name="add_product" class="submit-btn">Save Product</button>
    </form>
</div>

</body>
</html>