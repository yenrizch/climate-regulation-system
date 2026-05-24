<?php
session_start();
require 'config.php';

$error = "";

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $sql->execute([$username, $password]);

    if($sql->rowCount() > 0){
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
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
    box-sizing:border-box;
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
    cursor:pointer;
}
button:hover{
    background:#388e3c;
}
.error{
    color:red;
    text-align:center;
    margin-top:10px;
    font-size:14px;
}
</style>
</head>
<body>
<div class="login-box">
    <h2>🥬 Lettuce Greenhouse</h2>
    <?php if($error != "") echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>
