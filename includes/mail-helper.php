<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/mail-config.php';

/**
 * Sends an email using PHPMailer and SMTP.
 * 
 * @param string $to Email address to send to.
 * @param string $subject Subject of the email.
 * @param string $message Body of the email (HTML supported).
 * @param string $replyTo (Optional) Reply-to email address.
 * @return array Array with success status and message.
 */
function sendMail($to, $subject, $message, $replyTo = null) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAILHOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = USERNAME;
        $mail->Password   = PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom(SEND_FROM, SEND_FROM_NAME);
        $mail->addAddress($to);
        
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return ["success" => true, "message" => "Message sentiment successfully"];
    } catch (Exception $e) {
        return ["success" => false, "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}
?>
