<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JWTService
{
    private static function getConfig(): array
    {
        return require __DIR__ . '/../config/app.php';
    }

    public static function generate(array $user): string
    {
        $config = self::getConfig();
        $secret = $config['security']['jwt_secret'];
        // Ajustado para 7 dias de expiração do Token (7 * 24 * 60 * 60)
        $ttl = 604800;
        $now = time();

        $payload = [
            'iss' => $config['app']['base_url'],
            'iat' => $now,
            'exp' => $now + $ttl,
            'data' => $user
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    public static function validate(string $token): ?array
    {
        try {
            $config = self::getConfig();
            $decoded = JWT::decode($token, new Key($config['security']['jwt_secret'], 'HS256'));
            return (array) $decoded;
        } catch (Throwable) {
            return null;
        }
    }
}
