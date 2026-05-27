<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History - Lettuce Greenhouse</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f9f4; margin: 0; padding: 20px; }
        h2 { color: #226022; }
        .export-buttons { margin-bottom: 20px; display: flex; gap: 12px; }
        .btn-csv {
            background: #2e7d32; color: white; padding: 10px 24px;
            border: none; border-radius: 6px; cursor: pointer;
            font-size: 14px; text-decoration: none;
        }
        .btn-pdf {
            background: #c62828; color: white; padding: 10px 24px;
            border: none; border-radius: 6px; cursor: pointer;
            font-size: 14px; text-decoration: none;
        }
        .btn-csv:hover { background: #1b5e20; }
        .btn-pdf:hover { background: #8e0000; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th { background: #2e7d32; color: white; padding: 12px; text-align: left; }
        td { padding: 10px 12px; border-bottom: 1px solid #e0e0e0; }
        tr:hover { background: #f1f8f1; }
        .badge-on  { background: #e8f5e9; color: #2e7d32; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
        .badge-off { background: #ffebee; color: #c62828; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
    </style>
</head>
<body>

<h2>Climate Data History</h2>

<!-- Export Buttons -->
<div class="export-buttons">
    <a href="export_csv.php" class="btn-csv">Download CSV</a>
    <a href="export_pdf.php" class="btn-pdf">Download PDF</a>
</div>

<!-- Data Table -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Temperature</th>
            <th>Humidity</th>
            <th>Fan Status</th>
            <th>Date & Time</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $conn->query("SELECT * FROM climate_data ORDER BY id DESC LIMIT 100");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
        <tr>
            <td>#<?= $row['id'] ?></td>
            <td><?= $row['temperature'] ?> °C</td>
            <td><?= $row['humidity'] ?> %</td>
            <td>
                <span class="<?= $row['fan_status'] === 'ON' ? 'badge-on' : 'badge-off' ?>">
                    <?= $row['fan_status'] ?>
                </span>
            </td>
            <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>