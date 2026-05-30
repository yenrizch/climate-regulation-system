<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

require 'config.php';
require 'fpdf.php';

date_default_timezone_set('Asia/Manila');

$stmt = $conn->query("SELECT id, temperature, humidity, fan_status, time FROM climate_data ORDER BY id DESC LIMIT 100");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// ── Header Banner ──────────────────────────────────────────────
$pdf->SetFillColor(27, 67, 50);       // dark forest green
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Helvetica', 'B', 16);
$pdf->Cell(0, 14, 'Lettuce Greenhouse - Climate Data Report', 0, 1, 'C', true);

// Sub-header row
$pdf->SetFillColor(45, 106, 79);
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(0, 7, 'Badsbro Farm: Garden and Cafe  |  Maganay, Zamboanga Sibugay  |  Generated: ' . date('F d, Y  h:i:s A') . ' PHT', 0, 1, 'C', true);

$pdf->Ln(5);

// ── Summary boxes ──────────────────────────────────────────────
$total   = count($rows);
$avgTemp = $total > 0 ? round(array_sum(array_column($rows, 'temperature')) / $total, 1) : 'N/A';
$avgHum  = $total > 0 ? round(array_sum(array_column($rows, 'humidity'))    / $total, 1) : 'N/A';
$fanOnCount  = count(array_filter($rows, fn($r) => $r['fan_status'] === 'ON'));
$fanOffCount = $total - $fanOnCount;

$boxW = 66;
$boxes = [
    ['Total Records',   $total,          27, 67, 50],
    ['Avg Temperature', $avgTemp . ' C', 255, 107, 53],
    ['Avg Humidity',    $avgHum  . ' %', 79, 172, 254],
    ['Fan ON Count',    $fanOnCount,     230, 81, 0],
];

foreach ($boxes as $box) {
    [$label, $value, $r, $g, $b] = $box;
    $pdf->SetFillColor($r, $g, $b);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell($boxW, 8, $value, 0, 0, 'C', true);
    $pdf->SetX($pdf->GetX() - $boxW);
    // We'll do label on next line manually — use two-line cell trick
    $pdf->SetFont('Helvetica', '', 7);
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->SetXY($x, $y + 8);
    $pdf->SetFillColor($r - 20 < 0 ? 0 : $r - 20, $g - 20 < 0 ? 0 : $g - 20, $b - 20 < 0 ? 0 : $b - 20);
    $pdf->Cell($boxW, 5, strtoupper($label), 0, 0, 'C', true);
    $pdf->SetXY($x + $boxW + 2, $y);
}

$pdf->Ln(16);
$pdf->Ln(3);

// ── Table ──────────────────────────────────────────────────────
// Column widths (landscape A4 usable ~277mm minus margins)
$cols = [
    'ID'              => 18,
    'Temperature (C)' => 52,
    'Humidity (%)'    => 52,
    'Fan Status'      => 52,
    'Date & Time (PHT)' => 100,
];

// Table header
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->SetFillColor(220, 240, 220);
$pdf->SetTextColor(27, 67, 50);
$pdf->SetDrawColor(180, 210, 180);
$pdf->SetLineWidth(0.3);

foreach ($cols as $heading => $width) {
    $pdf->Cell($width, 9, $heading, 1, 0, 'C', true);
}
$pdf->Ln();

// Table rows
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetDrawColor(210, 230, 210);
$fill = false;

foreach ($rows as $row) {
    // Alternate row shading
    if ($fill) {
        $pdf->SetFillColor(240, 250, 242);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }

    // Fan status colour coding
    $fanStatus = strtoupper($row['fan_status']);
    if ($fanStatus === 'ON') {
        $pdf->SetTextColor(180, 60, 0);
    } else {
        $pdf->SetTextColor(30, 100, 50);
    }

    // Format PHT time
    $phtTime = date('M d, Y  h:i:s A', strtotime($row['time']) + (8 * 3600));

    $pdf->SetTextColor(40, 40, 40);
    $pdf->Cell($cols['ID'],              7, '#' . $row['id'],         1, 0, 'C', true);
    $pdf->Cell($cols['Temperature (C)'], 7, $row['temperature'] . ' C', 1, 0, 'C', true);
    $pdf->Cell($cols['Humidity (%)'],    7, $row['humidity']    . ' %', 1, 0, 'C', true);

    // Colour the fan cell
    if ($fanStatus === 'ON') {
        $pdf->SetFillColor(255, 243, 224);
        $pdf->SetTextColor(180, 60, 0);
    } else {
        $pdf->SetFillColor(232, 245, 233);
        $pdf->SetTextColor(30, 100, 50);
    }
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell($cols['Fan Status'], 7, $fanStatus, 1, 0, 'C', true);
    $pdf->SetFont('Helvetica', '', 8);

    // Reset for time cell
    if ($fill) {
        $pdf->SetFillColor(240, 250, 242);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Cell($cols['Date & Time (PHT)'], 7, $phtTime, 1, 1, 'C', true);

    $fill = !$fill;
}

// ── Footer ─────────────────────────────────────────────────────
$pdf->Ln(4);
$pdf->SetDrawColor(180, 210, 180);
$pdf->SetLineWidth(0.4);
$pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Helvetica', 'I', 7);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 5, 'Lettuce Greenhouse Monitoring System  |  Total records shown: ' . $total . '  |  ' . date('Y'), 0, 0, 'C');

// ── Output ─────────────────────────────────────────────────────
$pdf->Output('D', 'climate_data_' . date('Y-m-d') . '.pdf');
exit;
