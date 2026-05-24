<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

include 'config.php';

$sql = "SELECT * FROM climate_data ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    <style>

        body{
            font-family: Arial;
            background:#f2f2f2;
            padding:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:white;
        }

        th, td{
            padding:12px;
            border:1px solid #ccc;
            text-align:center;
        }

        th{
            background:green;
            color:white;
        }

        .top{
            display:flex;
            justify-content:space-between;
            margin-bottom:20px;
        }

        a{
            background:red;
            color:white;
            padding:10px;
            text-decoration:none;
        }

    </style>

</head>
<body>

<div class="top">

    <h2>Climate Regulation Dashboard</h2>

    <a href="logout.php">Logout</a>

</div>

<table>

    <tr>
        <th>ID</th>
        <th>Temperature</th>
        <th>Humidity</th>
        <th>Fan Status</th>
        <th>Time</th>
    </tr>

<?php

while($row = mysqli_fetch_assoc($result)){

?>

<tr>

    <td><?php echo $row['id']; ?></td>

    <td><?php echo $row['temperature']; ?></td>

    <td><?php echo $row['humidity']; ?></td>

    <td><?php echo $row['fan_status']; ?></td>

    <td><?php echo $row['time']; ?></td>

</tr>

<?php
}
?>

</table>

</body>
</html>
