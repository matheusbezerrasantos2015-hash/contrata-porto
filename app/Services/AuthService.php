<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class AuthService
{
    public function __construct(
        protected MailService $mailService
    ) {}

    /**
     * Registra um novo usuário (candidato ou empresa).
     *
     * @param array $data
     * @return User
     * @throws Exception
     */
    public function register(array $data): User
    {
        $role = strtoupper((string)($data['role'] ?? 'CANDIDATO'));

        if (!in_array($role, ['CANDIDATO', 'EMPRESA'])) {
            throw new Exception("Perfil de usuário inválido.");
        }

        return DB::transaction(function () use ($data, $role) {
            // Cria o usuário
            $user = User::create([
                'nome'     => $data['nome'],
                'email'    => $data['email'],
                'password' => $data['senha'],
                'tipo'     => $role,
                'cidade'   => $role === 'EMPRESA' ? 'Porto Ferreira' : ($data['cidade'] ?? null),
                'estado'   => $role === 'EMPRESA' ? 'SP' : ($data['estado'] ?? null),
            ]);

            // Se for empresa, cria a empresa vinculada
            if ($role === 'EMPRESA') {
                $companyData = $data['company'] ?? [];
                if (empty($companyData['nome_fantasia']) || empty($companyData['cnpj'])) {
                    throw new Exception("Preencha os dados da empresa (nome fantasia e cnpj).");
                }

                Company::create([
                    'user_id'       => $user->id,
                    'nome_fantasia' => $companyData['nome_fantasia'],
                    'cnpj'          => preg_replace('/\D/', '', $companyData['cnpj']),
                    'descricao'     => $companyData['descricao'] ?? null,
                    'site'          => $companyData['site'] ?? null,
                ]);
            }

            // Gera código de verificação
            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            EmailVerification::create([
                'user_id'    => $user->id,
                'code'       => $code,
                'expires_at' => Carbon::now()->addMinutes(15),
            ]);

            // Dispara e-mail de verificação
            $this->mailService->sendVerificationEmail($user->email, $user->nome, $code);

            return $user;
        });
    }

    /**
     * Autentica um usuário e gera um token Sanctum.
     *
     * @param string $email
     * @param string $senha
     * @return array
     * @throws Exception
     */
    public function login(string $email, string $senha): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception('Credenciais inválidas.', 401);
        }

        if (!Hash::check($senha, $user->getAuthPassword())) {
            throw new Exception('Credenciais inválidas.', 401);
        }

        if (!$user->hasVerifiedEmail()) {
            throw new Exception('Confirme seu e-mail antes de entrar.', 401);
        }

        // Carrega relacionamento da empresa se for empresa
        $nomeFantasia = null;
        $empresaId = null;
        if ($user->isEmpresa() && $user->company) {
            $nomeFantasia = $user->company->nome_fantasia;
            $empresaId = $user->company->id;
        }

        // Gera token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'usuario' => [
                'id'                => $user->id,
                'nome'              => $user->nome,
                'email'             => $user->email,
                'tipo'              => $user->tipo,
                'role'              => $user->tipo,
                'avatar'            => $user->avatar_url,
                'email_verified_at' => $user->email_verified_at,
                'empresa_id'        => $empresaId,
                'nome_fantasia'     => $nomeFantasia,
            ],
            'auth' => [
                'type'  => 'bearer',
                'token' => $token,
            ],
        ];
    }

    /**
     * Envia solicitação de recuperação de senha.
     *
     * @param string $email
     * @return void
     */
    public function forgotPassword(string $email): void
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(64);
            $user->update([
                'reset_token'            => $token,
                'reset_token_expires_at' => Carbon::now()->addHour(),
            ]);

            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
            $resetLink   = "{$frontendUrl}/reset-senha?token={$token}&email=" . urlencode($email);

            $this->mailService->sendPasswordRecoveryEmail($user->email, $user->nome, $resetLink);
        }
    }

    /**
     * Reseta a senha do usuário usando o token de redefinição.
     *
     * @param string $token
     * @param string $novaSenha
     * @return void
     * @throws Exception
     */
    public function resetPassword(string $token, string $novaSenha): void
    {
        $user = User::where('reset_token', $token)
                    ->where('reset_token_expires_at', '>', Carbon::now())
                    ->first();

        if (!$user) {
            throw new Exception("Link inválido ou expirado. Solicite um novo.", 400);
        }

        $user->update([
            'password'               => $novaSenha,
            'reset_token'            => null,
            'reset_token_expires_at' => null,
        ]);
    }

    /**
     * Verifica o e-mail do usuário com o código de 6 dígitos.
     *
     * @param string $email
     * @param string $code
     * @return array
     * @throws Exception
     */
    public function verifyEmail(string $email, string $code): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception("Usuário não encontrado.", 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->getLoginPayloadForUser($user);
        }

        $verification = EmailVerification::where('user_id', $user->id)
                                         ->where('code', $code)
                                         ->where('used', false)
                                         ->where('expires_at', '>', Carbon::now())
                                         ->first();

        if (!$verification) {
            throw new Exception("Código inválido ou expirado.", 400);
        }

        DB::transaction(function () use ($user, $verification) {
            $user->update(['email_verified_at' => Carbon::now()]);
            $verification->update(['used' => true]);
        });

        return $this->getLoginPayloadForUser($user);
    }

    /**
     * Reenvia o código de verificação para o usuário.
     *
     * @param string $email
     * @return void
     * @throws Exception
     */
    public function resendVerification(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception("Usuário não encontrado.", 404);
        }

        if ($user->hasVerifiedEmail()) {
            throw new Exception("Este e-mail já está verificado.", 400);
        }

        // Invalida códigos anteriores
        EmailVerification::where('user_id', $user->id)->update(['used' => true]);

        // Gera novo código
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        EmailVerification::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $this->mailService->sendVerificationEmail($user->email, $user->nome, $code);
    }

    /**
     * Helper para gerar a carga útil de login automático pós-verificação.
     */
    protected function getLoginPayloadForUser(User $user): array
    {
        $nomeFantasia = null;
        $empresaId = null;
        if ($user->isEmpresa() && $user->company) {
            $nomeFantasia = $user->company->nome_fantasia;
            $empresaId = $user->company->id;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'usuario' => [
                'id'                => $user->id,
                'nome'              => $user->nome,
                'email'             => $user->email,
                'tipo'              => $user->tipo,
                'role'              => $user->tipo,
                'avatar'            => $user->avatar_url,
                'email_verified_at' => $user->email_verified_at,
                'empresa_id'        => $empresaId,
                'nome_fantasia'     => $nomeFantasia,
            ],
            'auth' => [
                'type'  => 'bearer',
                'token' => $token,
            ],
        ];
    }
}
