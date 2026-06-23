<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function __construct(protected JobService $jobService) {}

    /**
     * GET /api/jobs
     * Lista vagas ativas paginadas.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('limit', 6);
        $page    = (int) $request->query('page', 1);

        $paginator = $this->jobService->listActive($page, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data'    => $this->formatPaginator($paginator),
        ]);
    }

    /**
     * GET /api/jobs/filter
     * Filtra vagas pelos parâmetros de query.
     */
    public function filter(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('limit', 6);
        $page    = (int) $request->query('page', 1);

        $filters = $request->only([
            'q', 'area', 'nivel', 'tipo', 'tipo_contrato',
            'modalidade', 'experiencia', 'salario_min', 'cidade', 'empresa_id',
        ]);

        $paginator = $this->jobService->filter($filters, $page, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data'    => $this->formatPaginator($paginator),
        ]);
    }

    /**
     * GET /api/jobs/my-company
     * Lista vagas da empresa autenticada.
     */
    public function myCompany(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada.',
            ], 404);
        }

        $perPage = (int) $request->query('limit', 20);
        $page    = (int) $request->query('page', 1);

        $paginator = JobListing::with('company')
            ->where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data'    => $this->formatPaginator($paginator),
        ]);
    }

    /**
     * GET /api/jobs/{id}
     * Retorna uma vaga pelo ID.
     */
    public function show(int $id): JsonResponse
    {
        $job = JobListing::with('company')->find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data'    => $this->formatJob($job),
        ]);
    }

    /**
     * POST /api/jobs
     * Cria uma nova vaga para a empresa autenticada.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo'    => 'required|string|max:255',
            'descricao' => 'required|string',
            'tipo'      => 'nullable|in:CLT,PJ,Estágio,Freelancer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada. Complete seu cadastro antes de publicar vagas.',
            ], 404);
        }

        $job = $this->jobService->createForCompany($request->all(), $company->id);

        return response()->json([
            'success' => true,
            'message' => 'Vaga publicada com sucesso!',
            'data'    => $this->formatJob($job->load('company')),
        ], 201);
    }

    /**
     * PUT /api/jobs/{id}
     * Atualiza uma vaga da empresa autenticada.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $job = JobListing::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga não encontrada.',
            ], 404);
        }

        $company = $request->user()->company;
        if (!$company || (int) $job->company_id !== (int) $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão para editar esta vaga.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'titulo'    => 'required|string|max:255',
            'descricao' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $this->jobService->update($job, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Vaga atualizada com sucesso!',
            'data'    => $this->formatJob($job->fresh('company')),
        ]);
    }

    /**
     * DELETE /api/jobs/{id}
     * Exclui (soft delete) uma vaga da empresa autenticada.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $job = JobListing::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga não encontrada.',
            ], 404);
        }

        $company = $request->user()->company;
        if (!$company || (int) $job->company_id !== (int) $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão para excluir esta vaga.',
            ], 403);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vaga excluída com sucesso.',
            'data'    => null,
        ]);
    }

    /**
     * PUT /api/jobs/{id}/status
     * Ativa/desativa uma vaga.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $job = JobListing::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga não encontrada.',
            ], 404);
        }

        $company = $request->user()->company;
        if (!$company || (int) $job->company_id !== (int) $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão.',
            ], 403);
        }

        $job->update(['ativo' => !$job->ativo]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso.',
            'data'    => ['status' => $job->ativo ? 'ATIVA' : 'PAUSADA'],
        ]);
    }

    /**
     * PUT /api/jobs/{id}/conclude
     * Conclui uma vaga (permanecerá visível por 3 dias).
     */
    public function conclude(Request $request, int $id): JsonResponse
    {
        $job = JobListing::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga não encontrada.',
            ], 404);
        }

        $company = $request->user()->company;
        if (!$company || (int) $job->company_id !== (int) $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão.',
            ], 403);
        }

        $job->update([
            'expires_at'   => now()->addDays(3),
            'encerrada_em' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vaga concluída com sucesso. Permanecerá visível por 3 dias.',
            'data'    => null,
        ]);
    }


    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Formata o resultado de um paginator para o padrão esperado pelo frontend.
     */
    private function formatPaginator($paginator): array
    {
        return [
            'vagas'        => $paginator->items() === []
                ? []
                : collect($paginator->items())->map(fn($j) => $this->formatJob($j))->values()->toArray(),
            'total'        => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
        ];
    }

    /**
     * Formata uma vaga para o padrão de resposta do frontend.
     */
    private function formatJob(JobListing $job): array
    {
        return [
            'id'             => $job->id,
            'titulo'         => $job->titulo,
            'cargo'          => $job->cargo,
            'area'           => $job->area,
            'descricao'      => $job->descricao,
            'requisitos'     => $job->requisitos,
            'beneficios'     => $job->beneficios,
            'diferenciais'   => $job->diferenciais,
            'tipo'           => $job->tipo,
            'tipo_contrato'  => $job->tipo,   // alias para compatibilidade
            'modalidade'     => $job->modalidade,
            'tipo_vaga'      => $job->modalidade, // alias
            'nivel'          => $job->nivel,
            'experiencia'    => $job->experiencia,
            'carga_horaria'  => $job->carga_horaria,
            'salario_min'    => $job->salario_min,
            'salario_max'    => $job->salario_max,
            'cidade'         => $job->cidade,
            'estado'         => $job->estado,
            'ativo'          => (bool) $job->ativo,
            'expires_at'     => $job->expires_at?->toISOString(),
            'created_at'     => $job->created_at?->toISOString(),
            'empresa_id'     => $job->company_id,
            'empresa_nome'   => $job->company?->nome_fantasia,
            'empresa_logo'   => $job->company?->logo_url,
            'empresa_site'   => $job->company?->site,
        ];
    }
}
