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
        
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
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
