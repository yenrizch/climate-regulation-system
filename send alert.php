<?php
// send_alert.php
// ─────────────────────────────────────────────────────────────────────────────
// Reusable email alert helper.
// Call sendGreenhouseAlert() from insert.php (or check.php) whenever a
// threshold is breached.  Returns true on success, false on failure.
// ─────────────────────────────────────────────────────────────────────────────

require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'Exception.php';
require_once 'PHPMailer.php';
require_once 'SMTP.php';

// ─── Cooldown helpers (use DB so they survive across requests) ───────────────

function lastAlertAge(PDO $conn, string $type): int {
    // Returns how many minutes ago this alert type last fired.
    // Returns 9999 if it has never fired.
    $q = $conn->prepare(
        "SELECT sent_at FROM alert_log
         WHERE alert_type = ?
         ORDER BY sent_at DESC
         LIMIT 1"
    );
    $q->execute([$type]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) return 9999;
    return (int) round((time() - strtotime($row['sent_at'])) / 60);
}

function logAlert(PDO $conn, string $type, float $temp, float $hum, string $fan): void {
    $q = $conn->prepare(
        "INSERT INTO alert_log (alert_type, temp, humidity, fan_status)
         VALUES (?, ?, ?, ?)"
    );
    $q->execute([$type, $temp, $hum, $fan]);
}

// ─── HTML email body builder ─────────────────────────────────────────────────

function buildEmailBody(string $severity, string $reason, float $temp, float $hum, string $fan): string {
    $headerColor = ($severity === 'CRITICAL') ? '#D85A30' : '#BA7517';
    $time        = date('F d, Y · h:i A') . ' PHT';

    $tempBadge = ($temp > TEMP_WARNING)
        ? "<span style='color:#D85A30;font-weight:bold'>{$temp}°C ▲ HIGH</span>"
        : "<span style='color:#1D6F42;font-weight:bold'>{$temp}°C ✓ NORMAL</span>";

    $humBadge = ($hum > HUM_WARNING)
        ? "<span style='color:#D85A30;font-weight:bold'>{$hum}% ▲ HIGH</span>"
        : "<span style='color:#1D6F42;font-weight:bold'>{$hum}% ✓ NORMAL</span>";

    $fanBadge = (strtoupper($fan) === 'ON')
        ? "<span style='color:#1D6F42;font-weight:bold'>ON ✓</span>"
        : "<span style='color:#D85A30;font-weight:bold'>OFF ✗</span>";

    return "
<div style='font-family:Arial,sans-serif;max-width:560px;margin:0 auto;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;'>

  <div style='background:{$headerColor};padding:20px 24px;'>
    <h2 style='color:#ffffff;margin:0;font-size:18px;font-weight:700;'>
      [{$severity}] Lettuce Greenhouse Alert
    </h2>
    <p style='color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:13px;'>
      Climate-system &nbsp;·&nbsp; {$time}
    </p>
  </div>

  <div style='padding:22px 24px;background:#ffffff;'>
    <p style='font-size:14px;color:#374151;margin:0 0 18px;line-height:1.6;'>
      {$reason}
    </p>

    <table style='width:100%;border-collapse:collapse;font-size:14px;'>
      <tr style='background:#f9fafb;'>
        <td style='padding:10px 14px;color:#6b7280;border:1px solid #e5e7eb;width:40%;'>🌡️ Temperature</td>
        <td style='padding:10px 14px;border:1px solid #e5e7eb;'>{$tempBadge}</td>
      </tr>
      <tr>
        <td style='padding:10px 14px;color:#6b7280;border:1px solid #e5e7eb;'>💧 Humidity</td>
        <td style='padding:10px 14px;border:1px solid #e5e7eb;'>{$humBadge}</td>
      </tr>
      <tr style='background:#f9fafb;'>
        <td style='padding:10px 14px;color:#6b7280;border:1px solid #e5e7eb;'>🌀 Fan Status</td>
        <td style='padding:10px 14px;border:1px solid #e5e7eb;'>{$fanBadge}</td>
      </tr>
    </table>

    <div style='margin-top:20px;padding:12px 16px;background:#fef3c7;border-left:4px solid #BA7517;border-radius:4px;'>
      <p style='margin:0;font-size:13px;color:#92400e;'>
        <strong>Action needed:</strong> Please check your greenhouse immediately and adjust the climate conditions.
      </p>
    </div>

    <p style='font-size:11px;color:#9ca3af;margin:18px 0 0;border-top:1px solid #f3f4f6;padding-top:14px;'>
      Lettuce Greenhouse Climate Monitoring System &nbsp;·&nbsp; Auto-generated alert &nbsp;·&nbsp; Do not reply to this email.
    </p>
  </div>

</div>";
}

// ─── Main send function ──────────────────────────────────────────────────────

function sendGreenhouseAlert(
    PDO    $conn,
    string $type,
    string $severity,
    string $subject,
    string $reason,
    float  $temp,
    float  $hum,
    string $fan
): bool {
    // Cooldown guard — skip if same alert type fired recently
    if (lastAlertAge($conn, $type) < COOLDOWN_MIN) {
        return false;  // still within cooldown window, do not send
    }

    try {
        $mail = new PHPMailer(true);

        // SMTP setup
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        // From / To
        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress(MAIL_RECIPIENT);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = buildEmailBody($severity, $reason, $temp, $hum, $fan);
        $mail->AltBody = strip_tags($reason)
            . "\nTemperature: {$temp}°C | Humidity: {$hum}% | Fan: {$fan}";

        $mail->send();

        // Record in DB so cooldown works for the next request
        logAlert($conn, $type, $temp, $hum, $fan);

        return true;

    } catch (Exception $e) {
        // Email failed — do not crash the page, just log it
        error_log('PHPMailer error [' . $type . ']: ' . $mail->ErrorInfo);
        return false;
    }
}
