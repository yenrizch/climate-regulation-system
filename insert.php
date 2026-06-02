<?php

require 'config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

if (
    isset($_GET['temperature']) &&
    isset($_GET['humidity']) &&
    isset($_GET['fan_status'])
) {

    $temperature = $_GET['temperature'];
    $humidity    = $_GET['humidity'];
    $fan_status  = $_GET['fan_status'];

    // INSERT DATA
    $sql = $conn->prepare(
        "INSERT INTO climate_data (temperature, humidity, fan_status)
         VALUES (?, ?, ?)"
    );

    if ($sql->execute([$temperature, $humidity, $fan_status])) {

        // STATUS CHECK
        $tempStatus = ($temperature > 29) ? "HIGH" : "NORMAL";
        $humidityStatus = ($humidity > 80) ? "HIGH" : "NORMAL";

        try {

            $mail = new PHPMailer(true);

            // Gmail SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            // YOUR GMAIL
            $mail->Username   = 'rizcathnova@gmail.com';

            // APP PASSWORD
            $mail->Password   = 'uixpqdovtantkaoi';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender
            $mail->setFrom(
                'rizcathnova@gmail.com',
                'Climate-system'
            );

            // Receiver
            $mail->addAddress(
                'yenrizch@gmail.com'
            );

            $mail->Subject = 'Greenhouse Climate System Notification';

            $mail->Body =
"Greenhouse Monitoring Update

Temperature: {$temperature} °C ({$tempStatus})

Humidity: {$humidity}% ({$humidityStatus})

Fan Status: {$fan_status}

System Status:
The greenhouse climate is being monitored automatically.";

            $mail->send();

            echo "Data inserted and email sent.";

        } catch (Exception $e) {

            echo "Data inserted but email failed: "
                 . $mail->ErrorInfo;
        }

    } else {

        echo "Database Error";
    }

} else {

    echo "Missing parameters";
}
?>
