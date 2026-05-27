<?php
require 'config.php';
$stmt = $conn->query("DESCRIBE climate_data");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col){
    echo $col['Field'] . "<br>";
}
?>