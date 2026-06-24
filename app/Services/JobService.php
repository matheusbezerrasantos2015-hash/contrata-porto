<?php

namespace App\Services;

use App\Models\JobListing;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class JobService
{
    /**
     * Lista vagas ativas paginadas (padrão 6 por página, compatível com o frontend).
     *
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listActive(int $page = 1, int $perPage = 6): LengthAwarePaginator
    {
        return JobListing::with('company')
            ->ativas()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Filtra vagas com base nos parâmetros informados.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function filter(array $filters, int $page = 1, int $perPage = 6): LengthAwarePaginator
    {
        $query = JobListing::with('company')->ativas();

        if (!empty($filters['area'])) {
            $query->whereRaw('LOWER(area) = LOWER(?)', [$filters['area']]);
        }

        if (!empty($filters['nivel'])) {
            $query->where('nivel', $filters['nivel']);
        }

        // Suporta tanto 'tipo' quanto 'tipo_contrato'
        $tipo = $filters['tipo'] ?? $filters['tipo_contrato'] ?? null;
        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }

        if (!empty($filters['experiencia'])) {
            $query->where('experiencia', $filters['experiencia']);
        }

        if (!empty($filters['salario_min'])) {
            $query->where('salario_min', '>=', (float) $filters['salario_min']);
        }

        if (!empty($filters['cidade'])) {
            $query->where('cidade', $filters['cidade']);
        }

        if (!empty($filters['q'])) {
            $searchTerm = '%' . $filters['q'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(titulo) LIKE LOWER(?)', [$searchTerm])
                  ->orWhereRaw('LOWER(descricao) LIKE LOWER(?)', [$searchTerm]);
            });
        }

        if (!empty($filters['empresa_id'])) {
            $query->where(JobListing::companyForeignKey(), (int) $filters['empresa_id']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Cria uma nova vaga para uma empresa.
     *
     * @param array $data
     * @param int $companyId
     * @return JobListing
     */
    public function createForCompany(array $data, int $companyId): JobListing
    {
        return JobListing::create([
            JobListing::companyForeignKey() => $companyId,
            'titulo'        => $data['titulo'],
            'cargo'         => $data['cargo'] ?? null,
            'area'          => $data['area'] ?? null,
            'descricao'     => $data['descricao'],
            'requisitos'    => $data['requisitos'] ?? null,
            'beneficios'    => $data['beneficios'] ?? null,
            'diferenciais'  => $data['diferenciais'] ?? null,
            'tipo'          => $data['tipo'] ?? $data['tipo_contrato'] ?? 'CLT',
            'modalidade'    => $data['modalidade'] ?? $data['tipo_vaga'] ?? 'Presencial',
            'nivel'         => $data['nivel'] ?? 'Pleno',
            'experiencia'   => $data['experiencia'] ?? 'Sem experiência',
            'carga_horaria' => $data['carga_horaria'] ?? null,
            'salario_min'   => $data['salario_min'] ?? null,
            'salario_max'   => $data['salario_max'] ?? null,
            'cidade'        => $data['cidade'] ?? 'Porto Ferreira',
            'estado'        => $data['estado'] ?? 'SP',
            'ativo'         => true,
        ]);
    }

    /**
     * Atualiza uma vaga existente.
     *
     * @param JobListing $job
     * @param array $data
     * @return bool
     */
    public function update(JobListing $job, array $data): bool
    {
        return $job->update([
            'titulo'        => $data['titulo'],
            'cargo'         => $data['cargo'] ?? null,
            'area'          => $data['area'] ?? null,
            'descricao'     => $data['descricao'],
            'requisitos'    => $data['requisitos'] ?? null,
            'beneficios'    => $data['beneficios'] ?? null,
            'diferenciais'  => $data['diferenciais'] ?? null,
            'tipo'          => $data['tipo'] ?? $data['tipo_contrato'] ?? $job->tipo,
            'modalidade'    => $data['modalidade'] ?? $data['tipo_vaga'] ?? $job->modalidade,
            'nivel'         => $data['nivel'] ?? $job->nivel,
            'experiencia'   => $data['experiencia'] ?? $job->experiencia,
            'carga_horaria' => $data['carga_horaria'] ?? null,
            'salario_min'   => $data['salario_min'] ?? null,
            'salario_max'   => $data['salario_max'] ?? null,
            'cidade'        => $data['cidade'] ?? $job->cidade,
            'estado'        => $data['estado'] ?? $job->estado,
        ]);
    }
}
