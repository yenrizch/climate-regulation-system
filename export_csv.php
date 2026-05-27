<?php
require 'config.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="climate_data_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Temperature (°C)', 'Humidity (%)', 'Fan Status', 'Date & Time']);

$stmt = $conn->query("SELECT id, temperature, humidity, fan_status, time FROM climate_data ORDER BY id DESC");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['temperature'],
        $row['humidity'],
        $row['fan_status'],
        $row['time']   // ← fixed: was created_at
    ]);
}

fclose($output);
exit;
?>
