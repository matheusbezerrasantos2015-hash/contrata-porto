<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

final class UserController
{
    private User $userModel;
    public ?array $user = null; // Preenchido pelo Router se a rota exigir role

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Retorna dados do usuário logado
     */
    public function me(): void {
        // Se o router não preencheu (rota ANY), tentamos pegar manualmente
        if (!$this->user) {
            try {
                $this->user = AuthMiddleware::requireAuth();
            } catch (Exception $e) {
                Response::error('Não autorizado', 401);
            }
        }

        $userId = $this->user['id'];
        $user   = $this->userModel->findById($userId);
        
        if (!$user) {
            Response::error('Usuário não encontrado', 404);
        }

        Response::success('ok', [
            'id'       => $user['id'],
            'nome'     => $user['nome'],
            'email'    => $user['email'],
            'telefone' => $user['telefone'] ?? '',
            'role'     => $user['role'],
        ]);
    }

    /**
     * Atualiza nome e telefone (PUT /profile)
     */
    public function update(): void {
        $body     = Request::json();
        $userId   = $this->user['id'];
        $nome     = trim($body['nome'] ?? '');
        $telefone = trim($body['telefone'] ?? '');

        if (empty($nome)) {
            Response::error('O nome não pode ficar vazio.', 400);
        }

        $db   = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE users SET nome = ?, telefone = ?, updated_at = NOW()
             WHERE id = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$nome, $telefone, $userId]);

        // Get updated user data to regenerate token
        $userModel = new User();
        $updatedUser = $userModel->findById($userId);
        
        $nomeFantasia = null;
        if (strtoupper((string)$updatedUser['role']) === 'EMPRESA' && $updatedUser['empresa_id']) {
            require_once __DIR__ . '/../models/Company.php';
            $companyModel = new Company();
            $company = $companyModel->findById((int)$updatedUser['empresa_id']);
            $nomeFantasia = $company['nome_fantasia'] ?? null;
        }

        require_once __DIR__ . '/../core/JWTService.php';
        $token = JWTService::generate([
            'id' => (int) $updatedUser['id'],
            'nome' => $updatedUser['nome'],
            'email' => $updatedUser['email'],
            'role' => strtoupper((string) $updatedUser['role']),
            'empresa_id' => $updatedUser['empresa_id'] ?? null,
            'nome_fantasia' => $nomeFantasia,
        ]);

        Response::success('Perfil atualizado com sucesso!', [
            'nome'     => $nome,
            'telefone' => $telefone,
            'token'    => $token
        ]);
    }

    /**
     * Exclui conta fisicamente
     */
    public function deleteAccount(): void {
        $userId = $this->user['id'];
        $db     = Database::getConnection();

        // CASCADE no banco já remove applications e favorites
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$userId]);

        Response::success('Conta excluída.');
    }
}
