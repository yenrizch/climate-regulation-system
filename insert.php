<?php

require 'config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

if(
    isset($_GET['temperature']) &&
    isset($_GET['humidity']) &&
    isset($_GET['fan_status'])
){

    $temperature = $_GET['temperature'];
    $humidity    = $_GET['humidity'];
    $fan_status  = $_GET['fan_status'];

    // INSERT DATA INTO DATABASE
    $sql = $conn->prepare("
        INSERT INTO climate_data
        (temperature, humidity, fan_status)
        VALUES (?, ?, ?)
    ");

    if($sql->execute([$temperature, $humidity, $fan_status])){

        // IDENTIFY STATUS
        $tempStatus = ($temperature > 29)
            ? "HIGH"
            : "NORMAL";

        $humidityStatus = ($humidity > 80)
            ? "HIGH"
            : "NORMAL";

        // CREATE EMAIL
        $mail = new PHPMailer(true);

        try{

            // SMTP SETTINGS
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            // YOUR GMAIL
            $mail->Username   = 'rizcathnov@gmail.com';

            // GMAIL APP PASSWORD
            $mail->Password   = 'uojzsqncvdatfqzq';

            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // SENDER
            $mail->setFrom(
                'riznovacath.com',
                'Lettuce Greenhouse'
            );

            // RECEIVER
            $mail->addAddress(
                'yenrizch@gmail.com'
            );

            // EMAIL CONTENT
            $mail->isHTML(false);

            $mail->Subject =
                'Greenhouse Notification';

            $mail->Body = "

Greenhouse Alert!

Temperature: $temperature °C ($tempStatus)

Humidity: $humidity % ($humidityStatus)

Fan Status: $fan_status

The cooling fan has been activated automatically.

";
            // SEND EMAIL
            $mail->send();

            echo "Data inserted and email sent";

        }catch(Exception $e){

            echo "Email Error: {$mail->ErrorInfo}";
        }

    } else {

        echo "Database Error";
    }

} else {

    echo "Missing parameters";
}
?>
