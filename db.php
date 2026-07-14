<?php
// =========================================================
// Database Connection Settings
// Update these 4 values as per your hosting (cPanel -> MySQL Databases)
// =========================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'amc_system');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
