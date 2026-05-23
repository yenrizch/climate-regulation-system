<?php

session_start();

include 'config.php';

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    // PREPARED STATEMENT
    $stmt = $conn->prepare(
        "SELECT * FROM users
         WHERE username = ?"
    );

    $stmt->bind_param("s", $username);

    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $row = $result->fetch_assoc();

        // VERIFY PASSWORD
        if(password_verify($password, $row['password'])){

            $_SESSION['user'] = $username;

            header("Location: dashboard.php");
            exit();

        } else {

            echo "Invalid password";
        }

    } else {

        echo "User not found";
    }

    $stmt->close();
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

</style>

</head>

<body>

<div class="login-box">

<h2>Lettuce Greenhouse</h2>

<form method="POST">

<input
type="text"
name="username"
placeholder="Username"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button type="submit" name="login">
Login
</button>

</form>

</div>

</body>
</html>
