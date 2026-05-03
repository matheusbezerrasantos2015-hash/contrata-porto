<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Company
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO empresas (nome_fantasia, razao_social, cnpj, descricao, email_contato, telefone, cidade, estado)
             VALUES (:nome_fantasia, :razao_social, :cnpj, :descricao, :email_contato, :telefone, :cidade, :estado)'
        );

        $stmt->execute([
            'nome_fantasia' => $data['nome_fantasia'],
            'razao_social' => $data['razao_social'],
            'cnpj' => $data['cnpj'],
            'descricao' => $data['descricao'] ?? null,
            'email_contato' => $data['email_contato'],
            'telefone' => $data['telefone'] ?? null,
            'cidade' => $data['cidade'] ?? 'Porto Ferreira',
            'estado' => $data['estado'] ?? 'SP',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM empresas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        return $company ?: null;
    }

    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE empresas SET 
                nome_fantasia = :nome_fantasia, 
                razao_social = :razao_social, 
                email_contato = :email_contato, 
                telefone = :telefone, 
                site = :site, 
                cidade = :cidade, 
                setor = :setor, 
                num_funcionarios = :num_funcionarios, 
                descricao = :descricao
            WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'nome_fantasia' => $data['nome_fantasia'] ?? '',
            'razao_social' => $data['razao_social'] ?? '',
            'email_contato' => $data['email_contato'] ?? '',
            'telefone' => $data['telefone'] ?? null,
            'site' => $data['site'] ?? null,
            'cidade' => $data['cidade'] ?? 'Porto Ferreira',
            'setor' => $data['setor'] ?? null,
            'num_funcionarios' => $data['num_funcionarios'] ?? null,
            'descricao' => $data['descricao'] ?? null
        ]);
    }
    public function updateLogo(int $id, string $logoUrl): bool
    {
        $stmt = $this->db->prepare('UPDATE empresas SET logo_path = :logo_path WHERE id = :id');
        return $stmt->execute(['id' => $id, 'logo_path' => $logoUrl]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM empresas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
