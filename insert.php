<?php
require 'config.php';

if(
    isset($_GET['temperature']) &&
    isset($_GET['humidity']) &&
    isset($_GET['fan_status'])
){
    $temperature = $_GET['temperature'];
    $humidity    = $_GET['humidity'];
    $fan_status  = $_GET['fan_status'];

    $sql = $conn->prepare("INSERT INTO climate_data (temperature, humidity, fan_status) VALUES (?, ?, ?)");

    if($sql->execute([$temperature, $humidity, $fan_status])){
        echo "Data inserted successfully";
    } else {
        echo "Database Error";
    }
} else {
    echo "Missing parameters";
}
?>
