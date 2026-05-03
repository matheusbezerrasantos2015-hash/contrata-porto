<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/JWTService.php';

final class AuthService
{
    private Company $companyModel;

    public function __construct(private readonly User $userModel)
    {
        $this->companyModel = new Company();
    }

    public function register(string $nome, string $email, string $senha, string $role = 'CANDIDATO', array $companyData = []): int
    {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            $empresaId = null;
            if ($role === 'EMPRESA') {
                $empresaId = $this->companyModel->create($companyData);
            }

            $userId = $this->userModel->create([
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'role' => $role,
                'empresa_id' => $empresaId,
            ]);

            $db->commit();
            return $userId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function login(string $email, string $senha): ?array
    {
        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($senha, $user['senha'])) {
            return null;
        }

        $nomeFantasia = null;
        if (strtoupper((string)$user['role']) === 'EMPRESA' && $user['empresa_id']) {
            $company = $this->companyModel->findById((int)$user['empresa_id']);
            $nomeFantasia = $company['nome_fantasia'] ?? null;
        }

        $token = JWTService::generate([
            'id' => (int) $user['id'],
            'nome' => $user['nome'],
            'email' => $user['email'],
            'role' => strtoupper((string) $user['role']),
            'empresa_id' => $user['empresa_id'] ?? null,
            'nome_fantasia' => $nomeFantasia,
        ]);

        $user['nome_fantasia'] = $nomeFantasia;
        return ['user' => $user, 'token' => $token];
    }
}
