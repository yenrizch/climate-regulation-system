<?php
// insert.php
// ─────────────────────────────────────────────────────────────────────────────
// Receives sensor data from Arduino via GET request, saves it to the DB,
// then checks thresholds and sends email alerts if needed.
//
// Example Arduino call:
//   http://yoursite.com/insert.php?temperature=31&humidity=85&fan_status=ON
// ─────────────────────────────────────────────────────────────────────────────

require_once 'config.php';
require_once 'send_alert.php';

// ─── 1. Validate incoming parameters ────────────────────────────────────────
if (
    !isset($_GET['temperature']) ||
    !isset($_GET['humidity'])    ||
    !isset($_GET['fan_status'])
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing parameters: temperature, humidity, fan_status are required."]);
    exit;
}

$temperature = floatval($_GET['temperature']);
$humidity    = floatval($_GET['humidity']);
$fan_status  = trim($_GET['fan_status']);   // expected: "ON" or "OFF"

// ─── 2. Save reading to database ────────────────────────────────────────────
$sql = $conn->prepare(
    "INSERT INTO climate_data (temperature, humidity, fan_status)
     VALUES (?, ?, ?)"
);

if (!$sql->execute([$temperature, $humidity, $fan_status])) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database insert failed."]);
    exit;
}

// ─── 3. Check thresholds and send alerts ─────────────────────────────────────
// sendGreenhouseAlert() is defined in send_alert.php.
// It checks cooldown automatically — will not send duplicate alerts
// within COOLDOWN_MIN minutes (set to 15 in config.php).

$emailLog = [];

// --- Temperature: CRITICAL (≥ 32°C) -----------------------------------------
if ($temperature >= TEMP_CRITICAL) {
    $sent = sendGreenhouseAlert(
        $conn,
        'temp_critical',
        'CRITICAL',
        '[CRITICAL] Temperature dangerously high — ' . $temperature . '°C',
        "Temperature is <strong>{$temperature}°C</strong>, which is above the critical limit of " . TEMP_CRITICAL . "°C. Immediate action required.",
        $temperature, $humidity, $fan_status
    );
    $emailLog[] = 'temp_critical: ' . ($sent ? 'sent' : 'skipped (cooldown)');
}
// --- Temperature: WARNING (> 29°C and below critical) -------------------------
elseif ($temperature > TEMP_WARNING) {
    $sent = sendGreenhouseAlert(
        $conn,
        'temp_high',
        'WARNING',
        '[WARNING] Temperature above normal — ' . $temperature . '°C',
        "Temperature is <strong>{$temperature}°C</strong>, above the warning threshold of " . TEMP_WARNING . "°C.",
        $temperature, $humidity, $fan_status
    );
    $emailLog[] = 'temp_high: ' . ($sent ? 'sent' : 'skipped (cooldown)');
}

// --- Humidity: CRITICAL (≥ 90%) ----------------------------------------------
if ($humidity >= HUM_CRITICAL) {
    $sent = sendGreenhouseAlert(
        $conn,
        'hum_critical',
        'CRITICAL',
        '[CRITICAL] Humidity dangerously high — ' . $humidity . '%',
        "Humidity is <strong>{$humidity}%</strong>, above the critical limit of " . HUM_CRITICAL . "%. Mold and disease risk is very high.",
        $temperature, $humidity, $fan_status
    );
    $emailLog[] = 'hum_critical: ' . ($sent ? 'sent' : 'skipped (cooldown)');
}
// --- Humidity: WARNING (> 80% and below critical) ----------------------------
elseif ($humidity > HUM_WARNING) {
    $sent = sendGreenhouseAlert(
        $conn,
        'hum_high',
        'WARNING',
        '[WARNING] Humidity above normal — ' . $humidity . '%',
        "Humidity is <strong>{$humidity}%</strong>, above the warning threshold of " . HUM_WARNING . "%.",
        $temperature, $humidity, $fan_status
    );
    $emailLog[] = 'hum_high: ' . ($sent ? 'sent' : 'skipped (cooldown)');
}

// --- Fan OFF while temperature is high ----------------------------------------
if (strtoupper($fan_status) === 'OFF' && $temperature > FAN_OFF_TEMP) {
    $sent = sendGreenhouseAlert(
        $conn,
        'fan_off',
        'CRITICAL',
        '[CRITICAL] Fan is OFF while temperature is high — ' . $temperature . '°C',
        "Fan is <strong>OFF</strong> but temperature is {$temperature}°C. Please check the hardware immediately.",
        $temperature, $humidity, $fan_status
    );
    $emailLog[] = 'fan_off: ' . ($sent ? 'sent' : 'skipped (cooldown)');
}

// ─── 4. Return JSON response to Arduino ──────────────────────────────────────
echo json_encode([
    "status"       => "ok",
    "message"      => "Data inserted successfully.",
    "temperature"  => $temperature,
    "humidity"     => $humidity,
    "fan_status"   => $fan_status,
    "alerts_fired" => count(array_filter($emailLog, fn($l) => str_contains($l, 'sent'))),
    "email_log"    => $emailLog,
]);
