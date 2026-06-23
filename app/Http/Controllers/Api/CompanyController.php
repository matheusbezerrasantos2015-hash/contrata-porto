<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function __construct(protected CloudinaryService $cloudinaryService) {}
    /**
     * GET /api/empresa/profile
     * Retorna dados da empresa do usuário autenticado.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data'    => [
                'id'             => $company->id,
                'nome_fantasia'  => $company->nome_fantasia,
                'cnpj'           => $company->cnpj,
                'descricao'      => $company->descricao,
                'logo'           => $company->logo_url,
                'site'           => $company->site,
                'email_contato'  => $user->email,
                'telefone'       => $user->telefone,
                'nome_responsavel' => $user->nome,
                'email_responsavel' => $user->email,
            ],
        ]);
    }

    /**
     * PUT /api/empresa/profile
     * Atualiza dados da empresa e do usuário-empresa.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome_fantasia' => 'required|string|max:255',
            'telefone'      => 'nullable|string|max:20',
            'descricao'     => 'nullable|string',
            'site'          => 'nullable|url|max:255',
            'logo'          => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'logo.mimes' => 'O logo deve ser uma imagem JPEG, PNG ou WebP.',
            'logo.max'   => 'O tamanho máximo do logo é 2 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada.',
            ], 404);
        }

        // ── Upload do novo logo ──────────────────────────────────────────────
        $logoUrl = $company->logo; // mantém o atual por padrão
        if ($request->hasFile('logo')) {
            try {
                // Deleta o logo antigo do Cloudinary se existir
                if ($company->logo && str_starts_with($company->logo, 'http')) {
                    $parts    = explode('/', parse_url($company->logo, PHP_URL_PATH));
                    $filename = pathinfo(end($parts), PATHINFO_FILENAME);
                    $folder   = 'contrataporto/logos';
                    $this->cloudinaryService->deleteFile("{$folder}/{$filename}", 'image');
                }

                $uploaded = $this->cloudinaryService->uploadImageFile(
                    $request->file('logo'),
                    'contrataporto/logos'
                );
                $logoUrl = $uploaded['url'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar logo: ' . $e->getMessage(),
                ], 500);
            }
        }

        $company->update([
            'nome_fantasia' => $request->input('nome_fantasia'),
            'descricao'     => $request->input('descricao'),
            'site'          => $request->input('site'),
            'logo'          => $logoUrl,
        ]);

        $user->update([
            'telefone' => $request->input('telefone'),
        ]);

        // Gera novo token
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Dados atualizados com sucesso!',
            'data'    => [
                'nome_fantasia' => $company->nome_fantasia,
                'telefone'      => $user->telefone,
                'logo'          => $company->logo_url,
                'token'         => $token,
            ],
        ]);
    }

    /**
     * DELETE /api/empresa/profile
     * Exclui a conta da empresa (e o usuário vinculado via CASCADE).
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete(); // SoftDelete — CASCADE apaga a empresa via DB

        return response()->json([
            'success' => true,
            'message' => 'Conta excluída.',
            'data'    => null,
        ]);
    }

    /**
     * GET /api/empresas/{id}
     * Retorna dados públicos de uma empresa pelo ID.
     */
    public function show(int $id): JsonResponse
    {
        $company = Company::with('user')->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Empresa encontrada',
            'data'    => [
                'id'            => $company->id,
                'nome_fantasia' => $company->nome_fantasia,
                'cnpj'          => $company->cnpj,
                'descricao'     => $company->descricao,
                'logo'          => $company->logo_url,
                'site'          => $company->site,
            ],
        ]);
    }

    /**
     * POST /api/empresas
     * Cria uma empresa (se o usuário EMPRESA ainda não tiver uma).
     */
    public function create(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->company) {
            return response()->json([
                'success' => false,
                'message' => 'Você já possui uma empresa cadastrada.',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'nome_fantasia' => 'required|string|max:255',
            'cnpj'          => 'required|string',
            'descricao'     => 'nullable|string',
            'site'          => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $company = Company::create([
            'user_id'       => $user->id,
            'nome_fantasia' => $request->input('nome_fantasia'),
            'cnpj'          => preg_replace('/\D/', '', $request->input('cnpj')),
            'descricao'     => $request->input('descricao'),
            'site'          => $request->input('site'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Empresa criada com sucesso.',
            'data'    => ['id' => $company->id],
        ], 201);
    }
}
