<?php
session_start();

$conn = new mysqli("localhost", "root", "", "greenhouse_db");

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users
            WHERE username='$username'
            AND password='$password'";

    $result = $conn->query($sql);

    if($result->num_rows > 0){

        $_SESSION['username'] = $username;

        header("Location: dashboard.php");

    }else{
        echo "Invalid account";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Greenhouse Login</title>

<style>

body{
    background:#e8f5e9;
    font-family:Arial;
}

.login-box{

    width:350px;
    background:white;
    margin:100px auto;
    padding:30px;
    border-radius:15px;
    box-shadow:0 0 10px gray;
}

h2{
    text-align:center;
    color:#2e7d32;
}

input{

    width:100%;
    padding:12px;
    margin-top:10px;
    border:1px solid #ccc;
    border-radius:5px;
}

button{

    width:100%;
    padding:12px;
    background:#4caf50;
    color:white;
    border:none;
    margin-top:15px;
    border-radius:5px;
    font-size:16px;
}

</style>
</head>

<body>

<div class="login-box">

<h2>Lettuce Greenhouse</h2>

<form method="POST">

<input type="text" name="username" placeholder="Username" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit" name="login">
Login
</button>

</form>

</div>

</body>
</html>