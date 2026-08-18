<?php
// Start session globally if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
$host     = 'localhost';
$dbname   = 'model_store';
$username = 'root';
$password = ''; // Default XAMPP password is empty

try {
    // Configure PDO with UTF-8 character encoding
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    // Terminate script execution on database failure
    die("Database Connection Failed: " . $e->getMessage());
}

/**
 * Global Currency Formatter
 * Formats numeric values to Nepali Rupees (NPR) across the entire application.
 */
function format_price($amount) {
    return 'Rs. ' . number_format((float)$amount, 2);
}