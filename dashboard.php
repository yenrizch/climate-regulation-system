<?php

session_start();

if(!isset($_SESSION['username'])){

    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "greenhouse_db");

if($conn->connect_error){

    die("Connection Failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM sensor_data ORDER BY id DESC LIMIT 50");

$latest = $conn->query("SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1")->fetch_assoc();

$total = $conn->query("SELECT COUNT(*) as total FROM sensor_data")->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta http-equiv="refresh" content="5">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Lettuce Greenhouse Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:'Poppins',sans-serif;

    background:linear-gradient(135deg,#e8f5e9,#f1f8e9);

    min-height:100vh;

    padding:25px;
}

/* HEADER */

.header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:30px;
}

.logo-section{

    display:flex;

    align-items:center;

    gap:15px;
}

.logo{

    width:70px;

    height:70px;

    background:linear-gradient(135deg,#2e7d32,#66bb6a);

    border-radius:20px;

    display:flex;

    justify-content:center;

    align-items:center;

    color:white;

    font-size:32px;

    box-shadow:0 8px 20px rgba(0,0,0,0.2);
}

.title h1{

    color:#1b5e20;

    font-size:32px;

    font-weight:700;
}

.title p{

    color:#558b2f;

    font-size:14px;
}

.logout-btn{

    background:#d32f2f;

    color:white;

    text-decoration:none;

    padding:12px 18px;

    border-radius:12px;

    font-weight:600;

    transition:0.3s;
}

.logout-btn:hover{

    background:#b71c1c;
}

/* CARDS */

.cards{

    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));

    gap:20px;

    margin-bottom:30px;
}

.card{

    background:white;

    border-radius:20px;

    padding:25px;

    box-shadow:0 8px 20px rgba(0,0,0,0.08);

    position:relative;

    overflow:hidden;
}

.card::before{

    content:"";

    position:absolute;

    top:0;

    left:0;

    width:100%;

    height:6px;

    background:linear-gradient(90deg,#2e7d32,#81c784);
}

.card h3{

    color:#558b2f;

    margin-bottom:10px;

    font-size:16px;
}

.card h1{

    color:#1b5e20;

    font-size:38px;

    font-weight:700;
}

.card p{

    color:#777;

    margin-top:5px;
}

/* STATUS COLORS */

.on{

    color:#ff6f00;
}

.off{

    color:#2e7d32;
}

/* TABLE */

.table-container{

    background:white;

    border-radius:20px;

    overflow:hidden;

    box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

.table-header{

    background:linear-gradient(135deg,#2e7d32,#66bb6a);

    color:white;

    padding:20px;
}

.table-header h2{

    font-size:22px;
}

table{

    width:100%;

    border-collapse:collapse;
}

table th{

    background:#388e3c;

    color:white;

    padding:15px;

    text-align:left;
}

table td{

    padding:14px;

    border-bottom:1px solid #eee;
}

table tr:hover{

    background:#f1f8e9;
}

/* BADGES */

.badge{

    padding:6px 12px;

    border-radius:20px;

    font-size:13px;

    font-weight:600;
}

.badge-on{

    background:#fff3e0;

    color:#ef6c00;
}

.badge-off{

    background:#e8f5e9;

    color:#2e7d32;
}

/* FOOTER */

.footer{

    margin-top:25px;

    text-align:center;

    color:#777;

    font-size:14px;
}

/* RESPONSIVE */

@media(max-width:768px){

    .header{

        flex-direction:column;

        gap:20px;
    }

    .title h1{

        font-size:24px;
    }

    table{

        font-size:13px;
    }
}

</style>

</head>

<body>

<!-- HEADER -->

<div class="header">

    <div class="logo-section">

        <div class="logo">
            🥬
        </div>

        <div class="title">

            <h1>Lettuce Greenhouse</h1>

            <p>Real-Time Climate Monitoring System</p>

        </div>

    </div>

    <a href="logout.php" class="logout-btn">
        Logout
    </a>

</div>

<!-- CARDS -->

<div class="cards">

    <!-- TEMPERATURE -->

    <div class="card">

        <h3>Temperature</h3>

        <h1>
            <?= $latest['temperature']; ?>°C
        </h1>

        <p>Live greenhouse temperature</p>

    </div>

    <!-- HUMIDITY -->

    <div class="card">

        <h3>Humidity</h3>

        <h1>
            <?= $latest['humidity']; ?>%
        </h1>

        <p>Current air moisture</p>

    </div>

    <!-- FAN STATUS -->

    <div class="card">

        <h3>Fan Status</h3>

        <h1 class="<?= strtolower($latest['fan_status']); ?>">

            <?= $latest['fan_status']; ?>

        </h1>

        <p>Automatic ventilation</p>

    </div>

    <!-- TOTAL RECORDS -->

    <div class="card">

        <h3>Total Records</h3>

        <h1>

            <?= $total['total']; ?>

        </h1>

        <p>Database entries collected</p>

    </div>

</div>

<!-- TABLE -->

<div class="table-container">

    <div class="table-header">

        <h2>Live Greenhouse Data</h2>

    </div>

    <table>

        <tr>

            <th>ID</th>

            <th>Temperature</th>

            <th>Humidity</th>

            <th>Fan Status</th>

            <th>Time</th>

        </tr>

        <?php while($row = $result->fetch_assoc()){ ?>

        <tr>

            <td><?= $row['id']; ?></td>

            <td><?= $row['temperature']; ?> °C</td>

            <td><?= $row['humidity']; ?> %</td>

            <td>

                <?php

                if($row['fan_status'] == "ON"){

                    echo "<span class='badge badge-on'>ON</span>";

                }else{

                    echo "<span class='badge badge-off'>OFF</span>";
                }

                ?>

            </td>

            <td><?= $row['time']; ?></td>

        </tr>

        <?php } ?>

    </table>

</div>

<!-- FOOTER -->

<div class="footer">

    <p>
        Lettuce Greenhouse Monitoring System © 2026
    </p>

</div>

</body>
</html>

<?php

$conn->close();

?>