<?php

declare(strict_types=1);

final class Response
{
    /**
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     */
    public static function json(bool $success = true, string $message = '', mixed $data = null, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $output = json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // Se possível, envia o Content-Length para que o cliente saiba que a resposta terminou
        header('Content-Length: ' . strlen($output));
        header('Connection: close');
        
        echo $output;

        // Se estiver rodando com FastCGI (Nginx/PHP-FPM), fecha a conexão com o cliente aqui
        // mas permite que o script continue executando (trigger shutdown functions)
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            // Fallback para outros servidores: garante que o buffer seja enviado
            if (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();
        }
        
        exit;
    }

    public static function success(string $message = '', mixed $data = null, int $statusCode = 200): void
    {
        self::json(true, $message, $data, $statusCode);
    }

    public static function error(string $message = '', int $statusCode = 400): void
    {
        self::json(false, $message, null, $statusCode);
    }
}
