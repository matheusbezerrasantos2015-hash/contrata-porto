<?php

namespace App\Services;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Exception;

class ApplicationService
{
    public function __construct(
        protected CloudinaryService $cloudinaryService,
        protected MailService $mailService
    ) {}

    /**
     * Candidata um usuário candidato a uma vaga.
     *
     * @param User $user
     * @param int $jobId
     * @param string|null $mensagem
     * @param string|null $linkedin
     * @param string|null $portfolio
     * @param string|null $telefone
     * @param UploadedFile|null $curriculoFile
     * @return Application
     * @throws Exception
     */
    public function apply(
        User $user,
        int $jobId,
        ?string $mensagem = null,
        ?string $linkedin = null,
        ?string $portfolio = null,
        ?string $telefone = null,
        ?UploadedFile $curriculoFile = null
    ): Application {
        $job = JobListing::find($jobId);

        if (!$job || !$job->ativo || ($job->expires_at && $job->expires_at->isPast())) {
            throw new Exception('Esta vaga não está mais ativa.', 422);
        }

        // Verifica duplicidade
        $exists = Application::where('user_id', $user->id)
                             ->where('job_id', $jobId)
                             ->exists();

        if ($exists) {
            throw new Exception('Você já se candidatou a esta vaga.', 409);
        }

        $curriculoUrl = null;
        $curriculoPublicId = null;

        if ($curriculoFile) {
            $upload = $this->cloudinaryService->uploadPDF($curriculoFile);
            $curriculoUrl = $upload['url'];
            $curriculoPublicId = $upload['public_id'];
        }

        return DB::transaction(function () use ($user, $job, $mensagem, $linkedin, $portfolio, $telefone, $curriculoUrl, $curriculoPublicId) {
            $application = Application::create([
                'job_id'              => $job->id,
                'user_id'             => $user->id,
                'mensagem'            => $mensagem,
                'linkedin'            => $linkedin,
                'portfolio'           => $portfolio,
                'telefone'            => $telefone,
                'curriculo_url'       => $curriculoUrl,
                'curriculo_public_id' => $curriculoPublicId,
                'status'              => 'pendente',
            ]);

            // Dispara e-mail de confirmação ao candidato
            $companyName = $job->company->nome_fantasia ?? 'Empresa Confidencial';
            $this->mailService->sendApplicationConfirmationEmail(
                $user->email,
                $user->nome,
                $job->titulo,
                $companyName
            );

            return $application;
        });
    }

    /**
     * Atualiza o status da candidatura e dispara o e-mail correspondente.
     *
     * @param int $applicationId
     * @param int $companyId
     * @param string $status
     * @return Application
     * @throws Exception
     */
    public function updateStatus(int $applicationId, int $companyId, string $status): Application
    {
        $statusLower = strtolower(trim($status));
        if (!in_array($statusLower, ['pendente', 'em_analise', 'aprovado', 'recusado'])) {
            throw new Exception('Status inválido.', 422);
        }

        $application = Application::with(['jobListing', 'user'])->find($applicationId);

        if (!$application) {
            throw new Exception('Candidatura não encontrada.', 404);
        }

        // Valida se a vaga pertence a esta empresa
        if ((int) $application->jobListing->company_id !== (int) $companyId) {
            throw new Exception('Forbidden', 403);
        }

        $application->update([
            'status' => $statusLower
        ]);

        // Dispara e-mail de notificação ao candidato de forma assíncrona/segura
        $candidato = $application->user;
        $job = $application->jobListing;
        $companyName = $job->company->nome_fantasia ?? 'Empresa Confidencial';

        // Dispara e-mail de atualização
        $this->mailService->sendApplicationStatusEmail(
            $candidato->email,
            $candidato->nome,
            $job->titulo,
            $companyName,
            strtoupper($statusLower)
        );

        return $application;
    }
}
