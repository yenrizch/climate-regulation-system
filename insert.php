<?php

include 'config.php';

if(isset($_POST['submit'])){

    $temperature = $_POST['temperature'];
    $humidity = $_POST['humidity'];
    $fan_status = $_POST['fan_status'];

    $sql = "INSERT INTO climate_data 
    (temperature, humidity, fan_status)
    
    VALUES 
    
    ('$temperature', '$humidity', '$fan_status')";

    if(mysqli_query($conn, $sql)){

        echo "Data Inserted";

    }else{

        echo "Error: " . mysqli_error($conn);

    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Data</title>

    <style>

        body{
            font-family:Arial;
            background:#f2f2f2;
        }

        .box{
            width:300px;
            margin:50px auto;
            background:white;
            padding:20px;
            border-radius:10px;
        }

        input{
            width:100%;
            padding:10px;
            margin-top:10px;
        }

        button{
            width:100%;
            padding:10px;
            margin-top:10px;
            background:green;
            color:white;
            border:none;
        }

    </style>
</head>
<body>

<div class="box">

<h2>Insert Climate Data</h2>

<form method="POST">

    <input type="text" name="temperature" placeholder="Temperature" required>

    <input type="text" name="humidity" placeholder="Humidity" required>

    <input type="text" name="fan_status" placeholder="Fan Status" required>

    <button type="submit" name="submit">Insert</button>

</form>

</div>

</body>
</html>
