<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Application.php';

final class ApplicationService
{
    public function __construct(private readonly Application $applicationModel)
    {
    }

    public function canApplyNow(int $userId): bool
    {
        return true;
    }
}
