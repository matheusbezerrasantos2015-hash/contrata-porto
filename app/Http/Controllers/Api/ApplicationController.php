<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobListing;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    public function __construct(protected ApplicationService $applicationService) {}

    /**
     * POST /api/applications
     * Cria uma candidatura para uma vaga.
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vaga_id'   => 'required_without:job_id|integer',
            'job_id'    => 'required_without:vaga_id|integer',
            'mensagem'  => 'nullable|string',
            'linkedin'  => 'nullable|string|max:255',
            'portfolio' => 'nullable|string|max:255',
            'telefone'  => 'nullable|string|max:20',
            'curriculo' => 'nullable|file|mimes:pdf|max:10240', // 10MB max PDF
        ], [
            'curriculo.mimes' => 'O currículo deve ser um arquivo PDF.',
            'curriculo.max'   => 'O tamanho máximo do arquivo deve ser 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?? 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $jobId = (int) ($request->input('vaga_id') ?? $request->input('job_id'));
        $user  = $request->user();

        try {
            $curriculoFile = $request->file('curriculo');
            
            $application = $this->applicationService->apply(
                $user,
                $jobId,
                $request->input('mensagem'),
                $request->input('linkedin'),
                $request->input('portfolio'),
                $request->input('telefone'),
                $curriculoFile
            );

            return response()->json([
                'success' => true,
                'message' => 'Candidatura enviada com sucesso.',
                'data'    => [
                    'id'            => $application->id,
                    'curriculo_url' => $application->curriculo_url,
                ],
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
     * GET /api/applications/me
     * Lista as candidaturas do candidato autenticado.
     */
    public function myApplications(Request $request): JsonResponse
    {
        $user  = $request->user();
        $page  = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);

        $paginator = Application::with(['jobListing.company'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($app) {
            return [
                'id'            => $app->id,
                'vaga_id'       => $app->job_id,
                'job_id'        => $app->job_id,
                'user_id'       => $app->user_id,
                'status'        => $app->status,
                'created_at'    => $app->created_at?->toISOString(),
                'titulo'        => $app->jobListing?->titulo ?? 'Vaga Indisponível',
                'nome_fantasia' => $app->jobListing?->company?->nome_fantasia ?? 'Empresa Confidencial',
                'cidade'        => $app->jobListing?->cidade ?? '',
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'message' => 'Minhas candidaturas listadas com sucesso.',
            'data'    => [
                'items' => $items,
                'meta'  => [
                    'page'  => $paginator->currentPage(),
                    'limit' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    /**
     * GET /api/jobs/{id}/applications
     * Lista candidaturas recebidas para uma vaga (apenas para a empresa dona da vaga).
     */
    public function jobApplications(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada.',
            ], 404);
        }

        $job = JobListing::find($id);
        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga não encontrada.',
            ], 404);
        }

        if ((int) $job->company_id !== (int) $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        $page  = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);

        $paginator = Application::with('user')
            ->where('job_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($app) {
            return [
                'id'              => $app->id,
                'job_id'          => $app->job_id,
                'user_id'         => $app->user_id,
                'mensagem'        => $app->mensagem,
                'curriculo_url'   => $app->curriculo_url,
                'status'          => $app->status,
                'linkedin'        => $app->linkedin,
                'portfolio'       => $app->portfolio,
                'telefone'        => $app->telefone ?? $app->user?->telefone,
                'created_at'      => $app->created_at?->toISOString(),
                'candidato_nome'  => $app->user?->nome,
                'candidato_email' => $app->user?->email,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'message' => 'Candidaturas da vaga listadas com sucesso.',
            'data'    => [
                'items' => $items,
                'meta'  => [
                    'page'  => $paginator->currentPage(),
                    'limit' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    /**
     * GET /api/applications/{id}
     * Retorna detalhes de uma candidatura para a empresa.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada.',
            ], 404);
        }

        $application = Application::with(['user', 'jobListing'])->find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Candidatura não encontrada.',
            ], 404);
        }

        if ((int) $application->jobListing->company_id !== (int) $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Esta vaga não pertence à sua empresa.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Candidatura detalhada carregada com sucesso.',
            'data'    => [
                'id'                  => $application->id,
                'user_id'             => $application->user_id,
                'vaga_id'             => $application->job_id,
                'status'              => $application->status,
                'mensagem'            => $application->mensagem,
                'curriculo_url'       => $application->curriculo_url,
                'curriculo_public_id' => $application->curriculo_public_id,
                'candidato_nome'      => $application->user?->nome,
                'candidato_email'     => $application->user?->email,
                'telefone'            => $application->telefone ?? $application->user?->telefone ?? '',
                'cidade'              => $application->user?->cidade ?? '',
                'linkedin'            => $application->linkedin ?? $application->user?->linkedin ?? '',
                'portfolio'           => $application->portfolio ?? $application->user?->portfolio ?? '',
            ],
        ]);
    }

    /**
     * PUT /api/applications/{id}
     * Atualiza o status da candidatura (apenas para a empresa).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos para atualização de status.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $application = $this->applicationService->updateStatus($id, $company->id, $request->input('status'));

            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso.',
                'data'    => null,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() >= 400 ? $e->getCode() : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    /**
     * GET /api/applications/{id}/curriculo
     * Redireciona para o currículo da candidatura.
     */
    public function downloadCurriculo(Request $request, int $id)
    {
        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não encontrada.',
            ], 404);
        }

        $application = Application::with('jobListing')->find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Candidatura não encontrada.',
            ], 404);
        }

        if ((int) $application->jobListing->company_id !== (int) $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado para este currículo.',
            ], 403);
        }

        if (empty($application->curriculo_url)) {
            return response()->json([
                'success' => false,
                'message' => 'Esta candidatura não possui currículo.',
            ], 404);
        }

        return redirect()->away($application->curriculo_url);
    }
}
