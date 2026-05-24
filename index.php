<?php
session_start();
if (isset($_SESSION['username'])){
  header("Location: dashboard");
exit();
}
  header ("Location: login.php");
?>
