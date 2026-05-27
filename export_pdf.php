<?php
require 'config.php';
require 'fpdf.php';
$stmt = $conn->query("SELECT id, temperature, humidity, fan_status, time FROM climate_data ORDER BY id DESC LIMIT 100");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pdf = new FPDF();
$pdf->AddPage('L');
// Title
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(34, 85, 34);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 12, 'Lettuce Greenhouse - Climate Data Report', 0, 1, 'C', true);
$pdf->Ln(4);
// Date generated
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, 'Generated: ' . date('Y-m-d H:i:s'), 0, 1, 'R');
$pdf->Ln(2);
// Table header
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(220, 240, 220);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(180, 180, 180);
$pdf->Cell(20,  10, 'ID',              1, 0, 'C', true);
$pdf->Cell(55,  10, 'Temperature (C)', 1, 0, 'C', true);
$pdf->Cell(55,  10, 'Humidity (%)',    1, 0, 'C', true);
$pdf->Cell(55,  10, 'Fan Status',      1, 0, 'C', true);
$pdf->Cell(90,  10, 'Date & Time',     1, 1, 'C', true);
// Table rows
$pdf->SetFont('Arial', '', 9);
$fill = false;
foreach($rows as $row) {
    $pdf->SetFillColor($fill ? 245 : 255, $fill ? 250 : 255, $fill ? 245 : 255);
    $pdf->Cell(20,  8, $row['id'],          1, 0, 'C', true);
    $pdf->Cell(55,  8, $row['temperature'], 1, 0, 'C', true);
    $pdf->Cell(55,  8, $row['humidity'],    1, 0, 'C', true);
    $pdf->Cell(55,  8, $row['fan_status'],  1, 0, 'C', true);
    $pdf->Cell(90,  8, $row['time'],        1, 1, 'C', true);
    $fill = !$fill;
}
$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, 'Total records shown: ' . count($rows), 0, 1, 'R');
$pdf->Output('D', 'climate_data_' . date('Y-m-d') . '.pdf');
exit;
?>
