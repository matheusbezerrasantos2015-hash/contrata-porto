<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(protected CloudinaryService $cloudinaryService) {}

    /**
     * GET /api/me
     * Retorna os dados do usuário autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'id'       => $user->id,
            'nome'     => $user->nome,
            'email'    => $user->email,
            'telefone' => $user->telefone ?? '',
            'cidade'   => $user->cidade ?? '',
            'estado'   => $user->estado ?? '',
            'avatar'   => $user->avatar_url,
            'curriculo'=> $user->curriculo_url,
            'role'     => $user->tipo,
        ];

        // Se for empresa, adiciona dados da empresa
        if ($user->isEmpresa() && $user->company) {
            $data['empresa_id']    = $user->company->id;
            $data['nome_fantasia'] = $user->company->nome_fantasia;
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data'    => $data,
        ]);
    }

    /**
     * PUT /api/profile
     * Atualiza nome, telefone e avatar do candidato.
     * Aceita multipart/form-data quando há arquivo de avatar.
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome'     => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'cidade'   => 'nullable|string|max:100',
            'estado'   => 'nullable|string|max:2',
            'avatar'   => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'avatar.mimes' => 'O avatar deve ser uma imagem JPEG, PNG ou WebP.',
            'avatar.max'   => 'O tamanho máximo do avatar é 2 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // ── Upload do novo avatar ────────────────────────────────────────────
        $avatarUrl = $user->avatar; // mantém o atual por padrão
        if ($request->hasFile('avatar')) {
            try {
                // Deleta o avatar antigo do Cloudinary se existir
                if ($user->avatar && str_starts_with($user->avatar, 'http')) {
                    // Extrai o public_id da URL Cloudinary (último segmento sem extensão)
                    $parts    = explode('/', parse_url($user->avatar, PHP_URL_PATH));
                    $filename = pathinfo(end($parts), PATHINFO_FILENAME);
                    $folder   = 'contrataporto/avatars';
                    $this->cloudinaryService->deleteFile("{$folder}/{$filename}", 'image');
                }

                $uploaded = $this->cloudinaryService->uploadImageFile(
                    $request->file('avatar'),
                    'contrataporto/avatars'
                );
                $avatarUrl = $uploaded['url'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar avatar: ' . $e->getMessage(),
                ], 500);
            }
        }

        $user->update([
            'nome'     => $request->input('nome'),
            'telefone' => $request->input('telefone'),
            'cidade'   => $request->input('cidade'),
            'estado'   => $request->input('estado'),
            'avatar'   => $avatarUrl,
        ]);

        // Gera novo token (comportamento idêntico ao legado que atualizava o JWT)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso!',
            'data'    => [
                'nome'     => $user->nome,
                'telefone' => $user->telefone,
                'avatar'   => $user->avatar_url,
                'token'    => $token,
            ],
        ]);
    }

    /**
     * DELETE /api/profile
     * Exclui a conta do candidato (soft delete).
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete(); // SoftDelete

        return response()->json([
            'success' => true,
            'message' => 'Conta excluída.',
            'data'    => null,
        ]);
    }
}
