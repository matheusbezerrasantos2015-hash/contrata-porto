<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Application.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../services/ApplicationService.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Company.php';

final class ApplicationController
{
    private Application $applicationModel;
    private Job $jobModel;
    private User $userModel;
    private Company $companyModel;
    private ApplicationService $applicationService;

    public function __construct()
    {
        $this->applicationModel = new Application();
        $this->jobModel = new Job();
        $this->userModel = new User();
        $this->companyModel = new Company();
        $this->applicationService = new ApplicationService($this->applicationModel);
    }

    public function apply(): void
    {
        $user = AuthMiddleware::requireRole('candidato');
        
        // Detecta se é multipart/form-data ou JSON
        $isMultipart = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');
        
        if ($isMultipart) {
            $jobId = (int) Request::post('vaga_id', 0);
            $mensagem = Request::post('mensagem');
            $linkedin = Request::post('linkedin');
            $portfolio = Request::post('portfolio');
            $telefone = Request::post('telefone');
        } else {
            $input = Request::json();
            $jobId = (int) ($input['vaga_id'] ?? 0);
            $mensagem = $input['mensagem'] ?? null;
            $linkedin = $input['linkedin'] ?? null;
            $portfolio = $input['portfolio'] ?? null;
            $telefone = $input['telefone'] ?? null;
        }

        if ($jobId <= 0) {
            Response::json(false, 'vaga_id é obrigatório.', null, 422);
        }

        if (!$this->applicationService->canApplyNow($user['id'])) {
            Response::json(false, 'Aguarde alguns segundos antes de nova candidatura.', null, 429);
        }

        $job = $this->jobModel->findById($jobId);
        if (!$job) {
            Response::json(false, 'Not Found', null, 404);
        }

        if ($this->applicationModel->existsForUserAndJob($user['id'], $jobId)) {
            Response::json(false, 'Você já se candidatou para esta vaga.', null, 409);
        }

        // Lógica de Upload de Currículo
        $curriculoPath = null;
        $file = Request::file('curriculo');

        if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Response::json(false, 'Erro no upload do arquivo.', null, 422);
            }

            $allowedTypes = ['application/pdf'];
            if (!in_array($file['type'], $allowedTypes, true)) {
                Response::json(false, 'Apenas arquivos PDF são permitidos.', null, 422);
            }

            if ($file['size'] > 2 * 1024 * 1024) { // 2MB
                Response::json(false, 'Arquivo muito grande. Máximo 2MB.', null, 422);
            }

