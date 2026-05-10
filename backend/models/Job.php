<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Job
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO vagas (
                empresa_id, titulo, cargo, descricao, requisitos, diferenciais, 
                beneficios, salario_min, salario_max, tipo_contrato, tipo_vaga, 
                experiencia, carga_horaria, area, nivel, cidade, estado, status
            ) VALUES (
                :empresa_id, :titulo, :cargo, :descricao, :requisitos, :diferenciais, 
                :beneficios, :salario_min, :salario_max, :tipo_contrato, :tipo_vaga, 
                :experiencia, :carga_horaria, :area, :nivel, :cidade, :estado, :status
            )'
        );

        $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'titulo' => $data['titulo'],
            'cargo' => $data['cargo'],
            'descricao' => $data['descricao'],
            'requisitos' => $data['requisitos'] ?? null,
            'diferenciais' => $data['diferenciais'] ?? null,
            'beneficios' => $data['beneficios'] ?? null,
            'salario_min' => $data['salario_min'] ?? null,
            'salario_max' => $data['salario_max'] ?? null,
            'tipo_contrato' => $data['tipo_contrato'],
            'tipo_vaga' => $data['tipo_vaga'] ?? 'PRESENCIAL',
            'experiencia' => $data['experiencia'] ?? 'SEM_EXPERIENCIA',
            'carga_horaria' => $data['carga_horaria'] ?? null,
            'area' => $data['area'] ?? null,
            'nivel' => $data['nivel'] ?? null,
            'cidade' => $data['cidade'] ?? 'Porto Ferreira',
            'estado' => $data['estado'] ?? 'SP',
            'status' => $data['status'] ?? 'ATIVA',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findAll(int $limite, int $offset, ?string $busca = null): array
    {
        $stmt = $this->db->prepare("CALL sp_listar_vagas_ativas(:limite, :offset, :busca)");
        $stmt->execute([
            ':limite' => $limite,
            ':offset' => $offset,
            ':busca'  => !empty($busca) ? $busca : null
        ]);
        $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $vagas;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, e.nome_fantasia AS empresa_nome
             FROM vagas v
             INNER JOIN empresas e ON e.id = v.empresa_id
             WHERE v.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $job = $stmt->fetch();
        return $job ?: null;
    }

    public function findByFilters(array $filters, int $page = 1, int $limit = 10): array
    {
        $conditions = ["(v.status IN ('ATIVA', 'PAUSADA') OR (v.status = 'CONCLUIDA' AND v.data_expiracao > NOW()))"];
        $params = [];

        if (!empty($filters['area'])) {
            $conditions[] = 'LOWER(v.area) = LOWER(:area)';
            $params['area'] = $filters['area'];
        }

        if (!empty($filters['nivel'])) {
            $conditions[] = 'v.nivel = :nivel';
            $params['nivel'] = $filters['nivel'];
        }

        if (!empty($filters['tipo'])) {
            $conditions[] = 'v.tipo_contrato = :tipo';
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['tipo_contrato'])) {
            $conditions[] = 'v.tipo_contrato = :tipo_contrato';
            $params['tipo_contrato'] = $filters['tipo_contrato'];
        }

        if (!empty($filters['experiencia'])) {
            $conditions[] = 'v.experiencia = :experiencia';
            $params['experiencia'] = $filters['experiencia'];
        }

        if (!empty($filters['salario_min'])) {
            $conditions[] = 'v.salario_min >= :salario_min';
            $params['salario_min'] = (float)$filters['salario_min'];
        }

        if (!empty($filters['cidade'])) {
            $conditions[] = 'v.cidade = :cidade';
            $params['cidade'] = $filters['cidade'];
        }

        if (!empty($filters['q'])) {
            $conditions[] = '(LOWER(v.titulo) LIKE LOWER(:q1) OR LOWER(v.descricao) LIKE LOWER(:q2))';
            $params['q1'] = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['empresa_id'])) {
            $conditions[] = 'v.empresa_id = :empresa_id';
            $params['empresa_id'] = (int) $filters['empresa_id'];
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare(
            "SELECT v.*, e.nome_fantasia AS empresa_nome,
                     (SELECT COUNT(*) FROM applications WHERE vaga_id = v.id) AS total_candidatos
             FROM vagas v
             INNER JOIN empresas e ON e.id = v.empresa_id
             {$where}
             ORDER BY v.publicada_em DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }    public function countByFilters(array $filters): int
    {
        $conditions = ["(v.status IN ('ATIVA', 'PAUSADA') OR (v.status = 'CONCLUIDA' AND v.data_expiracao > NOW()))"];
        $params = [];

        if (!empty($filters['area'])) {
            $conditions[] = 'LOWER(v.area) = LOWER(:area)';
            $params['area'] = $filters['area'];
        }

        if (!empty($filters['nivel'])) {
            $conditions[] = 'v.nivel = :nivel';
            $params['nivel'] = $filters['nivel'];
        }

        if (!empty($filters['tipo'])) {
            $conditions[] = 'v.tipo_contrato = :tipo';
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['tipo_contrato'])) {
            $conditions[] = 'v.tipo_contrato = :tipo_contrato';
            $params['tipo_contrato'] = $filters['tipo_contrato'];
        }

        if (!empty($filters['experiencia'])) {
            $conditions[] = 'v.experiencia = :experiencia';
            $params['experiencia'] = $filters['experiencia'];
        }

        if (!empty($filters['salario_min'])) {
            $conditions[] = 'v.salario_min >= :salario_min';
            $params['salario_min'] = (float)$filters['salario_min'];
        }

        if (!empty($filters['cidade'])) {
            $conditions[] = 'v.cidade = :cidade';
            $params['cidade'] = $filters['cidade'];
        }

        if (!empty($filters['q'])) {
            $conditions[] = '(LOWER(v.titulo) LIKE LOWER(:q1) OR LOWER(v.descricao) LIKE LOWER(:q2))';
            $params['q1'] = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['empresa_id'])) {
            $conditions[] = 'v.empresa_id = :empresa_id';
            $params['empresa_id'] = (int) $filters['empresa_id'];
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM vagas v {$where}");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }



    public function belongsToCompany(int $jobId, int $companyId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM vagas WHERE id = :id AND empresa_id = :empresa_id LIMIT 1');
        $stmt->execute(['id' => $jobId, 'empresa_id' => $companyId]);

        return (bool) $stmt->fetch();
    }

    public function findByCompany(int $companyId, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare(
            "SELECT * FROM vagas 
             WHERE empresa_id = :empresa_id AND status != 'CONCLUIDA'
             ORDER BY publicada_em DESC 
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':empresa_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE vagas SET
                titulo = :titulo,
                cargo = :cargo,
                descricao = :descricao,
                requisitos = :requisitos,
                diferenciais = :diferenciais,
                beneficios = :beneficios,
                salario_min = :salario_min,
                salario_max = :salario_max,
                tipo_contrato = :tipo_contrato,
                tipo_vaga = :tipo_vaga,
                experiencia = :experiencia,
                carga_horaria = :carga_horaria,
                area = :area,
                nivel = :nivel,
                cidade = :cidade
             WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'titulo' => $data['titulo'],
            'cargo' => $data['cargo'],
            'descricao' => $data['descricao'],
            'requisitos' => $data['requisitos'] ?? null,
            'diferenciais' => $data['diferenciais'] ?? null,
            'beneficios' => $data['beneficios'] ?? null,
            'salario_min' => $data['salario_min'] ?? null,
            'salario_max' => $data['salario_max'] ?? null,
            'tipo_contrato' => $data['tipo_contrato'],
            'tipo_vaga' => $data['tipo_vaga'] ?? 'PRESENCIAL',
            'experiencia' => $data['experiencia'] ?? 'SEM_EXPERIENCIA',
            'carga_horaria' => $data['carga_horaria'] ?? null,
            'area' => $data['area'] ?? null,
            'nivel' => $data['nivel'] ?? null,
            'cidade' => $data['cidade'] ?? 'Porto Ferreira'
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM vagas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function conclude(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE vagas SET 
                status = 'CONCLUIDA', 
                data_expiracao = DATE_ADD(NOW(), INTERVAL 3 DAY) 
             WHERE id = :id"
        );
        return $stmt->execute(['id' => $id]);
    }

    public function toggleStatus(int $id, string $newStatus): bool
    {
        $stmt = $this->db->prepare('UPDATE vagas SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $newStatus, 'id' => $id]);
    }
}
