<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Favorite
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $userId, int $jobId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO favorites (user_id, vaga_id) VALUES (:user_id, :vaga_id)'
        );

        return $stmt->execute([
            'user_id' => $userId,
            'vaga_id' => $jobId,
        ]);
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT f.vaga_id, f.created_at, v.titulo, v.tipo_contrato, v.cidade, v.estado, e.nome_fantasia AS empresa_nome
             FROM favorites f
             INNER JOIN vagas v ON v.id = f.vaga_id
             INNER JOIN empresas e ON e.id = v.empresa_id
             WHERE f.user_id = :user_id
             ORDER BY f.created_at DESC'
        );

        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function deleteByJobAndUser(int $jobId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM favorites WHERE vaga_id = :job_id AND user_id = :user_id'
        );

        $stmt->execute([
            'job_id' => $jobId,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function exists(int $userId, int $jobId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT user_id FROM favorites WHERE user_id = :user_id AND vaga_id = :vaga_id LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'vaga_id' => $jobId,
        ]);

        return (bool) $stmt->fetch();
    }
}
