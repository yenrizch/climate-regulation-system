<?php

$host = "kodama.proxy.rlwy.net";
$user = "root";
$password = "GdEtSmIIVmxVJMJgIShDkOAAgCVmFUlx";
$database = "railway";
$port = "46152";

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
