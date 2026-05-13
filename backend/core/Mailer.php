<?php
declare(strict_types=1);

class Mailer
{
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): bool {
        $apiKey = getEnv2('BREVO_API_KEY', '');

        if (empty($apiKey)) {
            error_log('[MAILER] BREVO_API_KEY não configurada.');
            return false;
        }

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            error_log('[MAILER] E-mail inválido: ' . $toEmail);
            return false;
        }

        $fromEmail = getEnv2('MAIL_FROM', 'noreply@contrataporto.com');
        $fromName  = getEnv2('MAIL_FROM_NAME', 'ContrataPorto');

        $payload = json_encode([
            'sender'     => ['name' => $fromName, 'email' => $fromEmail],
            'to'         => [['email' => $toEmail, 'name' => $toName]],
            'subject'    => $subject,
            'htmlContent'=> $htmlBody,
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'api-key: ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[MAILER] cURL error: ' . $curlError);
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log('[MAILER] Brevo API error: HTTP ' 
                . $httpCode . ' | Resposta: ' . $response 
                . ' | Destinatário: ' . $toEmail);
            return false;
        }

        error_log('[MAILER] E-mail enviado com sucesso para ' . $toEmail);
        return true;
    }
}
