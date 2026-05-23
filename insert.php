<?php

include 'config.php';

// CHECK IF VALUES EXIST
if (
    isset($_GET['temperature']) &&
    isset($_GET['humidity']) &&
    isset($_GET['fan_status'])
) {

    // GET VALUES FROM ESP32
    $temperature = $_GET['temperature'];
    $humidity = $_GET['humidity'];
    $fan_status = $_GET['fan_status'];

    // INSERT DATA
    $sql = "INSERT INTO sensor_data
    (temperature, humidity, fan_status)

    VALUES
    ('$temperature', '$humidity', '$fan_status')";

    // EXECUTE QUERY
    if ($conn->query($sql) === TRUE) {

        echo "Data inserted successfully";

    } else {

        echo "Database Error: " . $conn->error;
    }

} else {

    echo "Missing parameters";
}

// CLOSE CONNECTION
$conn->close();

?>
