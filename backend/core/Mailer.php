<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
  private static function getEnv(string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
  }

  public static function send(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody
  ): bool {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $mail = new PHPMailer(true);
    try {
      // Configurações do Servidor
      $mail->isSMTP();
      $mail->Host       = self::getEnv('MAIL_HOST', 'smtp.gmail.com');
      $mail->SMTPAuth   = true;
      $mail->Username   = self::getEnv('MAIL_USER', '');
      $mail->Password   = self::getEnv('MAIL_PASS', '');
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = (int)self::getEnv('MAIL_PORT', 587);
      $mail->CharSet    = 'UTF-8';
      
      // Configurações de Timeout para evitar travamento
      $mail->Timeout = 15; // Aumentado para 15s
      $mail->SMTPConnectTimeout = 10; // Aumentado para 10s

      // Debug (opcional - habilite se precisar ver logs detalhados de SMTP)
      // $mail->SMTPDebug = 2; 

      $mail->setFrom(
        self::getEnv('MAIL_FROM', ''),
        self::getEnv('MAIL_FROM_NAME', 'ContrataPorto')
      );
      $mail->addAddress($toEmail, $toName);
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $htmlBody;
      
      if ($mail->send()) {
          error_log("[MAILER] Sucesso: Enviado para $toEmail — Assunto: $subject");
          return true;
      }
      
      error_log("[MAILER] Falha inesperada ao enviar para $toEmail");
      return false;
    } catch (Exception $e) {
      error_log("[MAILER] EXCEPTION para $toEmail: " . $e->getMessage() . " | SMTP Error: " . $mail->ErrorInfo);
      return false;
    } catch (\Throwable $t) {
      error_log("[MAILER] ERRO FATAL para $toEmail: " . $t->getMessage());
      return false;
    }
  }
}
