<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
  public static function send(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody
  ): bool {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host       = $_ENV['MAIL_HOST']      ?? 'smtp.gmail.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = $_ENV['MAIL_USER']      ?? '';
      $mail->Password   = $_ENV['MAIL_PASS']      ?? '';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
      $mail->CharSet    = 'UTF-8';
      
      // Configurações de Timeout para evitar travamento
      $mail->Timeout = 10; // Timeout total de 10 segundos
      $mail->SMTPConnectTimeout = 5; // Timeout de conexão de 5 segundos

      $mail->setFrom(
        $_ENV['MAIL_FROM']      ?? '',
        $_ENV['MAIL_FROM_NAME'] ?? 'ContrataPorto'
      );
      $mail->addAddress($toEmail, $toName);
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $htmlBody;
      $mail->send();
      error_log("[MAILER] Enviado para: $toEmail — Assunto: $subject");
      return true;
    } catch (Exception $e) {
      error_log("[MAILER] ERRO para $toEmail: " . $e->getMessage());
      return false;
    } catch (\Throwable $t) {
      error_log("[MAILER] ERRO FATAL para $toEmail: " . $t->getMessage());
      return false;
    }
  }
}
