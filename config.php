<?php

$host = "mysql.railway.internal";
$user = "root";
$password = "GdEtSmIIVmxVJMJgIShDkOAAgCVmFUlx";
$database = "railway";
$port = "3306";

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
