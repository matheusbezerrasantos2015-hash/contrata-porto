<?php

declare(strict_types=1);

final class Request
{
    public static function json(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return is_array($input) ? self::sanitize($input) : [];
    }

    public static function query(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? self::sanitize($_GET[$key]) : $default;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? self::sanitize($_POST[$key]) : $default;
    }

    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public static function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }
        if (is_string($value)) {
            return trim(strip_tags($value));
        }
        return $value;
    }

    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

  public static function bearerToken(): ?string
  {
      $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

      // Fallback para FastCGI/Apache onde o PHP não injeta proativamente na $_SERVER
      if (empty($header) && function_exists('apache_request_headers')) {
          $requestHeaders = apache_request_headers();
          
          if (isset($requestHeaders['Authorization'])) {
              $header = $requestHeaders['Authorization'];
          } elseif (isset($requestHeaders['authorization'])) {
              $header = $requestHeaders['authorization'];
          }
      }

      if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
          return trim($matches[1]);
      }
      return null;
  }
}
