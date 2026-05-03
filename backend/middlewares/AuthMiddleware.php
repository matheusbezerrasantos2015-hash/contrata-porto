<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/JWTService.php';

final class AuthMiddleware
{
    public static function user(): ?array
    {
        $token = Request::bearerToken();
        error_log("[AUTH_DEBUG] Header recebido: " . ($token ? 'Sim' : 'NULO'));
        if (!$token) {
            return null;
        }

        $payload = JWTService::validate($token);
        if (!$payload || !isset($payload['data'])) {
            return null;
        }

        $data = (array) $payload['data'];

        return [
            'id' => (int) ($data['id'] ?? 0),
            'email' => (string) ($data['email'] ?? ''),
            'role' => strtoupper((string) ($data['role'] ?? 'CANDIDATO')),
            'empresa_id' => isset($data['empresa_id']) ? (int) $data['empresa_id'] : null,
            'token' => $token
        ];
    }

    public static function requireAuth(): array
    {
        $user = self::user();

        if (!$user || $user['id'] <= 0) {
            Response::json(false, 'Unauthorized', null, 401);
            exit;
        }

        return $user;
    }

    public static function requireRole(string $role): array
    {
        $user = self::requireAuth();

        $userRole = strtoupper($user['role'] ?? '');
        $expected = strtoupper($role);

        if ($userRole !== $expected) {
            Response::json(false, 'Forbidden', null, 403);
            exit;
        }

        return $user;
    }
}
