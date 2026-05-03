<?php

declare(strict_types=1);

final class RateLimiter
{
    public static function hit(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $path = __DIR__ . '/../storage/ratelimit/' . sha1($key) . '.json';
        $now = time();

        $payload = ['start' => $now, 'count' => 0];
        if (is_file($path)) {
            $stored = json_decode((string) file_get_contents($path), true);
            if (is_array($stored) && isset($stored['start'], $stored['count'])) {
                $payload = $stored;
            }
        }

        if (($now - (int) $payload['start']) > $windowSeconds) {
            $payload = ['start' => $now, 'count' => 0];
        }

        $payload['count'] = (int) $payload['count'] + 1;
        file_put_contents($path, json_encode($payload), LOCK_EX);

        return $payload['count'] <= $maxAttempts;
    }
}
