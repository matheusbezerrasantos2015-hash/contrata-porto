<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * POST /api/favorites
     * Salva uma vaga nos favoritos.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vaga_id' => 'required_without:job_id|integer',
            'job_id'  => 'required_without:vaga_id|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'vaga_id é obrigatório.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $jobId = (int) ($request->input('vaga_id') ?? $request->input('job_id'));
        $user  = $request->user();

        // Verifica se a vaga existe
        if (!JobListing::where('id', $jobId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga não encontrada.',
            ], 404);
        }

        // Verifica se já está nos favoritos
        $exists = Favorite::where('user_id', $user->id)
            ->where('job_id', $jobId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga já está nos favoritos.',
            ], 409);
        }

        Favorite::create([
            'user_id' => $user->id,
            'job_id'  => $jobId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vaga salva com sucesso.',
            'data'    => null,
        ], 201);
    }

    /**
     * GET /api/favorites
     * Lista as vagas favoritadas pelo candidato.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = Favorite::with(['jobListing.company'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($fav) {
                return [
                    'vaga_id'       => $fav->job_id,
                    'created_at'    => $fav->created_at?->toISOString(),
                    'titulo'        => $fav->jobListing?->titulo ?? 'Vaga Indisponível',
                    'tipo_contrato' => $fav->jobListing?->tipo ?? 'CLT',
                    'cidade'        => $fav->jobListing?->cidade ?? '',
                    'estado'        => $fav->jobListing?->estado ?? '',
                    'empresa_nome'  => $fav->jobListing?->company?->nome_fantasia ?? 'Empresa Confidencial',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Favoritos listados com sucesso.',
            'data'    => $favorites,
        ]);
    }

    /**
     * DELETE /api/favorites/{id}
     * Remove uma vaga dos favoritos pelo ID da vaga (vaga_id / job_id).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $deleted = Favorite::where('user_id', $user->id)
            ->where('job_id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga favoritada não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Favorito removido com sucesso.',
            'data'    => null,
        ]);
    }
}
