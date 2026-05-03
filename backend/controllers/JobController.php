<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../services/JobService.php';

final class JobController
{
    private Job $jobModel;
    private JobService $jobService;

    public function __construct()
    {
        $this->jobModel = new Job();
        $this->jobService = new JobService($this->jobModel);
    }

    public function create(): void
    {
        $user = AuthMiddleware::requireRole('empresa');
        $input = Request::json();

        $requiredFields = ['titulo', 'descricao', 'cargo', 'tipo_contrato', 'tipo_vaga'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                Response::json(false, "Campo obrigatório: {$field}", null, 422);
            }
        }

        $companyId = (int) ($user['empresa_id'] ?? 0);
        if ($companyId <= 0) {
            Response::json(false, 'Forbidden', null, 403);
        }

        $jobId = $this->jobService->createForCompany($input, $companyId);
        Response::json(true, 'Vaga criada com sucesso.', ['id' => $jobId], 201);
    }

    public function list(): void
    {
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 6; // fixo, não muda
        $offset   = ($page - 1) * $per_page;

        $db = Database::getConnection();
        
        // Query principal (limitada a 6)
        $sql = "SELECT v.*, e.nome_fantasia AS empresa_nome 
                FROM vagas v 
                INNER JOIN empresas e ON e.id = v.empresa_id 
                WHERE v.status = 'ATIVA' 
                ORDER BY v.publicada_em DESC 
                LIMIT 6 OFFSET {$offset}";
        
        $stmt = $db->query($sql);
        $vagas = $stmt->fetchAll();

        // Query para contar total
        $sqlTotal = "SELECT COUNT(*) as total FROM vagas WHERE status = 'ATIVA'";
        $total = (int)$db->query($sqlTotal)->fetch()['total'];

        Response::json(true, 'OK', [
            'vagas'       => $vagas,
            'pagination'  => [
                'page'        => $page,
                'per_page'    => 6,
                'total'       => $total,
                'total_pages' => ceil($total / 6),
                'has_next'    => $page < ceil($total / 6),
                'has_prev'    => $page > 1,
            ]
        ]);
    }

    public function show(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::json(false, 'Not Found', null, 404);
        }

        $job = $this->jobModel->findById($id);
        if (!$job) {
            Response::json(false, 'Not Found', null, 404);
        }

        Response::json(true, 'Vaga encontrada com sucesso.', $job, 200);
    }

    public function filter(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 6;
        $filters = [
            'area'          => Request::query('area'),
            'nivel'         => Request::query('nivel'),
            'tipo'          => Request::query('tipo'),
            'tipo_contrato' => Request::query('tipo_contrato'),
            'experiencia'   => Request::query('experiencia'),
            'cidade'        => Request::query('cidade'),
            'empresa_id'    => Request::query('empresa_id'),
            'salario_min'   => Request::query('salario_min'),
            'q'             => Request::query('q'),
        ];

        $vagas = $this->jobModel->findByFilters($filters, $page, $per_page);
        
        // Obter total para os filtros aplicados
        // Nota: findByFilters já retorna apenas os resultados. Precisamos do total.
        // Vou assumir que o model Job.php precisa de um método countByFilters ou similar.
        // Por enquanto, farei o count manual para seguir a regra de "mesmos filtros".
        
        $total = $this->jobModel->countByFilters($filters);

        Response::json(true, 'OK', [
            'vagas'       => $vagas,
            'pagination'  => [
                'page'        => $page,
                'per_page'    => 6,
                'total'       => $total,
                'total_pages' => ceil($total / 6),
                'has_next'    => $page < ceil($total / 6),
                'has_prev'    => $page > 1,
            ]
        ]);
    }

    public function myCompanyJobs(): void
    {
        $user = AuthMiddleware::requireRole('empresa');
        $companyId = (int) ($user['empresa_id'] ?? 0);
        
        if ($companyId <= 0) {
            Response::json(false, 'Forbidden', null, 403);
        }

        $page = max(1, (int) Request::query('page', 1));
        $limit = min(50, max(1, (int) Request::query('limit', 10)));
        
        $jobs = $this->jobModel->findByCompany($companyId, $page, $limit);
        
        Response::json(true, 'Vagas listadas com sucesso.', [
            'items' => $jobs,
            'meta' => ['page' => $page, 'limit' => $limit]
        ], 200);
    }

    public function update(array $params): void
    {
        $user = AuthMiddleware::requireRole('empresa');
        $id = (int) ($params['id'] ?? 0);
        
        if ($id <= 0) {
            Response::json(false, 'ID inválido.', null, 422);
        }

        $job = $this->jobModel->findById($id);
        if (!$job || (int)$job['empresa_id'] !== (int)$user['empresa_id']) {
            Response::json(false, 'Vaga não encontrada ou acesso negado.', null, 403);
        }

        $input = Request::json();
        $requiredFields = ['titulo', 'descricao', 'cargo', 'tipo_contrato', 'tipo_vaga'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                Response::json(false, "Campo obrigatório: {$field}", null, 422);
            }
        }

        if ($this->jobModel->update($id, $input)) {
            Response::json(true, 'Vaga atualizada com sucesso.', null, 200);
        } else {
            Response::json(false, 'Erro ao atualizar vaga.', null, 500);
        }
    }

    public function delete(array $params): void
    {
        $user = AuthMiddleware::requireRole('empresa');
        $id = (int) ($params['id'] ?? 0);

        $job = $this->jobModel->findById($id);
        if (!$job || (int)$job['empresa_id'] !== (int)$user['empresa_id']) {
            Response::json(false, 'Vaga não encontrada ou acesso negado.', null, 403);
        }

        // Deletar candidaturas (applications) vinculadas em cascata manualmente como solicitado
        require_once __DIR__ . '/../models/Application.php';
        $appModel = new Application();
        $appModel->deleteByJob($id);

        if ($this->jobModel->delete($id)) {
            Response::json(true, 'Vaga e candidaturas deletadas com sucesso.', null, 200);
        } else {
            Response::json(false, 'Erro ao deletar vaga.', null, 500);
        }
    }

    public function conclude(array $params): void
    {
        $user = AuthMiddleware::requireRole('empresa');
        $id = (int) ($params['id'] ?? 0);

        $job = $this->jobModel->findById($id);
        if (!$job || (int)$job['empresa_id'] !== (int)$user['empresa_id']) {
            Response::json(false, 'Vaga não encontrada ou acesso negado.', null, 403);
        }

        if ($this->jobModel->conclude($id)) {
            Response::json(true, 'Vaga concluída com sucesso. Permanecerá visível por 3 dias.', null, 200);
        } else {
            Response::json(false, 'Erro ao concluir vaga.', null, 500);
        }
    }

    public function toggleStatus(array $params): void
    {
        $user = AuthMiddleware::requireRole('empresa');
        $id = (int) ($params['id'] ?? 0);
        
        if ($id <= 0) {
            Response::json(false, 'ID inválido.', null, 422);
        }

        $job = $this->jobModel->findById($id);
        if (!$job || (int)$job['empresa_id'] !== (int)$user['empresa_id']) {
            Response::json(false, 'Vaga não encontrada ou acesso negado.', null, 403);
        }

        $newStatus = $job['status'] === 'ATIVA' ? 'PAUSADA' : 'ATIVA';
        if ($this->jobModel->toggleStatus($id, $newStatus)) {
            Response::json(true, 'Status atualizado com sucesso.', ['status' => $newStatus], 200);
        } else {
            Response::json(false, 'Erro ao atualizar status.', null, 500);
        }
    }
}
