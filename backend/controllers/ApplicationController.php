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
require_once __DIR__ . '/../core/CloudinaryUploader.php';

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

        // Lógica de Upload de Currículo via Cloudinary
        $curriculoUrl      = null;
        $curriculoPublicId = null;

        if (isset($_FILES['curriculo']) && $_FILES['curriculo']['error'] === UPLOAD_ERR_OK) {
            try {
                $upload            = CloudinaryUploader::uploadPDF($_FILES['curriculo']);
                $curriculoUrl      = $upload['url'];
                $curriculoPublicId = $upload['public_id'];
            } catch (Exception $e) {
                Response::json(false, $e->getMessage(), null, 422);
                return;
            }
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("CALL sp_candidatar_vaga(:user_id, :vaga_id, :mensagem, :curriculo, @resultado)");
        $stmt->execute([
            ':user_id'   => $user['id'],
            ':vaga_id'   => $jobId,
            ':mensagem'  => $mensagem,
            ':curriculo' => $curriculoUrl // Passamos a URL aqui para a SP se ela suportar, ou NULL
        ]);
        $stmt->closeCursor();
        $codigo = (int) $db->query("SELECT @resultado AS codigo")->fetchColumn();

        if ($codigo === 1) {
            Response::json(false, 'Esta vaga não está mais ativa.', null, 422);
        } elseif ($codigo === 2) {
            Response::json(false, 'Você já se candidatou a esta vaga.', null, 409);
        } elseif ($codigo === 99) {
            Response::json(false, 'Erro interno no banco de dados.', null, 500);
        }

        $applicationId = (int) $db->lastInsertId();

        // Persistir URL e Public ID se houver upload
        if ($curriculoUrl && !empty($applicationId)) {
            $stmtUpdate = $db->prepare(
                'UPDATE applications
                 SET curriculo_url = :url, curriculo_public_id = :pid
                 WHERE id = :id'
            );
            $stmtUpdate->execute([
                ':url' => $curriculoUrl,
                ':pid' => $curriculoPublicId,
                ':id'  => $applicationId,
            ]);
        }

        // Etapa 2: Envio de e-mail postergado
        $companyModel = $this->companyModel;
        register_shutdown_function(function() use ($user, $job, $companyModel) {
            try {
                if ($job) {
                    $empresa = $companyModel->findById((int)$job['empresa_id']);
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

        Response::json(true, 'Candidatura enviada com sucesso.', [
            'id' => $applicationId,
            'curriculo_url' => $curriculoUrl
        ], 201);
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

        if ($id <= 0) {
            Response::json(false, 'Dados inválidos para atualização de status.', null, 422);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("CALL sp_atualizar_status_candidatura(:app_id, :empresa_id, :status, @res)");
        $stmt->execute([
            ':app_id'     => $id,
            ':empresa_id' => (int) $user['empresa_id'],
            ':status'     => $status
        ]);
        $stmt->closeCursor();
        $codigo = (int) $db->query("SELECT @res AS res")->fetchColumn();

        if ($codigo === 1) {
            Response::json(false, 'Forbidden', null, 403);
        } elseif ($codigo === 2) {
            Response::json(false, 'Status inválido.', null, 422);
        } elseif ($codigo !== 0) {
            Response::json(false, 'Erro interno no banco de dados.', null, 500);
        }

        // Notificação de e-mail postergada
        $applicationModel = $this->applicationModel;
        register_shutdown_function(function() use ($id, $status, $applicationModel) {
            try {
                $emailData = $applicationModel->findByIdWithEmailData($id);
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

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT a.*, u.nome, u.email, u.telefone,
                    a.curriculo_url, a.curriculo_public_id
             FROM applications a
             JOIN users u ON u.id = a.user_id
             WHERE a.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

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
