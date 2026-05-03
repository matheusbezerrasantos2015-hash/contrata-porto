<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Favorite.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';

final class FavoriteController
{
    private Favorite $favoriteModel;
    private Job $jobModel;

    public function __construct()
    {
        $this->favoriteModel = new Favorite();
        $this->jobModel = new Job();
    }

    public function create(): void
    {
        $user = AuthMiddleware::requireRole('candidato');
        $input = Request::json();

        $jobId = (int) ($input['vaga_id'] ?? $input['job_id'] ?? 0);
        if ($jobId <= 0) {
            Response::json(false, 'vaga_id é obrigatório.', null, 422);
        }

        if (!$this->jobModel->findById($jobId)) {
            Response::json(false, 'Not Found', null, 404);
        }

        if ($this->favoriteModel->exists($user['id'], $jobId)) {
            Response::json(false, 'Vaga já está nos favoritos.', null, 409);
        }

        $this->favoriteModel->create($user['id'], $jobId);
        Response::json(true, 'Vaga salva com sucesso.', null, 201);
    }

    public function index(): void
    {
        $user = AuthMiddleware::requireRole('candidato');
        $favorites = $this->favoriteModel->findByUser($user['id']);

        Response::json(true, 'Favoritos listados com sucesso.', $favorites, 200);
    }

    public function destroy(array $params): void
    {
        $user = AuthMiddleware::requireRole('candidato');
        $favoriteId = (int) ($params['id'] ?? 0);

        if ($favoriteId <= 0) {
            Response::json(false, 'ID inválido.', null, 422);
        }

        $deleted = $this->favoriteModel->deleteByJobAndUser($favoriteId, $user['id']);

        if (!$deleted) {
            Response::json(false, 'Not Found', null, 404);
        }

        Response::json(true, 'Favorito removido com sucesso.', null, 200);
    }
}
