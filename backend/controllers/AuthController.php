<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/JWTService.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../core/RateLimiter.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../core/Mailer.php';

final class AuthController
{
    private User $userModel;
    private AuthService $authService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->authService = new AuthService($this->userModel);
    }

    public function register(): void
    {
        $ip = Request::ip();
        if (!RateLimiter::hit('register:' . $ip, 1000, 300)) {
            Response::json(false, "Muitas tentativas de cadastro.", null, 429);
        }

        $input = Request::json();
        $nome = (string) ($input['nome'] ?? '');
        $email = (string) ($input['email'] ?? '');
        $senha = (string) ($input['senha'] ?? '');
        $role = strtoupper((string) ($input['role'] ?? 'CANDIDATO'));

        if (!in_array($role, ['CANDIDATO', 'EMPRESA'], true)) {
            Response::json(false, "Perfil de usuário inválido.", null, 422);
        }

        // Regra: se algum estiver vazio (ou nulo)
        if ($nome === '' || $email === '' || $senha === '') {
            Response::json(false, "Preencha todos os campos obrigatórios", null, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 8) {
            Response::json(false, "Dados inválidos: e-mail malformado ou senha muito curta.", null, 422);
        }

        $companyData = [];
        if ($role === 'EMPRESA') {
            $company = $input['company'] ?? [];
            if (empty($company['nome_fantasia']) || empty($company['razao_social']) || empty($company['cnpj'])) {
                Response::json(false, "Preencha os dados da empresa (nome fantasia, razão social e cnpj).", null, 400);
            }
            $companyData = [
                'nome_fantasia' => $company['nome_fantasia'],
                'razao_social' => $company['razao_social'],
                'cnpj' => $company['cnpj'],
                'email_contato' => !empty($company['email_contato']) ? $company['email_contato'] : $email,
                'descricao' => null,
                'telefone' => null,
                'cidade' => 'Porto Ferreira',
                'estado' => 'SP'
            ];
        }

        if ($this->userModel->findByEmail($email) !== null) {
            Response::json(false, "Email já cadastrado", null, 409);
        }

        try {
            $userId = $this->authService->register($nome, $email, $senha, $role, $companyData);

            // Envio de Email de Boas-Vindas em Try/Catch separado para não travar o cadastro
            try {
                if ($role === 'CANDIDATO') {
                    ob_start();
                    include __DIR__ . '/../templates/emails/boas_vindas_candidato.php';
                    $html = ob_get_clean();
                    Mailer::send($email, $nome, 'Bem-vindo(a) ao ContrataPorto! 👋', $html);
                } else if ($role === 'EMPRESA') {
                    ob_start();
                    $nome_fantasia = $companyData['nome_fantasia'];
                    include __DIR__ . '/../templates/emails/boas_vindas_empresa.php';
                    $html = ob_get_clean();
                    Mailer::send($companyData['email_contato'], $nome_fantasia, 'Sua empresa está no ContrataPorto! 🎉', $html);
                }
            } catch (\Exception $mailEx) {
                error_log("Erro ao enviar email de boas-vindas: " . $mailEx->getMessage());
            }

            Response::json(true, "Cadastro realizado com sucesso.", ['id' => $userId], 201);
        } catch (\Exception $e) {
            Response::json(false, "Erro ao cadastrar: " . $e->getMessage(), null, 500);
        }
    }

    public function login(): void
    {
        $ip = Request::ip();
        if (!RateLimiter::hit('login:' . $ip, 1000, 300)) {
            Response::json(false, "Sua conta foi temporariamente bloqueada após 3 tentativas inválidas. Tente novamente em 5 minutos.", null, 429);
        }

        $input = Request::json();
        $email = (string) ($input['email'] ?? '');
        $senha = (string) ($input['senha'] ?? '');

        if ($email === '' || $senha === '') {
            Response::json(false, "Preencha todos os campos obrigatórios", null, 400);
        }

        // Verifica se usuário existe antes de tentar login para retornar 404
        $user = $this->userModel->findByEmail($email);
        if ($user === null) {
            Response::json(false, "Usuário não encontrado", null, 404);
        }

        $auth = $this->authService->login($email, $senha);
        if (!$auth) {
            Response::json(false, "Unauthorized", null, 401);
        }

        Response::json(true, "Login realizado com sucesso", [
            'usuario' => [
                'id' => (int) $auth['user']['id'],
                'nome' => $auth['user']['nome'],
                'email' => $auth['user']['email'],
                'role' => strtoupper((string) $auth['user']['role']),
                'empresa_id' => $auth['user']['empresa_id'] ?? null,
                'nome_fantasia' => $auth['user']['nome_fantasia'] ?? null,
            ],
            'auth' => ['type' => 'bearer', 'token' => $auth['token']],
        ], 200);
    }

    public function forgotPassword(): void
    {
        $input = Request::json();
        $email = (string) ($input['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('E-mail inválido.', 422);
        }

        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $db = Database::getConnection();
            
            // Adicionando expires_at (1 hora)
            $stmt = $db->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $stmt->execute([$email, $token]);

            $config = require __DIR__ . '/../config/app.php';
            $frontendUrl = rtrim($config['app']['frontend_url'] ?? 'http://localhost', '/');
            $resetLink = "{$frontendUrl}/pages/reset-senha.html?token={$token}";

            error_log("[RESET] Token gerado para: {$email}");

            try {
                $nome = $user['nome'] ?? 'Usuário';
                ob_start();
                include __DIR__ . '/../templates/emails/recuperacao_senha.php';
                $html = ob_get_clean();

                Mailer::send($email, $nome, 'Redefinir sua senha no ContrataPorto', $html);
            } catch (\Exception $e) {
                error_log("[RESET] Erro ao enviar e-mail: " . $e->getMessage());
            }
        }

        // Retorna sucesso mesmo se não encontrar para segurança
        Response::success('Se o e-mail estiver cadastrado, você receberá o link.');
    }

    public function resetPassword(): void
    {
        $input = Request::json();
        $token = (string) ($input['token'] ?? '');
        $nova_senha = (string) ($input['nova_senha'] ?? '');

        if (strlen($nova_senha) < 6) {
            Response::error('A senha deve ter pelo menos 6 caracteres.', 400);
        }

        $db = Database::getConnection();
        // Busca token válido
        $stmt = $db->prepare('
            SELECT pr.*, u.id as user_id 
            FROM password_resets pr
            INNER JOIN users u ON pr.email = u.email
            WHERE pr.token = ? AND pr.expires_at > NOW()
            ORDER BY pr.created_at DESC LIMIT 1
        ');
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset) {
            Response::error('Link inválido ou expirado. Solicite um novo.', 400);
        }

        $userId = (int) $reset['user_id'];
        $newHash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        $this->userModel->updatePassword($userId, $newHash);
        
        // Deleta o token usado
        $delStmt = $db->prepare('DELETE FROM password_resets WHERE token = ?');
        $delStmt->execute([$token]);

        Response::success('Senha alterada com sucesso! Faça login com a nova senha.');
    }

    public function logout(): void
    {
        $user = AuthMiddleware::requireAuth();
        // No backend revocation needed for stateless JWT in this step

        Response::json(true, "Logout realizado com sucesso", null, 200);
    }
}
