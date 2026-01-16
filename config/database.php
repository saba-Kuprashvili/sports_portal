<?php
// მონაცემთა ბაზის კონფიგურაცია
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sports_portal');

// PDO კავშირის დამყარება
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("კავშირის შეცდომა: " . $e->getMessage());
}

// სესიის დაწყება
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// საიტის კონფიგურაცია
define('SITE_NAME', 'სპორტული პორტალი');
define('SITE_URL', 'http://localhost/sports-portal');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// დროის ზონა
date_default_timezone_set('Asia/Tbilisi');
?>