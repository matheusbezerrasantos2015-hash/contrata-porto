<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Job.php';

final class JobService
{
    public function __construct(private readonly Job $jobModel)
    {
    }

    public function createForCompany(array $input, int $companyId): int
    {
        return $this->jobModel->create([
            ...$input,
            'empresa_id' => $companyId,
        ]);
    }
}
