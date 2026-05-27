<?php
require 'config.php';
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if(
    isset($_GET['temperature']) &&
    isset($_GET['humidity']) &&
    isset($_GET['fan_status'])
){
    $temperature = $_GET['temperature'];
    $humidity    = $_GET['humidity'];
    $fan_status  = $_GET['fan_status'];

    $sql = $conn->prepare("INSERT INTO climate_data (temperature, humidity, fan_status) VALUES (?, ?, ?)");

    if($sql->execute([$temperature, $humidity, $fan_status])){
        echo "Data inserted successfully";

        // --- EMAIL NOTIFICATION ---
        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_USER, 'Climate Regulation System');
            $mail->addAddress(SMTP_NOTIFY);

            $mail->isHTML(true);
            $mail->Subject = 'New Climate Data Recorded';
            $mail->Body    = "
                <h2>Climate Regulation System</h2>
                <p>A new record has been inserted:</p>
                <table border='1' cellpadding='8' cellspacing='0'>
                    <tr><td><b>Temperature</b></td><td>{$temperature} &deg;C</td></tr>
                    <tr><td><b>Humidity</b></td><td>{$humidity} %</td></tr>
                    <tr><td><b>Fan Status</b></td><td>{$fan_status}</td></tr>
                </table>
                <p>Recorded at: " . date('Y-m-d H:i:s') . "</p>
            ";

            $mail->send();

        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
        // --- END EMAIL NOTIFICATION ---

    } else {
        echo "Database Error";
    }
} else {
    echo "Missing parameters";
}
?>
