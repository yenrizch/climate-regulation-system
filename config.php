<?php
$host = "kodama.proxy.rlwy.net";
$user = "root";
$pass = getenv("DB_PASS");
$db   = "railway";
$port = "29496";

try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8",
        $user,
        $pass
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ✏️ CHANGE THESE 3 LINES ONLY
define('SMTP_HOST',   'smtp.gmail.com');
define('SMTP_PORT',   587);
define('SMTP_USER',   'rizcathnova@gmail.com'); // Gmail you just created
define('SMTP_PASS',   'irtl odth axje shta');     // App Password from Part 2
define('SMTP_NOTIFY', 'yenrizch@gmail.com'); // who receives the alert
?>
