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

            // Gera código de verificação
            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO email_verifications (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))');
            $stmt->execute([$userId, $code]);

            // Captura dados para o e-mail diferido
            $emailPayload = [
                'email' => $email,
                'nome' => $nome,
                'code' => $code,
                'role' => $role,
                'company_email' => $companyData['email_contato'] ?? $email,
                'nome_fantasia' => $companyData['nome_fantasia'] ?? $nome
            ];

            // Etapa 2 (Assíncrona/Diferida): Envio de E-mail após a resposta
            register_shutdown_function(function() use ($emailPayload) {
                try {
                    ob_start();
                    $nome = $emailPayload['nome'];
                    $code = $emailPayload['code'];
                    include __DIR__ . '/../templates/emails/verificacao_email.php';
                    $html = ob_get_clean();
                    Mailer::send($emailPayload['email'], $emailPayload['nome'], 'Confirme seu e-mail — ContrataPorto', $html);
                } catch (\Exception $mailEx) {
                    error_log("[ASYNC_MAIL_ERR] Erro no registro (verificação): " . $mailEx->getMessage());
                }
            });

            Response::json(true, "Cadastro realizado com sucesso. Verifique seu e-mail para confirmar a conta.", ['id' => $userId, 'requires_verification' => true], 201);
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

        // Verifica se o e-mail está confirmado
        if (isset($auth['user']['email_verified']) && (int)$auth['user']['email_verified'] === 0) {
            Response::json(false, "Confirme seu e-mail antes de fazer login. Verifique sua caixa de entrada.", [
                'requires_verification' => true,
                'email' => $email
            ], 403);
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

            register_shutdown_function(function() use ($email, $user, $resetLink) {
                try {
                    $nome = $user['nome'] ?? 'Usuário';
                    ob_start();
                    include __DIR__ . '/../templates/emails/recuperacao_senha.php';
                    $html = ob_get_clean();
                    Mailer::send($email, $nome, 'Redefinir sua senha no ContrataPorto', $html);
                } catch (\Exception $e) {
                    error_log("[ASYNC_MAIL_ERR] Erro no reset: " . $e->getMessage());
                }
            });
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

    public function verifyEmail(): void
    {
        $input = Request::json();
        $email = (string) ($input['email'] ?? '');
        $code = (string) ($input['code'] ?? '');

        if ($email === '' || $code === '') {
            Response::json(false, "Dados insuficientes.", null, 400);
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            Response::json(false, "Usuário não encontrado.", null, 404);
        }

        if ((int)$user['email_verified'] === 1) {
            Response::json(true, "E-mail já verificado.", null, 200);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('
            SELECT * FROM email_verifications 
            WHERE user_id = ? AND code = ? AND used = 0 AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ');
        $stmt->execute([$user['id'], $code]);
        $verification = $stmt->fetch();

        if (!$verification) {
            Response::json(false, "Código inválido ou expirado.", null, 400);
        }

        try {
            $db->beginTransaction();

            // Marca como verificado
            $db->prepare('UPDATE users SET email_verified = 1 WHERE id = ?')->execute([$user['id']]);
            // Marca código como usado
            $db->prepare('UPDATE email_verifications SET used = 1 WHERE id = ?')->execute([$verification['id']]);

            $db->commit();

            // Gera token para logar automaticamente
            // Como não temos a senha aqui, vamos buscar o usuário atualizado e gerar o token manualmente
            // ou usar o AuthService se tivermos um método apropriado.
            // Aqui vamos buscar os dados necessários.
            
            $nomeFantasia = null;
            if (strtoupper((string)$user['role']) === 'EMPRESA' && $user['empresa_id']) {
                require_once __DIR__ . '/../models/Company.php';
                $companyModel = new Company();
                $company = $companyModel->findById((int)$user['empresa_id']);
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

            Response::json(true, "E-mail verificado com sucesso!", [
                'usuario' => [
                    'id' => (int) $user['id'],
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                    'role' => strtoupper((string) $user['role']),
                    'empresa_id' => $user['empresa_id'] ?? null,
                    'nome_fantasia' => $nomeFantasia,
                ],
                'auth' => ['type' => 'bearer', 'token' => $token],
            ], 200);

        } catch (Exception $e) {
            $db->rollBack();
            Response::json(false, "Erro ao verificar e-mail.", null, 500);
        }
    }

    public function resendVerification(): void
    {
        $input = Request::json();
        $email = (string) ($input['email'] ?? '');

        if ($email === '') {
            Response::json(false, "E-mail é obrigatório.", null, 400);
        }

        // Rate limit para reenvio
        $ip = Request::ip();
        if (!RateLimiter::hit('resend_verification:' . $ip, 600, 3)) {
            Response::json(false, "Muitas solicitações. Tente novamente em 10 minutos.", null, 429);
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            Response::json(false, "Usuário não encontrado.", null, 404);
        }

        if ((int)$user['email_verified'] === 1) {
            Response::json(false, "Este e-mail já está verificado.", null, 400);
        }

        $db = Database::getConnection();
        // Invalida códigos anteriores
        $db->prepare('UPDATE email_verifications SET used = 1 WHERE user_id = ? AND used = 0')->execute([$user['id']]);

        // Gera novo código
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $db->prepare('INSERT INTO email_verifications (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))');
        $stmt->execute([$user['id'], $code]);

        $emailPayload = [
            'email' => $email,
            'nome' => $user['nome'],
            'code' => $code
        ];

        register_shutdown_function(function() use ($emailPayload) {
            try {
                ob_start();
                $nome = $emailPayload['nome'];
                $code = $emailPayload['code'];
                include __DIR__ . '/../templates/emails/verificacao_email.php';
                $html = ob_get_clean();
                Mailer::send($emailPayload['email'], $emailPayload['nome'], 'Seu novo código de verificação — ContrataPorto', $html);
            } catch (\Exception $mailEx) {
                error_log("[ASYNC_MAIL_ERR] Erro no reenvio de verificação: " . $mailEx->getMessage());
            }
        });

        Response::json(true, "Novo código enviado para o seu e-mail.");
    }
}