            $storageDir = __DIR__ . '/../uploads/curriculos';
            // Pasta já deve existir mas criamos por segurança
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0777, true);
            }

            $filename = sprintf('%d_%d.pdf', $user['id'], time());
            $destination = $storageDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $curriculoPath = 'uploads/curriculos/' . $filename;
            } else {
                Response::json(false, 'Erro ao salvar o arquivo do currículo.', null, 500);
            }
        }

        $applicationId = $this->applicationModel->create([
            'vaga_id' => $jobId,
            'user_id' => $user['id'],
            'mensagem' => $mensagem,
            'linkedin' => $linkedin,
            'portfolio' => $portfolio,
            'telefone' => $telefone,
            'curriculo_path' => $curriculoPath,
        ]);

        // Etapa 2: Envio de e-mail postergado
        register_shutdown_function(function() use ($user, $job) {
            try {
                if ($job) {
                    $empresa = $this->companyModel->findById((int)$job['empresa_id']);
                    ob_start();
                    $nomeUsuario = $user['nome'];
                    $tituloVaga = $job['titulo'];
                    $nomeEmpresa = $empresa ? $empresa['nome_fantasia'] : 'Empresa Confidencial';
                    include __DIR__ . '/../templates/emails/candidatura_confirmada.php';
                    $html = ob_get_clean();
                    
                    Mailer::send(
                        $user['email'] ?? '',
                        $user['nome'] ?? '',
                        "Candidatura Confirmada - {$job['titulo']}",
                        $html
                    );
                }
            } catch (\Exception $e) {
                error_log("[ASYNC_MAIL_ERR] Erro na candidatura: " . $e->getMessage());
            }
        });

        Response::json(true, 'Candidatura enviada com sucesso.', ['id' => $applicationId], 201);
    }

    public function myApplications(): void
    {
        $user = AuthMiddleware::requireRole('candidato');
        $page = max(1, (int) Request::query('page', 1));
        $limit = min(50, max(1, (int) Request::query('limit', 10)));

        $applications = $this->applicationModel->findByUser($user['id'], $page, $limit);
        Response::json(true, 'Minhas candidaturas listadas com sucesso.', [
            'items' => $applications,
            'meta' => ['page' => $page, 'limit' => $limit]
        ], 200);
    }

    public function jobApplications(array $params): void
    {
        $user = AuthMiddleware::requireRole('empresa');

        $jobId = (int) ($params['id'] ?? 0);
        $page = max(1, (int) Request::query('page', 1));
        $limit = min(50, max(1, (int) Request::query('limit', 10)));

        $companyId = (int) ($user['empresa_id'] ?? 0);
        if ($jobId <= 0 || $companyId <= 0) {
            Response::json(false, 'Parâmetros inválidos.', null, 422);
        }

        if (!$this->jobModel->belongsToCompany($jobId, $companyId)) {
            Response::json(false, 'Forbidden', null, 403);
        }

        $applications = $this->applicationModel->findByJob($jobId, $page, $limit);
        Response::json(true, 'Candidaturas da vaga listadas com sucesso.', [
            'items' => $applications,
            'meta' => ['page' => $page, 'limit' => $limit]
        ], 200);
    }

    public function updateStatus(array $params): void
    {
        $user = AuthMiddleware::requireRole('empresa');

        $id = (int) ($params['id'] ?? 0);
        $input = Request::json();
        $status = strtoupper((string) ($input['status'] ?? ''));

        if ($id <= 0 || !in_array($status, ['EM_ANALISE', 'APROVADO', 'RECUSADO'], true)) {
            Response::json(false, 'Dados inválidos para atualização de status.', null, 422);
        }

        $application = $this->applicationModel->findById($id);
        $companyId = (int) ($user['empresa_id'] ?? 0);
        if (!$application || !$this->jobModel->belongsToCompany((int) $application['vaga_id'], $companyId)) {
            Response::json(false, 'Forbidden', null, 403);
        }

        $updated = $this->applicationModel->updateStatus($id, $status);
        if (!$updated) {
            Response::json(false, 'Not Found', null, 404);
        }

        // Notificação de e-mail postergada
        register_shutdown_function(function() use ($id, $status) {
            try {
                $emailData = $this->applicationModel->findByIdWithEmailData($id);
                $templates = [
                    'APROVADO'   => 'status_aprovado',
                    'RECUSADO'   => 'status_recusado',
                    'EM_ANALISE' => 'status_em_analise',
                ];

                $template = $templates[$status] ?? null;
                $candidatoEmail = $emailData['candidato_email'] ?? '';

                if ($template && !empty($candidatoEmail)) {
                    ob_start();
                    $nome = $emailData['candidato_nome']  ?? '';
                    $vaga = $emailData['vaga_titulo']     ?? 'Vaga';
                    $nomeEmpresa = $emailData['empresa_nome']    ?? 'Empresa Confidencial';
                    include __DIR__ . "/../templates/emails/{$template}.php";
                    $html = ob_get_clean();

                    Mailer::send($candidatoEmail, $nome, "Atualização da sua candidatura — {$vaga}", $html);
                }
            } catch (\Exception $e) {
                error_log("[ASYNC_MAIL_ERR] Erro no updateStatus: " . $e->getMessage());
            }
        });

        Response::json(true, 'Status atualizado com sucesso.', null, 200);
    }

    public function show(array $params): void
    {
        $user = AuthMiddleware::requireRole('empresa');
        $id = (int) ($params['id'] ?? 0);

        if ($id <= 0) {
            Response::json(false, 'ID inválido.', null, 422);
        }

        $application = $this->applicationModel->findByIdEnriched($id);
        if (!$application) {
            Response::json(false, 'Candidatura não encontrada.', null, 404);
        }

        $companyId = (int) ($user['empresa_id'] ?? 0);
        if (!$this->jobModel->belongsToCompany((int) $application['vaga_id'], $companyId)) {
            Response::json(false, 'Forbidden: Esta vaga não pertence à sua empresa.', null, 403);
        }

        Response::json(true, 'Candidatura detalhada carregada com sucesso.', $application, 200);
    }

    public function downloadCurriculo(array $params): void
    {
        $user = AuthMiddleware::requireRole('empresa');
        $id = (int) ($params['id'] ?? 0);

        $application = $this->applicationModel->findById($id);
        if (!$application) {
            Response::json(false, 'Candidatura não encontrada.', null, 404);
        }

        $companyId = (int) ($user['empresa_id'] ?? 0);
        if (!$this->jobModel->belongsToCompany((int) $application['vaga_id'], $companyId)) {
            Response::json(false, 'Acesso negado para este currículo.', null, 403);
        }

        $enriched = $this->applicationModel->findByIdEnriched($id);
        $filePath = $enriched['curriculo_path'] ?? null;

        if (!$filePath) {
            Response::json(false, 'Esta candidatura não possui currículo anexado.', null, 404);
        }

        $fullPath = __DIR__ . '/../' . $filePath;

        if (!file_exists($fullPath)) {
            Response::json(false, 'Arquivo do currículo não encontrado no servidor.', null, 404);
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="curriculo_' . $id . '.pdf"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
