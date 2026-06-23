<?php

namespace App\Services;

use App\Mail\SimpleMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Envia e-mail de verificação com código de 6 dígitos.
     */
    public function sendVerificationEmail(string $email, string $nome, string $code): void
    {
        try {
            Mail::to($email)->send(new SimpleMail(
                'Confirme seu e-mail — ContrataPorto',
                'emails.verificacao_email',
                ['nome' => $nome, 'code' => $code]
            ));
        } catch (\Throwable $e) {
            Log::error("[MAIL_ERROR] Falha ao enviar e-mail de verificação para {$email}: " . $e->getMessage());
        }
    }

    /**
     * Envia e-mail de redefinição de senha.
     */
    public function sendPasswordRecoveryEmail(string $email, string $nome, string $resetLink): void
    {
        try {
            Mail::to($email)->send(new SimpleMail(
                'Redefinir sua senha no ContrataPorto',
                'emails.recuperacao_senha',
                ['nome' => $nome, 'resetLink' => $resetLink]
            ));
        } catch (\Throwable $e) {
            Log::error("[MAIL_ERROR] Falha ao enviar e-mail de recuperação de senha para {$email}: " . $e->getMessage());
        }
    }

    /**
     * Envia e-mail de confirmação de candidatura ao candidato.
     */
    public function sendApplicationConfirmationEmail(string $email, string $nomeUsuario, string $tituloVaga, string $nomeEmpresa): void
    {
        try {
            Mail::to($email)->send(new SimpleMail(
                "Candidatura Confirmada - {$tituloVaga}",
                'emails.candidatura_confirmada',
                [
                    'nomeUsuario' => $nomeUsuario,
                    'tituloVaga'  => $tituloVaga,
                    'nomeEmpresa'  => $nomeEmpresa,
                ]
            ));
        } catch (\Throwable $e) {
            Log::error("[MAIL_ERROR] Falha ao enviar e-mail de confirmação de candidatura para {$email}: " . $e->getMessage());
        }
    }

    /**
     * Envia e-mail de mudança de status da candidatura ao candidato.
     */
    public function sendApplicationStatusEmail(string $email, string $nome, string $vaga, string $nomeEmpresa, string $status): void
    {
        $templates = [
            'APROVADO'   => 'emails.status_aprovado',
            'RECUSADO'   => 'emails.status_recusado',
            'EM_ANALISE' => 'emails.status_em_analise',
        ];

        $template = $templates[strtoupper($status)] ?? null;

        if (!$template) {
            return;
        }

        $subjects = [
            'APROVADO'   => "Sua candidatura foi aprovada! — {$vaga}",
            'RECUSADO'   => "Atualização sobre sua candidatura — {$vaga}",
            'EM_ANALISE' => "Sua candidatura está em análise! — {$vaga}",
        ];

        $subject = $subjects[strtoupper($status)] ?? "Atualização da sua candidatura — {$vaga}";

        try {
            Mail::to($email)->send(new SimpleMail(
                $subject,
                $template,
                [
                    'nome'        => $nome,
                    'vaga'        => $vaga,
                    'nomeEmpresa' => $nomeEmpresa,
                    'baseUrl'     => config('app.frontend_url') ?? env('FRONTEND_URL') ?? 'http://localhost:5173',
                ]
            ));
        } catch (\Throwable $e) {
            Log::error("[MAIL_ERROR] Falha ao enviar e-mail de alteração de status ({$status}) para {$email}: " . $e->getMessage());
        }
    }
}
