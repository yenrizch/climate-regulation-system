<?php
// ─── Database (Railway MySQL) ────────────────────────────────────────────────
$host = "kodama.proxy.rlwy.net";
$user = "root";
$pass = getenv("DB_PASS");   // set DB_PASS in Railway → Variables
$db   = "railway";
$port = "29496";

try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8",
        $user,
        $pass
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "DB connection failed: " . $e->getMessage()]));
}

// ─── Email (PHPMailer / Gmail SMTP) ─────────────────────────────────────────
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USERNAME',  'rizcathnova@gmail.com');
define('MAIL_PASSWORD',  'uixpqdovtantkaoi');
define('MAIL_FROM_NAME', 'Climate-system');
define('MAIL_RECIPIENT', 'yenrizche@gmail.com');

// ─── Alert thresholds ────────────────────────────────────────────────────────
define('TEMP_WARNING',  29);   // °C  — WARNING email above this
define('TEMP_CRITICAL', 32);   // °C  — CRITICAL email above this
define('HUM_WARNING',   80);   // %   — WARNING email above this
define('HUM_CRITICAL',  90);   // %   — CRITICAL email above this
define('FAN_OFF_TEMP',  28);   // °C  — CRITICAL if fan OFF above this
define('COOLDOWN_MIN',  15);   // minutes between repeat alerts of same type
