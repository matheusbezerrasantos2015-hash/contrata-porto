<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome'                    => 'required|string|max:255',
            'email'                   => 'required|email|unique:users,email',
            'senha'                   => 'required|min:8',
            'role'                    => 'required|in:CANDIDATO,EMPRESA',
            'company.nome_fantasia'   => 'required_if:role,EMPRESA|string',
            'company.cnpj'            => 'required_if:role,EMPRESA|string',
        ], [
            'email.unique'            => 'Email já cadastrado',
            'company.nome_fantasia.required_if' => 'O nome fantasia da empresa é obrigatório.',
            'company.cnpj.required_if'          => 'O CNPJ da empresa é obrigatório.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = $this->authService->register($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso. Verifique seu e-mail para confirmar a conta.',
                'data'    => ['id' => $user->id, 'requires_verification' => true],
            ], 201);
        } catch (\Exception $e) {
            $code = $e->getCode() >= 400 ? $e->getCode() : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'senha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Preencha todos os campos obrigatórios.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->authService->login(
                $request->input('email'),
                $request->input('senha')
            );

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data'    => $result,
            ], 200);
        } catch (\Exception $e) {
            $code = match (true) {
                $e->getCode() === 404 => 404,
                $e->getCode() === 403 => 403,
                default               => 401,
            };
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => $code === 403 ? ['requires_verification' => true, 'email' => $request->input('email')] : null,
            ], $code);
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
            'data'    => null,
        ]);
    }

    /**
     * POST /api/auth/recover
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'E-mail inválido.',
            ], 422);
        }

        // Seguro por design: não revela se o e-mail existe
        $this->authService->forgotPassword($request->input('email'));

        return response()->json([
            'success' => true,
            'message' => 'Se o e-mail estiver cadastrado, você receberá o link.',
            'data'    => null,
        ]);
    }

    /**
     * POST /api/auth/reset
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token'      => 'required|string',
            'nova_senha' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $this->authService->resetPassword(
                $request->input('token'),
                $request->input('nova_senha')
            );
            return response()->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso! Faça login com a nova senha.',
                'data'    => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/auth/verify-email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados insuficientes.',
                'errors'  => $validator->errors(),
            ], 400);
        }

        try {
            $result = $this->authService->verifyEmail(
                $request->input('email'),
                $request->input('code')
            );
            return response()->json([
                'success' => true,
                'message' => 'E-mail verificado com sucesso!',
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() >= 400 ? $e->getCode() : 400;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    /**
     * POST /api/auth/resend-verification
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'E-mail é obrigatório.',
            ], 400);
        }

        try {
            $this->authService->resendVerification($request->input('email'));
            return response()->json([
                'success' => true,
                'message' => 'Novo código enviado para o seu e-mail.',
                'data'    => null,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() >= 400 ? $e->getCode() : 400;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }
}
