<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Application
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO applications (vaga_id, user_id, mensagem, curriculo_path, status, linkedin, portfolio, telefone)
             VALUES (:vaga_id, :user_id, :mensagem, :curriculo_path, :status, :linkedin, :portfolio, :telefone)'
        );
 
        $stmt->execute([
            'vaga_id' => $data['vaga_id'],
            'user_id' => $data['user_id'],
            'mensagem' => $data['mensagem'] ?? null,
            'curriculo_path' => $data['curriculo_path'] ?? null,
            'status' => $data['status'] ?? 'PENDENTE',
            'linkedin' => $data['linkedin'] ?? null,
            'portfolio' => $data['portfolio'] ?? null,
            'telefone' => $data['telefone'] ?? null,
        ]);
 
        return (int) $this->db->lastInsertId();
    }

    public function findByUser(int $userId, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare(
            'SELECT c.id, c.vaga_id, c.vaga_id AS job_id, c.user_id, c.status, c.created_at AS created_at,
                    v.titulo AS titulo, e.nome_fantasia AS nome_fantasia, v.cidade
             FROM applications c
             INNER JOIN vagas v ON v.id = c.vaga_id
             INNER JOIN empresas e ON e.id = v.empresa_id
             WHERE c.user_id = :user_id
             ORDER BY c.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByJob(int $jobId, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare(
            'SELECT c.*, u.nome AS candidato_nome, u.email AS candidato_email
             FROM applications c
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.vaga_id = :vaga_id
             ORDER BY c.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':vaga_id', $jobId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, vaga_id, user_id, status FROM applications WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Busca candidatura com todos os dados de e-mail em uma única query (LEFT JOIN).
     * Retorna: candidato_email, candidato_nome, vaga_titulo, empresa_nome
     */
    public function findByIdWithEmailData(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                a.id, a.vaga_id, a.user_id, a.status,
                u.email  AS candidato_email,
                u.nome   AS candidato_nome,
                v.titulo AS vaga_titulo,
                e.nome_fantasia AS empresa_nome
             FROM applications a
             LEFT JOIN users  u ON u.id = a.user_id
             LEFT JOIN vagas  v ON v.id = a.vaga_id
             LEFT JOIN empresas e ON e.id = v.empresa_id
             WHERE a.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByIdEnriched(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.nome AS candidato_nome, u.email AS candidato_email
             FROM applications c
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE applications SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteByJob(int $jobId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM applications WHERE vaga_id = :jobId');
        return $stmt->execute(['jobId' => $jobId]);
    }

    public function existsForUserAndJob(int $userId, int $jobId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM applications WHERE user_id = :user_id AND vaga_id = :vaga_id LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'vaga_id' => $jobId,
        ]);

        return (bool) $stmt->fetch();
    }

    public function hasRecentApplication(int $userId, int $seconds = 30): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM applications WHERE user_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL :seconds SECOND) LIMIT 1'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':seconds', $seconds, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}
