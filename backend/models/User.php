<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['email' => $email]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            unset($user['senha']);
        }
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (nome, email, senha, role, empresa_id) VALUES (:nome, :email, :senha, :role, :empresa_id)'
        );

        $stmt->execute([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => $data['senha'],
            'role' => strtoupper((string) ($data['role'] ?? 'CANDIDATO')),
            'empresa_id' => $data['empresa_id'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET 
                nome = :nome, 
                telefone = :telefone, 
                cidade = :cidade, 
                linkedin = :linkedin, 
                portfolio = :portfolio, 
                area = :area, 
                nivel_experiencia = :nivel_experiencia, 
                sobre = :sobre
            WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'nome' => $data['nome'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'linkedin' => $data['linkedin'] ?? null,
            'portfolio' => $data['portfolio'] ?? null,
            'area' => $data['area'] ?? null,
            'nivel_experiencia' => $data['nivel_experiencia'] ?? null,
            'sobre' => $data['sobre'] ?? null
        ]);
    }

    public function updateAvatar(int $id, string $avatarPath): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET avatar_path = :avatar_path WHERE id = :id');
        return $stmt->execute(['id' => $id, 'avatar_path' => $avatarPath]);
    }

    public function updatePassword(int $id, string $newHash): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET senha = :senha WHERE id = :id');
        return $stmt->execute(['id' => $id, 'senha' => $newHash]);
    }

    public function deletePhysical(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
