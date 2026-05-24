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

    $sql = "INSERT INTO climate_data
    (temperature, humidity, fan_status)
    VALUES
    ('$temperature', '$humidity', '$fan_status')";

    if(mysqli_query($conn, $sql)){

        echo "Data inserted successfully";

    } else {

        echo "Database Error: " . mysqli_error($conn);
    }

} else {

    echo "Missing parameters";
}

mysqli_close($conn);

?>
