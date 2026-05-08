<?php
declare(strict_types=1);

/**
 * Mailer — Envio de e-mails via API HTTP do Resend (resend.com)
 *
 * Utiliza cURL (HTTPS/443) em vez de SMTP (porta 587),
 * contornando o bloqueio de portas SMTP do Railway.
 *
 * Variáveis de ambiente necessárias:
 *   RESEND_API_KEY    — chave de API do Resend (obrigatória)
 *   MAIL_FROM         — endereço remetente (deve ser de domínio verificado no Resend)
 *   MAIL_FROM_NAME    — nome exibido do remetente (padrão: ContrataPorto)
 *   MAIL_OVERRIDE_TO  — (opcional) redireciona TODOS os e-mails para este endereço.
 *                       Útil durante desenvolvimento/plano gratuito do Resend sem
 *                       domínio verificado, que só permite enviar ao próprio dono.
 *                       O assunto recebe o prefixo "[Para: <destinatário original>]"
 *                       para manter rastreabilidade.
 *
 * IMPORTANTE: enquanto o domínio não estiver verificado no Resend,
 * use 'onboarding@resend.dev' como MAIL_FROM.
 */
class Mailer
{
    // ------------------------------------------------------------------ //
    //  Helpers de ambiente                                                 //
    // ------------------------------------------------------------------ //

    /**
     * Lê variável de ambiente de todas as fontes disponíveis no PHP.
     */
    private static function env(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return ($value !== false && $value !== null && $value !== '')
            ? (string) $value
            : $default;
    }

    // ------------------------------------------------------------------ //
    //  API pública                                                         //
    // ------------------------------------------------------------------ //

    /**
     * Envia um e-mail via Resend API.
     *
     * @param string $toEmail  Endereço de e-mail do destinatário.
     * @param string $toName   Nome do destinatário.
     * @param string $subject  Assunto do e-mail.
     * @param string $htmlBody Corpo do e-mail em HTML.
     *
     * @return bool true em caso de sucesso, false em caso de erro.
     */
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): bool {
        // --- Validações básicas -------------------------------------------

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            error_log('[MAILER] Endereço inválido: ' . $toEmail);
            return false;
        }

        $apiKey = self::env('RESEND_API_KEY');

        if ($apiKey === '') {
            error_log('[MAILER] RESEND_API_KEY não configurada. E-mail NÃO enviado.');
            return false;
        }

        // --- Fallback de destinatário (dev / Resend sem domínio verificado) ---
        //
        // Quando MAIL_OVERRIDE_TO está definido, todos os e-mails são
        // redirecionados para esse endereço. O assunto ganha um prefixo
        // "[Para: <email original>]" para rastreabilidade nos logs.

        $overrideTo = self::env('MAIL_OVERRIDE_TO');

        if ($overrideTo !== '') {
            if (!filter_var($overrideTo, FILTER_VALIDATE_EMAIL)) {
                error_log('[MAILER] MAIL_OVERRIDE_TO contém endereço inválido: ' . $overrideTo);
                return false;
            }
            $subject = "[Para: {$toEmail}] " . $subject;
            $toEmail = $overrideTo;
            $toName  = 'ContrataPorto Dev';
        }

        // --- Montagem do payload -----------------------------------------

        $fromAddress = self::env('MAIL_FROM', 'onboarding@resend.dev');
        $fromName    = self::env('MAIL_FROM_NAME', 'ContrataPorto');
        $from        = "{$fromName} <{$fromAddress}>";

        $payload = json_encode([
            'from'    => $from,
            'to'      => ["{$toName} <{$toEmail}>"],
            'subject' => $subject,
            'html'    => $htmlBody,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        // --- Chamada à API do Resend via cURL ----------------------------

        $ch = curl_init('https://api.resend.com/emails');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            // Força TLS 1.2+ e valida certificado (produção)
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // --- Tratamento de erros ----------------------------------------

        if ($curlError !== '') {
            error_log('[MAILER] cURL error ao contatar Resend: ' . $curlError);
            return false;
        }

        // Resend retorna 200 ou 201 em sucesso
        if ($httpCode !== 200 && $httpCode !== 201) {
            error_log(
                '[MAILER] Resend API error: HTTP ' . $httpCode .
                ' | Resposta: ' . $response .
                ' | Destinatário: ' . $toEmail
            );
            return false;
        }

        error_log('[MAILER] E-mail enviado com sucesso para ' . $toEmail . ' | Assunto: ' . $subject);
        return true;
    }
}
