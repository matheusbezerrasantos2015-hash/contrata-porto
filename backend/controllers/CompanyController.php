<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

final class CompanyController
{
    private Company $companyModel;
    public ?array $user = null; // Preenchido pelo Router

    public function __construct()
    {
        $this->companyModel = new Company();
    }

    /**
     * Retorna dados da empresa (GET /empresa/profile)
     */
    public function getProfile(): void {
        $userId  = $this->user['id'];
        $db      = Database::getConnection();
        $stmt    = $db->prepare(
            'SELECT e.id, e.nome_fantasia, e.email_contato, e.telefone,
                    u.nome as nome_responsavel, u.email as email_responsavel
             FROM empresas e
             INNER JOIN users u ON e.user_id = u.id
             WHERE u.id = ?'
        );
        $stmt->execute([$userId]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) {
            Response::error('Empresa não encontrada', 404);
        }
        Response::success('ok', $empresa);
    }

    /**
     * Atualiza nome_fantasia e telefone (PUT /empresa/profile)
     */
    public function updateProfile(): void {
        $body          = Request::json();
        $userId        = $this->user['id'];
        $nome_fantasia = trim($body['nome_fantasia'] ?? '');
        $telefone      = trim($body['telefone'] ?? '');

        if (empty($nome_fantasia)) {
            Response::error('O nome da empresa não pode ficar vazio.', 400);
        }

        $db   = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE empresas SET nome_fantasia = ?, telefone = ?,
             updated_at = NOW()
             WHERE user_id = ?'
        );
        $stmt->execute([$nome_fantasia, $telefone, $userId]);

        Response::success('Dados atualizados com sucesso!', [
            'nome_fantasia' => $nome_fantasia,
            'telefone'      => $telefone,
        ]);
    }

    /**
     * Exclui conta da empresa (DELETE /empresa/profile)
     */
    public function deleteAccount(): void {
        $userId = $this->user['id'];
        $db     = Database::getConnection();

        // CASCADE remove: empresas → vagas → applications → favorites
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$userId]);

        Response::success('Conta excluída.');
    }

    /**
     * Outros métodos necessários para a empresa (se houver)
     */
    public function create(): void {
        // Mantido para compatibilidade se necessário, mas o foco é settings
    }

    public function show(array $params): void {
        $id = (int) ($params['id'] ?? 0);
        $company = $this->companyModel->findById($id);
        if (!$company) {
            Response::error('Not Found', 404);
        }
        Response::success('Empresa encontrada', $company);
    }
}
