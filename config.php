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
?>
